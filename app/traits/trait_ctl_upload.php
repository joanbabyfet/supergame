<?php


namespace App\traits;


use App\services\serv_upload;
use Illuminate\Http\Request;

/**
 * Trait trait_upload
 * 类继承替代方案, 可在类之间共享接口, 代码复用
 * @package App\traits
 */
trait trait_ctl_upload
{
    /**
     * 普通上传
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, serv_upload $serv_upload)
    {
        $formname   = $request->input('formname', 'file'); //上传表单字段
        $dir        = $request->input('dir', 'image'); //文件上传目录
        $thumb_w    = $request->input('thumb_w', 0); //图片缩略图宽度
        $thumb_h    = $request->input('thumb_h', 0); //图片缩略图高度

        [$status, $ret] = $serv_upload->upload($request, $formname, $dir, $thumb_w, $thumb_h);
        if($status < 0)
        {
            return res_error($serv_upload->get_err_msg($status), $status);
        }

        if(!empty($ret['filename']))
        {
            //TODO 上传到aws，未来再扩充
        }
        return res_success($ret);
    }
}
