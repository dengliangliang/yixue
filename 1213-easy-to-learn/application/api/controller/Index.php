<?php

namespace app\api\controller;

use app\common\controller\Api;
use com\nlf\calendar\Lunar;
use think\Db;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }



    // base64转图片
    public function saveBase64($base64_image_content){

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            //图片后缀
            $type = $result[2];
            //保存位置--图片名
            $image_name=date('His').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT).".".$type;
            $image_file_path = '/uploads/sign/'.date('Ymd');
            $image_file = ROOT_PATH.'public'.$image_file_path;
            $imge_real_url = $image_file.'/'.$image_name;
            $imge_web_url = $image_file_path.'/'.$image_name;
            if (!file_exists($image_file)){
                mkdir($image_file, 0777);
                fopen($image_file.'\\'.$image_name, "w");
            }
            //解码
            $decode=base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents($imge_real_url, $decode)){
                $data['code'] = 1;
                $data['imageName']=$image_name;
                $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ?"https://": "http://";
                //$wzurl = $protocol . $_SERVER['HTTP_HOST'];
                //$data['url']=$protocol .$_SERVER['HTTP_HOST'].'/public'.$imge_web_url;
                $data['url'] = $imge_web_url;
                $data['msg'] = '保存成功！';
            }else{
                $data['code'] = 0;
                $data['imgageName']='';
                $data['url']='';
                $data['msg']='图片保存失败！';
            }
        }else{
            $data['code'] = 0;
            $data['imgageName']='';
            $data['url']='';
            $data['msg']='base64图片格式有误！';
        }
        $this->success('获取成功', $data);
        //return $data;
        //print_r($data);exit;
        //$this->success($data['msg']);
    }
}
