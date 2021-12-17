<?php

namespace App\Mail;

use App\Models\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeactivatePublisher extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Publisher $publisher;
    protected string $origin;

    /**
     * Create a new message instance.
     *
     * @param Publisher $publisher
     * @param string $origin
     */
    public function __construct(Publisher $publisher, string $origin = 'admin')
    {
        $this->publisher = $publisher;
        $this->origin = $origin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->origin == 'admin' ? 'Empresa' : $this->publisher->name;

        return $this->subject($subject . ' - Confirmação de desativação de acesso.')
            ->markdown('mails.publisher.deactivate', ['publisher' => $this->publisher, 'origin' => $this->origin])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
