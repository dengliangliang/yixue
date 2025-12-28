<?php

/**
 * 将参数数组按照字典排序
 *
 * @param $array
 * @return bool|string
 */
function sorting($array)
{
    if (empty($array) || !is_array($array)) {
        return false;
    }
    ksort($array);
    $str = '';
    foreach ($array as $k => $v) {
        if ($v != '' && !is_array($v)) {
            $str.= $k.'='.$v.'=';

        }
    }
    $str = trim($str, '=');
    return $str;
}

function signCheck(array $info): string
{
    $key = '19200625';
    ksort($info);
    $string = '';
    foreach ($info as $key => $v) {
        if (empty($val) || $key == 'sign') {
            continue;
        }
        $string .= $key . '=' . $v . '&';
    }
    if (get_magic_quotes_gpc()) {
        $string = stripslashes($string);
    }
    $token = md5($string . 'key=' . $key);
    return $token;
}