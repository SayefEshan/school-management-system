<?php

return [
    'name' => 'PushNotification',
    'firebase_project_id' => config('settings.firebase_project_id.value', env('FIREBASE_PROJECT_ID')),
    'google_project_id' => config('settings.google_project_id.value', env('GOOGLE_PROJECT_ID')),
];
