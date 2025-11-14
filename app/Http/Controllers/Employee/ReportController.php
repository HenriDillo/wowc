<?php

namespace App\Http\Controllers\Employee;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Item;
use App\Models\Material;
use App\Models\ItemStockTransaction;
use App\Models\MaterialStockTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Show unified reports page with sales, inventory, and calendar
     */
    public function unified(Request $request)
    {
        // Sales Report Data
        $period = $request->get('period', 'month');
        $customStart = $request->get('custom_start');
        $customEnd = $request->get('custom_end');

        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'custom' => $customStart ? Carbon::parse($customStart) : now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $endDate = match ($period) {
            'day' => now()->endOfDay(),
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'custom' => $customEnd ? Carbon::parse($customEnd) : now()->endOfMonth(),
            default => now()->endOfMonth(),
        };

        $totalSalesQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        $totalSales = $totalSalesQuery->sum('total_amount');
        $totalOrders = $totalSalesQuery->count();

        $orderTypeBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->selectRaw('order_type, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('order_type')
            ->get();

        $paymentMethodBreakdown = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        $productSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('items.id, items.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as total_sales')
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_sales')
            ->get();

        $dailySales = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->selectRaw("DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as amount")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Inventory Report Data
        $materialsInventory = Material::select(
            'id',
            'name',
            'stock',
            DB::raw('COALESCE(reorder_level, 0) as reorder_level')
        )->get();

        $itemsInventory = Item::select(
            'id',
            'name',
            'stock',
            DB::raw('COALESCE(reorder_level, 0) as reorder_level')
        )->get();

        $lowStockMaterials = Material::whereNotNull('reorder_level')
            ->whereColumn('stock', '<=', 'reorder_level')
            ->get();
        
        $lowStockItems = Item::whereNotNull('reorder_level')
            ->whereColumn('stock', '<=', 'reorder_level')
            ->get();

        $recentMovements = DB::table('material_stock_transactions')
            ->join('materials', 'material_stock_transactions.material_id', '=', 'materials.id')
            ->selectRaw("materials.name, material_stock_transactions.type, material_stock_transactions.quantity, material_stock_transactions.remarks, material_stock_transactions.created_at, 'material' as source_type")
            ->union(
                DB::table('item_stock_transactions')
                    ->join('items', 'item_stock_transactions.item_id', '=', 'items.id')
                    ->selectRaw("items.name, item_stock_transactions.type, item_stock_transactions.quantity, item_stock_transactions.remarks, item_stock_transactions.created_at, 'item' as source_type")
            )
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                $item->created_at = \Carbon\Carbon::parse($item->created_at);
                return $item;
            });

        // Calendar Data
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $restockDates = Order::whereBetween('expected_restock_date', [$startOfMonth, $endOfMonth])
            ->where('order_type', 'backorder')
            ->where('expected_restock_date', '!=', null)
            ->selectRaw("DATE(expected_restock_date) as date, COUNT(*) as count")
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $orderDeadlines = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['pending', 'processing'])
            ->selectRaw("DATE(DATE_ADD(created_at, INTERVAL 3 DAY)) as deadline_date, COUNT(*) as count")
            ->groupBy('deadline_date')
            ->get()
            ->keyBy('deadline_date');

        $customOrderDates = DB::table('custom_orders')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $deliveries = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'shipped')
            ->where('delivered_at', null)
            ->selectRaw("DATE(DATE_ADD(created_at, INTERVAL 3 DAY)) as delivery_date, COUNT(*) as count")
            ->groupBy('delivery_date')
            ->get()
            ->keyBy('delivery_date');

        $events = [
            'restock' => $restockDates,
            'deadlines' => $orderDeadlines,
            'customs' => $customOrderDates,
            'deliveries' => $deliveries,
        ];

        return view('employee.reports.unified-report', compact(
            'period',
            'customStart',
            'customEnd',
            'startDate',
            'endDate',
            'totalSales',
            'totalOrders',
            'orderTypeBreakdown',
            'paymentMethodBreakdown',
            'productSales',
            'dailySales',
            'materialsInventory',
            'itemsInventory',
            'lowStockMaterials',
            'lowStockItems',
            'recentMovements',
            'month',
            'year',
            'events'
        ));
    }

    /**
     * Show sales report page (kept for backwards compatibility)
     */
    public function salesReport(Request $request)
    {
        return $this->unified($request);
    }

    /**
     * Show inventory report page (kept for backwards compatibility)
     */
    public function inventoryReport(Request $request)
    {
        return $this->unified($request);
    }

    /**
     * Show calendar page (kept for backwards compatibility)
     */
    public function calendar(Request $request)
    {
        return $this->unified($request);
    }
}
