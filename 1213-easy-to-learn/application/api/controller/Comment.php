<?php
namespace app\api\controller;

use app\common\controller\Api;
use think\Config;
use app\common\model\Order;
use app\common\model\OrderComment;
use think\Db;
use think\Exception;

class Comment extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /*
     * 添加评价
     *
     * @param int $order_id
     * @param int $star
     * @param string $images
     * @param string $video
     * @param string $text
     */
    public function addComment($order_id = 0, $star = 5, $images = '', $video = '', $text = '')
    {
        $order_res = Order::get(['user_id' => $this->auth->id, 'id' => $order_id]);
        if (empty($order_res)) $this->error('该订单信息不存在');
        if ($order_res->is_comment == 1) $this->error('请勿重复评价');
        if (empty($text)) $this->error('评价内容不能为空');
        if (!empty($images)) {
            $image_arr = explode(',', $images);
            if (count($image_arr) > 9) $this->error('最高只允许上传9张');
        }
        Db::startTrans();
        try {
            $add_res = OrderComment::create([
                'order_id' => $order_id,
                'line_site_id' => $order_res->line_site_id,
                'classes_id' => $order_res->classes_id,
                'driver_id' => $order_res->driver_id,
                'user_id' => $this->auth->id,
                'star' => $star,
                'images' => $images,
                'video' => $video,
                'text' => $text
            ]);
            if (empty($add_res)) $this->error('添加失败');
            $upd_res = $order_res->save(['is_comment' => 1]);
            if (empty($upd_res)) $this->error('更新失败');
            Db::commit();
            $this->success('评价成功');
        } catch (Exception $e) {
            Db::rollback();
            $this->error('评价失败');
        }
    }
}