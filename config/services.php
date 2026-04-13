<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],




    // 'green_api' => [
    //     'host' => env('GREEN_API_URL'), // https://7105.api.greenapi.com
    //     'instance_id' => env('GREEN_API_INSTANCE'),
    //     'token' => env('GREEN_API_TOKEN'),
    // ],

    // 'evolution' => [
    //     'host' => env('EV_HOST'),
    //     'token' => env('EV_API_TOKEN'),
    // ],

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    ],


    'green_api' => [
        'host' => env('GREEN_API_HOST', 'https://api.green-api.com'),
        'partner_token' => env('GREEN_API_PARTNER_TOKEN'),
    ],


    'baileys' => [
    'url' => env('BAILEYS_URL', 'http://localhost:3000'),
],

];
