<?php


namespace App\services;


class serv_array
{
    /**
     * 转一维数组
     * @param array $array
     * @param array $key_pair
     * @return array
     */
    public function one_array(array $array,array $key_pair)
    {
        list($key,$vkey) = $key_pair;

        $result_array = array_column($array, $vkey, $key);
        $result_array = array_filter($result_array); //遍历数组中每个值并干掉值为0

        return $result_array;
    }

    /**
     * IN (ID) 使用的一维数组
     *
     * @param array $array
     * @param $field
     * @return array
     */
    public function sql_in(array $array, $field)
    {
        return array_unique(array_column($array,$field)) + [-1]; //array_unique去重
    }
}
