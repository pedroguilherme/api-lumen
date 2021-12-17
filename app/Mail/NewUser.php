<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUser extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected User $user;
    protected string $password;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Empresa - Seja bem-vindo!')
            ->markdown('mails.auth.new', ['user' => $this->user, 'password' => $this->password])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
