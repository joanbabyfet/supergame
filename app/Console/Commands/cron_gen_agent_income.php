<?php

namespace App\Console\Commands;

use App\services\serv_agent_income;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class cron_gen_agent_income extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent_income:gen {from_date?}'; //参数加?表选填

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成渠道代理收入记录';

    private $serv_agent_income;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(serv_agent_income $serv_agent_income)
    {
        parent::__construct();
        $this->serv_agent_income = $serv_agent_income;
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
        $this->serv_agent_income->generate_data($from_date);

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
