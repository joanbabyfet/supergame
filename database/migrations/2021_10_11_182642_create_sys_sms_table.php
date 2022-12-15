<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSysSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger("object_type")->default(0)->nullable()->comment('发送类型 1=所有用户 2=个人 3=会员等级 4=注册时间');
            $table->string('object_ids',255)->default('')->nullable()->comment('发送对象');
            $table->string('name',100)->default('')->nullable()->comment('短信名称');
            $table->string('content',500)->default('')->nullable()->comment('短信内容.中');
            $table->string('content_en',500)->default('')->nullable()->comment('短信内容.英');
            $table->integer('send_time')->default(0)->nullable()->comment("发送時間");
            $table->char('send_uid', 32)->default('0')->nullable()->comment("发送人");
        });
        $table = DB::getTablePrefix().'sys_sms';
        DB::statement("ALTER TABLE `{$table}` comment'短信营销发送记录'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_sms');
    }
}
