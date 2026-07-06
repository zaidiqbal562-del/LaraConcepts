<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\TwilioService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $to;
    public $message;

    public function __construct($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
    }

    public function handle(TwilioService $twilio)
    {
        $twilio->sendSms($this->to, $this->message);
    }
}
