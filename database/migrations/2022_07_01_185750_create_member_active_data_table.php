<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMemberActiveDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_active_data', function (Blueprint $table) {
            $table->char('date',10)->default('')->comment('日期');
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->string('timezone',10)->default('')->comment('统计时区');
            $table->integer('member_active_count')->default(0)->nullable()->comment("会员总登入人数");
            $table->integer('d1')->default(0)->nullable()->comment("次日活跃");
            $table->integer('d3')->default(0)->nullable()->comment("3日活跃");
            $table->integer('d7')->default(0)->nullable()->comment("7日活跃");
            $table->integer('d14')->default(0)->nullable()->comment("14日活跃");
            $table->integer('d30')->default(0)->nullable()->comment("30日活跃");
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->primary(['date', 'agent_id']);
        });
        $table = DB::getTablePrefix().'member_active_data';
        DB::statement("ALTER TABLE `{$table}` comment'會員活跃数据'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_active_data');
    }
}
