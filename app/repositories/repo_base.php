<?php


namespace App\repositories;

/**
 * 基础仓库
 * Class repo_base
 * @package App\repositories
 */
class repo_base
{
    private $model;
    public static $page_size = 20; //每页展示几笔

    public function __construct()
    {

    }

    /**
     * 获取列表
     * @param array $conds
     * @return \Illuminate\Support\Collection
     */
    public function lists(array $conds)
    {
        $pagesize = $conds['page_size'] ?? self::$page_size;
        $fields   = $this->get_fields($conds);
        $query = $this->model->select($fields);

        if (!empty($conds['where'])) {
            self::_where($query, $conds['where']);
        }

        if (!empty($conds['count'])) { //筛选后先返回总条数
            $count = $query->count();
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

        //分页显示数据
        if (isset($conds['page']) || isset($conds['offset'])) {
            $page   = max(1, (isset($conds['page']) ? $conds['page'] : 1));
            $offset = !empty($conds['offset']) ? intval($conds['offset']) : $pagesize * ($page - 1);
            $query->limit($pagesize)->offset($offset);
        } elseif (isset($conds['limit'])) {
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
        return $data; //返回对象
    }

    /**
     * 获取单条数据
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
     * @param array $data
     * @param string $table
     * @return bool
     */
    public function insert_data(array $data, $table = '')
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
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update_data(array $data, array $where)
    {
        if (empty($data) || empty($where)) return false;

        $query  = $this->model;
        $result = self::_where($query, $where)->update($data); //筛选

        return $result;
    }

    /**
     * 删除数据
     * @param array $where
     * @return bool
     */
    public function del_data(array $where)
    {
        if (empty($where)) return false;

        $query  = $this->model;
        $result = self::_where($query, $where)->delete(); //筛选

        return $result;
    }

    /**
     * 加总
     * @param array $conds
     * @return mixed
     */
    public function get_sum(array $conds)
    {
        return $this->get_count(array_merge($conds, ['sum' => 1]));
    }

    /**
     * 最大值
     * @param array $conds
     * @return mixed
     */
    public function get_max(array $conds)
    {
        return $this->get_count(array_merge($conds, ['max' => 1]));
    }

    /**
     * 最小值
     * @param array $conds
     * @return mixed
     */
    public function get_min(array $conds)
    {
        return $this->get_count(array_merge($conds, ['min' => 1]));
    }

    /**
     * 平均
     * @param array $conds
     * @return mixed
     */
    public function get_avg(array $conds)
    {
        return $this->get_count(array_merge($conds, ['avg' => 1]));
    }

    /**
     * 获取查询字段
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
     * @param $query
     * @param $where
     * @return bool
     */
    protected static function _where($query, $where)
    {
        if (empty($where)) return false;

        foreach ($where as $column => $value) {
            if (is_numeric($column)) {
                $field = $value[0];

                if (count($value) == 2) {
                    $query = is_array($value[1]) ? $query->whereIn($field, $value[1]) : $query->where($field, $value[1]);
                } else {
                    if (is_array($value[2])) {
                        $query = ($value[1] == 'not in') ? $query->whereNotIn($field, $value[2]) :
                            $query->whereIn($field, $value[2]);
                    } else {
                        $query = $query->where($field, $value[1], $value[2]);
                    }
                }
            } else {
                $query = is_array($value) ? $query->whereIn($column, $value) : $query->where($column, $value);
            }
        }
        return $query;
    }
}
