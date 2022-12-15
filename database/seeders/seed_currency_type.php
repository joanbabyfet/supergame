<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_currency_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //0 = time();

        $fields = [
            'id',
            'name',
            'en_name',
            'code',
            'symbol',
            'exg_rate',
            'sort',
            'status',
            'create_time',
        ];

        $rows = [
            [1, '人民币', 'Chinese Yuan', 'CNY', '¥', 7.051100, 0, 1, 0],
            [2, '美元', 'United States Dollar', 'USD', '$', 1.000000, 0, 1, 0],
            [3, '港币', 'Hong Kong Dollar', 'HKD', 'HK$', 7.845050, 0, 1, 0],
            [4, '英镑', 'uk', 'UK', 'uk', 1.300000, 0, 1, 0],
            [5, '日元', 'Japanese Yen', 'JPY', 'J￥', 106.587030, 0, 1, 0],
            [6, '加拿大元币', 'Canadian Dollar', 'CAD', 'Can$', 1.331854, 0, 1, 0],
            [7, '瑞尔', 'Cambodian Riel', 'KHR', '៛', 4083.449815, 0, 1, 0],
            [8, '新加坡元', 'Singapore Dollar', 'SGD', '', 1.385330, 0, 1, 0],
            [9, '韩元', 'South Korean Won', 'KRW', '₩', 1210.320000, 0, 1, 0],
        ];

        $insert_data = [];
        foreach ($rows as $row)
        {
            $item = [];
            foreach ($fields as $k => $field)
            {
                $item[$field] = $row[$k];
            }
            $insert_data[] = $item;
        }
        DB::table('currency_type')->insert($insert_data);
    }
}
