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

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order,
        public OrderStatusEnum $status,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject()
                . " - #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->getView(),
            with: [
                'order' => $this->order,
            ],
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

    private function getSubject(): string
    {
        return match ($this->status) {
            OrderStatusEnum::CONFIRMED =>
                __('messages.emails.subjects.confirmed'),
            OrderStatusEnum::READY =>
                __('messages.emails.subjects.ready'),
            OrderStatusEnum::DELIVERING =>
                __('messages.emails.subjects.delivering'),
            OrderStatusEnum::COMPLETED =>
                __('messages.emails.subjects.completed'),
            OrderStatusEnum::CANCELLED =>
                __('messages.emails.subjects.cancelled'),
            default => __('messages.emails.order_update'),
        };
    }

    private function getView(): string
    {
        return match ($this->status) {
            OrderStatusEnum::CONFIRMED =>
                'emails.order.order-confirmed',
            OrderStatusEnum::READY =>
                'emails.order.order-ready',
            OrderStatusEnum::DELIVERING =>
                'emails.order.order-delivering',
            OrderStatusEnum::COMPLETED =>
                'emails.order.order-completed',
            OrderStatusEnum::CANCELLED =>
                'emails.order.order-cancelled',
            default => throw new \LogicException(
                'Unsupported order email status.',
            ),
        };
    }
}
