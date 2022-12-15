<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWsConnLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ws_conn_log', function (Blueprint $table) {
            $table->id();
            $table->char('uid',32)->default('')->nullable()->comment('用戶id');
            $table->char('agent_id',32)->default('')->nullable()->comment('渠道代理id');
            $table->string('client_id', 22)->default('')->nullable()->comment('SOCKET链接ID');
            $table->integer('conn_time')->default(0)->nullable()->comment('链接时间');
            $table->string('conn_ip', 15)->default('')->nullable()->comment('链接IP');
        });
        $table = DB::getTablePrefix().'ws_conn_log';
        DB::statement("ALTER TABLE `{$table}` comment'WS连接日志表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ws_conn_log');
    }
}
