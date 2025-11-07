<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BackorderReady extends Mailable
{
    use Queueable, SerializesModels;

    public $orderItem;

    /**
     * Create a new message instance.
     */
    public function __construct($orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your back order is ready to ship')
                    ->view('emails.backorder_ready')
                    ->with(['oi' => $this->orderItem]);
    }
}
