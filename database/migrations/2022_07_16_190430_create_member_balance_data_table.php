<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMemberBalanceDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_balance_data', function (Blueprint $table) {
            $table->char('date',10)->default('')->comment('日期');
            $table->char('uid',32)->default('')->comment('用户id');
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->string('timezone',10)->default('')->comment('统计时区');
            $table->decimal('deposit_amount',15, 2)->default(0)->nullable()->comment('用户充值金额');
            $table->decimal('withdraw_amount',15, 2)->default(0)->nullable()->comment('用户取款金额');
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->primary(['date', 'uid']);
        });
        $table = DB::getTablePrefix().'member_balance_data';
        DB::statement("ALTER TABLE `{$table}` comment'用户额度记录'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_balance_data');
    }
}
