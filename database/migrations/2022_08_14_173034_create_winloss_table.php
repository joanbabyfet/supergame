<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWinlossTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winloss', function (Blueprint $table) {
            $table->char('bet_id', 19)->default('')->comment("注单号");
            $table->char('round_id', 22)->default('')->comment("局号");
            $table->integer('game_id')->default(0)->nullable()->comment('游戏id');
            $table->char('agent_id',32)->default('')->nullable()->comment('渠道代理id');
            $table->char('uid',32)->default('')->nullable()->comment('用戶id');
            $table->integer('room_id')->default(0)->nullable()->comment("房间id");
            $table->integer('table_id')->default(0)->nullable()->comment("桌子id");
            $table->string('bet_type', 10)->default('')->nullable()->comment('下注类型 1=当庄 2=帮庄 3=公庄 4=买闲');
            $table->decimal('bet_amount',15, 2)->default(0)->nullable()->comment('下注额');
            $table->decimal('valid_bet_amount',15, 2)->default(0)->nullable()->comment('有效下注额');
            $table->decimal('winloss_amount',15, 2)->default(0)->nullable()->comment('派彩金额, 包含下注额');
            $table->decimal('platform_commission',15, 2)->default(0)->nullable()->comment('平台抽水');
            $table->decimal('table_owner_commission',15, 2)->default(0)->nullable()->comment('桌主抽水');
            $table->decimal('gz_amount',15, 2)->default(0)->nullable()->comment('玩家公庄输赢 赢公庄为负(-) 输公垐为正(+)');
            $table->char('currency',3)->default('')->nullable()->comment('币种');
            $table->integer('settle_time')->default(0)->nullable()->comment("结算時間");
            $table->string('conn_ip', 15)->default('')->nullable()->comment('用户链接IP');
            $table->json('result')->nullable()->comment('游戏结果，json格式 0=tie 1=win -1=loss');
            $table->primary(['bet_id']);
            $table->index('uid');
            $table->index('settle_time');
            $table->index('table_id');
            $table->index('round_id');
        });
        $table = DB::getTablePrefix().'winloss';
        DB::statement("ALTER TABLE `{$table}` comment'投注记录表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('winloss');
    }
}
