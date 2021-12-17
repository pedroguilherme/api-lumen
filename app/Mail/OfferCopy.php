<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferCopy extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected array $offer;

    /**
     * Create a new message instance.
     *
     * @param array $offer
     */
    public function __construct(array $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Empresa - Recebemos seu contato!')
            ->markdown('mails.offers.copy', ['offer' => $this->offer])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
