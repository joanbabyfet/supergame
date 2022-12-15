<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * 注册到Kernel，定義應用的 artisan 指令
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\cron_example::class,
    ];

    /**
     * Define the application's command schedule.
     * withoutOverlapping 上文件锁防止相同脚本未结束时重覆调用, runInBackground 会干掉重覆脚本
     * 过期时间默认1440分钟, 改为30分钟
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //每分钟
        //$schedule->command('name:action')->everyMinute()->withoutOverlapping(30)->runInBackground();

        //每天一点
        //$schedule->command('name:action')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        $schedule->command('agent_balance_data:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        $schedule->command('member_active_data:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        $schedule->command('member_increase_data:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        //$schedule->command('member_online_per_hour:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        $schedule->command('member_retention_data:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();
        $schedule->command('agent_income:gen')->dailyAt('01:00')->withoutOverlapping(30)->runInBackground();

        //每小时
        //$schedule->command('name:action')->hourly()->withoutOverlapping(30)->runInBackground();

        //每小时 某分
        //$schedule->command('name:action')->hourlyAt(30)->withoutOverlapping(30)->runInBackground();

        //每天 某时:某分
        //$schedule->command('name:action')->dailyAt('11:00')->withoutOverlapping(30)->runInBackground();

        //每周-某天 某时:某分 day=1为周一
        //$schedule->command('name:action')->weeklyOn(3, '01:30')->withoutOverlapping(30)->runInBackground();

        //每月-某天 某时:某分 day=1为一号
        //$schedule->command('name:action')->monthlyOn(1, '01:00')->withoutOverlapping(30)->runInBackground();

        //每年 某月-某日 某时-某分
        //$schedule->command('name:action')->cron('00 22 25 12 *')->withoutOverlapping(30)->runInBackground();
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
