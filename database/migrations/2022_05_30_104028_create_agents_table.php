<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->char('id',32)->default('')->comment('用戶id');
            $table->char('pid',32)->default('0')->comment('上级id');
            $table->string('username', 20)->default('')->nullable()->comment("用户帐号");
            $table->string('password', 60)->default('')->nullable()->comment("用户密码");
            $table->string('avatar',100)->default('')->nullable()->comment('头像');
            $table->string('realname',50)->default('')->nullable()->comment('名称');
            $table->string('desc',200)->default('')->nullable()->comment('说明');
            $table->decimal('agent_balance',15, 2)->default(0)->nullable()->comment('代理额度');
            $table->decimal('remain_balance',15, 2)->default(0)->nullable()->comment('剩馀额度');
            $table->string('email', 100)->default('')->nullable()->comment('信箱');
            $table->string('phone_code', 5)->default('')->nullable()->comment('手機號國碼');
            $table->string('phone', 20)->default('')->nullable()->comment('手機號');
            $table->tinyInteger("status")->default(1)->nullable()->comment('帐号状态 1:正常 0:禁止登陆');
            $table->string('safe_ips', 200)->default('')->nullable()->comment('登陆IP限制');
            $table->tinyInteger('is_first_login')->default(1)->nullable()->comment('是否首次登录');
            $table->tinyInteger('is_audit')->default(0)->nullable()->comment('登陆是否需要后台进行人工审核 0: 不需要 1:需要');
            $table->integer('session_expire')->default(1440)->nullable()->comment('SESSION有效期，默认24分钟');
            $table->string('session_id', 50)->default('')->nullable()->comment('登陆时session_id');
            $table->string('reg_ip', 15)->default('')->nullable()->comment('注册ip');
            $table->integer('login_time')->default(0)->nullable()->comment('最后登录时间');
            $table->string('login_ip', 15)->default('')->nullable()->comment('最后登录IP');
            $table->char('login_country', 2)->default('')->nullable()->comment('最后登录国家');
            $table->string('remember_token', 100)->default('')->nullable()->comment('');
            $table->string('api_token', 60)->default('')->nullable()->comment("登录token");
            $table->string('contact_person',50)->default('')->nullable()->comment('联系人');
            $table->string('domain', 100)->default('')->nullable()->comment("代理域名");
            $table->char('currency',3)->default('')->nullable()->comment('使用币种');
            $table->tinyInteger('wallet_type')->default(0)->nullable()->comment('钱包类型 1=转帐钱包 2=单一钱包');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->primary(['id']);
            $table->index('username');
            $table->index('realname');
            $table->index('create_time');
            $table->index('pid');
        });
        $table = DB::getTablePrefix().'agents';
        DB::statement("ALTER TABLE `{$table}` comment'渠道代理表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agents');
    }
}
