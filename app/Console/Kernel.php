<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GenerateDemoData::class,
        \App\Console\Commands\GenerateRecurringInvoice::class,
        \App\Console\Commands\ImportTicketFromEmail::class,
        \App\Console\Commands\RemindPeople::class,
        \App\Console\Commands\RemoveTemporaryFiles::class,
        \App\Console\Commands\UpdateStatus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
     
      
       $schedule->command('update:status')->daily();
       $schedule->command('remove:temp_files')->daily();
       $schedule->command('gen:recurring_invoice')->daily();
       
       $schedule->command('run:reminder')->hourly();
       $schedule->command('import:ticket')->everyFifteenMinutes();

       
       //$schedule->command('queue:restart')->everyMinute();

       // if (!strstr(shell_exec('ps xf'), 'php artisan queue:work')) {
       //      $schedule->command('queue:work')
       //               ->everyMinute()->emailOutputTo('admin@gmail.com');
       //  }
       $schedule->command('queue:restart')->everyMinute();
       $schedule->command('queue:work --sleep=3 --tries=3')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
