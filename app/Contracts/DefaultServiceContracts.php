<?php

namespace App\Contracts;

interface DefaultServiceContracts
{
    public function search($data, $jsonResponse = true);

    public function show($data, $jsonResponse = true);

    public function store($data, $jsonResponse = true);

    public function update($data, $jsonResponse = true);

    public function destroy($data, $jsonResponse = true);
}
