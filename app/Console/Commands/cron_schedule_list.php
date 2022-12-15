<?php

namespace App\Console\Commands;

use App\repositories\repo_crond;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Redis;

/**
 *
 * Class cron_schedule_list
 * @package App\Console\Commands
 */
class cron_schedule_list extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $schedule;
    private $repo_crond;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        Schedule $schedule,
        repo_crond $repo_crond
    )
    {
        parent::__construct();
        $this->schedule = $schedule;
        $this->repo_crond = $repo_crond;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = [];
        //在闭包中, 通过&$data 内部函数可以引用外部函数的参数和变量, 参数和变量不会被收回
        $events = array_map(function ($event) use (&$data) {
            $command = $this->filter_command($event->command);
            $lasttime = Redis::get('cron_lasttime:'.$command);
            $runtime = Redis::get('cron_runtime:'.$command);
            $update_time = time();
            $data_item = [
                'runtime_format'    => $event->expression, //例 0 1 * * *
                'command_name'      => $command,
                'lasttime'          => $lasttime, //上次执行时间
                'runtime'           => $runtime, //上次运行时间
                'update_time'       => $update_time
            ];
            $data[] = $data_item;

            //格式化数据
            $data_item['lasttime'] = empty($lasttime) ? '尚未执行' : Carbon::createFromTimestamp($lasttime)->format('Y-m-d H:i');
            $data_item['runtime'] = $runtime ?: '尚未执行';
            unset($data_item['update_time']);
            return $data_item;
        }, $this->schedule->events());

        //添加或更新数据
        $this->repo_crond->insertOrUpdate($data,
            ['command_name'],
            ['runtime_format', 'lasttime', 'runtime', 'update_time'],
        );

        $this->table( //用表格展示
            ['时间格式', '任务脚本', '上次执行时间', '上次运行时间'],
            $events
        );
    }

    /**
     * 过滤出命令名称 例 member_balance_data:gen
     * @param $command
     * @return string
     */
    public function filter_command($command)
    {
        $parts = explode(' ', $command);
        $parts[1] = str_replace('"', '', $parts[1]); //干掉windows双引号, 例 "artisan"
        $parts[1] = str_replace("'", "", $parts[1]); //干掉linux单引号, 例 'artisan'

        if (count($parts) > 2 && $parts[1] === "artisan")
        {
            $parts = array_slice($parts, 2);
        }
        return implode(' ', $parts);
    }
}
