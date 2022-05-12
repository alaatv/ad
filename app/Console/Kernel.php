<?php

namespace App\Console;

use App\Console\Commands\Fetching;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $since = Carbon::now()->subMonths(3)->toDateString();
//        cron('0 0 * */3 *')
        //TODO: enter the exact sourceName of chibekhunam instead of test
        $schedule->command(Fetching::class, ['test', '--since=' . $since])
            ->everyMinute()->timezone('Asia/Tehran');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
