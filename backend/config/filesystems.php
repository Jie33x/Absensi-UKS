<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'supabase' => [
            'driver' => 'supabase',
            'url' => env('SUPABASE_URL'),
            'key' => env('SUPABASE_SERVICE_ROLE_KEY'),
            'bucket' => env('SUPABASE_STORAGE_BUCKET', 'pmr-signatures'),
            'signed_url_expires_in' => env('SUPABASE_SIGNED_URL_EXPIRES_IN', 3600),
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
