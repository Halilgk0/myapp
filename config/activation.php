<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activation Email Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the settings for user account activation emails.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Expiration Time
    |--------------------------------------------------------------------------
    |
    | This value controls the number of hours that the activation token is
    | considered valid. After this time, the user will need to request a new
    | activation email.
    |
    */
    'expires' => env('ACTIVATION_EXPIRES', 24),

    /*
    |--------------------------------------------------------------------------
    | Activation Email Subject
    |--------------------------------------------------------------------------
    |
    | This value is the subject of the activation email that is sent to users
    | after registration.
    |
    */
    'subject' => env('ACTIVATION_SUBJECT', 'Hesap Aktivasyonu'),

    /*
    |--------------------------------------------------------------------------
    | Activation Notification
    |--------------------------------------------------------------------------
    |
    | This value determines if the activation notification should be queued.
    |
    */
    'queue' => env('ACTIVATION_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Activation URL
    |--------------------------------------------------------------------------
    |
    | This value is the base URL for the activation link.
    |
    */
    'url' => env('APP_URL') . '/device/activate',
];
