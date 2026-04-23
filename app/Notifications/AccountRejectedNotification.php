<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private ?string $raison = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Demande d\'inscription Orienta — Décision')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous informons que votre demande d\'inscription sur la plateforme **Orienta+216** n\'a pas pu être acceptée à ce stade.');

        if ($this->raison) {
            $mail->line('**Motif :** ' . $this->raison);
        }

        return $mail
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, veuillez contacter l\'administration directement.')
            ->salutation('L\'équipe Orienta 🎓');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}