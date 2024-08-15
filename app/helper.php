<?php 
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class NotificationHelper
{
        function fcmnotify($token,$notification){
        $getFcmKey = 'AAAAQQIAPcI:APA91bEYtEP8MQpFutPjP4Cv4jsbzrLV2ImuUj4UkYp6zgdTOoLXeovYooBMB12jDIWv45_Cug6vIH2DlOLAtUEF-5K0A2cwvKufRGdaE4cJKTL89VdhfGTdu2EDSgt4kpRIwz2f6PFd';
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
       

        $extraNotificationData = ["message" => $notification];

        $fcmNotification = [
            'to'        => $token,
            'notification' => $notification,
            'data' => $extraNotificationData,
        ];

        $headers = [
            'Authorization: key=' .$getFcmKey,
            'Content-Type: application/json'
        ];

        

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        
        // Execute the cURL request
        $response = curl_exec($ch);
        
        // Close the cURL session
        curl_close($ch);

        Log::info('FCM Response: ' . $response);
        return $response;
    
      
    }
}