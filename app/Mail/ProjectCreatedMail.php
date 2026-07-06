<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;
    public User $creator;

    /**
     * Create a new message instance.
     */
    public function __construct(Project $project, User $creator)
    {
        $this->project = $project;
        $this->creator = $creator;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New project created: ' . $this->project->name)
                    ->view('emails.project_created')
                    ->with([
                        'project' => $this->project,
                        'creator' => $this->creator,
                    ]);
    }
}
