<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->Increments('id')->comment("id");
            $table->char('game_id',32)->default('')->nullable()->comment('游戏id');
            $table->string('name',50)->default('')->nullable()->comment('房间名称');
            $table->string('cover_img',100)->default('')->nullable()->comment('封面图片');
            $table->string('video_url',100)->default('')->nullable()->comment('视频源');
            $table->string('desc',300)->default('')->nullable()->comment('房间描述');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->index('name');
            $table->index('create_time');
        });
        $table = DB::getTablePrefix().'room';
        DB::statement("ALTER TABLE `{$table}` comment'房间表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room');
    }
}
