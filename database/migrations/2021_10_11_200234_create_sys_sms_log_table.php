<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSysSmsLogTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('sys_sms_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uid', 32)->default('0')->nullable()->comment("接收人id");
            $table->string('phone',20)->default('')->nullable()->comment('接收人手机号');
            $table->string('content',500)->default('')->nullable()->comment('短信内容');
            $table->integer('send_time')->default(0)->nullable()->comment("发送時間");
            $table->char('send_uid', 32)->default('0')->nullable()->comment("发送人id");
            $table->tinyInteger("status")->default(0)->nullable()->comment('发送状态 1=成功 0=失败');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('sys_sms_log');
    }
}
