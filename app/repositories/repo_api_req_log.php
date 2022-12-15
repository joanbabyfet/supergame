<?php


namespace App\repositories;


use App\Models\mod_api_req_log;
use App\traits\trait_repo_base;

class repo_api_req_log
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_api_req_log $mod_example)
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
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['req_time', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $req_data   = !empty($conds['req_data']) ? $conds['req_data']:'';
        $res_data   = !empty($conds['res_data']) ? $conds['res_data']:'';
        $type       = !empty($conds['type']) ? $conds['type']:'';
        $date_start   = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';

        $where = []; //筛选
        $type and $where[] = ['type', '=', $type];
        $date_start and $where[] = ['req_time', '>=', (int)$date_start]; //开始时间
        $date_end and $where[] = ['req_time', '<=', (int)$date_end]; //结束时间
        $req_data and $where[] = ['req_data', 'like', "%{$req_data}%"];
        $res_data and $where[] = ['res_data', 'like', "%{$res_data}%"];

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
     * 添加
     * @param array $data
     * @return int|mixed
     */
    public function add_log(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'type'      => 'required', //哪端调用
            'url'       => 'required', //请求接口地址
            'method'    => 'required', //请求接口方式 GET/POST/PUT
            'req_data'  => '',
            'res_data'  => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $req_data = empty($data_filter['req_data']) ? request()->all() : $data_filter['req_data'];
            $res_data = $data_filter['res_data'];
            $req_data = is_array($req_data) || is_object($req_data) ?
                json_encode($req_data, JSON_UNESCAPED_UNICODE) : $req_data;
            $res_data = is_array($res_data) || is_object($res_data) ?
                json_encode($res_data, JSON_UNESCAPED_UNICODE) : $res_data;
            $req_ip = request()->ip();

            $data_filter['uid'] = defined('AUTH_UID') ? AUTH_UID : '';
            $data_filter['req_data'] = $req_data;
            $data_filter['res_data'] = $res_data;
            $data_filter['req_time'] = time();
            $data_filter['req_country'] = ip2country($req_ip);
            $data_filter['req_ip'] = $req_ip;
            $this->insert($data_filter);

            //记录日志
            logger(__METHOD__, [
                'type'              => $data_filter['type'],
                'url'               => $data_filter['url'],
                'method'            => $data_filter['method'],
                'uid'               => $data_filter['uid'],
                'header'            => request()->header(), //头部参数也要保存
                'req_data'          => $data_filter['req_data'],
                'res_data'          => $data_filter['res_data'],
                'req_time'          => $data_filter['req_time'],
                'req_ip'            => $data_filter['req_ip'],
            ]);
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
            'id'           => 'required',
        ], $data);

        $status = 1;
        try
        {
            $id = $data_filter['id'];
            unset($data_filter['id']);

            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }
            $this->delete(['_id' => $id]);
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
