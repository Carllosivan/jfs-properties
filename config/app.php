<?php

return [
    'name' => 'JFS Properties',
    'tagline' => 'Sistema Integrado de Gestão Imobiliária, Incidentes e Manutenção',
    'env' => getenv('APP_ENV') ?: 'production',
    'url' => getenv('APP_URL') ?: 'http://localhost:3000',
    'currency' => 'MZN',
    'currency_symbol' => 'MT',
    'timezone' => 'Africa/Maputo',
    'session_lifetime' => 7200, // 2 hours
    'max_login_attempts' => 5,
    'lockout_time' => 900, // 15 minutes
    'upload_max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    'allowed_doc_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'],
    'company' => [
        'name' => 'JFS Properties, Lda.',
        'nuit' => '400987654',
        'email' => 'suporte@jfsproperties.co.mz',
        'phone' => '+258 84 123 4567',
        'address' => 'Av. Julius Nyerere, Edifício Jat V, Maputo, Moçambique'
    ]
];
