<?php

namespace Database\Factories;

use App\Models\mod_winloss;
use Illuminate\Database\Eloquent\Factories\Factory;

class mod_winlossFactory extends Factory
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
        $date = 1658630499;
        $bet_amount = $this->faker->numberBetween(50, 100);

        return [
            'bet_id'                    => make_order_id(),
            'round_id'                  => make_order_id(),
            'game_id'                   => 1,
            'agent_id'                  => '9eff3e40b42fa665b18437d2e91a7b3c',
            'uid'                       => 'cc2ad8fa6f5af2f56349044dd1c369ce',
            'room_id'                   => 1,
            'table_id'                  => 10000001,
            'bet_type'                  => '2,3,4',
            'bet_amount'                => $bet_amount,
            'valid_bet_amount'          => $bet_amount,
            'winloss_amount'            => $this->faker->numberBetween(50, 100),
            'platform_commission'       => $this->faker->numberBetween(50, 100),
            'table_owner_commission'    => $this->faker->numberBetween(50, 100),
            'currency'                  => config('global.currency'),
            'settle_time'               => $date,
            'conn_ip'                   => '127.0.0.1',
            'result'                    => null,
        ];
    }
}
