<?php

return [
    'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
    'token'       => env('ULTRAMSG_TOKEN'),
    'base_url'    => env('ULTRAMSG_BASE_URL', 'https://api.ultramsg.com'),
    'default_country_code' => env('DEFAULT_COUNTRY_CODE', '233'),
];
