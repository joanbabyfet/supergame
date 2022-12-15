<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAdminUsersLoginTable extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('admin_users_login_log', function (Blueprint $table) {
            $table->Increments('id');
            $table->char('uid',32)->default('')->nullable()->comment('用戶id');
            $table->string('username',20)->default('')->nullable()->comment('用戶名');
            $table->string('session_id',50)->default('')->nullable()->comment('session id');
            $table->string('agent',500)->default('')->nullable()->comment('瀏覽器信息');
            $table->integer('login_time')->default(0)->nullable()->comment('登入時間');
            $table->string('login_ip',15)->default('')->nullable()->comment('登入ip');
            $table->string('login_country',2)->default('')->nullable()->comment('登入國家');
            $table->tinyInteger('status')->default(0)->nullable()->comment('登录时状态 1=成功，0=失败');
            $table->string('cli_hash',32)->default('')->nullable()->comment('用户登录名和ip的hash');
            $table->index('username');
            $table->index('login_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('admin_users_login_log');
    }
}
