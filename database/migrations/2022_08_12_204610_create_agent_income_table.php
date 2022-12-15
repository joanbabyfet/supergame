<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAgentIncomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_income', function (Blueprint $table) {
            $table->string('date',10)->default('')->comment('日期');
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->string('timezone',10)->default('')->comment('统计时区');
            $table->decimal('gz_amount',15, 2)->default(0)->nullable()->comment('公庄输赢');
            $table->decimal('commission',15, 2)->default(0)->nullable()->comment('抽水(平台)');
            $table->decimal('platform_income',15, 2)->default(0)->nullable()->comment('游戏总损益');
            $table->decimal('deposit_amount',15, 2)->default(0)->nullable()->comment('存款总额');
            $table->decimal('withdraw_amount',15, 2)->default(0)->nullable()->comment('提款总额');
            $table->decimal('net_amount',15, 2)->default(0)->nullable()->comment('存提净额');
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->primary(['date', 'agent_id']);
        });
        $table = DB::getTablePrefix().'agent_income';
        DB::statement("ALTER TABLE `{$table}` comment'渠道代理收入表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_income');
    }
}
