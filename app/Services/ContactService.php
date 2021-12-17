<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Models\Contact;
use App\Traits\DefaultService;

class ContactService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(Contact $model)
    {
        $this->model = $model;
    }
}
