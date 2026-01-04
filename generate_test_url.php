<?php
/**
 * 生成测试URL工具
 * 用于生成第三方跳转我方jump接口和share接口的完整测试URL
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
$backend_url_jump = 'https://yixueadmin.linqingkeji.com/api/user/jump';
$backend_url_share = 'https://yixueadmin.linqingkeji.com/api/user/share';

echo "=== 易学测算系统 - 第三方跳转接口测试URL生成工具 ===\n\n";

// ========== JUMP 接口测试 ==========
echo "========== JUMP 接口测试URL (携带customerNo) ==========\n\n";
echo "后端接口: /api/user/jump\n";
echo "请求方式: GET\n";
echo "用途: 第三方系统跳转，携带客户号\n\n";

$jump_test_cases = [
    ['env' => 'sit', 'agentCode' => '60000079', 'customerNo' => '12345678'],
    ['env' => 'sit', 'agentCode' => 'TEST001', 'customerNo' => 'C001'],
];

foreach ($jump_test_cases as $index => $test) {
    $env = $test['env'];
    $agentCode = $test['agentCode'];
    $customerNo = $test['customerNo'];
    $salt = $salts[$env];

    // 生成加签字符串 (jump接口包含customerNo)
    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}";
    $sign = md5($str . $salt);
    $full_url = "{$backend_url_jump}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}&sign={$sign}";

    echo "--- JUMP 测试用例 " . ($index + 1) . " ---\n";
    echo "环境: " . strtoupper($env) . "\n";
    echo "代理人工号: {$agentCode}\n";
    echo "客户ID: {$customerNo}\n";
    echo "签名: {$sign}\n";
    echo "URL:\n{$full_url}\n\n";
}

// ========== SHARE 接口测试 ==========
echo "\n========== SHARE 接口测试URL (不携带customerNo) ==========\n\n";
echo "后端接口: /api/user/share\n";
echo "请求方式: GET\n";
echo "用途: 分享链接，不携带客户号，用户点击后跳转回对方系统\n\n";

$share_test_cases = [
    ['env' => 'sit', 'agentCode' => '60000079'],
    ['env' => 'sit', 'agentCode' => 'TEST001'],
];

foreach ($share_test_cases as $index => $test) {
    $env = $test['env'];
    $agentCode = $test['agentCode'];
    $salt = $salts[$env];

    // 生成加签字符串 (share接口不包含customerNo)
    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}";
    $sign = md5($str . $salt);
    $full_url = "{$backend_url_share}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&sign={$sign}";

    echo "--- SHARE 测试用例 " . ($index + 1) . " ---\n";
    echo "环境: " . strtoupper($env) . "\n";
    echo "代理人工号: {$agentCode}\n";
    echo "加签字符串: {$str}\n";
    echo "签名: {$sign}\n";
    echo "URL:\n{$full_url}\n\n";
}

// ========== 各环境SHARE链接汇总 ==========
echo "\n========== 各环境 SHARE 测试URL汇总 ==========\n\n";

foreach (['sit', 'uat', 'prd'] as $env) {
    $agentCode = '60000079';
    $salt = $salts[$env];

    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}";
    $sign = md5($str . $salt);
    $full_url = "{$backend_url_share}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&sign={$sign}";

    echo strtoupper($env) . " 环境 SHARE:\n";
    echo "{$full_url}\n\n";
}

// ========== 各环境JUMP链接汇总 ==========
echo "\n========== 各环境 JUMP 测试URL汇总 ==========\n\n";

foreach (['sit', 'uat', 'prd'] as $env) {
    $agentCode = '60000079';
    $customerNo = '12345678';
    $salt = $salts[$env];

    $str = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}";
    $sign = md5($str . $salt);
    $full_url = "{$backend_url_jump}?merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}&sign={$sign}";

    echo strtoupper($env) . " 环境 JUMP:\n";
    echo "{$full_url}\n\n";
}
