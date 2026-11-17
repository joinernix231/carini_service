<?php

/*
| CORS: permite que la PWA (navegador) llame a la API desde otro dominio.
| El login está en /login (fuera de /api), por eso se incluye aparte.
| CORS_ALLOWED_ORIGINS acepta una lista separada por comas; por defecto '*',
| que es seguro aquí porque la API usa tokens Bearer, no cookies.
*/

return [
    'paths' => ['api/*', 'login'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*')))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,
    'supports_credentials' => false,
];
