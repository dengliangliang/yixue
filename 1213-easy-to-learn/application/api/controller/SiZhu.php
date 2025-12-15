<?php
namespace app\api\controller;

use app\common\controller\Api;
use com\nlf\calendar\Lunar;
use com\nlf\calendar\Solar;
use think\Config;
use think\Db;

class SiZhu extends Api
{
    protected $noNeedLogin = ['getSiZhuRes', 'getQiYun', 'getResult', 'notify'];
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }

    /*
     * 根据记录id获取四柱八字信息
     *
     * @param $record_id  排盘记录id
     */
    public function getSiZhuRes($record_id)
    {
        $startTime = microtime(true);
        \think\Log::info("[getSiZhuRes] ========== 开始处理 ==========");
        \think\Log::info("[getSiZhuRes] record_id: {$record_id}");
        
        try {
            $record_res = Db::name('record')
                ->where('id', $record_id)
                ->find();
            \think\Log::info("[getSiZhuRes] 查询record耗时: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
            \think\Log::info("[getSiZhuRes] record_res: " . json_encode($record_res, JSON_UNESCAPED_UNICODE));
            
            if (empty($record_res)) {
                \think\Log::error("[getSiZhuRes] 错误: 未获取到测算记录");
                $this->error('未获取到测算记录');
            }
            if (empty($record_res['yin_li_date'])) {
                \think\Log::error("[getSiZhuRes] 错误: 日期不能为空");
                $this->error('日期不能为空');
            }
            
            // 检查是否需要计算record_shen（延迟计算优化）
            $shen_count = Db::name('record_shen')->where('record_id', $record_id)->count();
            \think\Log::info("[getSiZhuRes] shen_count: {$shen_count}");
            
            if ($shen_count == 0) {
                \think\Log::info("[getSiZhuRes] 需要计算record_shen，调用RecordService");
                // 直接调用静态方法，避免控制器实例化导致的认证问题
                $calcResult = \app\api\service\RecordService::updRecordRes($record_id);
                \think\Log::info("[getSiZhuRes] RecordService返回: " . var_export($calcResult, true));
                \think\Log::info("[getSiZhuRes] record_shen计算完成, 耗时: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
                
                // 重新查询shen_count
                $shen_count = Db::name('record_shen')->where('record_id', $record_id)->count();
                \think\Log::info("[getSiZhuRes] 计算后shen_count: {$shen_count}");
            }
        } catch (\Exception $e) {
            \think\Log::error("[getSiZhuRes] 异常: " . $e->getMessage());
            \think\Log::error("[getSiZhuRes] 堆栈: " . $e->getTraceAsString());
            $this->error('计算异常: ' . $e->getMessage());
        }
        
        $city_res = Db::name('area')->where('id', $record_res['area_id'])->find();
        $record_res['city'] = $city_res['name'];
        $record_res['province'] = Db::name('area')->where('id', $city_res['pid'])->value('name');
        $now_lunar = Lunar::fromDate(new \DateTime());
        //print_r($now_lunar->getTimeZhi()."时\n");
        //echo $now_lunar->getMonthInChinese().'月'."\n";
        //echo $now_lunar->getDayInChinese()."\n";exit;
        //$now_year_gan_zhi = $now_lunar->getYearInGanZhi();
        //print_r($now_year_gan_zhi);exit;

        // 优化：批量查询record_shen（原8次查询 -> 1次查询）
        $all_shen = Db::name('record_shen')->where('record_id', $record_id)->select();
        $shen_map = [];
        foreach ($all_shen as $shen) {
            $shen_map[$shen['shen_in']] = $shen;
        }
        $year_gan_res = $shen_map[0] ?? [];
        $month_gan_res = $shen_map[1] ?? [];
        $day_gan_res = $shen_map[2] ?? [];
        $time_gan_res = $shen_map[3] ?? [];

        $ju_ben_gan_zhi = Db::name('si_zhu_shi_shen')
            ->where('ri_gan_name', $day_gan_res['gan_zhi_name'])
            ->where('gan_name', $now_lunar->getYearGan())
            ->value('shi_shen_name');
        $ju_ben = Db::name('year_ju_ben')->where('year_zhu', $ju_ben_gan_zhi)->value('text');
        Db::name('record')->where('id', $record_id)->update(['ju_ben' => $ju_ben, 'ju_ben_gan_zhi' => $ju_ben_gan_zhi]);

        // 地支数据已在上面批量查询中获取
        $year_zhi_res = $shen_map[5] ?? [];
        $month_zhi_res = $shen_map[6] ?? [];
        $day_zhi_res = $shen_map[7] ?? [];
        $time_zhi_res = $shen_map[8] ?? [];

        $zao = [
            [
                "text_top" => $year_gan_res['gan_zhi_name'],
                "icon_top" => $year_gan_res['wu_xing'],
                "text_bom" => $year_zhi_res['gan_zhi_name'],
                'icon_bom' => $year_zhi_res['wu_xing']
            ],
            [
                "text_top" => $month_gan_res['gan_zhi_name'],
                "icon_top" => $month_gan_res['wu_xing'],
                "text_bom" => $month_zhi_res['gan_zhi_name'],
                'icon_bom' => $month_zhi_res['wu_xing']
            ],
            [
                "text_top" => $day_gan_res['gan_zhi_name'],
                "icon_top" => $day_gan_res['wu_xing'],
                "text_bom" => $day_zhi_res['gan_zhi_name'],
                'icon_bom' => $day_zhi_res['wu_xing']
            ],
            [
                "text_top" => $time_gan_res['gan_zhi_name'],
                "icon_top" => $time_gan_res['wu_xing'],
                "text_bom" => $time_zhi_res['gan_zhi_name'],
                'icon_bom' => $time_zhi_res['wu_xing']
            ]
            /*$year_gan_res['gan_zhi_name'].' '.$year_gan_res['wu_xing'],
            $month_gan_res['gan_zhi_name'].' '.$month_gan_res['wu_xing'],
            $day_gan_res['gan_zhi_name'].' '.$day_gan_res['wu_xing'],
            $time_gan_res['gan_zhi_name'].' '.$time_gan_res['wu_xing'],
            $year_zhi_res['gan_zhi_name'].' '.$year_zhi_res['wu_xing'],
            $month_zhi_res['gan_zhi_name'].' '.$month_zhi_res['wu_xing'],
            $day_zhi_res['gan_zhi_name'].' '.$day_zhi_res['wu_xing'],
            $time_zhi_res['gan_zhi_name'].' '.$time_zhi_res['wu_xing']*/
        ];
        $gan_shi_shen = [
            $year_gan_res['shen_name'],
            $month_gan_res['shen_name'],
            //'日元',
            $time_gan_res['shen_name'],
        ];
        $zhi_shi_shen = [
            $year_zhi_res['shen_name'],
            $month_zhi_res['shen_name'],
            $day_zhi_res['shen_name'],
            $time_zhi_res['shen_name'],
        ];

        $wu_xing_zao = [
            $year_gan_res['wu_xing'],
            $month_gan_res['wu_xing'],
            $day_gan_res['wu_xing'],
            $time_gan_res['wu_xing'],
            $year_zhi_res['wu_xing'],
            $month_zhi_res['wu_xing'],
            $day_zhi_res['wu_xing'],
            $time_zhi_res['wu_xing'],
        ];
        $wu_xing_arr = $this->getAllWuXing($wu_xing_zao);

        // 一
        $all_max = max($wu_xing_arr['all_wu_xing_num']);
        //echo "\n";
        $all_wu_xing_result = array_keys($wu_xing_arr['all_wu_xing_num'], $all_max);

        if (count($all_wu_xing_result) == 1) {
            $wu_xing_name = $all_wu_xing_result[0];
            // 特殊情况
            if (count($wu_xing_arr['all_wu_xing_num']) < 5 && $all_max >= 4 && $wu_xing_name == $month_zhi_res['wu_xing']) {
                $min_wu_xing_name = $wu_xing_name;
                $wu_xing_name = $this->getTeWuXingName($min_wu_xing_name, $wu_xing_zao, $record_id, $gan_shi_shen, $zhi_shi_shen);
                $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
                $wu_xing_name = $update_res['max_wu_xing'];
                $min_wu_xing_name = $update_res['min_wu_xing'];
                //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
                $this->success('获取成功', compact('record_res', 'zao','wu_xing_name', 'min_wu_xing_name'));
            }
            $min_wu_xing_name = $this->getMinWuXingName($record_id);
            //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
            $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
            $wu_xing_name = $update_res['max_wu_xing'];
            $min_wu_xing_name = $update_res['min_wu_xing'];
            $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
        }
        // 二
        $gan_all_max = max($wu_xing_arr['gan_wu_xing_num']);
        $gan_wu_xing_result = array_keys($wu_xing_arr['gan_wu_xing_num'], $gan_all_max);
        if (count($gan_wu_xing_result) == 1) {
            $wu_xing_name = $gan_wu_xing_result[0];
            $min_wu_xing_name = $this->getMinWuXingName($record_id);
            //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
            $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
            $wu_xing_name = $update_res['max_wu_xing'];
            $min_wu_xing_name = $update_res['min_wu_xing'];
            $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
        }
        // 三
        $pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
        $pian_key = [];
        foreach ($gan_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($pian_key, $k);
            }
        }
        if (count($pian_key) == 1) {
            if ($pian_key[0] == 0) {
                $wu_xing_name = $year_gan_res['wu_xing'];
            }
            if ($pian_key[0] == 1) {
                $wu_xing_name = $month_gan_res['wu_xing'];
            }
            if ($pian_key[0] == 2) {
                $wu_xing_name = $time_gan_res['wu_xing']; // $ri_gan_wu_xing;
            }
            $min_wu_xing_name = $this->getMinWuXingName($record_id);
            //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
            $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
            $wu_xing_name = $update_res['max_wu_xing'];
            $min_wu_xing_name = $update_res['min_wu_xing'];
            $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
        }
        if (count($pian_key) > 1) {
            $san_wu_xing_arr = [];
            foreach ($pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($san_wu_xing_arr, $year_gan_res['wu_xing']);
                }
                if ($v == 1) {
                    array_push($san_wu_xing_arr, $month_gan_res['wu_xing']);
                }
                if ($v == 2) {
                    array_push($san_wu_xing_arr, $time_gan_res['wu_xing']); // $ri_gan_wu_xing
                }
            }
            if (in_array(count($pian_key), [2, 3])) {
                $max_xing = max(array_count_values($san_wu_xing_arr));
                $max_key_arr = array_keys($san_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    $wu_xing_name = $max_key_arr[0];
                    $min_wu_xing_name = $this->getMinWuXingName($record_id);
                    //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
                    $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
                    $wu_xing_name = $update_res['max_wu_xing'];
                    $min_wu_xing_name = $update_res['min_wu_xing'];
                    $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
                }
            }

        }
        // 四
        $zhi_pian_key = [];
        foreach ($zhi_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($zhi_pian_key, $k);
            }
        }
        if (count($zhi_pian_key) == 1) {
            if ($zhi_pian_key[0] == 0) {
                $wu_xing_name = $year_zhi_res['wu_xing'];
            }
            if ($zhi_pian_key[0] == 1) {
                $wu_xing_name = $month_zhi_res['wu_xing'];
            }
            if ($zhi_pian_key[0] == 2) {
                $wu_xing_name = $day_zhi_res['wu_xing'];
            }
            if ($zhi_pian_key[0] == 3) {
                $wu_xing_name = $time_zhi_res['wu_xing'];
            }
            $min_wu_xing_name = $this->getMinWuXingName($record_id);
            //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
            $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
            $wu_xing_name = $update_res['max_wu_xing'];
            $min_wu_xing_name = $update_res['min_wu_xing'];
            $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
        }
        if (count($zhi_pian_key) > 1) {
            $si_wu_xing_arr = [];
            foreach ($zhi_pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($si_wu_xing_arr, $year_zhi_res['wu_xing']);
                }
                if ($v == 1) {
                    array_push($si_wu_xing_arr, $month_zhi_res['wu_xing']);
                }
                if ($v == 2) {
                    array_push($si_wu_xing_arr, $day_zhi_res['wu_xing']);
                }
                if ($v == 3) {
                    array_push($si_wu_xing_arr, $time_zhi_res['wu_xing']);
                }
            }
            if (in_array(count($zhi_pian_key), [2, 3, 4])) {
                $max_xing = max(array_count_values($si_wu_xing_arr));
                $max_key_arr = array_keys($si_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    $wu_xing_name = $max_key_arr[0];
                    $min_wu_xing_name = $this->getMinWuXingName($record_id);
                    //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
                    $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
                    $wu_xing_name = $update_res['max_wu_xing'];
                    $min_wu_xing_name = $update_res['min_wu_xing'];
                    $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
                }
            }
        }
        // 五
        $get_qi_yun_gan_zhi_res = $this->getQiYun($record_id);
        $wu_xing_name = $this->getWuXingName($get_qi_yun_gan_zhi_res, $wu_xing_zao, $gan_shi_shen, $zhi_shi_shen);
        $min_wu_xing_name = $this->getMinWuXingName($record_id);
        //Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
        $update_res = self::updRecord($record_id, $wu_xing_name, $min_wu_xing_name);
        $wu_xing_name = $update_res['max_wu_xing'];
        $min_wu_xing_name = $update_res['min_wu_xing'];
        $this->success('获取成功', compact('record_res','zao','wu_xing_name', 'min_wu_xing_name'));
    }
    // 更新五行
    public static function updRecord($record_id, $wu_xing_name, $min_wu_xing_name)
    {
        $wu_xing = ['金', '木', '水', '火', '土'];
        if (empty($wu_xing_name)) {
            $randomIndex = array_rand($wu_xing);
            $wu_xing_name = $wu_xing[$randomIndex];
        }
        if (empty($min_wu_xing_name)) {
            $key = array_search($wu_xing_name, $wu_xing);
            if (!empty($key)) unset($wu_xing[$key]);
            $randomIndex = array_rand($wu_xing);
            $min_wu_xing_name = $wu_xing[$randomIndex];
        }
        Db::name('record')->where('id', $record_id)->update(['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name]);
        return ['max_wu_xing' => $wu_xing_name, 'min_wu_xing' => $min_wu_xing_name];
    }


    // 获取计算结果
    // version参数: 2025=一期, 2026=二期
    public function getResult($record_id, $version = '2025')
    {
        $startTime = microtime(true);
        \think\Log::info("[getResult] 开始处理, record_id: {$record_id}, version: {$version}");
        
        $record_res = Db::name('record')
            ->where('id', $record_id)
            //->where('user_id', $this->auth->id)
                ->field('id, max_wu_xing, min_wu_xing, ju_ben, ju_ben_gan_zhi')
            ->find();
        \think\Log::info("[getResult] 查询record耗时: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
        
        if (empty($record_res)) $this->error('未获取到测算记录');
        if (empty($record_res['max_wu_xing']) || empty($record_res['min_wu_xing'])) {
            // 如果尚未计算，先触发计算
            \think\Log::info("[getResult] max_wu_xing或min_wu_xing为空，触发getSiZhuRes计算");
            $this->getSiZhuRes($record_id);
            // 重新查询
            $record_res = Db::name('record')
                ->where('id', $record_id)
                ->field('id, max_wu_xing, min_wu_xing, ju_ben, ju_ben_gan_zhi')
                ->find();
            if (empty($record_res['max_wu_xing']) || empty($record_res['min_wu_xing'])) {
                $this->error('计算结果失败，请重试');
            }
        }
        
        $step1 = microtime(true);
        $wang_yun = Db::name('wang_yun')->where('wu_xing_name', $record_res['min_wu_xing'])->find();
        $wang_yun['zhu_yi'] .= '、'.Db::name('wang_yun')->where('wu_xing_name', $record_res['max_wu_xing'])->value('zhu_yi');
        \think\Log::info("[getResult] 查询wang_yun耗时: " . round((microtime(true) - $step1) * 1000, 2) . "ms");
        
        $step2 = microtime(true);
        $xing_ge_max = Db::name('wu_xing_result')
            ->where('wu_xing', $record_res['max_wu_xing'])
            ->where('style', 0)
            ->find();
        $xing_ge_min = Db::name('wu_xing_result')
            ->where('wu_xing', $record_res['min_wu_xing'])
            ->where('style', 1)
            ->find();
        \think\Log::info("[getResult] 查询wu_xing_result耗时: " . round((microtime(true) - $step2) * 1000, 2) . "ms");
        
        Db::name('record')->where('id', $record_id)->update(['result' => '已完成']);
        
        // 基础返回数据
        $result = compact('record_res', 'wang_yun', 'xing_ge_max', 'xing_ge_min');
        
        // 二期扩展数据
        if ($version == '2026') {
            $step3 = microtime(true);
            $result['binfu'] = $this->getBinfuData($record_id, $record_res);
            \think\Log::info("[getResult] getBinfuData耗时: " . round((microtime(true) - $step3) * 1000, 2) . "ms");
            
            $step4 = microtime(true);
            $result['shi_shen'] = $this->getShiShenData($record_id, $record_res);
            \think\Log::info("[getResult] getShiShenData耗时: " . round((microtime(true) - $step4) * 1000, 2) . "ms");
            
            $step5 = microtime(true);
            $result['shen_sha'] = $this->getShenShaData($record_id);
            \think\Log::info("[getResult] getShenShaData耗时: " . round((microtime(true) - $step5) * 1000, 2) . "ms");
            
            $step6 = microtime(true);
            $result['fang_wei'] = $this->getFangWeiData($record_id);
            \think\Log::info("[getResult] getFangWeiData耗时: " . round((microtime(true) - $step6) * 1000, 2) . "ms");
            
            $step7 = microtime(true);
            $result['cang_gan'] = $this->getCangGanData($record_id);
            \think\Log::info("[getResult] getCangGanData耗时: " . round((microtime(true) - $step7) * 1000, 2) . "ms");
        }
        
        \think\Log::info("[getResult] 总耗时: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
        $this->success('获取成功', $result);
    }

    public function getAllWuXing($arr, $style = 1)
    {

        //print_r($arr);
        $all_wu_xing_num = array_count_values($arr);
        //print_r($all_wu_xing_num);

        if ($style == 1) {
            $gan_wu_xing = array_slice($arr, 0, 4);
            $zhi_wu_xing = array_slice($arr, 4, 4);
        } else {
            $gan_wu_xing = array_slice($arr, 0, 5);
            $zhi_wu_xing = array_slice($arr, 5, 5);
        }


        $gan_wu_xing_num = array_count_values($gan_wu_xing);
        //print_r($gan_wu_xing_num);
        $zhi_wu_xing_num = array_count_values($zhi_wu_xing);
        //print_r($zhi_wu_xing_num);
        return compact('arr', 'all_wu_xing_num', 'gan_wu_xing_num', 'zhi_wu_xing_num');
    }

    public function getMinWuXingName($record_id)
    {
        // 第一步：先看原局
        //1. 直接数数量，看哪个五行最少                                      3
        //2. 如出现原局五行缺失一种，则以缺失元素论最少                         2
        //3. 如果出现原局五行缺失两种及两种以上的情况，直接跳转到“看大运”所涉及方法  1
        //4.如果五行未缺失，但数量最少者出现并列的情况，则：                     4
        //a、看并列最少的五行里，哪个五行有更少的天干，则此五行为最少（透干少者为少）；4.2
        //b、如果天干和地支数量均一样多，则看哪个五行的天干透出的是正类十神，则此五行为最少，如果天干透出的均为正类或偏类十神，则看地支的十神，哪种地支是正类十神，则此五行为最少； 4.1
        //c、如果透出天干和地支的均为正类或者偏类，则看大运                       4.3

        $record_shen_res = Db::name('record_shen')->where('record_id', $record_id)->where('is_da_yun', 0)->select();
        $all_wu_xing_num = array_count_values(array_column($record_shen_res,'wu_xing'));
        $xing_arr = ['金', '木', '水', '火', '土'];

        $count_xu_xing = count($all_wu_xing_num);
        if ($count_xu_xing <= 3) {
            return $this->getDaYunMinWuXingName($record_id);
        }

        if ($count_xu_xing == 4) {
            $min_xing_arr = array_diff($xing_arr, array_keys($all_wu_xing_num));
            return array_values($min_xing_arr)[0];
        }
        $all_min = min($all_wu_xing_num);
        $all_wu_xing_result = array_keys($all_wu_xing_num, $all_min);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }
        //$all_wu_xing_result = ['金', '木', '火'];
        $four_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                ->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->count();
            array_push($four_gan_num, $num);
        }
        $min_gan = min($four_gan_num);
        $min_gan_result = array_keys($four_gan_num, $min_gan);
        if (count($min_gan_result) == 1) {
            return $all_wu_xing_result[$min_gan_result[0]];
        }

        $four1_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                ->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four1_gan_num, $num);
        }
        $min1_gan = max($four1_gan_num);
        if ($min1_gan > 0) {
            $min1_gan_result = array_keys($four1_gan_num, $min1_gan);
            if (count($min1_gan_result) == 1) {
                return $all_wu_xing_result[$min1_gan_result[0]];
            }
            // 看大运
            return $this->getDaYunMinWuXingName($record_id);
        }
        $four2_zhi_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 1)
                ->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four2_zhi_num, $num);
        }
        $min2_zhi = max($four2_zhi_num);
        if ($min2_zhi > 0) {
            $min2_zhi_result = array_keys($four2_zhi_num, $min2_zhi);
            if (count($min2_zhi_result) == 1) {
                return $all_wu_xing_result[$min2_zhi_result[0]];
            }
            // 看大运
            return $this->getDaYunMinWuXingName($record_id);
        }
        return '';
    }

    public function getDaYunMinWuXingName($record_id)
    {
        $record_shen_res = Db::name('record_shen')->where('record_id', $record_id)->select();
        $all_wu_xing_num = array_count_values(array_column($record_shen_res,'wu_xing'));
        $xing_arr = ['金', '木', '水', '火', '土'];
        $count_xu_xing = count($all_wu_xing_num);
        if ($count_xu_xing <= 3) {
            // 换个别的大运
            return $this->getNextDaYunMinWuXingName($record_id, 1);
        }
        if ($count_xu_xing == 4) {
            $min_xing_arr = array_diff($xing_arr, array_keys($all_wu_xing_num));
            return array_values($min_xing_arr)[0];
        }
        $all_min = min($all_wu_xing_num);
        $all_wu_xing_result = array_keys($all_wu_xing_num, $all_min);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }
        $four_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->count();
            array_push($four_gan_num, $num);
        }
        $min_gan = min($four_gan_num);
        $min_gan_result = array_keys($four_gan_num, $min_gan);
        if (count($min_gan_result) == 1) {
            return $all_wu_xing_result[$min_gan_result[0]];
        }
        $four1_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four_gan_num, $num);
        }
        $min1_gan = max($four1_gan_num);
        if ($min1_gan > 0) {
            $min1_gan_result = array_keys($four1_gan_num, $min1_gan);
            if (count($min1_gan_result) == 1) {
                return $all_wu_xing_result[$min1_gan_result[0]];
            }
            // 看大运
            return $this->getNextDaYunMinWuXingName($record_id, 1);
        }
        $four2_zhi_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 1)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four2_zhi_num, $num);
        }
        $min2_zhi = max($four2_zhi_num);
        if ($min2_zhi > 0) {
            $min2_zhi_result = array_keys($four2_zhi_num, $min2_zhi);
            if (count($min2_zhi_result) == 1) {
                return $all_wu_xing_result[$min2_zhi_result[0]];
            }
            // 看大运
            return $this->getNextDaYunMinWuXingName($record_id, 1);
        }
        return '';
    }
    public function getNextDaYunMinWuXingName($record_id, $del_key = 1)
    {
        $da_yun = $this->getQiYun($record_id, $del_key);

        Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('shen_in', 4)
            ->update([
                'shen_name' => $da_yun['gan_shi_shen'],
                'wu_xing' => $da_yun['gan_xing'],
                'gan_zhi_name' => mb_substr($da_yun['gan_zhi'], 0, 1)
        ]);
        Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('shen_in', 9)
            ->update([
                'shen_name' => $da_yun['zhi_shi_shen'],
                'wu_xing' => $da_yun['zhi_xing'],
                'gan_zhi_name' => mb_substr($da_yun['gan_zhi'], -1)
        ]);
        $record_shen_res = Db::name('record_shen')->where('record_id', $record_id)->select();
        $all_wu_xing_num = array_count_values(array_column($record_shen_res,'wu_xing'));
        $xing_arr = ['金', '木', '水', '火', '土'];
        $count_xu_xing = count($all_wu_xing_num);
        if ($count_xu_xing <= 3) {
            // 换个别的大运
            return $this->getNextDaYunMinWuXingName($record_id, 2);
        }
        if ($count_xu_xing == 4) {
            $min_xing_arr = array_diff($xing_arr, array_keys($all_wu_xing_num));
            return array_values($min_xing_arr)[0];
        }
        $all_min = min($all_wu_xing_num);
        $all_wu_xing_result = array_keys($all_wu_xing_num, $all_min);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }
        $four_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->count();
            array_push($four_gan_num, $num);
        }
        $min_gan = min($four_gan_num);
        $min_gan_result = array_keys($four_gan_num, $min_gan);
        if (count($min_gan_result) == 1) {
            return $all_wu_xing_result[$min_gan_result[0]];
        }
        $four1_gan_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 0)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four_gan_num, $num);
        }
        $min1_gan = max($four1_gan_num);
        if ($min1_gan > 0) {
            $min1_gan_result = array_keys($four1_gan_num, $min1_gan);
            if (count($min1_gan_result) == 1) {
                return $all_wu_xing_result[$min1_gan_result[0]];
            }
            // 看大运
            return $this->getNextDaYunMinWuXingName($record_id, 3);
        }
        $four2_zhi_num = [];
        foreach ($all_wu_xing_result as $v) {
            $num = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('gan_zhi_style', 1)
                //->where('is_da_yun', 0)
                ->where('wu_xing', $v)
                ->where('shen_style', 1)
                ->count();
            array_push($four2_zhi_num, $num);
        }
        $min2_zhi = max($four2_zhi_num);
        if ($min2_zhi > 0) {
            $min2_zhi_result = array_keys($four2_zhi_num, $min2_zhi);
            if (count($min2_zhi_result) == 1) {
                return $all_wu_xing_result[$min2_zhi_result[0]];
            }
            // 看大运
            return $this->getNextDaYunMinWuXingName($record_id, 4);
        }
        return '';
    }

    public function getWuXingName($get_qi_yun_gan_zhi_res, $wu_xing_zao, $gan_shi_shen, $zhi_shi_shen)
    {
        array_push($wu_xing_zao, $get_qi_yun_gan_zhi_res['gan_xing']);
        array_push($wu_xing_zao, $get_qi_yun_gan_zhi_res['zhi_xing']);
        $wu_xing_arr = $this->getAllWuXing($wu_xing_zao, 2);
        // 一
        $all_max = max($wu_xing_arr['all_wu_xing_num']);
        // echo "\n";
        $all_wu_xing_result = array_keys($wu_xing_arr['all_wu_xing_num'], $all_max);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }
        // 二
        $gan_all_max = max($wu_xing_arr['gan_wu_xing_num']);
        $gan_wu_xing_result = array_keys($wu_xing_arr['gan_wu_xing_num'], $gan_all_max);
        if (count($gan_wu_xing_result) == 1) {
            return $gan_wu_xing_result[0];
        }
        // 三
        $pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
        $pian_key = [];
        array_push($gan_shi_shen, $get_qi_yun_gan_zhi_res['gan_shi_shen']);
        foreach ($gan_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($pian_key, $k);
            }
        }
        if (count($pian_key) == 1) {
            if ($pian_key[0] == 0) {
                return $wu_xing_zao[0];
            }
            if ($pian_key[0] == 1) {
                return $wu_xing_zao[1];
            }
            if ($pian_key[0] == 2) {
                return $wu_xing_zao[3];// $wu_xing_zao[2];
            }
            if ($pian_key[0] == 3) {
                return $wu_xing_zao[8];
            }

        }
        if (count($pian_key) > 1) {
            $san_wu_xing_arr = [];
            foreach ($pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[0]);
                }
                if ($v == 1) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[1]);
                }
                if ($v == 2) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[3]); // $wu_xing_zao[2]
                }
                if ($v == 3) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[8]);
                }
            }
            if (in_array(count($pian_key), [2, 3])) {
                $max_xing = max(array_count_values($san_wu_xing_arr));
                $max_key_arr = array_keys($san_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }

        }
        // 四
        $zhi_pian_key = [];
        array_push($zhi_shi_shen, $get_qi_yun_gan_zhi_res['zhi_shi_shen']);
        foreach ($zhi_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($zhi_pian_key, $k);
            }
        }
        if (count($zhi_pian_key) == 1) {
            if ($zhi_pian_key[0] == 0) {
                return $wu_xing_zao[4];
            }
            if ($zhi_pian_key[0] == 1) {
                return $wu_xing_zao[5];
            }
            if ($zhi_pian_key[0] == 2) {
                return $wu_xing_zao[6];
            }
            if ($zhi_pian_key[0] == 3) {
                return $wu_xing_zao[7];
            }
            if ($zhi_pian_key[0] == 4) {
                return $wu_xing_zao[9];
            }
        }
        if (count($zhi_pian_key) > 1) {
            $si_wu_xing_arr = [];
            foreach ($zhi_pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[4]);
                }
                if ($v == 1) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[5]);
                }
                if ($v == 2) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[6]);
                }
                if ($v == 3) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[7]);
                }
                if ($v == 4) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[9]);
                }
            }
            if (in_array(count($zhi_pian_key), [2, 3, 4])) {
                $max_xing = max(array_count_values($si_wu_xing_arr));
                $max_key_arr = array_keys($si_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }
        }
    }

    public function getTeWuXingName($min_wu_xing_name, $wu_xing_zao, $record_id, $gan_shi_shen, $zhi_shi_shen)
    {
        $last_wu_xing_zao = $wu_xing_zao;
        $new_wu_xing_name = [];
        foreach ($wu_xing_zao as $k => $v) {
            if ($v == $min_wu_xing_name) {
                $wu_xing_zao[$k] = '';
            } else {
                $new_wu_xing_name[$k] = $v;
            }
        }
        //print_r($last_wu_xing_zao);
        //print_r($wu_xing_zao);
        //print_r($new_wu_xing_name);

        // 一
        $all_wu_xing_num = array_count_values($new_wu_xing_name);
        $all_max = max($all_wu_xing_num);
        $all_wu_xing_result = array_keys($all_wu_xing_num, $all_max);
        //print_r($all_wu_xing_result);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }

        // 二
        $gan_wu_xing = array_slice($wu_xing_zao, 0, 4);
        $zhi_wu_xing = array_slice($wu_xing_zao, 4, 4);
        foreach ($gan_wu_xing as $k => $v) {
            if ($v == '') {
                unset($gan_wu_xing[$k]);
            }
        }
        foreach ($zhi_wu_xing as $k => $v) {
            if ($v == '') {
                unset($zhi_wu_xing[$k]);
            }
        }
        $gan_wu_xing_num = array_count_values($gan_wu_xing);
        $gan_all_max = max($gan_wu_xing_num);
        $gan_wu_xing_result = array_keys($gan_wu_xing_num, $gan_all_max);
        //print_r($gan_wu_xing_result);exit;
        if (count($gan_wu_xing_result) == 1) {
            return $gan_wu_xing_result[0];
        }

        // 三
        $pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
        $pian_key = [];
        //print_r($gan_wu_xing);

        $gan_shi_shen[3] = $gan_shi_shen[2];
        $gan_shi_shen[2] = '';
        // print_r($gan_shi_shen);
        foreach ($gan_shi_shen as $k => $v) {
            if (isset($gan_wu_xing[$k])) {
                if (in_array($v, $pian_shen)) {
                    array_push($pian_key, $k);
                }
            }

        }

        if (count($pian_key) == 1) {
            if ($pian_key[0] == 0) {
                return $last_wu_xing_zao[0];
            }
            if ($pian_key[0] == 1) {
                return $last_wu_xing_zao[1];
            }
            if ($pian_key[0] == 3) {
                return $last_wu_xing_zao[3];
            }

        }
        if (count($pian_key) > 1) {
            $san_wu_xing_arr = [];
            foreach ($pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($san_wu_xing_arr, $last_wu_xing_zao[0]);
                }
                if ($v == 1) {
                    array_push($san_wu_xing_arr, $last_wu_xing_zao[1]);
                }
                if ($v == 3) {
                    array_push($san_wu_xing_arr, $last_wu_xing_zao[3]);
                }
            }
            if (in_array(count($pian_key), [2, 3])) {
                $max_xing = max(array_count_values($san_wu_xing_arr));
                $max_key_arr = array_keys($san_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }

        }
        // 四
        $zhi_pian_key = [];
        foreach ($zhi_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($zhi_pian_key, $k);
            }
        }
        if (count($zhi_pian_key) == 1) {
            if ($zhi_pian_key[0] == 0) {
                return $last_wu_xing_zao[4];
            }
            if ($zhi_pian_key[0] == 1) {
                return $last_wu_xing_zao[5];
            }
            if ($zhi_pian_key[0] == 2) {
                return $last_wu_xing_zao[6];
            }
            if ($zhi_pian_key[0] == 3) {
                return $last_wu_xing_zao[7];
            }
        }
        if (count($zhi_pian_key) > 1) {
            $si_wu_xing_arr = [];
            foreach ($zhi_pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($si_wu_xing_arr, $last_wu_xing_zao[4]);
                }
                if ($v == 1) {
                    array_push($si_wu_xing_arr, $last_wu_xing_zao[5]);
                }
                if ($v == 2) {
                    array_push($si_wu_xing_arr, $last_wu_xing_zao[6]);
                }
                if ($v == 3) {
                    array_push($si_wu_xing_arr, $last_wu_xing_zao[7]);
                }
            }
            if (in_array(count($zhi_pian_key), [2, 3, 4])) {
                $max_xing = max(array_count_values($si_wu_xing_arr));
                $max_key_arr = array_keys($si_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }
        }
        // 五
        $get_qi_yun_gan_zhi_res = $this->getQiYun($record_id);
        $new_te_wu_xing_name = $this->getNewWuXingName($get_qi_yun_gan_zhi_res, $wu_xing_zao, $gan_shi_shen, $zhi_shi_shen, $min_wu_xing_name);
        return $new_te_wu_xing_name;;
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
        $lunar = Lunar::fromYmd($date_arr[0],$date_arr[1],$date_arr[2], $hour, $minute);
        //echo $lunar->toFullString()."\n";
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

    public function getQiYun($record_id, $del_key = 0)
    {
        $record_res = Db::name('record')
            ->where('id', $record_id)
            //->where('user_id', $this->auth->id)
            ->find();
        if (empty($record_res)) $this->error('未获取到测算记录');
        $date_arr = explode('-', $record_res['yin_li_date']);
        if (count($date_arr) == 4) {
            if (empty($date_arr[1])) {
                $date_arr[1] = -$date_arr[2];
                $date_arr[2] = $date_arr[3];
            }
        }
        $lunar = Lunar::fromYmd($date_arr[0],$date_arr[1],$date_arr[2], $record_res['zhen_hour'], $record_res['zhen_minute']);
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
            $text[] =  $dayun->getStartYear()."年 ". $dayun->getStartAge()."岁 ".$dayun->getGanZhi();
            $da_yun[] = $dayun->getGanZhi();
            $age[] = $dayun->getStartAge() -1;
            $year[] = $dayun->getStartYear();
        }
        $now_age = date('Y') - $date_arr[0];
        $gan_zhi = '';
        foreach ($age as $k => $v) {
            if ($now_age <= $v) {
                $gan_zhi = $da_yun[$k - $del_key];
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

    public function getNewWuXingName($get_qi_yun_gan_zhi_res, $wu_xing_zao, $gan_shi_shen, $zhi_shi_shen, $min_wu_xing_name)
    {
        if ($get_qi_yun_gan_zhi_res['gan_xing'] == $min_wu_xing_name) {
            array_push($wu_xing_zao, '');
        } else {
            array_push($wu_xing_zao, $get_qi_yun_gan_zhi_res['gan_xing']);
        }
        if ($get_qi_yun_gan_zhi_res['zhi_xing'] == $min_wu_xing_name) {
            array_push($wu_xing_zao, '');
        } else {
            array_push($wu_xing_zao, $get_qi_yun_gan_zhi_res['zhi_xing']);
        }
        //print_r($wu_xing_zao);
        $wu_xing_arr = $this->getAllWuXing($wu_xing_zao, 2);
        //print_r($wu_xing_arr);

        // 一
        foreach ($wu_xing_arr['all_wu_xing_num'] as $k => $v) {
            if ($k == '') {
                unset($wu_xing_arr['all_wu_xing_num'][$k]);
            }
        }
        $all_max = max($wu_xing_arr['all_wu_xing_num']);
        // echo "\n";
        $all_wu_xing_result = array_keys($wu_xing_arr['all_wu_xing_num'], $all_max);
        if (count($all_wu_xing_result) == 1) {
            return $all_wu_xing_result[0];
        }
        // 二
        foreach ($wu_xing_arr['gan_wu_xing_num'] as $k => $v) {
            if ($k == '') {
                unset($wu_xing_arr['gan_wu_xing_num'][$k]);
            }
        }
        $gan_all_max = max($wu_xing_arr['gan_wu_xing_num']);
        $gan_wu_xing_result = array_keys($wu_xing_arr['gan_wu_xing_num'], $gan_all_max);
        if (count($gan_wu_xing_result) == 1) {
            return $gan_wu_xing_result[0];
        }
        // 三
        $zheng_shen = ['正官', '正印', '正财', '食神', '比肩'];
        $pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
        $pian_key = [];

        array_push($gan_shi_shen, $get_qi_yun_gan_zhi_res['gan_shi_shen']);
        foreach ($gan_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($pian_key, $k);
            }
        }
        if (count($pian_key) == 1) {
            if ($pian_key[0] == 0) {
                return $wu_xing_zao[0];
            }
            if ($pian_key[0] == 1) {
                return $wu_xing_zao[1];
            }
            /*if ($pian_key[0] == 2) {
                return $wu_xing_zao[3];
            }*/
            if ($pian_key[0] == 3) {
                return $wu_xing_zao[3];
            }
            if ($pian_key[0] == 4) {
                return $wu_xing_zao[8];
            }

        }
        if (count($pian_key) > 1) {
            $san_wu_xing_arr = [];
            foreach ($pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[0]);
                }
                if ($v == 1) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[1]);
                }
                if ($v == 3) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[3]); // $wu_xing_zao[2]
                }
                if ($v == 4) {
                    array_push($san_wu_xing_arr, $wu_xing_zao[8]);
                }
            }
            if (in_array(count($pian_key), [2, 3])) {
                $max_xing = max(array_count_values($san_wu_xing_arr));
                $max_key_arr = array_keys($san_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }

        }
        // 四
        $zhi_pian_key = [];
        array_push($zhi_shi_shen, $get_qi_yun_gan_zhi_res['zhi_shi_shen']);
        foreach ($zhi_shi_shen as $k => $v) {
            if (in_array($v, $pian_shen)) {
                array_push($zhi_pian_key, $k);
            }
        }
        if (count($zhi_pian_key) == 1) {
            if ($zhi_pian_key[0] == 0) {
                return $wu_xing_zao[4];
            }
            if ($zhi_pian_key[0] == 1) {
                return $wu_xing_zao[5];
            }
            if ($zhi_pian_key[0] == 2) {
                return $wu_xing_zao[6];
            }
            if ($zhi_pian_key[0] == 3) {
                return $wu_xing_zao[7];
            }
            if ($zhi_pian_key[0] == 4) {
                return $wu_xing_zao[9];
            }
        }
        if (count($zhi_pian_key) > 1) {
            $si_wu_xing_arr = [];
            foreach ($zhi_pian_key as $k => $v) {
                if ($v == 0) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[4]);
                }
                if ($v == 1) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[5]);
                }
                if ($v == 2) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[6]);
                }
                if ($v == 3) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[7]);
                }
                if ($v == 4) {
                    array_push($si_wu_xing_arr, $wu_xing_zao[9]);
                }
            }
            if (in_array(count($zhi_pian_key), [2, 3, 4])) {
                $max_xing = max(array_count_values($si_wu_xing_arr));
                $max_key_arr = array_keys($si_wu_xing_arr, $max_xing);
                if (count($max_key_arr) == 1) {
                    return $max_key_arr[0];
                }
            }
        }
    }

    // 回传接口
    public function notify($record_id)
    {
        // 调试日志：记录请求开始
        \think\Log::info("[notify] 开始处理回传请求, record_id: {$record_id}");
        
        if (empty($record_id)) {
            \think\Log::error("[notify] record_id为空");
            $this->error('record_id不能为空');
        }
        
        $record_res = Db::name('record')
            ->where('id', $record_id)
            ->find();
            
        // 调试日志：记录查询结果
        \think\Log::info("[notify] 查询record结果: " . json_encode($record_res));
        
        if (empty($record_res)) {
            \think\Log::error("[notify] 未找到record记录, record_id: {$record_id}");
            $this->error('未找到记录');
        }
        
        // 使用下划线命名的字段名（数据库实际字段名）
        $url = "https://test2.citicpruagents.com.cn/xytapp-sit/ext/components/v1/common/callback";
        $data = [
            'uid' => $record_id,
            'merchantId' => $record_res['merchant_id'] ?? $record_res['merchantId'] ?? '',
            'activityCode' => $record_res['activity_code'] ?? $record_res['activityCode'] ?? '',
            'agentCode' => $record_res['agent_code'] ?? $record_res['agentCode'] ?? '',
            'customerNo' => $record_res['customer_no'] ?? $record_res['customerNo'] ?? '',
            'result' => $record_res['result'] ?? ''
        ];
        
        // 调试日志：记录发送数据
        \think\Log::info("[notify] 准备发送数据: " . json_encode($data));
        
        // 要发送的数据
        ksort($data);
        $json_str = json_encode($data);
        $data['sign'] = md5($json_str.'e8893507eba541628598ed6605bd42ca');
        $payload = json_encode($data);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 调试日志：记录响应
        \think\Log::info("[notify] HTTP状态码: {$httpCode}, 响应: {$response}");

        if(curl_errno($ch)){
            $error = curl_error($ch);
            \think\Log::error("[notify] cURL错误: {$error}");
            curl_close($ch);
            $this->error('网络请求失败: ' . $error);
        }

        curl_close($ch);

        $result = json_decode($response, true);
        if (empty($result)) {
            \think\Log::error("[notify] 响应解析失败: {$response}");
            $this->error('响应解析失败');
        }
        
        if ($result['code'] != 200) {
            \think\Log::error("[notify] 回传失败: " . ($result['message'] ?? '未知错误'));
            $this->error($result['message'] ?? '回传失败');
        }
        
        \think\Log::info("[notify] 回传成功");
        $this->success('成功');
    }
    
    /**
     * 获取禀赋数据(二期)
     * 逻辑: 若某人八字中有某单一五行数量>=4且在月支出现,则以此五行论天生禀赋
     */
    private function getBinfuData($record_id, $record_res)
    {
        $binfu = [
            'hasBinfu' => false,
            'binfuWuXing' => '',
            'binfuDesc' => ''
        ];
        
        // 获取月支五行
        $month_zhi_res = Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('shen_in', 6)
            ->find();
        if (empty($month_zhi_res)) return $binfu;
        
        $month_zhi_wu_xing = $month_zhi_res['wu_xing'];
        
        // 统计八字中各五行数量
        $record_shen_list = Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('is_da_yun', 0)
            ->select();
        $wu_xing_count = [];
        foreach ($record_shen_list as $item) {
            $wx = $item['wu_xing'];
            if (!isset($wu_xing_count[$wx])) $wu_xing_count[$wx] = 0;
            $wu_xing_count[$wx]++;
        }
        
        // 检查是否有五行>=4且在月支出现
        foreach ($wu_xing_count as $wx => $count) {
            if ($count >= 4 && $wx == $month_zhi_wu_xing) {
                $binfu['hasBinfu'] = true;
                $binfu['binfuWuXing'] = $wx;
                // 获取禀赋描述
                $binfu_res = Db::name('binfu')
                    ->where('wu_xing', $wx)
                    ->where('is_binfu', 1)
                    ->find();
                $binfu['binfuDesc'] = $binfu_res ? $binfu_res['description'] : '';
                break;
            }
        }
        
        return $binfu;
    }
    
    /**
     * 获取十神数据(二期)
     * 包含流年十神和十神组合
     */
    private function getShiShenData($record_id, $record_res)
    {
        $shi_shen = [
            'liu_nian_shi_shen' => '',
            'liu_nian_desc' => '',
            'liu_nian_short_desc' => '',
            'has_zuhe' => false,
            'zuhe_name' => '',
            'zuhe_desc' => ''
        ];
        
        // 获取日干
        $day_gan_res = Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('shen_in', 2)
            ->find();
        if (empty($day_gan_res)) return $shi_shen;
        
        $day_gan = $day_gan_res['gan_zhi_name'];
        
        // 2026年丙午年,流年天干为丙
        $liu_nian_gan = '丙';
        
        // 计算流年十神
        $liu_nian_shi_shen_res = Db::name('si_zhu_shi_shen')
            ->where('ri_gan_name', $day_gan)
            ->where('gan_name', $liu_nian_gan)
            ->find();
        if ($liu_nian_shi_shen_res) {
            $shi_shen['liu_nian_shi_shen'] = $liu_nian_shi_shen_res['shi_shen_name'];
            
            // 获取十神描述
            $liu_nian_desc_res = Db::name('liu_nian_shi_shen')
                ->where('shi_shen_name', $shi_shen['liu_nian_shi_shen'])
                ->find();
            if ($liu_nian_desc_res) {
                $shi_shen['liu_nian_desc'] = $liu_nian_desc_res['description'];
                $shi_shen['liu_nian_short_desc'] = mb_substr($liu_nian_desc_res['description'], 0, 50) . '...';
            }
        }
        
        // 检查是否与原局形成组合
        $yuan_ju_shi_shen_list = Db::name('record_shen')
            ->where('record_id', $record_id)
            ->where('is_da_yun', 0)
            ->where('shen_in', '<', 4)
            ->column('shen_name');
        
        foreach ($yuan_ju_shi_shen_list as $yuan_ju_shi_shen) {
            if ($yuan_ju_shi_shen == '日元') continue;
            $zuhe_res = Db::name('shi_shen_zuhe')
                ->where('liu_nian_shi_shen', $shi_shen['liu_nian_shi_shen'])
                ->where('yuan_ju_shi_shen', $yuan_ju_shi_shen)
                ->find();
            if ($zuhe_res) {
                $shi_shen['has_zuhe'] = true;
                $shi_shen['zuhe_name'] = $zuhe_res['zuhe_name'];
                $shi_shen['zuhe_desc'] = $zuhe_res['description'];
                break;
            }
        }
        
        return $shi_shen;
    }
    
    /**
     * 获取神煞数据(二期)
     * 根据年干/年支/日干/日支匹配2026年神煞
     */
    private function getShenShaData($record_id)
    {
        $shen_sha_list = [];
        
        // 获取年干、年支、日干、日支
        $year_gan = Db::name('record_shen')->where('record_id', $record_id)->where('shen_in', 0)->value('gan_zhi_name');
        $year_zhi = Db::name('record_shen')->where('record_id', $record_id)->where('shen_in', 5)->value('gan_zhi_name');
        $day_gan = Db::name('record_shen')->where('record_id', $record_id)->where('shen_in', 2)->value('gan_zhi_name');
        $day_zhi = Db::name('record_shen')->where('record_id', $record_id)->where('shen_in', 7)->value('gan_zhi_name');
        
        // 获取所有2026年(午)的神煞配置
        $shen_sha_configs = Db::name('shen_sha')->where('liu_nian_zhi', '午')->select();
        
        foreach ($shen_sha_configs as $config) {
            $condition_values = explode(',', $config['condition_value']);
            $matched = false;
            
            switch ($config['condition_type']) {
                case 'year_gan':
                    $matched = in_array($year_gan, $condition_values) || in_array($day_gan, $condition_values);
                    break;
                case 'year_zhi':
                    $matched = in_array($year_zhi, $condition_values) || in_array($day_zhi, $condition_values);
                    break;
                case 'day_gan':
                    $matched = in_array($day_gan, $condition_values);
                    break;
                case 'day_zhi':
                    $matched = in_array($day_zhi, $condition_values);
                    break;
            }
            
            if ($matched) {
                $shen_sha_list[] = [
                    'name' => $config['shen_sha_name'],
                    'description' => $config['description']
                ];
            }
        }
        
        return $shen_sha_list;
    }
    
    /**
     * 获取方位数据(二期)
     * 事业位(正官)、财位(正财/偏财)、贵人位(正印)
     */
    private function getFangWeiData($record_id)
    {
        $fang_wei = [
            'shiyeWei' => '',
            'shiyeDesc' => '',
            'zhengcaiWei' => '',
            'piancaiWei' => '',
            'guirenWei' => '',
            'guirenDesc' => ''
        ];
        
        // 获取所有十神记录
        $record_shen_list = Db::name('record_shen')
            ->where('record_id', $record_id)
            ->select();
        
        foreach ($record_shen_list as $item) {
            $gan_zhi = $item['gan_zhi_name'];
            if (empty($gan_zhi)) continue;
            
            // 获取方位
            $fang_wei_res = Db::name('gan_zhi_fang_wei')->where('gan_zhi', $gan_zhi)->find();
            $fang_wei_str = $fang_wei_res ? $fang_wei_res['fang_wei'] : '';
            
            switch ($item['shen_name']) {
                case '正官':
                    if (empty($fang_wei['shiyeWei'])) {
                        $fang_wei['shiyeWei'] = $fang_wei_str;
                        $fang_wei['shiyeDesc'] = '正官所在方位,利于事业发展';
                    }
                    break;
                case '正财':
                    if (empty($fang_wei['zhengcaiWei'])) {
                        $fang_wei['zhengcaiWei'] = $fang_wei_str;
                    }
                    break;
                case '偏财':
                    if (empty($fang_wei['piancaiWei'])) {
                        $fang_wei['piancaiWei'] = $fang_wei_str;
                    }
                    break;
                case '正印':
                    if (empty($fang_wei['guirenWei'])) {
                        $fang_wei['guirenWei'] = $fang_wei_str;
                        $fang_wei['guirenDesc'] = '正印所在方位,利于遇贵人相助';
                    }
                    break;
            }
        }
        
        return $fang_wei;
    }
    
    /**
     * 获取藏干数据(二期)
     */
    private function getCangGanData($record_id)
    {
        $cang_gan = [];
        
        // 获取四柱地支
        $zhi_list = [
            ['shen_in' => 5, 'name' => '年支'],
            ['shen_in' => 6, 'name' => '月支'],
            ['shen_in' => 7, 'name' => '日支'],
            ['shen_in' => 8, 'name' => '时支']
        ];
        
        foreach ($zhi_list as $zhi) {
            $zhi_res = Db::name('record_shen')
                ->where('record_id', $record_id)
                ->where('shen_in', $zhi['shen_in'])
                ->find();
            if ($zhi_res) {
                $di_zhi = $zhi_res['gan_zhi_name'];
                $cang_gan_res = Db::name('di_zhi_cang_gan')->where('di_zhi', $di_zhi)->find();
                $cang_gan[] = [
                    'zhi' => $di_zhi,
                    'cang_gan' => $cang_gan_res ? $cang_gan_res['cang_gan'] : '',
                    'zhu_qi' => $cang_gan_res ? $cang_gan_res['zhu_qi'] : '',
                    'zhong_qi' => $cang_gan_res ? $cang_gan_res['zhong_qi'] : '',
                    'yu_qi' => $cang_gan_res ? $cang_gan_res['yu_qi'] : ''
                ];
            }
        }
        
        return $cang_gan;
    }
}