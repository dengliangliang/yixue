<?php
/**
 * 验签调试脚本
 */

// 从URL获取参数 (或手动设置)
$test_url = 'https://yixueadmin.linqingkeji.com/api/user/jump?merchantId=JWLY&activityCode=2026TSMHWL&agentCode=60000079&customerNo=12345678&sign=ae6b4b13abea23ec8c18d8e10e3e0aa8';

// 解析URL参数
$parts = parse_url($test_url);
parse_str($parts['query'], $params);

$merchantId = $params['merchantId'] ?? '';
$activityCode = $params['activityCode'] ?? '';
$agentCode = $params['agentCode'] ?? '';
$customerNo = $params['customerNo'] ?? '';
$sign = $params['sign'] ?? '';

echo "========== 验签调试信息 ==========\n\n";
echo "接收到的参数:\n";
echo "merchantId: {$merchantId}\n";
echo "activityCode: {$activityCode}\n";
echo "agentCode: {$agentCode}\n";
echo "customerNo: {$customerNo}\n";
echo "sign: {$sign}\n\n";

// 配置 (从 citicpru.php)
$salts = [
    'sit' => 'a20b6f5a009745f08716935243fa476c',
    'uat' => 'dbc4235b0d0c470c83966b6ea2a2e2f4',
    'prd' => '7323bd5e6f6644c7b846524a6e8017ea',
];

// 测试所有环境
foreach ($salts as $env => $salt) {
    echo "---------- " . strtoupper($env) . " 环境验签 ----------\n";
    echo "盐值: {$salt}\n\n";

    // 方法1: 不带URL编码
    $str1 = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCode}&customerNo={$customerNo}";
    $calculated_sign1 = md5($str1 . $salt);

    echo "方法1 (不带URL编码):\n";
    echo "加签字符串: {$str1}\n";
    echo "计算签名: {$calculated_sign1}\n";
    echo "匹配结果: " . ($calculated_sign1 === $sign ? "✅ 成功" : "❌ 失败") . "\n\n";

    // 方法2: 带URL编码
    $agentCodeEncoded = rawurlencode($agentCode);
    $str2 = "merchantId={$merchantId}&activityCode={$activityCode}&agentCode={$agentCodeEncoded}&customerNo={$customerNo}";
    $calculated_sign2 = md5($str2 . $salt);

    echo "方法2 (agentCode使用URL编码):\n";
    echo "加签字符串: {$str2}\n";
    echo "计算签名: {$calculated_sign2}\n";
    echo "匹配结果: " . ($calculated_sign2 === $sign ? "✅ 成功" : "❌ 失败") . "\n\n";

    if ($calculated_sign1 === $sign || $calculated_sign2 === $sign) {
        echo "🎉 验签成功! 环境: " . strtoupper($env) . "\n";
        echo "---\n\n";
        break;
    }
    echo "---\n\n";
}

// 额外测试: 尝试其他可能的参数组合
echo "========== 其他可能性测试 ==========\n\n";

// 测试是否缺少参数
$test_cases = [
    [
        'desc' => '只有必需参数',
        'str' => "merchantId={$merchantId}&activityCode={$activityCode}&customerNo={$customerNo}",
    ],
    [
        'desc' => '参数顺序1: JWLY在前',
        'str' => "JWLY{$activityCode}{$agentCode}{$customerNo}",
    ],
    [
        'desc' => '参数顺序2: 无键值对',
        'str' => "{$merchantId}{$activityCode}{$agentCode}{$customerNo}",
    ],
];

foreach ($test_cases as $case) {
    foreach ($salts as $env => $salt) {
        $test_sign = md5($case['str'] . $salt);
        if ($test_sign === $sign) {
            echo "✅ 找到匹配!\n";
            echo "环境: " . strtoupper($env) . "\n";
            echo "方式: {$case['desc']}\n";
            echo "字符串: {$case['str']}\n";
            echo "签名: {$test_sign}\n";
            break 2;
        }
    }
}

echo "\n========== 建议 ==========\n";
echo "1. 检查配置文件中的 'env' 设置\n";
echo "2. 确认使用的盐值环境是否正确\n";
echo "3. 如果仍失败，请提供完整的请求URL进行调试\n";
