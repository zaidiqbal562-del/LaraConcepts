<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\PushNotificationDevice;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendProjectCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Project $project;
    public User $creator;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, User $creator)
    {
        $this->project = $project;
        $this->creator = $creator;  
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $serverKey = env('FIREBASE_SERVER_KEY');
        if (empty($serverKey)) {
            Log::warning('FIREBASE_SERVER_KEY not configured; skipping FCM notifications.');
            return;
        }

        $tokens = PushNotificationDevice::whereNotNull('fcm_token')
            ->where('user_id', '!=', $this->creator->id)
            ->pluck('fcm_token')
            ->unique()
            ->filter()
            ->values()
            ->all();

        if (empty($tokens)) {
            Log::info('No FCM tokens found for other users; nothing to send.', ['project_id' => $this->project->id]);
            return;
        }

        $title = 'New Project: ' . $this->project->name;
        $body = $this->creator->name . ' created a new project.';

        // FCM legacy endpoint accepts up to 1000 registration_ids per request
        $chunks = array_chunk($tokens, 1000);

        foreach ($chunks as $chunk) {
            $payload = [
                'registration_ids' => $chunk,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'project_id' => $this->project->id,
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->failed()) {
                Log::error('FCM send failed', ['status' => $response->status(), 'body' => $response->body(), 'sent_count' => count($chunk)]);
            } else {
                Log::info('FCM sent', ['status' => $response->status(), 'sent_count' => count($chunk), 'project_id' => $this->project->id]);
            }
        }
    }
}
