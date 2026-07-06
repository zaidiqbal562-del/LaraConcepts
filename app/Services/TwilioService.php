<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    public function sendSms($to, $message)
    {
        $client = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN')
        );

        return $client->messages->create(
            $to,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message,
            ]
        );
    }
}