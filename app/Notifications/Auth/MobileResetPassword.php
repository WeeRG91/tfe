<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MobileResetPassword extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expirationMinutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset your password')
            ->greeting("Hello {$notifiable->name},")
            ->line(
                'You are receiving this email because we received a password reset request for your TFE account.',
            )
            ->action(
                'Reset password',
                $this->resetUrl($notifiable),
            )
            ->line(
                "This password reset link expires in {$expirationMinutes} minutes.",
            )
            ->line(
                'If you did not request a password reset, no further action is required.',
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    protected function resetUrl(object $notifiable): string
    {
        $baseUrl = (string) config(
            'services.mobile.password_reset_url',
            'tfemobile://auth/reset-password',
        );

        $query = http_build_query(
            [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

        return "{$baseUrl}?{$query}";
    }
}
