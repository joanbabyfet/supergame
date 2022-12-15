<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAgentBalanceDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_balance_data', function (Blueprint $table) {
            $table->char('date',10)->default('')->comment('日期');
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->string('timezone',10)->default('')->comment('统计时区');
            $table->decimal('deposit_amount',15, 2)->default(0)->nullable()->comment('用户充值金额');
            $table->decimal('withdraw_amount',15, 2)->default(0)->nullable()->comment('用户取款金额');
            $table->decimal('agent_balance',15, 2)->default(0)->nullable()->comment('代理额度');
            $table->decimal('remain_balance',15, 2)->default(0)->nullable()->comment('剩馀额度');
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->primary(['date', 'agent_id']);
        });
        $table = DB::getTablePrefix().'agent_balance_data';
        DB::statement("ALTER TABLE `{$table}` comment'渠道代理额度统计记录'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_balance_data');
    }
}
