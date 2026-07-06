<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PushNotificationDevice;

class PushNotificationController extends Controller
{
    public function saveFcmtoken(Request $request){
        $request->validate([
        'user_id' => 'required|exists:users,id',  
        'fcm_token' => 'required|string',
        ]);
            try {
                // Ensure we don't attempt to insert duplicate (user_id, fcm_token) pairs.
                $device = PushNotificationDevice::firstOrCreate(
                    ['user_id' => $request->user_id, 'fcm_token' => $request->fcm_token]
                );

                if ($device->wasRecentlyCreated) {
                    \Log::info('FCM token created', ['user_id' => $request->user_id]);
                } else {
                    \Log::info('FCM token already exists', ['user_id' => $request->user_id]);
                }

                return response()->json(['message' => 'FCM token saved successfully']);
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle rare race condition leading to duplicate entry
                \Log::warning('FCM token save race or duplicate', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'FCM token save conflict, ignored.'], 200);
            }
    }
}
