<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use com\nlf\calendar\Lunar;
use com\nlf\calendar\Solar;
use fast\Random;
use think\Config;
use think\Db;
use think\Exception;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'getCityList', 'getProvinceList', 'addRecord', 'jump', 'checkRecord', 'share', 'getShareRedirectUrl'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 第三方跳转入口 (Handshake/Jump)
     */
    public function jump($merchantId = '', $activityCode = '', $agentCode = '', $customerNo = '', $sign = '')
    {
        if (empty($merchantId) || empty($activityCode) || empty($customerNo) || empty($sign)) {
            $this->error('缺少必要参数');
        }

        // 验签逻辑
        $config = Config::get('citicpru');
        $salt = $config['salts'][$config['env']] ?? '';

        $agentCodeStr = rawurlencode($agentCode);
        $str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCodeStr&customerNo=$customerNo";

        if (md5($str . $salt) != $sign) {
            // 尝试非编码版本
            $str2 = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";
            if (md5($str2 . $salt) != $sign) {
                $this->error('验签失败');
            }
        }

        // 5. 获取前端 H5 地址并跳转
        $env = $config['env'] ?? 'sit';
        $h5BaseUrl = $config['h5_urls'][$env] ?? 'https://yixue.linqingkeji.com/';
        $h5BaseUrl = rtrim($h5BaseUrl, '/');

        $params = http_build_query([
            'merchantId' => $merchantId,
            'activityCode' => $activityCode,
            'agentCode' => $agentCode,
            'customerNo' => $customerNo,
            'sign' => $sign,
            'from' => 'jump'  // 标识来源,用于前端判断是否自动打开九紫离火弹窗
        ]);

        $redirectUrl = $h5BaseUrl . "/#/pages/index/index?" . $params;
        header("Location: " . $redirectUrl);
        exit;
    }

    /**
     * 分享跳转接口(不携带customerNo)
     * 用于代理人分享给客户的纯分享链接
     * 注意: 此接口不需要验签
     */
    public function share($merchantId = '', $activityCode = '', $agentCode = '', $sign = '')
    {
        if (empty($merchantId) || empty($activityCode)) {
            $this->error('缺少必要参数');
        }

        // 获取前端 H5 地址并跳转 (无需验签)
        $config = Config::get('citicpru');
        $env = $config['env'] ?? 'sit';
        $h5BaseUrl = $config['h5_urls'][$env] ?? 'https://yixue.linqingkeji.com/';
        $h5BaseUrl = rtrim($h5BaseUrl, '/');

        $params = http_build_query([
            'merchantId' => $merchantId,
            'activityCode' => $activityCode,
            'agentCode' => $agentCode,
            'sign' => $sign,
            'from' => 'share'  // 标识来源为分享链接,前端不自动打开弹窗
        ]);

        $redirectUrl = $h5BaseUrl . "/#/pages/index/index?" . $params;
        header("Location: " . $redirectUrl);
        exit;
    }

    /**
     * 获取share分享用户跳转回对方系统的URL
     * @param string $merchantId 商户ID
     * @param string $activityCode 活动编码
     * @param string $agentCode 代理人编码
     * @param string $sign 签名
     */
    public function getShareRedirectUrl($merchantId = '', $activityCode = '', $agentCode = '', $sign = '')
    {
        if (empty($merchantId) || empty($activityCode)) {
            $this->error('缺少必要参数');
        }

        $config = Config::get('citicpru');
        $env = $config['env'] ?? 'sit';
        $baseUrl = $config['share_redirect_urls'][$env] ?? '';

        if (empty($baseUrl)) {
            $this->error('未配置跳转地址');
        }

        // 构建跳转URL
        $params = http_build_query([
            'isShare' => 'true',
            'merchantId' => $merchantId,
            'activityCode' => $activityCode,
            'agentCode' => $agentCode,
            'sign' => $sign
        ]);

        $redirectUrl = $baseUrl . '?' . $params;

        $this->success('获取成功', ['redirect_url' => $redirectUrl]);
    }

    /**

     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /*
     * 获取省份城市列表
     *
     */
    public function getProvinceList()
    {
        // 优化：使用缓存，省份数据基本不变
        $cache_key = 'province_list';
        $province_list = cache($cache_key);
        if (empty($province_list)) {
            $province_list = Db::name('area')
                ->where('pid', 0)
                ->field('id, name')
                ->select();
            cache($cache_key, $province_list, 86400); // 缓存1天
        }
        $this->success('获取成功', $province_list);
    }

    /*
     * 获取省份下面城市列表
     *
     * @param $id
     */
    public function getCityList($id)
    {
        if (empty($id))
            $this->error('未获取到省份');
        // 优化：使用缓存
        $cache_key = 'city_list_' . $id;
        $city_list = cache($cache_key);
        if (empty($city_list)) {
            $city_list = Db::name('area')
                ->where('pid', $id)
                ->order('first asc')
                ->field('id, name')
                ->select();
            cache($cache_key, $city_list, 86400); // 缓存1天
        }
        $this->success('获取成功', $city_list);
    }

    /**
     * 检查是否有测算记录
     * @param string $customer_id 客户号(可选)
     * @param string $openid 微信OpenID(可选)
     * 两个参数至少需要一个,使用OR逻辑查询
     */
    public function checkRecord($customer_id = '', $openid = '')
    {
        if (empty($customer_id) && empty($openid)) {
            $this->error('参数错误:至少需要customer_id或openid');
        }

        $has_record = false;
        $last_record_id = 0;

        // 如果提供了customer_id,先通过customer_id查询
        if (!empty($customer_id)) {
            $user = \app\common\model\User::get(['username' => $customer_id]);
            if ($user) {
                $last_record = Db::name('record')
                    ->where('user_id', $user->id)
                    ->order('id desc')
                    ->find();

                if ($last_record) {
                    $has_record = true;
                    $last_record_id = $last_record['id'];
                }
            }
        }

        // 如果通过customer_id没找到记录,且提供了openid,则通过openid查询
        if (!$has_record && !empty($openid)) {
            $last_record = Db::name('record')
                ->where('openid', $openid)
                ->order('id desc')
                ->find();

            if ($last_record) {
                $has_record = true;
                $last_record_id = $last_record['id'];
            }
        }

        if ($has_record) {
            $this->success('获取成功', [
                'has_record' => true,
                'last_record_id' => $last_record_id
            ]);
        } else {
            $this->success('未找到记录', ['has_record' => false]);
        }
    }

    /*
     * 添加测算记录
     *
     * @param $customerNo
     * @param $date
     * @param int $hour
     * @param int $gender
     */
    public function addRecord($customerNo = '', $date, $hour = 0, $minute = 0, $gender = 0, $area_id = 0, $merchantId = '', $activityCode = '', $agentCode = '', $sign = '', $openid = '', $startTime = '')
    {
        // 只验证必需的三个参数：date, area_id, gender
        if (empty($date) || empty($area_id))
            $this->error('参数不能为空');

        // 如果customerNo为空，生成一个默认值
        if (empty($customerNo)) {
            $customerNo = 'H5_' . uniqid();
        }

        // 修复：对 agentCode 进行循环 URL 解码，处理任意深度的编码
        // 问题原因：不同入口传入的 agentCode 可能是未编码、单次编码或双重编码状态
        // 使用 rawurldecode 而不是 urldecode，因为 urldecode 会将 + 号解码为空格
        // 而 agentCode 是 Base64 编码，其中的 + 号应保持原样
        $maxIterations = 5; // 防止无限循环
        $iteration = 0;
        $decoded = rawurldecode($agentCode);
        while ($decoded !== $agentCode && $iteration < $maxIterations) {
            $agentCode = $decoded;
            $decoded = rawurldecode($agentCode);
            $iteration++;
        }
        if ($iteration > 0) {
            \think\Log::info("[addRecord] agentCode 解码次数: {$iteration}");
        }
        // 增强日志输出：更易读的时间格式
        $logTime = date('Y-m-d H:i:s');
        \think\Log::info("=== [{$logTime}] addRecord 开始 ===");
        \think\Log::info("[addRecord] 📥 请求参数:");
        \think\Log::info("  - customerNo: {$customerNo}");
        \think\Log::info("  - openid: " . (!empty($openid) ? $openid : '【空】'));
        \think\Log::info("  - merchantId: {$merchantId}");
        \think\Log::info("  - date: {$date}, hour: {$hour}, minute: {$minute}");
        \think\Log::info("  - gender: {$gender}, area_id: {$area_id}");

        $yang_li_arr = explode('-', $date);
        // 实例化
        //$solar = Solar::fromYmd($yang_li_arr[0], $yang_li_arr[1], $yang_li_arr[2]);
        $before_time = strtotime($yang_li_arr[0] . '-' . $yang_li_arr[1] . '-' . $yang_li_arr[2] . ' ' . $hour . ':' . $minute);
        $area_res = Db::name('area')->where('id', $area_id)->find();
        if (empty($area_res))
            $this->error('未获取到城市信息');
        $zhen_second = $area_res['zhen_second'];
        if (empty($area_res['zhen_second'])) {
            $zhen_second = Db::name('area')->where('pid', $area_res['pid'])->value('zhen_second');
        }
        $now_time = $before_time + $zhen_second + 900;
        if (date('H', $now_time) == '23') {
            $now_time += 86400;
        }
        $solar = Solar::fromYmdHms(date('Y', $now_time), date('m', $now_time), date('d', $now_time), date('H', $now_time), date('i', $now_time), date('s', $now_time));
        // 转农历
        $solar = $solar->getLunar();
        $yin_li_date = $solar->getYear() . '-' . $solar->getMonth() . '-' . $solar->getDay();
        $user_id = 0;
        $chk_user = \app\common\model\User::get(['username' => $customerNo]);
        if (empty($chk_user)) {
            $ret = $this->auth->register($customerNo, Random::alnum(), '', '', []);
            if (empty($ret)) {
                $this->error($this->auth->getError());
            } else {
                $user_id = $this->auth->id;
            }
        } else {
            $user_id = $chk_user['id'];
        }

        // 准备记录数据
        $recordData = [
            'user_id' => $user_id,
            'user_name' => $customerNo,
            'yang_li_date' => $date,
            'yin_li_date' => $yin_li_date,
            'hour' => $hour,
            'gender' => $gender,
            'minute' => $minute,
            'area_id' => $area_id,
            'zhen_hour' => date('H', $now_time),
            'zhen_minute' => date('i', $now_time),
            'zhen_yang_day' => date('Y-m-d', $now_time),
            'merchantId' => $merchantId,
            'activityCode' => $activityCode,
            'agentCode' => $agentCode,
            'customerNo' => $customerNo,
            'openid' => $openid,
            'start_time' => $startTime
        ];

        // 判断该记录是否存在过(通过 customerNo 或 openid 查询)
        $chk_record = null;

        // 优先通过 customerNo 查询(如果有的话)
        if (!empty($customerNo) && strpos($customerNo, 'H5_') !== 0) {
            $chk_record = Db::name('record')->where('customerNo', $customerNo)->order('id desc')->find();
        }

        // 如果没找到，且有 openid，通过 openid 查询
        if (empty($chk_record) && !empty($openid)) {
            $chk_record = Db::name('record')->where('openid', $openid)->order('id desc')->find();
        }

        if (!empty($chk_record)) {
            // 记录存在，更新字段，但保留原始的 customerNo（避免覆盖三方传入的客户号）
            $recordData['updatetime'] = time();

            // 如果数据库中已有非 H5_ 开头的 customerNo，则保留原始值
            $originalCustomerNo = $chk_record['customerNo'] ?? '';
            if (!empty($originalCustomerNo) && strpos($originalCustomerNo, 'H5_') !== 0) {
                // 保留原始的 customerNo 和 user_name
                $recordData['customerNo'] = $originalCustomerNo;
                $recordData['user_name'] = $originalCustomerNo;
                \think\Log::info("[addRecord] 保留原始 customerNo: {$originalCustomerNo}");
            }

            // 重置完成状态，允许重新回调
            $recordData['result'] = '未完成';
            $recordData['end_time'] = null;
            // 同时清除旧的排盘结果，以便重新计算
            $recordData['max_wu_xing'] = null;
            $recordData['min_wu_xing'] = null;

            Db::name('record')->where('id', $chk_record['id'])->update($recordData);

            // 【关键修复】删除旧的 record_shen 数据，否则 getSiZhuRes 会使用旧数据
            // 这是导致"二次测算使用旧排盘"的根本原因
            $deletedCount = Db::name('record_shen')->where('record_id', $chk_record['id'])->delete();
            \think\Log::info("[addRecord] 🗑️ 已删除旧的 record_shen 数据: {$deletedCount} 条");

            \think\Log::info("[addRecord] ✏️ 更新已有记录:");
            \think\Log::info("  - record_id: {$chk_record['id']}");
            \think\Log::info("  - customerNo: {$recordData['customerNo']}");
            \think\Log::info("  - openid: " . (!empty($openid) ? $openid : '【空】'));
            \think\Log::info("  - 新数据: date={$date}, area_id={$area_id}, gender={$gender}");
            \think\Log::info("=== [" . date('Y-m-d H:i:s') . "] addRecord 结束(更新) ===");
            $this->success('添加成功', ['record_id' => $chk_record['id']]);
        }

        // 新增记录
        $recordData['createtime'] = time();
        $add_id = Db::name('record')->insertGetId($recordData);
        if (!empty($add_id)) {
            \think\Log::info("[addRecord] ✅ 新增记录成功:");
            \think\Log::info("  - record_id: {$add_id}");
            \think\Log::info("  - customerNo: {$customerNo}");
            \think\Log::info("  - openid: " . (!empty($openid) ? $openid : '【空】'));
            \think\Log::info("  - createtime: " . date('Y-m-d H:i:s', $recordData['createtime']));
            \think\Log::info("=== [" . date('Y-m-d H:i:s') . "] addRecord 结束(新增) ===");
            // 优化：先返回record_id，让前端快速跳转，后台异步计算
            // updRecordRes 将在 getSiZhuRes 中按需调用
            $this->success('添加成功', ['record_id' => $add_id, 'need_calc' => true]);
        }
        $this->error('添加记录失败');
    }

    public function updRecordRes($record_id)
    {
        $chk = Db::name('record_shen')->where('record_id', $record_id)->count();
        if ($chk > 0 && $chk < 9) {
            Db::name('record_shen')->where('record_id', $record_id)->delete();
        }
        $record_res = Db::name('record')->where('id', $record_id)->find();

        $time_res = self::getYearMonthDayTimeRes($record_res['yin_li_date'], $record_res['zhen_hour'], $record_res['zhen_minute']);
        $month_gan_name = mb_substr($time_res['month'], 0, 1);
        $month_zhi_name = mb_substr($time_res['month'], -1);
        $ri_gan_name = mb_substr($time_res['day'], 0, 1);
        $ri_zhi_name = mb_substr($time_res['day'], -1);
        $time_gan_name = mb_substr($time_res['time'], 0, 1);

        $da_yun = $this->getQiYun($record_id);

        // 优化：批量查询天干五行属性（原8次查询 -> 2次查询）
        $gan_names = [$time_res['year_gan_name'], $month_gan_name, $ri_gan_name, $time_gan_name];
        $gan_attrs = Db::name('tian_gan')->whereIn('tian_gan_name', $gan_names)->column('attribute', 'tian_gan_name');
        $year_gan_wu_xing = $gan_attrs[$time_res['year_gan_name']] ?? '';
        $month_gan_wu_xing = $gan_attrs[$month_gan_name] ?? '';
        $ri_gan_wu_xing = $gan_attrs[$ri_gan_name] ?? '';
        $time_gan_wu_xing = $gan_attrs[$time_gan_name] ?? '';

        $zhi_names = [$time_res['year_zhi_name'], $month_zhi_name, $ri_zhi_name, $time_res['time_text']];
        $zhi_attrs = Db::name('month_zhi')->whereIn('month_name', $zhi_names)->column('attribute', 'month_name');
        $year_zhi_wu_xing = $zhi_attrs[$time_res['year_zhi_name']] ?? '';
        $month_zhi_wu_xing = $zhi_attrs[$month_zhi_name] ?? '';
        $ri_zhi_wu_xing = $zhi_attrs[$ri_zhi_name] ?? '';
        $time_zhi_wu_xing = $zhi_attrs[$time_res['time_text']] ?? '';

        // 优化：批量查询十神（原3次查询 -> 1次查询）
        $shi_shen_list = Db::name('si_zhu_shi_shen')
            ->where('ri_gan_name', $ri_gan_name)
            ->whereIn('gan_name', [$time_res['year_gan_name'], $month_gan_name, $time_gan_name])
            ->column('shi_shen_name', 'gan_name');
        $gan_shi_shen = [
            $shi_shen_list[$time_res['year_gan_name']] ?? '',
            $shi_shen_list[$month_gan_name] ?? '',
            '日元',
            $shi_shen_list[$time_gan_name] ?? '',
            $da_yun['gan_shi_shen']
        ];

        // 优化：批量查询支十神（原4次查询 -> 1次查询）
        $zhi_gan_names = [
            $ri_gan_name . $time_res['year_zhi_name'],
            $ri_gan_name . $month_zhi_name,
            $ri_gan_name . $ri_zhi_name,
            $ri_gan_name . $time_res['time_text']
        ];
        $zhi_shi_shen_list = Db::name('tian_gan_zhi')->whereIn('gan_zhi_name', $zhi_gan_names)->column('shi_shen', 'gan_zhi_name');
        $zhi_shi_shen = [
            $zhi_shi_shen_list[$ri_gan_name . $time_res['year_zhi_name']] ?? '',
            $zhi_shi_shen_list[$ri_gan_name . $month_zhi_name] ?? '',
            $zhi_shi_shen_list[$ri_gan_name . $ri_zhi_name] ?? '',
            $zhi_shi_shen_list[$ri_gan_name . $time_res['time_text']] ?? '',
            $da_yun['zhi_shi_shen']
        ];
        $pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
        $gan_shi_shen_style = [
            in_array($gan_shi_shen[0], $pian_shen) ? 0 : 1,
            in_array($gan_shi_shen[1], $pian_shen) ? 0 : 1,
            in_array($gan_shi_shen[2], $pian_shen) ? 0 : 1,
            in_array($gan_shi_shen[3], $pian_shen) ? 0 : 1,
            in_array($gan_shi_shen[4], $pian_shen) ? 0 : 1,
        ];
        $zhi_shi_shen_style = [
            in_array($zhi_shi_shen[0], $pian_shen) ? 0 : 1,
            in_array($zhi_shi_shen[1], $pian_shen) ? 0 : 1,
            in_array($zhi_shi_shen[2], $pian_shen) ? 0 : 1,
            in_array($zhi_shi_shen[3], $pian_shen) ? 0 : 1,
            in_array($zhi_shi_shen[4], $pian_shen) ? 0 : 1
        ];

        // 优化：批量插入（原10次insert -> 1次insertAll）
        $insert_data = [
            [
                'record_id' => $record_id,
                'shen_name' => $gan_shi_shen[0],
                'shen_style' => $gan_shi_shen_style[0],
                'gan_zhi_style' => 0,
                'shen_in' => 0,
                'is_da_yun' => 0,
                'gan_zhi_name' => $time_res['year_gan_name'],
                'wu_xing' => $year_gan_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $gan_shi_shen[1],
                'shen_style' => $gan_shi_shen_style[1],
                'gan_zhi_style' => 0,
                'shen_in' => 1,
                'is_da_yun' => 0,
                'gan_zhi_name' => $month_gan_name,
                'wu_xing' => $month_gan_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $gan_shi_shen[2],
                'shen_style' => 2,
                'gan_zhi_style' => 0,
                'shen_in' => 2,
                'is_da_yun' => 0,
                'gan_zhi_name' => $ri_gan_name,
                'wu_xing' => $ri_gan_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $gan_shi_shen[3],
                'shen_style' => $gan_shi_shen_style[3],
                'gan_zhi_style' => 0,
                'shen_in' => 3,
                'is_da_yun' => 0,
                'gan_zhi_name' => $time_gan_name,
                'wu_xing' => $time_gan_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $gan_shi_shen[4],
                'shen_style' => $gan_shi_shen_style[4],
                'gan_zhi_style' => 0,
                'shen_in' => 4,
                'is_da_yun' => 1,
                'gan_zhi_name' => mb_substr($da_yun['gan_zhi'], 0, 1),
                'wu_xing' => $da_yun['gan_xing']
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $zhi_shi_shen[0],
                'shen_style' => $zhi_shi_shen_style[0],
                'gan_zhi_style' => 1,
                'shen_in' => 5,
                'is_da_yun' => 0,
                'gan_zhi_name' => $time_res['year_zhi_name'],
                'wu_xing' => $year_zhi_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $zhi_shi_shen[1],
                'shen_style' => $zhi_shi_shen_style[1],
                'gan_zhi_style' => 1,
                'shen_in' => 6,
                'is_da_yun' => 0,
                'gan_zhi_name' => $month_zhi_name,
                'wu_xing' => $month_zhi_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $zhi_shi_shen[2],
                'shen_style' => $zhi_shi_shen_style[2],
                'gan_zhi_style' => 1,
                'shen_in' => 7,
                'is_da_yun' => 0,
                'gan_zhi_name' => $ri_zhi_name,
                'wu_xing' => $ri_zhi_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $zhi_shi_shen[3],
                'shen_style' => $zhi_shi_shen_style[3],
                'gan_zhi_style' => 1,
                'shen_in' => 8,
                'is_da_yun' => 0,
                'gan_zhi_name' => $time_res['time_text'],
                'wu_xing' => $time_zhi_wu_xing
            ],
            [
                'record_id' => $record_id,
                'shen_name' => $zhi_shi_shen[4],
                'shen_style' => $zhi_shi_shen_style[4],
                'gan_zhi_style' => 1,
                'shen_in' => 9,
                'is_da_yun' => 1,
                'gan_zhi_name' => mb_substr($da_yun['gan_zhi'], -1),
                'wu_xing' => $da_yun['zhi_xing']
            ]
        ];
        Db::name('record_shen')->insertAll($insert_data);
    }

    public static function getYearMonthDayTimeRes($date, $hour = 0, $minute = 0)
    {
        $date_arr = explode('-', $date);
        if (count($date_arr) == 4) {
            if (empty($date_arr[1])) {
                $date_arr[1] = -$date_arr[2];
                $date_arr[2] = $date_arr[3];
            }
        }
        $lunar = Lunar::fromYmd($date_arr[0], $date_arr[1], $date_arr[2], $hour, $minute);
        //echo $lunar->toFullString()."\n";exit;
        return [
            'year_text' => $lunar->toString(),
            'yang_li' => $lunar->getSolar()->toString(),
            'year' => $lunar->getYearInGanZhi(),
            'year_gan_name' => mb_substr($lunar->getYearInGanZhi(), 0, 1),
            'year_zhi_name' => mb_substr($lunar->getYearInGanZhi(), -1),
            'month' => $lunar->getMonthInGanZhi(),
            'day' => $lunar->getDayInGanZhi(),
            'time' => $lunar->getTimeInGanZhi(),
            'time_text' => mb_substr($lunar->getTimeInGanZhi(), -1),
            'yin_li_month' => $date_arr[1],
            'yin_li_day' => $date_arr[2]
        ];
    }

    public function getQiYun($record_id)
    {
        $record_res = Db::name('record')
            ->where('id', $record_id)
            //->where('user_id', $this->auth->id)
            ->find();
        if (empty($record_res))
            $this->error('未获取到测算记录');
        $date_arr = explode('-', $record_res['yin_li_date']);
        if (count($date_arr) == 4) {
            if (empty($date_arr[1])) {
                $date_arr[1] = -$date_arr[2];
                $date_arr[2] = $date_arr[3];
            }
        }
        $lunar = Lunar::fromYmd($date_arr[0], $date_arr[1], $date_arr[2], $record_res['zhen_hour'], $record_res['zhen_minute']);
        //$baZi = $lunar->getEightChar();
        //print_r($baZi->getYearGan() . ' ' . $baZi->getMonthGan() . ' ' . $baZi->getDayGan() . ' ' . $baZi->getTimeGan());
        $yun = $lunar->getEightChar()->getYun($record_res['gender'], 2);
        //$qi_yun_text = '出生'.$yun->getStartYear().'年'.$yun->getStartMonth().'个月'.$yun->getStartDay().'天'.$yun->getStartHour().'小时后起运';
        $da_yun = [];
        $year = [];
        $age = [];
        $text = [];
        $da_yun_arr = $yun->getDaYun();
        for ($i = 1; $i < count($da_yun_arr); $i++) {
            $dayun = $da_yun_arr[$i];
            $text[] = $dayun->getStartYear() . "年 " . $dayun->getStartAge() . "岁 " . $dayun->getGanZhi();
            $da_yun[] = $dayun->getGanZhi();
            $age[] = $dayun->getStartAge() - 1;
            $year[] = $dayun->getStartYear();
        }
        //print_r($qi_yun_text);
        //echo "\n";
        //print_r($text);
        //print_r($age);
        //print_r($da_yun);
        //print_r($year);
        $now_age = date('Y') - $date_arr[0];
        $gan_zhi = '';
        foreach ($age as $k => $v) {
            if ($now_age <= $v) {
                $gan_zhi = $da_yun[$k];
                break;
            }
        }
        //print_r($now_age);
        //echo "\n";
        //print_r($gan_zhi);exit;
        $gan_xing = Db::name('tian_gan')->where('tian_gan_name', mb_substr($gan_zhi, 0, 1))->value('attribute');
        $zhi_xing = Db::name('month_zhi')->where('month_name', mb_substr($gan_zhi, -1))->value('attribute');
        $gan_shi_shen = Db::name('si_zhu_shi_shen')
            ->where('ri_gan_name', $lunar->getEightChar()->getDayGan())
            ->where('gan_name', mb_substr($gan_zhi, 0, 1))
            ->value('shi_shen_name');
        $zhi_shi_shen = Db::name('tian_gan_zhi')->where('gan_zhi_name', $gan_zhi)->value('shi_shen');
        return compact('da_yun', 'year', 'age', 'gan_zhi', 'gan_xing', 'zhi_xing', 'gan_shi_shen', 'zhi_shi_shen');
    }








    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="account", type="string", required=true, description="账号")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="code", type="string", required=true, description="验证码")
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @ApiParams (name="avatar", type="string", required=true, description="头像地址")
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="nickname", type="string", required=true, description="昵称")
     * @ApiParams (name="bio", type="string", required=true, description="个人简介")
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="platform", type="string", required=true, description="平台名称")
     * @ApiParams (name="code", type="string", required=true, description="Code码")
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
