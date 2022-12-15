<?php

namespace App\Console\Commands;

use App\services\serv_member_online_data;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

/**
 * 弃用, 改为游戏服打点后聚合实时统计
 * Class cron_gen_member_online_per_hour
 * @package App\Console\Commands
 */
class cron_gen_member_online_per_hour extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member_online_per_hour:gen {from_date?}'; //参数加?表选填

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成会员在线每小时数据';

    private $serv_member_online_data;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(serv_member_online_data $serv_member_online_data)
    {
        parent::__construct();
        $this->serv_member_online_data = $serv_member_online_data;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $time_start = microtime(true);

        //业务
        $from_date = $this->argument('from_date') ?? date('Y/m/d', strtotime('-1 day'));
        $this->serv_member_online_data->generate_per_hour_data($from_date);

        $size = memory_get_usage();
        $unit = array('b','kb','mb','gb','tb','pb');
        $memory = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
        $time = microtime(true) - $time_start;
        $date = date('Y-m-d H:i:s');
        echo "[{$date}] {$this->getName()} Done in $time seconds\t $memory\n";
        //将上次执行时间与运行时间写入redis
        Redis::set('cron_lasttime:'.$this->getName(), ceil($time_start));
        Redis::set('cron_runtime:'.$this->getName(), number_format($time, 3));
    }
}
