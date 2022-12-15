<?php

namespace App\Jobs;

use App\services\serv_sys_sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class job_send_sms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $content      = ''; //短信内容
    protected $content_en   = '';
    protected $send_users   = []; //发送对象
    protected $send_uid     = ''; //谁发送的

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->content      = $data['content'] ?? '';
        $this->content_en   = $data['content_en'] ?? '';
        $this->send_users   = $data['send_users'] ?? [];
        $this->send_uid     = $data['send_uid'] ?? '';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->send_users))   //有發送對象才送
        {
            app(serv_sys_sms::class)->_send_sms([
                'content'       => $this->content,
                'content_en'    => $this->content_en,
                'send_users'    => $this->send_users,
                'send_uid'      => $this->send_uid,
            ]);
        }
    }
}
