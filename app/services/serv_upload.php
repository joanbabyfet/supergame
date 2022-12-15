<?php


namespace App\services;

use App\traits\trait_service_base;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class serv_upload
{
    use trait_service_base;

    private $serv_util;
    private $allowed_types;
    private $max_size;

    //上传目录数量
    public static $dir_num = 128;

    public function __construct(serv_util $serv_util)
    {
        $this->serv_util        = $serv_util;
        $this->allowed_types    = config('global.allowed_types');
        $this->max_size         = config('global.max_size');
    }

    /**
     * 普通上传
     *
     * @param string $formname
     * @param string $dir
     * @param int $thumb_width
     * @param float $thumb_height
     * @return array
     */
    public function upload(Request $request, $formname = 'file', $dir = 'image', $thumb_w = 0, $thumb_h = 0)
    {
        $save_name  = $request->input('save_name', ''); //自定义上传文件名
        $status     = 1;
        $ret = [];
        try
        {
            // 判断是否存在上传的文件
            if ($request->hasFile($formname))
            {
                $file = $request->file($formname);
                $upload_dir = storage_path('app/public/')."/{$dir}";

                // 目录不存在则生成
                if (!$this->serv_util->path_exists($upload_dir))
                {
                    $this->exception('保存目录不存在', -1);
                }

                $filesize = $file->getSize(); //原文件大小

                $realname = $file->getClientOriginalName(); //原文件名 testimg.jpg
                $file_ext = $file->getClientOriginalExtension();  //扩展名 jpg
                //$tmp_name  = $file->getFilename(); //临时文件名 php1Z8ML9
                $tmp_name  = $file->getRealPath(); //临时文件名 /Applications/MAMP/tmp/php/php1Z8ML9
                log::debug("上传开始：{$realname}");//记录日志

                $allowed_types = explode('|', $this->allowed_types);
                if (!in_array($file_ext, $allowed_types))
                {
                    $this->exception('上传的文件格式不符合规定', -2);
                }

                // 判断文件大小
                if ($this->max_size != 0)
                {
                    $max_size = $this->max_size * 1024;
                    if ($filesize > $max_size)
                    {
                        $this->exception('上传的文件太大', -3);
                    }
                }

                //md5_file要给绝对定址, 否则会报错, 兼容可自定义文件名
                $filename = empty($save_name) ? md5_file($tmp_name).'.'.$file_ext :
                    $save_name.'.'.$file_ext;

                // 如果需要分隔目录上传
                if (self::$dir_num > 0)
                {
                    $dir_num = $this->serv_util->str2number($filename, self::$dir_num);
                    $this->serv_util->path_exists($upload_dir.'/'.$dir_num);
                    $filename = $dir_num.'/'.$filename;
                }
                else
                {
                    $filename = $dir.'/'.$filename;
                }

                $dir_num = empty($dir_num) ? '':$dir_num;
                //將文件從暫存位置（由PHP設定來決定）移動至你指定的永久保存位置
                if ($file->move($upload_dir.'/'.$dir_num, $filename))
                {
                    @chmod($upload_dir.'/'.$filename, 0777);

                    $filelink = (self::$dir_num > 0) ? Storage::disk('public')->url("{$dir}/{$filename}") :
                        Storage::disk('public')->url("{$filename}");

                    if ($thumb_w > 0 || $thumb_h > 0)
                    {
                        [$status, $filename, $filelink] = $this->thumb($upload_dir, $filename, $file_ext, $thumb_w, $thumb_h);

                        if($status < 0)
                        {
                            $this->exception('缩图保存目录不存在', -4);
                        }
                    }
                    $ret = [
                        'realname' => $realname,
                        'filename' => $filename,
                        'filelink' => $filelink,
                    ];

                    log::debug("上传成功：{$realname}->{$ret['filename']}");//记录日志
                }
            }
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return [$status, $ret];
    }

    /**
     * 缩图
     * @param $upload_dir
     * @param $filename
     * @param string $file_ext
     * @param int $thumb_w
     * @param int $thumb_h
     * @return array
     */
    public function thumb( $upload_dir, $filename, $file_ext = 'jpg', $thumb_w = 0, $thumb_h = 0 )
    {
        $status = 1;
        $filelink = '';
        try
        {
            $pathinfo = getimagesize($upload_dir.'/'.$filename);
            $width  = $pathinfo[0]; //上傳圖片原始寬
            $height = $pathinfo[1]; //上傳圖片原始高

            // 缩略图的临时目录
            $filepath_tmp = storage_path('app/public').'/tmp';
            // 缩略图的临时文件名
            $filename_tmp = md5($filename).'.'.$file_ext;

            // 目录不存在则生成
            if (!$this->serv_util->path_exists($filepath_tmp))
            {
                $this->exception('缩图保存目录不存在', -4);
            }

            $img = Image::make($upload_dir.'/'.$filename);

            if ( $thumb_w > 0 && $thumb_h > 0 )
            {
                $img->resize($thumb_w, $thumb_h)->save($filepath_tmp.'/'.$filename_tmp);
            }
            // 只设置了宽度，自动计算高度，高度等比例缩放
            elseif ( $thumb_w > 0 && $thumb_h == 0 )
            {
                $img->widen($thumb_w)->save($filepath_tmp.'/'.$filename_tmp);
            }
            // 只设置了高度，自动计算宽度，宽度等比例缩放
            elseif ( $thumb_h > 0 && $thumb_w == 0 )
            {
                $img->heighten($thumb_h)->save($filepath_tmp.'/'.$filename_tmp);
            }

            $filename = md5_file($filepath_tmp.'/'.$filename_tmp).".".$file_ext;
            //$filename = uniqid().'.'.$file_ext;

            // 如果需要分隔目录上传
            if (self::$dir_num > 0)
            {
                $dir_num = $this->serv_util->str2number($filename, self::$dir_num);
                if (!$this->serv_util->path_exists($upload_dir.'/'.$dir_num))
                {
                    $this->exception('缩图保存目录不存在', -5);
                }
                $filename = $dir_num.'/'.$filename;
            }

            //不同路徑的話，移動檔案並更名
            rename($filepath_tmp.'/'.$filename_tmp, "{$upload_dir}/{$filename}");

            $filelink = Storage::disk('public')->url("image/{$filename}");
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return [$status, $filename, $filelink];
    }
}
