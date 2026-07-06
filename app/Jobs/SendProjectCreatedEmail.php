<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use App\Mail\ProjectCreatedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendProjectCreatedEmail implements ShouldQueue
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
        User::where('id', '!=', $this->creator->id)
            ->whereNotNull('email')
            ->chunk(100, function ($users) {
                foreach ($users as $u) {
                    if (!empty($u->email)) {
                        Mail::to($u->email)->queue(new ProjectCreatedMail($this->project, $this->creator));
                    }
                }
            });
    }
}
