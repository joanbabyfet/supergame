<?php


namespace App\traits;


use App\lib\response;
use Illuminate\Support\Facades\DB;

/**
 * 公共数据库操作方法
 * Trait trait_repo_base
 * @package App\traits
 */
trait trait_repo_base
{
    private $model;
    public $page_size = 20; //每页展示几笔
    //AES加解密算法使用key
    public static $crypt_key = 'NfGiFzgqjWPaz';
    public static $msg_maps = [];
    //public $transaction = false; //弃用该做法, 外部是否开启事务,外部开启了事务，要设置成true

    /**
     * 获取列表
     *
     * @param array $conds
     * @return \Illuminate\Support\Collection
     */
    public function lists(array $conds)
    {
        $pagesize = $conds['page_size'] ?? $this->page_size;
        $fields   = $this->get_fields($conds);
        $query = $this->model->select($fields);

        if (!empty($conds['where'])) {
            self::_where($query, $conds['where']);
        }

        if (!empty($conds['where_raw'])) { //自定义筛选
            $query->whereRaw($conds['where_raw'][0], $conds['where_raw'][1]);
        }

        if (isset($conds['with_count'])) { //统计子关联记录的条数
            $query->withCount($conds['with_count']);
        }

        if (!empty($conds['count']))  //筛选后先返回总条数
        {
            if (empty($conds['field'])) {
                $conds['field'] = empty($this->model->getKeyName()) ? '*' :
                    (is_array($this->model->getKeyName()) ? '*' : //定义2个主键以上
                        $this->model->getKeyName());
            }
            $count = $query->count($conds['field']); //分组后返回总条数错误,所以单独处理
        }

        //是否加锁
        if (!empty($conds['lock']) || !empty($conds['share'])) {
            if (!empty($conds['lock'])) {
                $query->lockForUpdate(); //排他鎖(寫鎮),框架默认走主库,事務中使用才生效
            } else if (!empty($conds['share'])) {
                $query->sharedLock(); //共享鎖(讀鎖),框架默认走从库,事務中使用才生效
            }
            //锁表一律只走主库
            $query->useWritePdo();
        }

        if (!empty($conds['order_by'])) {
            $order_by = $conds['order_by'];
            $query->orderby($order_by[0], $order_by[1]);
        }

        //对于app, 不需要计算总条数, 只需返回是否需要下一页
        if (!empty($conds['next_page']))
        {
            $_pagesize = $pagesize;
            ++$pagesize;
        }

        //分页显示数据, 带page参数就给分页, 不带page给all
        if (isset($conds['page']) || isset($conds['offset']))
        {
            $page   = max(1, (isset($conds['page']) ? $conds['page'] : 1));
            $offset = !empty($conds['offset']) ? intval($conds['offset']) :
                 ($page - 1) * (!empty($conds['next_page']) ? $_pagesize : $pagesize);
            $query->limit($pagesize)->offset($offset);
        }
        elseif (isset($conds['limit']))
        {
            $query->limit($conds['limit']);
        }

        if (!empty($conds['group_by'])) {
            $query->groupby($conds['group_by']);
        }

        //统一返回对象
        $data = $query->get();

        //以指定字段當鍵名
        if (!empty($conds['index'])) {
            $data = $data->keyBy($conds['index']);
        }

        if (isset($conds['append'])) //展示扩充字段(默认展示model定义) []=不展示
        {
            $data->each->setAppends($conds['append']);
        }

        //是否加载外表
        if (isset($conds['load'])) {
            $data = $data->load($conds['load']);
        }

        //是否显示总条数
        if (!empty($conds['count'])) {
            $data = collect([
                'total' => $count ?? 0,
                'lists' => $data
            ]);
        }
        elseif(!empty($conds['next_page'])) //是否有下一页
        {
            $has_next_page = 0;
            if (count($data) > $_pagesize) //根据数据总条数判断是否有下一页
            {
                $has_next_page = 1;
                $data->pop(); //丢掉最后一条数据
            }

            $data = collect([
                'next_page' => $has_next_page,
                'lists'     => $data
            ]);
        }
        return $data; //返回对象
    }

