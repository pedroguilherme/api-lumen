<?php

namespace App\Mail;

use App\Models\Plan;
use App\Models\Publisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdatePlanPublisher extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Publisher $publisher;
    protected Plan $newPlan;
    protected Plan $oldPlan;

    /**
     * Create a new message instance.
     *
     * @param Publisher $publisher
     * @param Plan $oldPlan
     */
    public function __construct(Publisher $publisher, Plan $newPlan, Plan $oldPlan)
    {
        $this->publisher = $publisher;
        $this->newPlan = $newPlan;
        $this->oldPlan = $oldPlan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->publisher->name . ' - Confirmação de troca de plano.')
            ->markdown('mails.publisher.update_plan', [
                'publisher' => $this->publisher,
                'newPlan' => $this->newPlan,
                'oldPlan' => $this->oldPlan
            ])
            ->from('naoresponda@empresa.com.br', 'Empresa');
    }
}
