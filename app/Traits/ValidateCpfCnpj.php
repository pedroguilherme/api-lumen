<?php

namespace App\Traits;

use App\Helpers\HelpersValidate;

trait ValidateCpfCnpj
{
    /**
     * Valida campos de CPF / CNPJ
     *
     * @param $data
     * @return array|bool
     */
    private function validateCpfCnpj($data)
    {
        if ($data['type'] == 'J') {
            $check = HelpersValidate::checkCnpj($data['cpf_cnpj']);
        } else {
            $check = HelpersValidate::checkCpf($data['cpf_cnpj']);
        }

        if (!$check) {
            $errors = [
                'type' => 'VALIDATION_ERROR',
                'data' => [
                    'fields' => [],
                    'messages' => [],
                ]
            ];

            array_push($errors['data']['fields'], 'cpf_cnpj');
            array_push($errors['data']['messages'], [($data['type'] == 'J' ? 'CNPJ' : 'CPF') . ' inválido']);

            return $errors;
        }

        return true;
    }
}
