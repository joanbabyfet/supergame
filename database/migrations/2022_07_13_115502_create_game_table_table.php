<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGameTableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_table', function (Blueprint $table) {
            $table->bigIncrements('id')->from(10000001)->comment("id");
            $table->integer('game_id')->default(0)->nullable()->comment('游戏id');
            $table->char('agent_id',32)->default('')->nullable()->comment('渠道代理id');
            $table->integer('room_id')->default(0)->nullable()->comment("房间id");
            $table->string('name',50)->default('')->nullable()->comment('桌子名称');
            $table->tinyInteger('type')->default(1)->nullable()->comment('类型：1=现金 2=信用');
            $table->char('uid',32)->default('')->nullable()->comment('用戶id/桌主');
            $table->integer('start_time')->default(0)->nullable()->comment("开始时间");
            $table->integer('end_time')->default(0)->nullable()->comment("结束時間");
            $table->decimal('bet_min',15, 2)->default(0.00)->nullable()->comment('最小下注额');
            $table->decimal('duration',15, 2)->default(0.00)->nullable()->comment('游戏时长几天');
            $table->tinyInteger('is_secret')->default(0)->nullable()->comment('是否加密：0=否 1=是');
            $table->string('password', 60)->default('')->nullable()->comment("桌子密码");
            $table->integer('commission')->default(0)->nullable()->comment('抽水% (建桌时玩家所选)');
            $table->decimal('min_order',15, 2)->default(0.00)->nullable()->comment('建桌低消');
            $table->decimal('deduct_fee',15, 2)->default(0.00)->nullable()->comment('实际扣除费用(建桌低消)');
            $table->json('config')->nullable()->comment('配置信息，json格式');
            $table->string('video_url',100)->default('')->nullable()->comment('视频源');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用(已删除) 1=启用 2=等待执行(Pending)');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->index('name');
            $table->index('agent_id');
            $table->index('uid');
            $table->index('room_id');
            $table->index('end_time');
            $table->index('create_time');
        });
        $table = DB::getTablePrefix().'game_table';
        DB::statement("ALTER TABLE `{$table}` comment'游戏桌表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_table');
    }
}
