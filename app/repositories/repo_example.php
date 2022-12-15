<?php


namespace App\repositories;


use App\Models\mod_example;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;

/**
 * 数据操作方法
 * Class repo_example
 * @package App\repositories
 */
class repo_example
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔
    public $detail_cache_key = "example:id:%s";

    public function __construct(mod_example $mod_example)
    {
        $this->model = $mod_example;
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
        $status     = $conds['status'] ?? null;
        $title      = !empty($conds['title']) ? $conds['title'] : '';

        $where = []; //筛选
        $where[] = ['delete_time', '=', 0]; //未删除
        $title and $where[] = ['title', 'like', "%{$title}%"];
        is_numeric($status) and $where[] = ['status', '=', $status];

        $rows = $this->lists([
            'fields'        => $conds['fields'] ?? null,
            'where'         => $where,
            'page'          => $conds['page'] ?? null,
            'page_size'     => $page_size,
            'order_by'      => $order_by,
            'group_by'      => $group_by,
            'count'         => $conds['count'] ?? null, //是否显示总条数
            'limit'         => $conds['limit'] ?? null,
            'field'         => $conds['field'] ?? null,
            'append'        => $conds['append'] ?? null, //展示扩充字段(默认展示) []=不展示
            'lock'          => $conds['lock'] ?? null, //排他鎖
            'share'         => $conds['share'] ?? null, //共享鎖
            'load'          => $conds['load'] ?? null, //加载外表
            'index'         => $conds['index'] ?? null,
            'with_count'    => $conds['with_count'] ?? null,
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
            'id'            => $do == 'edit' ? 'required' : '',
            'cat_id'        => '',
            'title'         => 'required',
            'content'       => '',
            'img'           => '',
            'sort'          => '',
            'status'        => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            unset($data_filter['do'], $data_filter['id']);

            if($do == 'add')
            {
                $row = $this->find(['where' => [['title', '=', $data_filter['title']]]]);
                if($row)
                {
                    $this->exception('标题已经存在', -2);
                }
                $data_filter['id'] = random('web');
                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->insert($data_filter);
                $ret_data['id'] = $data_filter['id'];
            }
            elseif($do == 'edit')
            {
                $data_filter['update_time'] = time();
                $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->update($data_filter, ['id' => $id]);
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
     * 软删除
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function del(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            unset($data_filter['id']);

            $data_filter['delete_time'] = time();
            $data_filter['delete_user'] = defined('AUTH_UID') ? AUTH_UID : '';
            $this->update($data_filter, ['id' => $id]);
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
     * 啟用或禁用
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function change_status(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
            'status'        => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter) || !is_numeric($data_filter['status']))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            unset($data_filter['id']);

            $data_filter['update_time'] = time();
            $this->update($data_filter, ['id' => $id]);
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
}
