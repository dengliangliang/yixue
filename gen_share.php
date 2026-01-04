<?php
$str = 'merchantId=JWLY&activityCode=2026TSMHWL&agentCode=60000079';
$salt = 'a20b6f5a009745f08716935243fa476c';
$sign = md5($str . $salt);
echo "String: $str\n";
echo "Salt: $salt\n";
echo "Sign: $sign\n";
echo "\nShare URL:\n";
echo "https://yixueadmin.linqingkeji.com/api/user/share?merchantId=JWLY&activityCode=2026TSMHWL&agentCode=60000079&sign=$sign\n";
