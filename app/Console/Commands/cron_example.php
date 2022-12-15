<?php

namespace App\Console\Commands;

use App\Jobs\job_example;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class cron_example extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'example:export'; // laravel指令命名,统一用name:action

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '腳本描述';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
