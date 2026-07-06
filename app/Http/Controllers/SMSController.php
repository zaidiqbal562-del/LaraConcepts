<?php

namespace App\Http\Controllers;
use App\Jobs\SendSmsJob;
use Illuminate\Http\Request;

class SMSController extends Controller
{
    public function index()
    {
        return view('sms.index');
    }   
    public function sendSms()
    {
        SendSmsJob::dispatch(
            '+1 318 646 1699',
            'Hello from Laravel!'
        );

        return response()->json([
            'message' => 'SMS queued'
        ]);
    }
}
