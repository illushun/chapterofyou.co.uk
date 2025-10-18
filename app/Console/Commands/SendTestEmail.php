<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use App\Mail\TestConfirmation;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mailtrap integration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 0);

        Mail::to(env("TEST_EMAIL_1"))->send(new TestConfirmation());
        Mail::to(env("TEST_EMAIL_2"))->send(new TestConfirmation());

        $this->info("Sent emails.");

        return Command::SUCCESS;
    }
}
