<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsOplogTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('agents_oplog', function (Blueprint $table) {
            $table->Increments('id');
            $table->char('uid',32)->default('')->nullable()->comment('渠道代理id');
            $table->string('username',20)->default('')->nullable()->comment('用戶名');
            $table->string('session_id',50)->default('')->nullable()->comment('session id');
            $table->string('msg',200)->default('')->nullable()->comment('消息内容');
            $table->integer('module_id')->default(0)->nullable()->comment('模块id');
            $table->integer('op_time')->default(0)->nullable()->comment('操作時間');
            $table->string('op_ip',15)->default('')->nullable()->comment('操作ip');
            $table->string('op_country',2)->default('')->nullable()->comment('操作國家');
            $table->string('op_url',100)->default('')->nullable()->comment('操作地址');
            $table->index('username');
            $table->index('op_time');
            $table->index('module_id');
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('agents_oplog');
    }
}
