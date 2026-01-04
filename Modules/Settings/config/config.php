<?php

/*
|--------------------------------------------------------------------------
| Settings Configuration
|--------------------------------------------------------------------------
|
| Here you can define the settings that will be displayed in the settings
| page. The settings will be stored in the database and can be accessed
| using the config helper function. The settings will be displayed in
| the settings page in the order they are defined in this file.
| - group: The group of the setting (default: General)
| - key: The key of the setting
| - value: The default value of the setting
| - type: The type of the setting (text, textarea, file, image, integer, float, boolean, select, multi-select, array, json)
| - options: The options for select and multi-select types
| - description: The description of the setting
| - is_visible: Whether the setting is visible in the settings page (default: true)
| - is_required: Whether the setting is required (default: true)
| - is_disabled: Whether the setting is disabled (default: false)
*/
try {
    return [
        /*
        |--------------------------------------------------------------------------
        | General Settings
        |--------------------------------------------------------------------------
        */
        'app_name' => [
            'group' => 'General',
            'value' => 'App Name',
            'type' => 'text',
            'description' => 'The name of the application',
        ],
        'app_logo' => [
            'group' => 'General',
            'value' => '/images/logo.png',
            'type' => 'image',
            'description' => 'The logo of the application',
        ],
        'app_logo_small' => [
            'group' => 'General',
            'value' => '/images/logo-sm.png',
            'type' => 'image',
            'description' => 'The small logo of the application',
        ],
        'development_support_email' => [
            'group' => 'General',
            'value' => 'dev@example.com',
            'type' => 'text',
            'description' => 'The email address for development support',
        ],
        'maintenance_mode' => [
            'group' => 'General',
            'value' => '0',
            'type' => 'boolean',
            'description' => 'Enable maintenance mode',
        ],
        'maintenance_message' => [
            'group' => 'General',
            'value' => 'The site is under maintenance. Please try again later.',
            'type' => 'text',
            'description' => 'The message to display when in maintenance mode',
        ],
        'max_verification_attempts' => [
            'group' => 'General',
            'value' => '3',
            'type' => 'integer',
            'description' => 'The maximum number of verification attempts allowed in a day for a user',
        ],
        /*
        |--------------------------------------------------------------------------
        | Mobile App Settings
        |--------------------------------------------------------------------------
        */
        'app_version' => [
            'group' => 'Mobile App',
            'value' => '1.0.0',
            'type' => 'text',
            'description' => 'The version of the application',
        ],
        'android_app_code' => [
            'group' => 'Mobile App',
            'value' => '1',
            'type' => 'integer',
        ],
        'android_app_code_min' => [
            'group' => 'Mobile App',
            'value' => '1',
            'type' => 'integer',
        ],
        'ios_app_code' => [
            'group' => 'Mobile App',
            'value' => '1',
            'type' => 'integer',
        ],
        'ios_app_code_min' => [
            'group' => 'Mobile App',
            'value' => '1',
            'type' => 'integer',
        ],
        'app_icon' => [
            'group' => 'Mobile App',
            'value' => '/images/logo-sm.png',
            'type' => 'image',
            'description' => 'The icon of the application',
        ],
        'play_store_url' => [
            'group' => 'Mobile App',
            'value' => '#',
            'type' => 'text',
            'description' => 'The URL of the application in the Play Store',
        ],
        'app_store_url' => [
            'group' => 'Mobile App',
            'value' => '#',
            'type' => 'text',
        ],
        'app_maintenance_mode' => [
            'group' => 'Mobile App',
            'value' => '0',
            'type' => 'boolean',
            'description' => 'Enable maintenance mode for the application',
        ],
        /*
        |--------------------------------------------------------------------------
        | Contact Settings
        |--------------------------------------------------------------------------
        */
        'address' => [
            'group' => 'Contact',
            'value' => "Address 1, Address 2",
            'type' => 'textarea',
        ],
        'email' => [
            'group' => 'Contact',
            'value' => "test@example.com",
            'type' => 'text',
        ],
        'facebook' => [
            'group' => 'Contact',
            'value' => "#",
            'type' => 'text',
        ],
        'instagram' => [
            'group' => 'Contact',
            'value' => "#",
            'type' => 'text',
        ],
        'twitter' => [
            'group' => 'Contact',
            'value' => "#",
            'type' => 'text',
        ],
        'youtube' => [
            'group' => 'Contact',
            'value' => "#",
            'type' => 'text',
        ],
        /*
        |--------------------------------------------------------------------------
        | Email Settings
        |--------------------------------------------------------------------------
        */
        'email_mailers' => [
            'group' => 'General',
            'value' => json_encode([
                [
                    'TYPE' => 'LOG',
                    'VALUE' => [
                        'transport' => 'log',
                        'channel' => null,
                    ]
                ]
            ]),
            'type' => 'json',
        ],
        'email_mailer' => [
            'group' => 'General',
            'value' => 'LOG',
            'type' => 'select',
        ],
        /*
        |--------------------------------------------------------------------------
        | SMS Settings
        |--------------------------------------------------------------------------
        */
        'sms_gateway' => [
            'group' => 'General',
            'value' => 'LOG',
            'type' => 'select',
        ],
        'sms_gateways' => [
            'group' => 'General',
            'value' => json_encode([
                [
                    'TYPE' => 'LOG',
                    'VALUE' => [
                        'endpoint' => 'LOG',
                        'method' => 'POST',
                        'mobile_key' => 'mobile',
                        'message_key' => 'message',
                    ]
                ]
            ]),
            'type' => 'json',
        ],
        /*
        |--------------------------------------------------------------------------
        | Firebase Settings
        |--------------------------------------------------------------------------
        */
        'firebase_credentials_json' => [
            'group' => 'Firebase',
            'value' => '#',
            'type' => 'text',
            'description' => 'The credentials JSON for the Firebase project',
        ],
        'firebase_project_id' => [
            'group' => 'Firebase',
            'value' => '#',
            'type' => 'text',
            'description' => 'The project ID for the Firebase project',
        ],
        'google_project_id' => [
            'group' => 'Firebase',
            'value' => '#',
            'type' => 'text',
            'description' => 'The project ID for the Google project',
        ],
        /*
        |--------------------------------------------------------------------------
        | Social Auth Settings
        |--------------------------------------------------------------------------
        */
        'google_client_id' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'google_client_secret' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'google_redirect_uri' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'github_client_id' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'github_client_secret' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'github_redirect_uri' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_client_id' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_client_secret' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_redirect_uri' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_team_id' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_key_id' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
        'apple_key_file' => [
            'group' => 'Social Auth',
            'value' => '#',
            'type' => 'text',
        ],
    ];
} catch (JsonException $e) {
    report($e);
    return [];
}
