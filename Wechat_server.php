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
    protected $noNeedLogin = ['jsconfig', 'getOpenid'];
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
     * 通过 code 获取 openid
     */
    public function getOpenid()
    {
        $code = $this->request->get('code');
        if (empty($code)) {
            $this->error('缺少 code 参数');
        }

        try {
            $config = Config::get('wechat');

            if (empty($config['app_id']) || $config['app_id'] === 'wx_your_app_id') {
                $this->error('微信公众号 AppID 未配置');
            }

            // 直接调用微信 OAuth 接口获取 access_token 和 openid
            // 此方法适用于 snsapi_base 静默授权场景
            $appId = $config['app_id'];
            $appSecret = $config['secret'];

            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$appSecret}&code={$code}&grant_type=authorization_code";

            // 使用 curl 请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception('curl 请求失败: ' . curl_error($ch));
            }
            curl_close($ch);

            $result = json_decode($response, true);

            // 记录日志便于调试
            \think\Log::info('[Wechat Openid] 微信返回原始: ' . $response);
            \think\Log::info('[Wechat Openid] JSON解析结果: ' . print_r($result, true));

            // 检查 JSON 解析是否成功
            if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON解析失败: ' . json_last_error_msg());
            }

            if (isset($result['errcode']) && $result['errcode'] != 0) {
                throw new \Exception('微信返回错误: ' . ($result['errmsg'] ?? '未知错误') . ' (code: ' . $result['errcode'] . ')');
            }

            if (empty($result['openid'])) {
                \think\Log::error('[Wechat Openid] 返回数据中没有openid，完整数据: ' . json_encode($result));
                throw new \Exception('返回数据中没有 openid');
            }

            \think\Log::info('[Wechat Openid] 成功获取 openid: ' . $result['openid']);

            $this->success('获取成功', [
                'openid' => $result['openid']
            ]);
            return; // 确保方法终止

        } catch (\Throwable $e) {
            \think\Log::error('[Wechat Openid] 获取失败: ' . $e->getMessage());
            $this->error('获取 openid 失败: ' . $e->getMessage());
            return; // 确保方法终止
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
