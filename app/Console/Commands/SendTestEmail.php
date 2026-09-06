<?php

namespace App\Console\Commands;

use App\Mail\TestConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /** @var string */
    protected $signature = 'test:email:send';

    /** @var string */
    protected $description = 'Test mailtrap integration.';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 0);

        Mail::to(env('TEST_EMAIL_1'))->send(new TestConfirmation);
        Mail::to(env('TEST_EMAIL_2'))->send(new TestConfirmation);

        $this->info('Sent emails.');

        return Command::SUCCESS;
    }
}
