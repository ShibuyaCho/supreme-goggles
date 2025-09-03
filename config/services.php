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

    /*
    |--------------------------------------------------------------------------
    | Cannabis POS Services
    |--------------------------------------------------------------------------
    */

    'metrc' => [
        'base_url' => env('METRC_BASE_URL', 'https://api-or.metrc.com'),
        'user_key' => env('METRC_USER_KEY'),
        'vendor_key' => env('METRC_VENDOR_KEY'),
        'username' => env('METRC_USERNAME'),
        'password' => env('METRC_PASSWORD'),
        'facility_license' => env('METRC_FACILITY'),
        'tag_prefix' => env('METRC_TAG_PREFIX', '1A4'),
        'enabled' => env('METRC_ENABLED', true),
    ],

    'pos' => [
        'sales_tax' => env('POS_SALES_TAX', 20.0),
        'excise_tax' => env('POS_EXCISE_TAX', 10.0),
        'cannabis_tax' => env('POS_CANNABIS_TAX', 17.0),
        'tax_inclusive' => env('POS_TAX_INCLUSIVE', false),
        'auto_print_receipt' => env('POS_AUTO_PRINT_RECEIPT', true),
        'require_customer' => env('POS_REQUIRE_CUSTOMER', true),
        'age_verification' => env('POS_AGE_VERIFICATION', true),
        'limit_enforcement' => env('POS_LIMIT_ENFORCEMENT', true),
        'accept_cash' => env('POS_ACCEPT_CASH', true),
        'accept_debit' => env('POS_ACCEPT_DEBIT', true),
        'accept_check' => env('POS_ACCEPT_CHECK', false),
        'round_to_nearest' => env('POS_ROUND_TO_NEAREST', false),
        'store_name' => env('POS_STORE_NAME', 'Cannabis POS'),
        'store_address' => env('POS_STORE_ADDRESS', ''),
        'receipt_footer' => env('POS_RECEIPT_FOOTER', "Thank you for your business!\nKeep receipt for returns and warranty."),
    ],

    'oregon_limits' => [
        'flower' => 56.7, // grams
        'concentrates' => 10, // grams  
        'edibles' => 454, // grams
        'clones' => 4, // units
    ],

];
