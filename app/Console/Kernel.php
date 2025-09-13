<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
  protected function schedule(Schedule $schedule)
{
    $schedule->command('rappels:envoyer')->dailyAt('09:00');
}

protected $middleware = [
    // ... autres middlewares
    \App\Http\Middleware\AddCorsHeaders::class,
     // Si vous utilisez le package fruitcake
];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
