<?php


namespace App\services;


use App\Models\mod_menu;
use App\repositories\repo_menu;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\Redis;

class serv_menu
{
    use trait_service_base;

    private $repo_menu;
    private $serv_util;

    public function __construct(
        repo_menu $repo_menu,
        serv_util $serv_util
    )
    {
        $this->repo_menu    = $repo_menu;
        $this->serv_util    = $serv_util;
    }

    //获取菜单列表
    public function get_menu_data(array $data)
    {
        $default_guard = get_default_guard(); //默认守卫
        $id = empty($data['id']) ? 0 : $data['id'];
        $guard = empty($data['guard']) ? $default_guard : $data['guard'];
        $order_by = empty($data['order_by']) ? ['create_time', 'asc'] : $data['order_by'];
        $purviews = empty($data['purviews']) ? [] : $data['purviews'];
        $is_permission = empty($data['is_permission']) ? 0 : $data['is_permission'];
        $guard == config('global.admin.guard') and $cache_key = $this->repo_menu->cache_key_admin;
        $guard == config('global.adminag.guard') and $cache_key = $this->repo_menu->cache_key_agent;

        //获取菜单缓存
        $menus = Redis::get($cache_key);
        if(empty($menus))
        {
            $menus = $this->repo_menu->get_list([
                'fields'        => ['id', 'parent_id', 'name', 'url', 'icon', 'perms', 'is_show'],
                'index'         => 'id',
                'status'        => mod_menu::ENABLE,
                'guard_name'    =>  $guard,
                'order_by'      =>  $order_by,
            ]);
            Redis::set($cache_key, json_encode($menus, JSON_UNESCAPED_UNICODE));
        }
        $menus = is_array($menus) ? $menus : json_decode($menus, true);
        $ids = empty($id) ? [] : array_merge($this->serv_util->get_all_child_ids($menus, $id), [$id]);

        $rows = [];
        foreach ($menus as $k => $row)
        {
            //有送分类id,则干掉自己及所有下级id
            if(in_array($k, $ids)) continue;
            $rows[] = $row;
        }
        //遍历过滤,返回权限关联为空或在用户权限列表里
        if($is_permission)
        {
            //重置数组键名从0开始
            $rows = array_values(array_filter($rows, function($item) use ($purviews) {
                return in_array('*', $purviews) ||
                    empty($item['perms']) ||
                    (!empty($item['perms']) && in_array($item['perms'], $purviews));
            }));
        }
        return $rows;
    }
}
