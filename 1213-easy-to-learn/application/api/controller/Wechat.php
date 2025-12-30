<?php
/**
 * 微信公众号接口控制器
 * 
 * 提供 JS-SDK 签名配置接口
 */
namespace app\api\controller;

use app\common\controller\Api;
use EasyWeChat\Factory;
use think\Config;
use think\Cache;

class Wechat extends Api
{
    // 无需登录即可访问的接口
    protected $noNeedLogin = ['jsconfig'];
    // 无需权限验证的接口
    protected $noNeedRight = '*';

    /**
     * 获取微信 JS-SDK 配置
     * 
     * 前端调用此接口获取 wx.config 所需的签名参数
     */
    public function jsconfig()
    {
        // 获取当前页面URL
        $url = $this->request->get('url');

        if (empty($url)) {
            // 如果没有传URL，使用请求的 Referer
            $url = $this->request->server('HTTP_REFERER');
        }

        if (empty($url)) {
            $this->error('缺少URL参数');
        }

        // URL 不应包含 # 及其后面的部分
        $url = strtok($url, '#');

        try {
            // 获取微信配置
            $config = Config::get('wechat');

            // 调试：检查配置是否加载成功
            if (empty($config)) {
                $this->error('微信配置未加载，请检查 application/extra/wechat.php 文件');
            }

            if (empty($config['app_id']) || $config['app_id'] === 'wx_your_app_id') {
                $this->error('微信公众号 AppID 未配置');
            }

            // 使用 EasyWeChat 创建公众号应用实例
            $app = Factory::officialAccount($config);

            // 获取 jsapi_ticket (EasyWeChat 会自动缓存)
            // getTicket() 返回数组 ['ticket' => '...', 'expires_in' => 7200, ...]
            $ticketResult = $app->jssdk->getTicket();
            $ticket = is_array($ticketResult) ? $ticketResult['ticket'] : $ticketResult;

            // 手动生成签名参数
            $timestamp = time();
            $nonceStr = $this->generateNonceStr();

            // 按照微信要求的格式拼接签名字符串
            $signatureString = sprintf(
                "jsapi_ticket=%s&noncestr=%s&timestamp=%d&url=%s",
                $ticket,
                $nonceStr,
                $timestamp,
                $url
            );

            // 使用 SHA1 生成签名
            $signature = sha1($signatureString);

            // 返回签名配置
            $this->success('获取成功', [
                'appId' => $config['app_id'],
                'timestamp' => $timestamp,
                'nonceStr' => $nonceStr,
                'signature' => $signature,
                'jsApiList' => [
                    'updateAppMessageShareData',
                    'updateTimelineShareData',
                    'onMenuShareAppMessage',
                    'onMenuShareTimeline',
                    'previewImage',
                    'downloadImage',
                    'chooseImage',
                ],
            ]);

        } catch (\think\exception\HttpResponseException $e) {
            // 这是 $this->error() 或 $this->success() 抛出的正常响应异常
            // 直接重新抛出，让框架处理
            throw $e;
        } catch (\Throwable $e) {
            // 记录错误日志
            \think\Log::error('[Wechat JSSDK] 签名失败: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());

            $this->error('获取微信配置失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成随机字符串
     */
    private function generateNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 清除微信缓存（调试用）
     */
    public function clearCache()
    {
        try {
            Cache::rm('easywechat.official_account.access_token');
            Cache::rm('easywechat.official_account.jsapi_ticket');

            $this->success('缓存已清除');
        } catch (\Exception $e) {
            $this->error('清除缓存失败: ' . $e->getMessage());
        }
    }
}
