<p>Hi {{ $oi->order->user->name ?? 'Customer' }},</p>

<p>Good news — the product "{{ $oi->item->name ?? '' }}" (Qty: {{ $oi->quantity }}) from your order #{{ $oi->order->id }} is now available and ready for fulfillment.</p>

@if(!empty($oi->order->expected_restock_date))
<p>Expected restock/delivery date: {{ $oi->order->expected_restock_date->format('M d, Y') }}</p>
@endif

<p>We'll notify you when your item is shipped. If you have any questions, reply to this email.</p>

<p>Thanks for shopping with us,
<br/>WOW Carmen</p>