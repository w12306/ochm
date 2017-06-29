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
        \Common\Console\Commands\ResetOpcache::class,
        \Common\Console\Commands\OptimizeSeparatedCommand::class,


        \App\Console\Commands\PutExecutive::class,
        \App\Console\Commands\PutExecutiveSelf::class,
        \App\Console\Commands\Sync\SyncLoands::class,
        \App\Console\Commands\Sync\Synclist::class,
        \App\Console\Commands\Sync\Syncphp::class,
        \App\Console\Commands\Sync\SyncCheck::class,
        \App\Console\Commands\Sync\Syntest::class,
        \App\Console\Commands\Sync\SyncAccount::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
        $schedule->call('adps:adps:update-ad-by-time')
            ->everyFiveMinutes()
            ->withoutOverlapping();
        */
    }
}
