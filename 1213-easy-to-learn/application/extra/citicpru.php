<?php

return [
    'activity_code' => '2026TSMHWL',
    'merchant_id' => 'JWLY',
    'salts' => [
        'sit' => 'a20b6f5a009745f08716935243fa476c',
        'uat' => 'dbc4235b0d0c470c83966b6ea2a2e2f4',
        'prd' => '7323bd5e6f6644c7b846524a6e8017ea',
    ],
    'callback_urls' => [
        'sit' => 'https://test2.citicpruagents.com.cn/xytapp-sit/ext/components/v1/common/callback',
        'uat' => 'https://test2.citicpruagents.com.cn/xytapp-uat/ext/components/v1/common/callback',
        'prd' => 'https://sqs.citicpruagents.com.cn/xytapp/ext/components/v1/common/callback',
    ],
    'h5_urls' => [
        'sit' => 'https://yixue.linqingkeji.com/',
        'uat' => 'https://yixue.linqingkeji.com/',
        'prd' => 'https://yixue.linqingkeji.com/',
    ],
    // 当前环境：sit, uat, prd
    'env' => 'sit',
];