    /**
     * 获取单条数据
     *
     * @param array $conds
     * @return mixed
     */
    public function find(array $conds)
    {
        $data = $this->lists(array_merge($conds, [
            'limit' => 1,
            'page'  => null, //会影响取单条数据
            'count' => null, //会影响取单条数据
        ]));
        //返回对象
        return $data->first();
    }

    /**
     * 获取字段的值
     *
     * @param array $conds
     * @return mixed
     */
    public function get_field_value(array $conds)
    {
        $data = $this->find($conds);
        return $data ? current($data->toArray()) : $data;
    }

    /**
     * 获取条数
     *
     * @param array $conds
     * @return mixed
     */
    public function get_count(array $conds)
    {
        if (empty($conds['field'])) {
            $conds['field'] = !empty($this->model->getKeyName()) ? $this->model->getKeyName() : '*';
        }

        $query = $this->model->select();
        //是否加锁
        if (!empty($conds['lock']) || !empty($conds['share'])) {
            if (!empty($conds['lock'])) {
                $query->lockForUpdate(); //排他鎖(寫鎮),框架默认走主库,事務中使用才生效
            } else if (!empty($conds['share'])) {
                $query->sharedLock(); //共享鎖(讀鎖),框架默认走从库,事務中使用才生效
            }
            //锁表一律只走主库
            $query->useWritePdo();
        }
        if (!empty($conds['where'])) {
            self::_where($query, $conds['where']);
        }

        if (!empty($conds['sum'])) {
            $data = $query->sum($conds['field']);
        } elseif (!empty($conds['max'])) {
            $data = $query->max($conds['field']);
        } elseif (!empty($conds['min'])) {
            $data = $query->min($conds['field']);
        } elseif (!empty($conds['avg'])) {
            $data = $query->avg($conds['field']);
        } else {
            $data = $query->count($conds['field']);
        }
        return $data;
    }

    /**
     * 添加数据
     *
     * @param array $data
     * @param string $table
     * @return bool
     */
    public function insert(array $data, $table = '')
    {
        if (empty($data)) return false;

        $query    = $this->model;
        $mutipule = is_array(reset($data)) ? true : false;
        if (!empty($mutipule)) //批量插入
        {
            foreach ($data as $k => $v) {
                ksort($v);
                $data[$k] = $v;
            }

            //框架insert支持批量插入
            $result = $query->insert($data);
        } else //单条插入
        {
            $result = $query->insertGetId($data);
        }
        return $result;
    }

    /**
     * 修改数据
     *
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update(array $data, array $where)
    {
        if (empty($data) || empty($where)) return false;

        $query  = $this->model;
        $result = self::_where($query, $where)->update($data); //筛选

        return $result;
    }

    /**
     * 批量插入或更新, 要设置主键/unique索引 （遇到重复主键更新，否则插入）
     * @param array $data
     * @param $unique_field unique索引
     * @param null $update 要更新的字段
     * @return bool
     */
    public function insertOrUpdate(array $data, $unique_field = [], $update = null)
    {
        if (empty($data)) return false;

        $query    = $this->model;
        $result = $query->upsert($data, $unique_field, $update);
        return $result;
    }

    /**
     * 删除数据
     *
     * @param array $where
     * @return bool
     */
    public function delete(array $where)
    {
        if (empty($where)) return false;

        $query  = $this->model;
        $result = self::_where($query, $where)->delete(); //筛选

        return $result;
    }

    /**
     * 加总
     *
     * @param array $conds
     * @return mixed
     */
    public function get_sum(array $conds)
    {
        return $this->get_count(array_merge($conds, ['sum' => 1]));
    }

