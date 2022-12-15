<?php

namespace App\Jobs;

use App\services\serv_sys_mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class job_send_mail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $to           = []; //收件人
    private $subject      = ''; //主旨
    private $view         = ''; //模版 例 mail.example
    private $view_data    = []; //模版数据
    private $serv_sys_mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->to           = $data['to'] ?? [];
        $this->subject      = $data['subject'] ?? '';
        $this->view_data    = $data['view_data'] ?? [];
        $this->view         = $data['view'] ?? '';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->to))   //有收件人才發送
        {
            app(serv_sys_mail::class)->_send_mail([
                'to'        => $this->to,
                'subject'   => $this->subject,
                'view'      => $this->view,
                'view_data' => $this->view_data,
            ]);
        }
    }
}
