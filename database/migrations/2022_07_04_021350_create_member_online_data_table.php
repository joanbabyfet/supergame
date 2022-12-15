<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMemberOnlineDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_online_per_hour', function (Blueprint $table) {
            $table->char('date',10)->default('')->comment('日期');
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->string('timezone',10)->default('')->comment('统计时区');
            $table->integer('h0')->default(0)->nullable()->comment("00:00在线人数");
            $table->integer('h1')->default(0)->nullable()->comment("01:00在线人数");
            $table->integer('h2')->default(0)->nullable()->comment("02:00在线人数");
            $table->integer('h3')->default(0)->nullable()->comment("03:00在线人数");
            $table->integer('h4')->default(0)->nullable()->comment("04:00在线人数");
            $table->integer('h5')->default(0)->nullable()->comment("05:00在线人数");
            $table->integer('h6')->default(0)->nullable()->comment("06:00在线人数");
            $table->integer('h7')->default(0)->nullable()->comment("07:00在线人数");
            $table->integer('h8')->default(0)->nullable()->comment("08:00在线人数");
            $table->integer('h9')->default(0)->nullable()->comment("09:00在线人数");
            $table->integer('h10')->default(0)->nullable()->comment("10:00在线人数");
            $table->integer('h11')->default(0)->nullable()->comment("11:00在线人数");
            $table->integer('h12')->default(0)->nullable()->comment("12:00在线人数");
            $table->integer('h13')->default(0)->nullable()->comment("13:00在线人数");
            $table->integer('h14')->default(0)->nullable()->comment("14:00在线人数");
            $table->integer('h15')->default(0)->nullable()->comment("15:00在线人数");
            $table->integer('h16')->default(0)->nullable()->comment("16:00在线人数");
            $table->integer('h17')->default(0)->nullable()->comment("17:00在线人数");
            $table->integer('h18')->default(0)->nullable()->comment("18:00在线人数");
            $table->integer('h19')->default(0)->nullable()->comment("19:00在线人数");
            $table->integer('h20')->default(0)->nullable()->comment("20:00在线人数");
            $table->integer('h21')->default(0)->nullable()->comment("21:00在线人数");
            $table->integer('h22')->default(0)->nullable()->comment("22:00在线人数");
            $table->integer('h23')->default(0)->nullable()->comment("23:00在线人数");
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->primary(['date', 'agent_id']);
        });
        $table = DB::getTablePrefix().'member_online_per_hour';
        DB::statement("ALTER TABLE `{$table}` comment'會員在线每小时数据'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_online_per_hour');
    }
}
