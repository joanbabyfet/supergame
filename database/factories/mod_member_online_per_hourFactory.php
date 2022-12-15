<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class mod_member_online_per_hourFactory extends Factory
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
            'date'          => $this->faker->unique()->date('Y/m/d', 'now'),
            'agent_id'      => '72530ce5b0d4640b30809d0bd702f3d4',
            'timezone'      => 'ETC/GMT-7',
            'h0'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h1'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h2'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h3'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h4'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h5'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h6'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h7'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h8'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h9'            => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h10'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h11'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h12'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h13'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h14'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h15'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h16'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h17'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h18'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h19'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h20'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h21'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h22'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'h23'           => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'create_time'   => 1660961535,
        ];
    }
}
