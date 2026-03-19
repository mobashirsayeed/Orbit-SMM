<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailFailover extends Command
{
    protected $signature = 'mail:test-failover';
    protected $description = 'Test email failover from SendGrid to Host SMTP';

    public function handle()
    {
        $to = 'your-test-email@example.com';
        
        try {
            Mail::raw('This is a test email from ORBIT Platform.', function ($message) use ($to) {
                $message->to($to)
                        ->subject('ORBIT Email Failover Test');
            });

            $this->info('✅ Email sent successfully!');
            $this->info('Check which mailer was used in logs if needed.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
        }

        return 0;
    }
}
