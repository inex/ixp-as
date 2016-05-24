<?php

namespace App\Console;

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
        // Commands\Inspire::class,
        Commands\CreateMeasurements::class,
        Commands\RunMeasurements::class,
        Commands\UpdateProbes::class,
        Commands\UpdateMeasurements::class,
        Commands\StopAllMeasurements::class,
        Commands\CompleteRequests::class,
        Commands\UpdateIXPs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // process new requests and create measurements
        $schedule->command('atlas:create-measurements')
                 ->everyMinute()->withoutOverlapping();

        $schedule->command('atlas:run-measurements')
                 ->everyMinute()->withoutOverlapping();

        $schedule->command('atlas:update-measurements')
                 ->everyMinute()->withoutOverlapping();

        $schedule->command('atlas:complete-requests')
                 ->everyMinute()->withoutOverlapping();

        $schedule->command('ixps:update')
                 ->daily()->withoutOverlapping();

        $schedule->command('atlas:update-probes')
                ->daily()->withoutOverlapping();

    }
}
