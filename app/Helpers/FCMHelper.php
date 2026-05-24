<?php

namespace App\Helpers;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

class FCMHelper
{
    public static function sendToBoss(string $title, string $body): bool
    {
        try {
            $boss = User::where('role', 'admin')->first();

            if (!$boss || empty($boss->fcm_token)) {
                return false;
            }

            $messaging = app('firebase.messaging');

            
            $message = CloudMessage::withTarget('token', $boss->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'notification' => [
                        'sound' => 'default',
                        'default_vibrate_timings' => true,
                        'notification_priority' => 'PRIORITY_HIGH',
                    ],
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ]));

            $messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error("FCMHelper Lỗi: " . $e->getMessage());
            return false;
        }
    }
}
