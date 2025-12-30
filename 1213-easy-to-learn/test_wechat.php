<?php
/**
 * 测试脚本：验证 EasyWeChat getTicket 返回值
 */
require __DIR__ . '/vendor/autoload.php';

// 使用 think\Config 方式加载配置
$config = [
    'app_id' => 'wx901e39b8310225bf',
    'secret' => 'af31b97711b30637450f0e349822ec05',
];

echo "配置信息:\n";
print_r($config);
echo "\n";

try {
    $app = \EasyWeChat\Factory::officialAccount($config);

    echo "获取 jsapi_ticket...\n";
    $ticketResult = $app->jssdk->getTicket();

    echo "getTicket() 返回类型: " . gettype($ticketResult) . "\n";
    echo "返回值:\n";
    var_dump($ticketResult);

    // 提取 ticket
    if (is_array($ticketResult)) {
        echo "\n是数组，提取 ticket 键:\n";
        if (isset($ticketResult['ticket'])) {
            echo "ticket = " . $ticketResult['ticket'] . "\n";
        } else {
            echo "没有 ticket 键！可用的键:\n";
            print_r(array_keys($ticketResult));
        }
    } else {
        echo "\n不是数组，直接使用: $ticketResult\n";
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}