    /**
     * 最大值
     *
     * @param array $conds
     * @return mixed
     */
    public function get_max(array $conds)
    {
        return $this->get_count(array_merge($conds, ['max' => 1]));
    }

    /**
     * 最小值
     *
     * @param array $conds
     * @return mixed
     */
    public function get_min(array $conds)
    {
        return $this->get_count(array_merge($conds, ['min' => 1]));
    }

    /**
     * 平均
     *
     * @param array $conds
     * @return mixed
     */
    public function get_avg(array $conds)
    {
        return $this->get_count(array_merge($conds, ['avg' => 1]));
    }

    /**
     * 获取查询字段
     *
     * @param array $conds
     * @return array|mixed
     */
    public function get_fields(array $conds)
    {
        if (empty($conds['fields']) || $conds['fields'] === '*') {
            $fields = ['*'];
        } else {
            $fields = $conds['fields'];
        }

        return $fields;
    }

    /**
     * 处理where条件
     *
     * @param $query
     * @param $where
     * @return bool
     */
    protected static function _where($query, $where)
    {
        if (empty($where)) return false;

        foreach ($where as $column => $value) {
            $boolean = $value[3] ?? 'and'; //and或or
            if (is_numeric($column)) {
                $field = $value[0];
                if (count($value) == 2) {
                    $query = is_array($value[1]) ? $query->whereIn($field, $value[1]) : $query->where($field, $value[1]);
                } else {
                    if (is_array($value[2])) {
                        $query = ($value[1] == 'not in') ? $query->whereNotIn($field, $value[2]) :
                            $query->whereIn($field, $value[2], $boolean);
                    } else {
                        $query = $query->where($field, $value[1], $value[2], $boolean);
                    }
                }
            } else {
                $query = is_array($value) ? $query->whereIn($column, $value) : $query->where($column, $value);
            }
        }
        return $query;
    }

    /**
     * 加解密字段，mysql要定义该字段为blob
     * @param $field 字段
     * @param bool $encode
     * @return string
     */
    public function crypt_field($field, $encode = false)
    {
        $crypt_key = self::$crypt_key;
        $func = !empty($encode) ? 'AES_ENCRYPT' : 'AES_DECRYPT';
        return  "CONVERT({$func}({$field}, '{$crypt_key}') USING utf8)";
    }

    /**
     * 字段值加密
     * @param $value
     * @return string
     */
    public function crypt_value($value)
    {
        $crypt_key = self::$crypt_key;
        $func = 'AES_ENCRYPT';
        return "{$func}('{$value}', '{$crypt_key}')";
    }

    /**
     * 自定义字段
     * @param $string
     * @return mixed
     */
    public function expr($string)
    {
        return DB::raw($string);
    }

    /**
     * 获取分年的表名
     * @param $table
     * @param null $timestamp
     * @param string $format
     * @return string
     */
    public function t($table, $timestamp = null, $format = 'Y')
    {
        if(empty($timestamp)) $timestamp = time();

        $year = date($format, $timestamp);
        return $table .'_'. $year;
    }

    /**
     * 获取异常信息
     *
     * @param $status
     * @return mixed|string
     */
    public function get_err_msg($status)
    {
        return isset(static::$msg_maps[$status]) ? static::$msg_maps[$status] : 'Unknown error!';
    }

    /**
     * 统一异常处理
     *
     * @param \Exception $e
     * @return int|mixed
     */
    public function get_exception_status(\Exception $e)
    {
        $err_code                = $e->getCode();
        $status                  = $err_code >= 0 ? response::UNKNOWN_ERROR_STATUS : $err_code;
        self::$msg_maps[$status] = $e->getMessage();

        return $status;
    }

    /**
     * 抛异常封装
     *
     * @param string $msg
     * @param null $code
     * @throws \Exception
     */
    public function exception($msg = '', $code = null)
    {
        $code = $code ? $code : response::UNKNOWN_ERROR_STATUS;
        throw new \Exception($msg, $code);
    }
}
