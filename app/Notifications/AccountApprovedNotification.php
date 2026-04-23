<?php
// app/Notifications/AccountApprovedNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = match ($notifiable->role) {
            'conseiller' => 'conseiller d\'orientation',
            'etudiant'   => 'étudiant',
            default      => 'utilisateur',
        };

        $loginUrl = url(route('login'));

        return (new MailMessage)
            ->subject('Votre avenir vient d’être débloqué')
            ->greeting('Bonjour ' . $notifiable->name . ' 👋')
            ->line("Bonne nouvelle ! Votre demande d'inscription en tant que **{$roleLabel}** sur la plateforme **Orienta+216** a été **approuvée** par l'administrateur.")
            ->line('Considérez ceci comme votre deuxième réussite aprés le bac 😉.')
            ->action('Se connecter maintenant', $loginUrl)
            ->line('À vous de jouer — votre futur n’attend plus.')
            ->salutation('L\'équipe Orienta+216🎓');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}