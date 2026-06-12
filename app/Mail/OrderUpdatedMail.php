<?php

namespace App\Mail;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $status;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->getSubject($this->status)} - #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: "emails.order.order-" . strtolower($this->status),
            with: [
                'order' => $this->order,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getSubject(string $status): string
    {
        return match ($status) {
            OrderStatusEnum::CONFIRMED->label() => 'Order Confirmed',
            OrderStatusEnum::READY->label() => 'Order Ready',
            OrderStatusEnum::DELIVERING->label() => 'Order Out For delivery',
            OrderStatusEnum::COMPLETED->label() => 'Order Completed',
        };
    }
}
