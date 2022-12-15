<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGameRoundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_round', function (Blueprint $table) {
            $table->char('round_id', 22)->default('')->comment("局号");
            $table->integer('game_id')->default(0)->nullable()->comment('游戏id');
            $table->integer('room_id')->default(0)->nullable()->comment("房间id");
            $table->integer('table_id')->default(0)->nullable()->comment("桌子id");
            $table->decimal('gz_amount',15, 2)->default(0)->nullable()->comment('公庄输赢');
            $table->decimal('platform_commission',15, 2)->default(0)->nullable()->comment('平台抽水');
            $table->decimal('table_owner_commission',15, 2)->default(0)->nullable()->comment('桌主抽水');
            $table->integer('settle_time')->default(0)->nullable()->comment("结算時間");
            $table->string('video_url',100)->default('')->nullable()->comment('视频源(暂不使用)');
            $table->string('pic',100)->default('')->nullable()->comment('图片');
            $table->json('result')->nullable()->comment('游戏结果，json格式');
            $table->primary(['round_id']);
            $table->index('table_id');
        });
        $table = DB::getTablePrefix().'game_round';
        DB::statement("ALTER TABLE `{$table}` comment'牌局记录表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_round');
    }
}
