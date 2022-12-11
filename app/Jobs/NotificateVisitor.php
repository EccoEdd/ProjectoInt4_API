<?php

namespace App\Jobs;

use App\Mail\MailNotificateVisitor;
use App\Models\Incubator;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificateVisitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected User $user;
    protected Incubator $incubator;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Incubator $incubator)
    {
        $this->user = $user;
        $this->incubator = $incubator;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info($this->user);
        Mail::to($this->user->email)->send(new MailNotificateVisitor($this->user, $this->incubator));
    }
}
