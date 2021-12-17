<?php

return [
    'internal' => [
        'type' => 'INTERNAL_SERVER_ERROR',
        'data' => 'Houve um erro em nossa aplicação, por favor entre em contato com nosso suporte.'
    ],
    'duplicate' => [
        'type' => 'DUPLICATE_RESOURCE',
        'data' => 'O recurso que está tentando cadastrar já existe no banco de dados.'
    ],
    'duplicate_user_email' => [
        'type' => 'DUPLICATE_EMAIL',
        'data' => 'O e-mail informado já está sendo utilizado. Caso necessário utilize a função "Recuperar Senha".'
    ],
    'duplicate_vehicle_plate' => [
        'type' => 'DUPLICATE_PLATE',
        'data' => 'O placa informada já está sendo utilizada por um veículo ativo.'
    ],
    'duplicate_credit_cart' => [
        'type' => 'DUPLICATE_CREDIT_CARD',
        'data' => 'O Cartão informado já encontra-se cadastrado.'
    ],
    'not_found' => [
        'type' => 'NOT_FOUND_RESOURCE',
        'data' => 'O recurso informado não existe no banco de dados.'
    ],
    'unauthorized' => [
        'type' => 'UNAUTHORIZED',
        'data' => 'Acesso negado.'
    ],
    'token_invalid' => [
        'type' => 'TOKEN_INVALID',
        'data' => 'O token informado não é válido ou está expirado.'
    ],
    'invalid' => [
        'type' => 'INVALID',
        'data' => 'Os dados informados não são válidos.'
    ],
    'plan_used' => [
        'type' => 'PLAN_USED',
        'data' => 'Todos os destaques desta modalidade já foram utilizados, contrate um plano maior.'
    ],
    'payment_default' => [
        'type' => 'PAYMENT_DEFAULT',
        'data' => 'Você não pode excluir a forma de pagamento principal.'
    ],
    'payment_method_not_found' => [
        'type' => 'PAYMENT_METHOD_NOT_FOUND',
        'data' => 'Não foi possível encontrar uma forma de pagamento cadastrada.'
    ],
    'not_allowed' => [
        'type' => 'NOT_ALLOWED',
        'data' => 'Não foi possível realizar esta operação.'
    ],
    'in_use' => [
        'type' => 'RESOURCE_IN_USE',
        'data' => 'Você não pode excluir esse recurso, pois ele está em uso.'
    ],
    'sold_vehicle' => [
        'type' => 'SOLD_VEHICLE',
        'data' => 'O veículo informado já foi vendido.'
    ],
];
