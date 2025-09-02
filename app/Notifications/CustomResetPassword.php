<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Recuperação de senha - ABS Document Management System')
            ->greeting('Viva!')
            ->line('Recebemos uma solicitação para redefinir sua senha.')
            ->action('Redefinir Senha', url(config('app.url') . route('password.reset', $this->token, false)))
            ->line('Se você não solicitou esta alteração, nenhuma ação adicional é necessária.')
            ->salutation('Atenciosamente, Equipe ABS');
    }
}
