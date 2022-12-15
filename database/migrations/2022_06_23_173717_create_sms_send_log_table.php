<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmsSendLogTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('sms_send_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone',20)->default('')->nullable()->comment('发送号码');
            $table->string('content',500)->default('')->nullable()->comment('发送内容');
            $table->integer('send_time')->default(0)->nullable()->comment("发送时间");
            $table->text('result')->nullable()->comment('发送结果');
            $table->text('req_data')->nullable()->comment('请求数据');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('sms_send_log');
    }
}
