<?php
/**
 * 生成测试URL工具
 * 用于生成第三方跳转我方jump接口的完整测试URL
 */

// 配置参数
$merchantId = 'JWLY';
$activityCode = '2026TSMHWL';

// 环境配置
$salts = [
    'sit' => 'a20b6f5a009745f08716935243fa476c',
    'uat' => 'dbc4235b0d0c470c83966b6ea2a2e2f4',
    'prd' => '7323bd5e6f6644c7b846524a6e8017ea',
];

// 后端接口地址
$backend_url = 'https://yixueadmin.linqingkeji.com/api/user/jump';

// 测试数据
$test_cases = [
    [
        'env' => 'sit',
        'agentCode' => '60000079',
        'customerNo' => '12345678',
    ],
    [
        'env' => 'sit',
        'agentCode' => 'TEST001',
        'customerNo' => 'C001',
    ],
];

echo "=== 易学测算系统 - 第三方跳转接口测试URL生成工具 ===\n\n";
echo "后端域名: yixueadmin.linqingkeji.com\n";
echo "接口路径: /api/user/jump\n";
echo "请求方式: GET\n\n";

foreach ($test_cases as $index => $test) {
    $env = $test['env'];
    $agentCode = $test['agentCode'];
    $customerNo = $test['customerNo'];
    $salt = $salts[$env];

    // 生成加签字符串
    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}";

    // 计算签名
    $sign = md5($str . $salt);

    // 生成完整URL
    $full_url = "{$backend_url}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}&sign={$sign}";

    echo "========== 测试用例 " . ($index + 1) . " ==========\n";
    echo "环境: " . strtoupper($env) . "\n";
    echo "盐值: {$salt}\n";
    echo "代理人工号: {$agentCode}\n";
    echo "客户ID: {$customerNo}\n\n";
    echo "加签字符串:\n{$str}\n\n";
    echo "签名结果:\n{$sign}\n\n";
    echo "完整测试URL:\n{$full_url}\n\n";
    echo "---\n\n";
}

// 生成其他环境的URL
echo "========== 其他环境测试URL ==========\n\n";

foreach (['sit', 'uat', 'prd'] as $env) {
    $agentCode = '60000079';
    $customerNo = '12345678';
    $salt = $salts[$env];

    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}";
    $sign = md5($str . $salt);
    $full_url = "{$backend_url}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}&sign={$sign}";

    echo strtoupper($env) . " 环境:\n";
    echo "{$full_url}\n\n";
}
