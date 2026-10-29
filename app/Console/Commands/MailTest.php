<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Test if mail is sending.
 */
class MailTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email to john.doe@gmail.com';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Mail::raw(sprintf('Mail-test from %s %s', \config('app.name'), \config('app.url')), function ($msg): void {
            $msg->to('john.doe@gmail.com')->subject('Test Email');
        });

        $this->info('Mail sent 📝');
    }
}
