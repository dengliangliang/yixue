<?php
// 简化版输出
$config = include __DIR__ . '/1213-easy-to-learn/application/extra/citicpru.php';

$merchantId = 'JWLY';
$activityCode = '2026TSMHWL';
$agentCode = '60000079';
$customerNo = '12345678';

echo "当前环境: " . $config['env'] . "\n";
echo "当前盐值: " . $config['salts'][$config['env']] . "\n\n";

$salt = $config['salts'][$config['env']];
$str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";
$sign = md5($str . $salt);

echo "正确的测试URL:\n";
echo "https://yixueadmin.linqingkeji.com/api/user/jump?merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo&sign=$sign\n";
