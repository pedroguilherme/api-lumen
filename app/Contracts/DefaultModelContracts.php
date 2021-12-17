<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

interface DefaultModelContracts extends AuditableInterface
{
    public function apply(array $filters = []);

    public function applyShowWith(array $filters = []);

    public function applyRequest(Request $request);
}
