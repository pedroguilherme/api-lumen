<?php

namespace App\Mail;

use App\Models\Billing;
use App\Models\Publisher;
use App\Models\SiteContact;
use App\Services\SiteContactService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdExpired extends Mailable implements ShouldQueue
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
        $cellphone = SiteContact::query()
                ->whereIn('key', ['commercialWhatsapp', 'contactWhatsapp', 'supportWhatsApp', 'cellphone', 'telephone'])
                ->orderBy('id')
                ->first()
                ->value('value') ?? null;

        $vehicle = ($this->billing->vehicle->brand->name ?? '') . ' ' . ($this->billing->vehicle->model->name ?? '') . ' ' . ($this->billing->vehicle->version->name ?? '');
        $title = 'Empresa - Renovação de Anúncio REF ANUNCIO: ' . $this->billing->vehicle->plate . ' - ' . $vehicle;
        return $this->subject($title)
            ->markdown('mails.payments.expired',
                ['publisher' => $this->publisher, 'billing' => $this->billing, 'cellphone' => $cellphone])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
