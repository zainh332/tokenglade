<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\DistributeStakingRewards::class,
        Commands\TakeTokenSnapshots::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('staking:reward')->hourly();
        $schedule->command('tokens:snapshot')->hourly();

        // LP rewards (controlled execution)
        $schedule->command('lp:distribute-rewards')
            ->mondays()
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
