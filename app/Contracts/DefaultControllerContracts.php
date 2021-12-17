<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface DefaultControllerContracts
{
    public function search(Request $request);

    public function show(Request $request, int $id);

    public function store(Request $request);

    public function update(Request $request, int $id);

    public function destroy(int $id);
}
