<?php
/**
 * 微信公众号配置
 * 
 * 使用说明：
 * 1. 登录微信公众平台 (mp.weixin.qq.com)
 * 2. 在 开发 -> 基本配置 中获取 AppID 和 AppSecret
 * 3. 在 公众号设置 -> 功能设置 中配置 JS接口安全域名
 * 4. 将 AppID 和 AppSecret 填入下方配置或 .env 文件中
 */
use think\Env;

return [
    // 公众号基础配置
    'app_id' => Env::get('wechat.app_id', 'wx901e39b8310225bf'),      // 微信公众号 AppID
    'secret' => Env::get('wechat.secret', 'af31b97711b30637450f0e349822ec05'),     // 微信公众号 AppSecret
    'token' => Env::get('wechat.token', 'your_token'),           // 公众号后台设置的 Token (消息接口用)
    'aes_key' => Env::get('wechat.aes_key', ''),                   // EncodingAESKey (消息加密用，可选)

    // 日志配置
    'log' => [
        'default' => 'dev',
        'channels' => [
            'dev' => [
                'driver' => 'daily',
                'path' => RUNTIME_PATH . 'log/wechat.log',
                'level' => 'debug',
            ],
        ],
    ],

    // OAuth 网页授权配置 (如需要)
    'oauth' => [
        'scopes' => ['snsapi_userinfo'],  // 授权类型
        'callback' => '/api/wechat/oauth_callback',  // 授权回调地址
    ],
];
