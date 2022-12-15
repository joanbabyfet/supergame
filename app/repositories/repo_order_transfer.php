<?php


namespace App\repositories;


use App\Models\mod_order_transfer;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;

class repo_order_transfer
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_order_transfer $mod_order_transfer)
    {
        $this->model = $mod_order_transfer;
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
        $type       = !empty($conds['type']) ? $conds['type'] : ''; //交易类型 1=充值 2=提现
        $uid        = !empty($conds['uid']) ? $conds['uid'] : '';
        $agent_id   = !empty($conds['agent_id']) ? $conds['agent_id'] : '';
        $pay_status = !empty($conds['pay_status']) ? $conds['pay_status'] : '';
        $date_start = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';
        $origin   = !empty($conds['origin']) ? $conds['origin'] : [];

        $where = []; //筛选
        $origin and $where[] = ['origin', 'in', $origin];
        $type and $where[] = ['type', '=', $type];
        $uid and $where[] = ['uid', 'in', $uid];
        $agent_id and $where[] = ['agent_id', '=', $agent_id];
        $pay_status and $where[] = ['pay_status', '=', $pay_status];
        $date_start and $where[] = ['pay_time', '>=', $date_start]; //开始时间
        $date_end and $where[] = ['pay_time', '<=', $date_end]; //结束时间

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
     * 创建订单
     * @param array $data
     * @return int
     */
    public function create(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'origin'            => 'required', //订单来源
            'uid'               => 'required',
            'agent_id'          => 'required',
            'transaction_id'    => '', //渠道代理的订单id
            'type'              => 'required', //交易类型 1=充值 2=提现
            'amount'            => 'required',
            'currency'          => '',
            'remark'            => '', //备注
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $row = $this->find(['where' => [['id', '=', $data_filter['transaction_id']]]]);
            if($row)
            {
                $this->exception('订单号重复', -2);
            }

            //创建订单
            $data_filter['id'] = $id = $this->make_order_id();
            $data_filter['trade_no'] = $id;
            $data_filter['pay_status'] = mod_order_transfer::PAY_STATUS_SUCCESS; //支付成功
            $data_filter['pay_time'] = time(); //支付时间
            $data_filter['create_time'] = time();
            $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
            $this->insert($data_filter);
            $ret_data['id'] = $id;
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
     * 生成订单id, bigint支持数字大小范围刚好19位
     * @param int $num 后缀几个数字
     * @return string
     */
    public function make_order_id($num = 7)
    {
        $uniqid = random('numeric', $num);
        return date("ymdHis").$uniqid;
    }

    /**
     * 获取某代理提款金额
     * @param $agent_id
     * @return int|mixed
     */
    public function get_agent_withdraw_amount($agent_id)
    {
        if (empty($agent_id)) return 0;

        $amount = $this->get_sum([
            'field'     => 'amount',
            'where'     => [
                ['agent_id', '=', $agent_id],
                ['type', '=', mod_order_transfer::WITHDRAW],
                ['pay_status', '=', mod_order_transfer::PAY_STATUS_SUCCESS]
            ]
        ]);
        return $amount;
    }
}
