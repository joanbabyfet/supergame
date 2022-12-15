<?php

namespace Database\Factories;

use App\Models\mod_order_transfer;
use App\services\serv_util;
use Illuminate\Database\Eloquent\Factories\Factory;

class mod_order_transferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        //定义仅该文件能用最大内存，建议不要定义在php.ini会造成每个文件都能用到512M, /生成10万条数据需至少512M
        ini_set('memory_limit', '512M');

        return [
            'id'                => random('web'),
            'trade_no'          => app(serv_util::class)->make_order_id(),
            'origin'            => 1, //代理下单
            'uid'               => 'bafe877aba1c574f8f14bedb18ad7a97',
            'agent_id'          => '9eff3e40b42fa665b18437d2e91a7b3c',
            'transaction_id'    => random('numeric', 10),
            'type'              => mod_order_transfer::DEPOSIT,
            'amount'            => $this->faker->numberBetween(50, 100), //50-100随机数字
            'currency'          => config('global.currency'),
            'pay_status'        => mod_order_transfer::PAY_STATUS_SUCCESS,
            'pay_time'          => $this->faker->unixTime('now'),
            'remark'            => '',
            'create_time'       => $this->faker->unixTime('now'),
        ];
    }
}
