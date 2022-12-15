<?php

use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateApiReqLogTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('api_req_log', function (Blueprint $table) {
            $table->Increments('id');
            $table->string('type',20)->default('')->nullable()->comment('端口类型，api/admin/agent');
            $table->char('uid',32)->default('')->nullable()->comment('用戶id');
            $table->text('req_data')->nullable()->comment('请求数据，json格式');
            $table->text('res_data')->nullable()->comment('响应数据，json格式');
            $table->integer('req_time')->default(0)->nullable()->comment('请求时间');
            $table->char('req_country', 2)->default('')->nullable()->comment('请求国家');
            $table->string('req_ip',15)->default('')->nullable()->comment('请求ip');
            $table->index('uid');
            $table->index('req_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('api_req_log');
    }
}
