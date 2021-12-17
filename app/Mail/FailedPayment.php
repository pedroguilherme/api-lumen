<?php

namespace App\Mail;

use App\Models\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FailedPayment extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Publisher $publisher;

    /**
     * Create a new message instance.
     *
     * @param Publisher $publisher
     */
    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Empresa - Falha ao processar pagamento.')
            ->markdown('mails.payments.failed', ['publisher' => $this->publisher])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
