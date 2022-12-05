<?php

namespace App\Jobs;

use App\Mail\MailAuthentication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailSenderAuth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected User $user;
    protected string $url;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $url)
    {
        $this->user = $user;
        $this->url  = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user->email)->send(new MailAuthentication($this->user, $this->url));
    }
}
