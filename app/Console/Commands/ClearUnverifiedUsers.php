<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ClearUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'my-command:clear-unverified-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This deletes all unverified users';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('status','=',false)->delete();
        return Command::SUCCESS;
    }
}
