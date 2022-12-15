<?php


namespace App\repositories;


use App\Models\mod_app_key;
use App\traits\trait_repo_base;

class repo_app_key
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔
    public $detail_cache_key = "app_key_id:%s";

    public function __construct(mod_app_key $mod_app_key)
    {
        $this->model = $mod_app_key;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['create_time', 'asc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $app_id       = !empty($conds['app_id']) ? $conds['app_id'] : '';

        $where = []; //筛选
        $app_id and $where[] = ['app_id', '=', $app_id];

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
            'do'            => 'required',
            'app_id'        => 'required',
            'app_key'       => $do == 'edit' ? '' : 'required',
            'agent_id'      => 'required',
            'desc'          => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['app_id'] ?? '';
            unset($data_filter['do']);

            if($do == 'add')
            {
                $row = $this->find(['where' => [['app_id', '=', $id]]]);
                if($row)
                {
                    $this->exception(trans('api_channel_has_bind_app_key'), -2);
                }

                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->insert($data_filter);
            }
            elseif($do == 'edit')
            {
                $data_filter['update_time'] = time();
                $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->update($data_filter, ['app_id' => $id]);
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
            'app_id'            => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['app_id'] ?? '';
            unset($data_filter['app_id']);

            $this->delete(['app_id' => $id]); //直接干掉, 不做软删除
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
     * 生成app_id和密匙
     * @return array
     */
    public function create_app_key()
    {
        return [
            'app_id'  => date("ymdHis").random('numeric', 7),
            'app_key' => random('web'),
        ];
    }
}
