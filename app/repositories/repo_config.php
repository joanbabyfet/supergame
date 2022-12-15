<?php


namespace App\repositories;


use App\Models\mod_config;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\Redis;

class repo_config
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔
    private $module = 'config';
    public $cache_key = "sys_db_config";

    public function __construct(mod_config $mod_config)
    {
        $this->model = $mod_config;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['create_time', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $name       = !empty($conds['name']) ? $conds['name']:'';
        $group       = !empty($conds['group']) ? $conds['group']:'';

        $where = []; //筛选
        $name and $where[] = ['name', 'like', "%{$name}%"]; //变量名
        $group and $where[] = ['group', '=', $group]; //分组

        $rows = $this->lists([
            'fields'    => $conds['fields'] ?? null,
            'where'     => $where,
            'page'      => $conds['page'] ?? null,
            'page_size' => $page_size,
            'order_by'  => $order_by,
            'group_by'  => $group_by,
            'count'     => $conds['count'] ?? null, //是否显示总条数
            'limit'     => $conds['limit'] ?? null,
            'field'     => $conds['field'] ?? null,
            'append'    => $conds['append'] ?? null, //展示扩充字段(默认展示) []=不展示
            'lock'      => $conds['lock'] ?? null, //排他鎖
            'share'     => $conds['share'] ?? null, //共享鎖
            'load'      => $conds['load'] ?? null, //加载外表
            'index'     => $conds['index'] ?? null,
        ])->toArray();
        return $rows;
    }

    /**
     * 添加或修改
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function save(array $data, &$ret_data = [])
    {
        $do             = isset($data['do']) ? $data['do'] : '';
        //参数过滤
        $data_filter = data_filter([
            'do'        => 'required',
            'type'      => 'required',
            'name'      => 'required',
            'value'     => 'required',
            'title'     => 'required',
            'group'     => 'required',
            'sort'      => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $name     = $data_filter['name'];
            unset($data_filter['do']);

            if($do == 'add')
            {
                $row = $this->find(['where' => [['name', '=', $name]]]);
                if($row)
                {
                    $this->exception('变量名称已经存在', -2);
                }

                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->insert($data_filter);
            }
            elseif($do == 'edit')
            {
                $data_filter['update_time'] = time();
                $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->update($data_filter, ['name' => $name]);
            }
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }

    /**
     * 删除
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function del(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'name'           => 'required',
        ], $data);

        $status = 1;
        try
        {
            $name = $data_filter['name'];
            unset($data_filter['name']);

            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }
            $this->delete(['name' => $name]);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }

        return $status;
    }

    /**
     * 获取变量值从库
     * @param $key
     * @return string
     */
    public function get_value($key)
    {
        $config = $this->find(['where' => [
            ['name', '=', $key]
        ]]);
        return $config['value'] ?? '';
    }

    /**
     * 获取配置, 在程序中调用 $this->repo_config->get(key)
     * @param $key 变量名
     * @param array $extra 其他信息
     * @return float|int|mixed|string
     */
    public function get($key, array $extra=[])
    {
        //参数过滤
        $data_filter = data_filter([
            'type'      => '', //返回类型
            'default'   => '', //返回默认值
            'group'     => '', //分组
        ], $extra);

        $configs = [];
        if (empty($data_filter['group'])) //没有给分组,则获取所有组别
        {
            if (empty($configs))
            {
                $this->module = '';
                $configs = $this->cache(); //获取配置缓存
            }
            $val = $data_filter['default'] ?? null;

            foreach ($configs as $group => $config)
            {
                if (isset($config[$key]))
                {
                    $val = $config[$key];
                    break;
                }
            }
        }
        else
        {
            $this->module = $data_filter['group'];
            $db_config = $this->cache(); //获取自redis
            $val = isset($db_config[$key]) ? $db_config[$key] : null;
        }

        if ($val === null)
        {
            return $data_filter['default'] ?? '';
        }

        switch ($data_filter['type'] ?? 'text')
        {
            case 'int':
                return (int)$val;
            case 'text':
            case 'string':
                return (string)$val;
            case 'float':
                return (float)$val;
        }
        return $val;
    }

    /**
     * 设置配置缓存
     * @param bool $update 是否更新缓存, 默认 false
     * @return array|mixed
     */
    public function cache(bool $update = false)
    {
        $configs = Redis::get($this->cache_key);

        if($update || empty($configs))
        {
            $rows = $this->get_list([ //获取所有配置
                'fields'    => ['name', 'value', 'group']
            ]);

            $configs = [];
            foreach($rows as $row)
            {
                $configs[$row['group']][$row['name']] = $row['value'];
            }
            Redis::set($this->cache_key, json_encode($configs, JSON_UNESCAPED_UNICODE)); //ttl不过期, 键名会一直存在redis
        }

        $configs = is_array($configs) ? $configs : json_decode($configs, true);
        !empty($this->module) and $configs = isset($configs[$this->module]) ? $configs[$this->module] : [];

        return $configs;
    }
}
