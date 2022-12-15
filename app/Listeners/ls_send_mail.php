<?php

namespace App\Listeners;

use App\Events\evt_send_mail;
use App\services\serv_sys_mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * 发送邮件事件监听类
 * Class ls_send_mail
 * @package App\Listeners
 */
class ls_send_mail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\evt_send_mail  $event
     * @return void
     */
    public function handle(evt_send_mail $event)
    {
        if(!empty($event->to))   //有收件人才發送
        {
            app(serv_sys_mail::class)->_send_mail([
                'to'        => $event->to,
                'subject'   => $event->subject,
                'view'      => $event->view,
                'view_data' => $event->view_data,
            ]);
        }
    }
}
