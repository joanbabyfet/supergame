<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game', function (Blueprint $table) {
            $table->Increments('id')->comment("游戏id");
            $table->string('code', 10)->default('')->nullable()->comment("游戏代号");
            $table->string('name', 50)->default('')->nullable()->comment("游戏名称");
            $table->char('type',5)->default('')->nullable()->comment('游戏类型 LC=真人视讯 CB=棋牌 SB=体育游戏 SL=老虎机 LK=彩票 FH=捕鱼 PK=扑克 OT=其他');
            $table->string('cover_img',100)->default('')->nullable()->comment('游戏图片');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
        });
        $table = DB::getTablePrefix().'game';
        DB::statement("ALTER TABLE `{$table}` comment'游戏表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game');
    }
}
