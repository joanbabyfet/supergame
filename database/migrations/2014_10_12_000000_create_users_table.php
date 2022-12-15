<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id',32)->default('')->comment('用戶id');
            $table->char('agent_id',32)->default('')->nullable()->comment('渠道代理id');
            $table->tinyInteger('origin')->default(0)->nullable()->comment('注册来源 1=H5 2=PC 3=安卓 4=IOS');
            $table->string('username', 41)->default('')->nullable()->comment("用户帐号,包含代理帐号前缀");
            $table->string('password', 60)->default('')->nullable()->comment("用户密码");
            $table->string('avatar',100)->default('')->nullable()->comment('头像');
            $table->string('realname',50)->default('')->nullable()->comment('姓名');
            $table->string('email', 100)->default('')->nullable()->comment('信箱');
            $table->string('phone_code', 5)->default('')->nullable()->comment('手機號國碼');
            $table->string('phone', 20)->default('')->nullable()->comment('手機號');
            $table->integer('country_id')->default(0)->nullable()->comment('國家id');
            $table->integer('province_id')->default(0)->nullable()->comment('省份id');
            $table->integer('city_id')->default(0)->nullable()->comment('城市id');
            $table->integer('area_id')->default(0)->nullable()->comment('區/縣id');
            $table->string('address',100)->default('')->nullable()->comment('商家地址');
            $table->tinyInteger("status")->default(1)->nullable()->comment('帐号状态 1:正常 0:禁止登陆');
            $table->string('ban_desc',180)->default('')->nullable()->comment('禁用原因');
            $table->decimal('withdraw_limit',15, 2)->default(0)->nullable()->comment('提领限制');
            $table->tinyInteger('is_first_login')->default(1)->nullable()->comment('是否首次登录(首次跳转修改密码页)');
            $table->tinyInteger('is_new_user')->default(0)->nullable()->comment('是否为新增用户 0=否 1=是');
            $table->tinyInteger('is_audit')->default(0)->nullable()->comment('登陆是否需要后台进行人工审核 0: 不需要 1:需要');
            $table->integer('session_expire')->default(1440)->nullable()->comment('SESSION有效期，默认24分钟');
            $table->string('session_id', 50)->default('')->nullable()->comment('登陆时session_id');
            $table->string('reg_ip', 15)->default('')->nullable()->comment('注册ip');
            $table->integer('login_time')->default(0)->nullable()->comment('最后登录时间');
            $table->string('login_ip', 15)->default('')->nullable()->comment('最后登录IP');
            $table->char('login_country', 2)->default('')->nullable()->comment('最后登录国家');
            $table->string('remember_token', 100)->default('')->nullable()->comment('');
            $table->string('api_token', 60)->default('')->nullable()->comment("登录token");
            $table->string('language', 10)->default('')->nullable()->comment('用戶語言');
            $table->char('currency',3)->default('')->nullable()->comment('使用币种');
            $table->integer('last_actived_time')->default(0)->nullable()->comment('最后活跃时间');
            $table->tinyInteger('online')->default(0)->nullable()->comment('SOCKET链接状态 1:在线 0:离线');
            $table->string('client_id', 20)->default('')->nullable()->comment('SOCKET链接ID');
            $table->string('conn_ip', 15)->default('')->nullable()->comment('链接IP');
            $table->integer('conn_time')->default(0)->nullable()->comment('链接时间');
            $table->integer('close_time')->default(0)->nullable()->comment('链接关闭时间');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->primary(['id']);
            $table->index('username');
            $table->index('agent_id');
            $table->index('create_time');
            $table->index('realname');
        });
        $table = DB::getTablePrefix().'users';
        DB::statement("ALTER TABLE `{$table}` comment'會員表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
