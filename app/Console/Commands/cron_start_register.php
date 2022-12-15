<?php

namespace App\Console\Commands;

use GatewayWorker\Register;
use Illuminate\Console\Command;
use Workerman\Worker;

class cron_start_register extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wk:register {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        global $argv;
        $action = $this->argument('action'); //获取参数
        if(!in_array($action, ['start', 'stop', 'restart', 'reload', 'status', 'connections']))
        {
            $this->error('参数错误');
            exit;
        }
        //$argv[0] = 'wk'; //干掉才能正常啟用
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d':''; //获取到选项返回true, 否则false
        $this->start_register();
        Worker::runAll(); //运行worker
    }

    private function start_register()
    {
        new Register('text://0.0.0.0:1236'); //实例化一个容器, register 必须是text协议
    }
}
