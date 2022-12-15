<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transfer', function (Blueprint $table) {
            $table->char('id',19)->default('')->comment('id');
            $table->char('trade_no',19)->default('')->nullable()->comment('对外展示交易单号(扩充)');
            $table->tinyInteger('origin')->default(0)->nullable()->comment('订单来源 1=玩家下单 2=后台下单 3=結算派彩 4=建桌低消预扣/退款');
            $table->char('uid',32)->default('')->nullable()->comment("用戶id");
            $table->char('agent_id',32)->default('')->nullable()->comment('渠道代理id');
            $table->char('transaction_id',32)->default('')->nullable()->comment('渠道代理的订单id/桌号');
            $table->tinyInteger('type')->default(1)->nullable()->comment('交易类型 1=充值 2=提现');
            $table->decimal('amount',15, 2)->default(0)->nullable()->comment('金额');
            $table->char('currency',3)->default('')->nullable()->comment('币种');
            $table->tinyInteger('pay_status')->default(0)->nullable()->comment('支付状态 0=待支付 1=成功 -1=失败');
            //$table->tinyInteger('pay_type')->default(0)->nullable()->comment('支付方式，1=现金 2=支付宝 3=微信 4=系统更改');
            $table->tinyInteger('callback_status')->default(0)->nullable()->comment('回调状态 0=未确认 1=成功 -1=失败');
            $table->integer('pay_time')->default(0)->nullable()->comment("支付時間");
            $table->integer('callback_time')->default(0)->nullable()->comment("回调時間");
            $table->string('remark',200)->default('')->nullable()->comment('备注');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->primary(['id']);
            $table->index('agent_id');
            $table->index('uid');
            $table->index('pay_time');
        });
        $table = DB::getTablePrefix().'order_transfer';
        DB::statement("ALTER TABLE `{$table}` comment'转帐记录表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_transfer');
    }
}
