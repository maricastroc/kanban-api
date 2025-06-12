<?php

declare(strict_types = 1);

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie', '*'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => ['http://localhost:3000', 'https://seusite.com'],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => ['Authorization'],
    'max_age'                  => 86400,
    'supports_credentials'     => true,
];
