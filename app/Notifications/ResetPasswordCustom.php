<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCustom extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('🔐 Réinitialisation de ton mot de passe - Orienta+216')
            ->greeting('Bonjour 👋')
            ->line("Tu as demandé à réinitialiser ton mot de passe sur Orienta+216.")
            ->action('Réinitialiser mon mot de passe', $url)
            ->line("Si tu n'es pas à l'origine de cette demande, ignore ce message.")
            ->salutation('L’équipe Orienta+216 🎓');
    }


    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
