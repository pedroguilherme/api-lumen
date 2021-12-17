<?php

namespace App\Mail;

use App\Models\Publisher;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected User $user;
    protected string $token;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $token
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Empresa - Resetar Senha')
            ->markdown('mails.auth.forgot-password', ['user' => $this->user, 'token' => $this->token])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
