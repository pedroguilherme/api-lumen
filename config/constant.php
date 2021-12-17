<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paginação
    |--------------------------------------------------------------------------
    |
    | Define o número de itens default do sistema
    |
    */

    "pagination" => 20,

    /*
    |--------------------------------------------------------------------------
    | Veículos
    |--------------------------------------------------------------------------
    |
    | Tipos de veículos possíveis de serem cadastrados no sistema
    | lembrando que cada pode ter campos diferentes no cadstro.
    |
    */

    'vehicle_type' => [ // Tipos de Veículos
        'Carro' => 'C',
        'Moto' => 'M',
        'Caminhão' => 'T',
    ],

    /*
    |--------------------------------------------------------------------------
    | Leads
    |--------------------------------------------------------------------------
    |
    | Tipos de leads possíveis do sistema receber
    |
    */

    'offer_type' => [ // Tipos de Leads
        'offer' => 'O', // Proposta / Oferta
        'seePhone' => 'S', // Ver Telefone do Anuncio
        'sellVehicle' => 'V' // Vender meu veículo
    ],

    /*
    |--------------------------------------------------------------------------
    | Usuários
    |--------------------------------------------------------------------------
    |
    | Tipos de usuários possíveis do sistema ter
    |
    */

    'user_type' => [ // Tipos de usuário
        'admin' => 'A',
        'store' => 'S',
        'person' => 'P',
    ],

    'people_type' => [ // Tipos de pessoas
        'company' => 'J',
        'normal' => 'F',
    ],

    /*
    |--------------------------------------------------------------------------
    | Imagens (Banners, Images, etc)
    |--------------------------------------------------------------------------
    |
    | Configurações gerais das Imagens e banners
    |
    */

    'banner_type' => [ // Tipos de banner
        'desktop' => 'D',
        'mobile' => 'M'
    ],

    // Caminho Padrão do bucket
    'default_path' => 'app',

    // Path das imagens conforme o tipo

    'images_path' => [
        'banner_super' => '/site/banners/super/',
        'banner_category' => '/site/banners/category/',
        'brand_image' => '/site/brands/',
        'logo_publisher' => '/anunciantes/$publisher_id/logos/',
        'vehicle_image' => '/anunciantes/$publisher_id/veiculos/$vehicle_id/images/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contatos
    |--------------------------------------------------------------------------
    |
    | Tipos de contatos possíveis do sistema receber
    |
    */

    'site_contacts' => [ // Tipos de campo de contato para o site
        'commercialWhatsapp',
        'supportWhatsApp',
        'contactWhatsapp',
        'commercialEmail',
        'supportEmail',
        'contactEmail',
        'financingEmail',
        'cellphone',
        'telephone',
        'instagram',
        'facebook',
        'youtube',
        'cnpj',
        'contact',
        'address',
    ],

    'publishers_contacts' => [
        'contactWhatsapp',
        'contactEmail',
        'offerEmail',
        'telephone',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagamentos
    |--------------------------------------------------------------------------
    |
    | Tipos de pagamentos possíveis do sistema receber
    |
    */

    'payment_method' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Planos
    |--------------------------------------------------------------------------
    |
    | Tipos de planos possíveis do sistema receber
    |
    */

    'plans' => [
        'basic' => 'basic',
        'intermediary' => 'intermediary',
        'advanced' => 'advanced',
    ],

    'pf_plans' => [
        'basic' => [
            'name' => 'Anúncio Básico',
            'tag' => 'basic',
            'value' => 19.90,
            'expiration' => 45,
            'spotlight' => 'S',
        ],
        'intermediary' => [
            'name' => 'Anúncio Intermediário',
            'tag' => 'intermediary',
            'value' => 29.90,
            'expiration' => 45,
            'spotlight' => 'G',
        ],
        'advanced' => [
            'name' => 'Anúncio Avançado',
            'tag' => 'advanced',
            'value' => 59.90,
            'expiration' => null,
            'spotlight' => 'D',
        ],
    ],

    'spotlight_abbreviation' => [
        'N' => 'normal',
        'S' => 'silver',
        'G' => 'gold',
        'D' => 'diamond',
    ],
];
