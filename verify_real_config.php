<?php
/**
 * 精确验签测试 - 使用实际配置文件
 */

// 引入ThinkPHP配置
define('APP_PATH', __DIR__ . '/1213-easy-to-learn/application/');
define('THINK_PATH', __DIR__ . '/1213-easy-to-learn/thinkphp/');

// 直接读取配置文件
$config = include __DIR__ . '/1213-easy-to-learn/application/extra/citicpru.php';

echo "========== 实际配置信息 ==========\n\n";
echo "商户ID: " . $config['merchant_id'] . "\n";
echo "活动编码: " . $config['activity_code'] . "\n";
echo "当前环境: " . $config['env'] . "\n";
echo "当前盐值: " . $config['salts'][$config['env']] . "\n\n";

// 测试数据
$merchantId = $config['merchant_id'];
$activityCode = $config['activity_code'];
$agentCode = '60000079';
$customerNo = '12345678';
$salt = $config['salts'][$config['env']];

echo "========== jump方法验签逻辑模拟 ==========\n\n";

// 完全按照jump方法的逻辑
echo "第1步: URL编码方式\n";
$agentCodeStr = rawurlencode($agentCode);
$str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCodeStr&customerNo=$customerNo";
echo "加签字符串: $str\n";
$sign1 = md5($str . $salt);
echo "计算签名: $sign1\n\n";

echo "第2步: 非编码方式 (fallback)\n";
$str2 = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";
echo "加签字符串: $str2\n";
$sign2 = md5($str2 . $salt);
echo "计算签名: $sign2\n\n";

echo "========== 生成正确的测试URL ==========\n\n";

// 使用非编码版本（因为数字不需要编码）
$correct_sign = $sign2;
$test_url = "https://yixueadmin.linqingkeji.com/api/user/jump?merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo&sign=$correct_sign";

echo "✅ 正确的测试URL:\n";
echo "$test_url\n\n";

echo "========== 问题诊断 ==========\n\n";

// 检查之前给的签名
$old_sign = 'ae6b4b13abea23ec8c18d8e10e3e0aa8';
echo "之前提供的签名: $old_sign\n";
echo "使用SIT环境计算: $sign2\n";
echo "匹配结果: " . ($old_sign === $sign2 ? "✅ 匹配" : "❌ 不匹配") . "\n\n";

if ($old_sign !== $sign2) {
    echo "⚠️ 问题分析:\n";
    echo "1. 之前的签名是用其他环境的盐值计算的\n";
    echo "2. 或者商户ID/活动编码配置不一致\n\n";

    // 尝试所有环境
    foreach ($config['salts'] as $env => $env_salt) {
        $test_sign = md5($str2 . $env_salt);
        if ($test_sign === $old_sign) {
            echo "✅ 找到匹配环境: " . strtoupper($env) . "\n";
            echo "该环境盐值: $env_salt\n";
        }
    }
}

echo "\n========== 三个环境的完整URL ==========\n\n";

foreach ($config['salts'] as $env => $env_salt) {
    $env_sign = md5($str2 . $env_salt);
    $env_url = "https://yixueadmin.linqingkeji.com/api/user/jump?merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo&sign=$env_sign";
    echo strtoupper($env) . " 环境:\n";
    echo "盐值: $env_salt\n";
    echo "签名: $env_sign\n";
    echo "URL: $env_url\n\n";
}
