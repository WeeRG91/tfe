<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MobileVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

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
        $expirationMinutes = (int) config(
            'auth.verification.expire',
            60
        );

        return (new MailMessage)
            ->subject('Verify your email address')
            ->greeting("Hello $notifiable->name,")
            ->line(
                'Please verify your email address to complete your TFE account.',
            )
            ->action(
                'Verify email address',
                $this->verificationUrl(
                    $notifiable,
                    $expirationMinutes,
                ),
            )
            ->line(
                "This verification link expires in $expirationMinutes minutes.",
            )
            ->line(
                'If you did not create this account, no further action is required.',
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

    /**
     * Build the temporary signed verification URL.
     */
    private function verificationUrl(
        object $notifiable,
        int $expirationMinutes,
    ): string {
        return URL::temporarySignedRoute(
            'api.v1.auth.email.verification.verify',
            now()->addMinutes($expirationMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1(
                    $notifiable->getEmailForVerification(),
                ),
            ],
        );
    }
}
