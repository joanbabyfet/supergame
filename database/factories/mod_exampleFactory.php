<?php

namespace Database\Factories;

use App\Models\mod_example;
use Illuminate\Database\Eloquent\Factories\Factory;

class mod_exampleFactory extends Factory
{
    protected $model = mod_example::class;

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
            'id'            => random('web'),
            'cat_id'        => $this->faker->numberBetween(0, 3), // 数字在 0-3 之间随机
            'title'         => $this->faker->words(3, true), //回3个单词，false表示返回一个数组；true表示返回一个字符串，单词之间用空格分开
            'content'       => $this->faker->realText(200),
            'img'           => '',
            'file'          => '',
            'is_hot'        => 0,
            'sort'          => 0,
            'status'        => 1,
            'create_time'   => $this->faker->unixTime('now'),
            'create_user'   => '1',
        ];
    }
}
