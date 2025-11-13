<p>Hi <?php echo e($oi->order->user->name ?? 'Customer'); ?>,</p>

<p>Good news — the product "<?php echo e($oi->item->name ?? ''); ?>" (Qty: <?php echo e($oi->quantity); ?>) from your order #<?php echo e($oi->order->id); ?> is now available and ready for fulfillment.</p>

<?php if(!empty($oi->order->expected_restock_date)): ?>
<p>Expected restock/delivery date: <?php echo e($oi->order->expected_restock_date->format('M d, Y')); ?></p>
<?php endif; ?>

<p>We'll notify you when your item is shipped. If you have any questions, reply to this email.</p>

<p>Thanks for shopping with us,
<br/>WOW Carmen</p><?php /**PATH C:\xampp\htdocs\wowc\resources\views/emails/backorder_ready.blade.php ENDPATH**/ ?>