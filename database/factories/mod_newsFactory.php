<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class mod_newsFactory extends Factory
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
            'id'            => random('web'),
            'cat_id'        => 1,
            'title'         => $this->faker->words(3, true), //回3个单词，false表示返回一个数组；true表示返回一个字符串，单词之间用空格分开
            'content'       => $this->faker->realText(200),
            'img'           => '',
            'sort'          => 0,
            'status'        => 1,
            'create_time'   => $this->faker->unixTime('now'),
            'create_user'   => '1',
        ];
    }
}
