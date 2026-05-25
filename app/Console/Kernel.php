<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
   
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
{
    // $schedule->command('upi:auto-status')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();

    // $schedule->command('payout:auto-status')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();

    // $schedule->command('pg:auto-status')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();

    // $schedule->command('payin:process')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();
    // $schedule->command('payin1:process')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();
    // $schedule->command('payin2:process')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();
    // $schedule->command('pg2:process')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();
    // $schedule->command('payoutaeps:process')
    //     ->everyMinute()
    //     ->withoutOverlapping()
    //     ->runInBackground();

    $schedule->command('pgpaycel:auto-status')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();


}
}
