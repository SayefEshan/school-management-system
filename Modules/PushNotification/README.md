# Push Notification Module

This module implements push notifications using Firebase Cloud Messaging (FCM).

## Features

- Firebase Cloud Messaging (FCM) integration
- Custom notification channel for Laravel
- User-specific notification delivery
- Support for multiple Firebase tokens per user

## Integration with Other Modules

The Push Notification module can be integrated with:

1. **Notification Module**: For managing both in-app and push notifications
2. **Chat Module**: For real-time message notifications
3. **Campaign Module**: For campaign updates
4. **ActivityLog Module**: For export completion notifications
5. **Download/Import Manager**: For task completion notifications

## Usage

### Basic Usage

To send a push notification:

```php
use Modules\PushNotification\Notifications\PushNotification;
use Illuminate\Support\Facades\Notification;

// Get the user or notifiable entity
$user = User::find(1);

// Send notification
Notification::send($user, new PushNotification(
    'Notification Title',
    'Notification body text',
    [
        'additional_data' => 'Any additional data to include',
        'action' => 'open_specific_page'
    ]
));
```

### Using the NotificationService

For a unified approach that handles both in-app and push notifications:

```php
use Modules\Notification\Services\NotificationService;

// Get the user
$user = User::find(1);

// Send both in-app and push notification
NotificationService::send(
    $user,
    'Notification Title',
    'Notification body message',
    [
        'additional_data' => 'Any additional data',
        'action' => 'open_specific_page'
    ],
    'info' // notification type: info, success, warning, error
);
```

## Creating Custom Notifications

To create a custom notification that supports FCM:

1. Create a notification class that extends `Illuminate\Notifications\Notification`
2. Add FCM to the channels in the `via()` method
3. Implement a `toFcm()` method that returns the notification data

Example:

```php
class CustomNotification extends Notification
{
    use Queueable;
    
    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Add FCM channel if the user has Firebase tokens
        if (method_exists($notifiable, 'firebaseTokens') && $notifiable->firebaseTokens()->exists()) {
            $channels[] = 'fcm';
        }
        
        return $channels;
    }
    
    public function toFcm($notifiable)
    {
        $tokens = $notifiable->firebaseTokens->pluck('token')->toArray();
        
        return [
            'to' => $tokens,
            'notification' => [
                'title' => 'Your notification title',
                'body' => 'Your notification body',
            ],
            'data' => [
                'custom_data' => 'Your custom data',
            ],
        ];
    }
    
    // Other methods like toArray(), toMail(), etc.
}
```

## Configuration

The FCM configuration is stored in `config/pushnotification.php`:

- `firebase_project_id`: Your Firebase project ID
- Service account credentials are stored in `storage/app/kubemoney-ai.json` 
