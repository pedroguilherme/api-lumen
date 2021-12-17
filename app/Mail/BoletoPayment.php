<?php

namespace App\Mail;

use App\Models\Billing;
use App\Models\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BoletoPayment extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Publisher $publisher;
    protected Billing $billing;

    /**
     * Create a new message instance.
     *
     * @param Publisher $publisher
     * @param Billing $billing
     */
    public function __construct(Publisher $publisher, Billing $billing)
    {
        $this->publisher = $publisher;
        $this->billing = $billing;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Empresa - Boleto para pagamento.')
            ->markdown('mails.payments.boleto', ['publisher' => $this->publisher, 'billing' => $this->billing])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
