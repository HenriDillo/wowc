<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure storage symlink exists in local dev for serving images consistently
        if (app()->environment('local') && !is_link(public_path('storage')) && is_dir(storage_path('app/public'))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Throwable $e) {
                // ignore if cannot link (shared hosting), images will fallback via disk->url
            }
        }
        // Merge session cart to user cart on login
        Event::listen(Login::class, function (Login $event): void {
            $userId = $event->user->id;
            $sessionId = Session::getId();

            // Active session cart
            $sessionCart = Cart::where('status', 'active')
                ->where('session_id', $sessionId)
                ->first();

            if (!$sessionCart) {
                return;
            }

            // Get or create user's active cart
            $userCart = Cart::firstOrCreate(
                ['status' => 'active', 'user_id' => $userId],
                ['session_id' => null]
            );

            // Move items
            $items = CartItem::where('cart_id', $sessionCart->id)->get();
            foreach ($items as $ci) {
                $existing = CartItem::where('cart_id', $userCart->id)
                    ->where('item_id', $ci->item_id)
                    ->first();
                if ($existing) {
                    $existing->quantity += $ci->quantity;
                    $existing->subtotal = $existing->quantity * (float) $existing->price;
                    $existing->save();
                    $ci->delete();
                } else {
                    $ci->cart_id = $userCart->id;
                    $ci->save();
                }
            }

            // Close session cart
            $sessionCart->status = 'converted';
            $sessionCart->save();
        });
    }
}
