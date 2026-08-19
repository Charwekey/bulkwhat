<?php

return [
    'api_key' => env('SMESS_API_KEY', env('SMESS_API_KEYS')),
    'base_url' => env('SMESS_BASE_URL', 'https://smess.io/api'),
    'default_country_code' => env('DEFAULT_COUNTRY_CODE', '233'),
];
