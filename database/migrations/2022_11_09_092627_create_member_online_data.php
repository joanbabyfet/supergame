<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMemberOnlineData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_online_data', function (Blueprint $table) {
            $table->id();
            $table->char('agent_id',32)->default('')->comment('渠道代理id');
            $table->integer('member_online_count')->default(0)->nullable()->comment("在线人数");
            $table->tinyInteger('game1')->default(0)->nullable()->comment('游戏1(lobby)');
            $table->tinyInteger('game2')->default(0)->nullable()->comment('游戏2(PaiGow)');
            $table->tinyInteger('game3')->default(0)->nullable()->comment('游戏3');
            $table->tinyInteger('game4')->default(0)->nullable()->comment('游戏4');
            $table->tinyInteger('game5')->default(0)->nullable()->comment('游戏5');
            $table->tinyInteger('game6')->default(0)->nullable()->comment('游戏6');
            $table->tinyInteger('game7')->default(0)->nullable()->comment('游戏7');
            $table->tinyInteger('game8')->default(0)->nullable()->comment('游戏8');
            $table->tinyInteger('game9')->default(0)->nullable()->comment('游戏9');
            $table->tinyInteger('game10')->default(0)->nullable()->comment('游戏10');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->index('agent_id');
            $table->index('create_time');
        });
        $table = DB::getTablePrefix().'member_online_data';
        DB::statement("ALTER TABLE `{$table}` comment'用户在线人数定时打点表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_online_data');
    }
}
