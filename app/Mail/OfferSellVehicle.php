<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferSellVehicle extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Offer $offer;

    /**
     * Create a new message instance.
     *
     * @param Offer $offer
     */
    public function __construct(Offer $offer)
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
        return $this->subject('Empresa - Oportunidade de Compra!')
            ->markdown('mails.offers.sellvehicle', ['offer' => $this->offer])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
