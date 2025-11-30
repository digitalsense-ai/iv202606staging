<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use \App\Classes\CommonClass;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {        
        $commonClass = new CommonClass();
        $system = $commonClass->getSystemInfoLazy();
        $systemtaskdates = $system->systemtaskdate;

        $excel_taskdate = 15;
        $excel_taskdates = $systemtaskdates->filter(function ($taskdate, $key) {
            return $taskdate->task_name == 'Statistics Excel';
        });

        if(count($excel_taskdates) > 0) 
            $excel_taskdate = $excel_taskdates->first()->task_date;  

        $schedule->command('stats:task')->monthlyOn($excel_taskdate, '00:00');
        $schedule->command('vatreg:task')->monthlyOn(1, '00:00');
        $schedule->command('apidatas:load')->dailyAt('00:00');    
        $schedule->command('exchangerate:task')->dailyAt('14:30');  
        //$schedule->command('reminder:task')->hourly();
        
        $schedule->command('irazure:task')->dailyAt('00:00'); 
        
        if(strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud")
        {
            $schedule->command('mailbox:task')->dailyAt('00:00'); 
            $schedule->command('irftp:task')->dailyAt('00:00'); 
            $schedule->command('irefacto:task')->dailyAt('00:00'); 
            $schedule->command('cargomailbox:task')->dailyAt('00:00'); 
        }
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
