<?php

namespace App\Console;

use App\Services\ChargesService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

use function Clue\StreamFilter\fun;

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
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(new ChargesService('J'))->dailyAt('04:00');
        $schedule->call(new ChargesService('F'))->dailyAt('02:00');
        $schedule->command('cache:clear')->dailyAt('01:00');
    }
}
