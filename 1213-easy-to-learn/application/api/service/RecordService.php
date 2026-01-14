<?php

namespace app\api\service;

use com\nlf\calendar\Lunar;
use think\Db;

/**
 * 记录计算服务类
 * 将计算逻辑从控制器中分离，避免控制器实例化导致的认证问题
 */
class RecordService
{
    /**
     * 更新记录的四柱八字计算结果
     * @param int $record_id 记录ID
     */
    public static function updRecordRes($record_id)
    {
        \think\Log::info("[RecordService] ========== updRecordRes 开始 ==========");
        \think\Log::info("[RecordService] record_id: {$record_id}");

        try {
            $chk = Db::name('record_shen')->where('record_id', $record_id)->count();
            \think\Log::info("[RecordService] 现有record_shen数量: {$chk}");

            if ($chk > 0 && $chk < 9) {
                Db::name('record_shen')->where('record_id', $record_id)->delete();
                \think\Log::info("[RecordService] 删除不完整的record_shen记录");
            }

            $record_res = Db::name('record')->where('id', $record_id)->find();
            \think\Log::info("[RecordService] record_res: " . json_encode($record_res, JSON_UNESCAPED_UNICODE));

            if (empty($record_res)) {
                \think\Log::error("[RecordService] 错误: record不存在");
                return false;
            }

            if (empty($record_res['yin_li_date'])) {
                \think\Log::error("[RecordService] 错误: yin_li_date为空");
                return false;
            }

            $time_res = self::getYearMonthDayTimeRes($record_res['yin_li_date'], $record_res['zhen_hour'], $record_res['zhen_minute']);
            $month_gan_name = mb_substr($time_res['month'], 0, 1);
            $month_zhi_name = mb_substr($time_res['month'], -1);
            $ri_gan_name = mb_substr($time_res['day'], 0, 1);
            $ri_zhi_name = mb_substr($time_res['day'], -1);
            $time_gan_name = mb_substr($time_res['time'], 0, 1);

            $da_yun = self::getQiYunData($record_id);

            // 批量查询天干五行属性
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

            // 批量查询十神
            $shi_shen_list = Db::name('si_zhu_shi_shen')
                ->where('ri_gan_name', $ri_gan_name)
                ->whereIn('gan_name', [$time_res['year_gan_name'], $month_gan_name, $time_gan_name])
                ->column('shi_shen_name', 'gan_name');
            $gan_shi_shen = [
                $shi_shen_list[$time_res['year_gan_name']] ?? '',
                $shi_shen_list[$month_gan_name] ?? '',
                '日元',
                $shi_shen_list[$time_gan_name] ?? '',
                $da_yun['gan_shi_shen'] ?? ''
            ];

            // 批量查询支十神（使用tian_gan_zhi表）
            $zhi_gan_names = [
                $ri_gan_name . $time_res['year_zhi_name'],
                $ri_gan_name . $month_zhi_name,
                $ri_gan_name . $ri_zhi_name,
                $ri_gan_name . $time_res['time_text']
            ];
            $zhi_shi_shen_list = Db::name('tian_gan_zhi')
                ->whereIn('gan_zhi_name', $zhi_gan_names)
                ->column('shi_shen', 'gan_zhi_name');
            $zhi_shi_shen = [
                $zhi_shi_shen_list[$ri_gan_name . $time_res['year_zhi_name']] ?? '',
                $zhi_shi_shen_list[$ri_gan_name . $month_zhi_name] ?? '',
                $zhi_shi_shen_list[$ri_gan_name . $ri_zhi_name] ?? '',
                $zhi_shi_shen_list[$ri_gan_name . $time_res['time_text']] ?? '',
                $da_yun['zhi_shi_shen'] ?? ''
            ];

            // 计算十神类型（正类=1，偏类=0，日元=2）
            $zheng_shen = ['正官', '正印', '正财', '食神', '比肩'];
            $gan_shi_shen_style = [];
            foreach ($gan_shi_shen as $k => $v) {
                if ($k == 2) {
                    $gan_shi_shen_style[$k] = 2; // 日元
                } elseif (in_array($v, $zheng_shen)) {
                    $gan_shi_shen_style[$k] = 1;
                } else {
                    $gan_shi_shen_style[$k] = 0;
                }
            }
            $zhi_shi_shen_style = [];
            foreach ($zhi_shi_shen as $k => $v) {
                if (in_array($v, $zheng_shen)) {
                    $zhi_shi_shen_style[$k] = 1;
                } else {
                    $zhi_shi_shen_style[$k] = 0;
                }
            }

            // 批量插入record_shen（包含完整字段）
            $insert_data = [
                ['record_id' => $record_id, 'shen_in' => 0, 'gan_zhi_name' => $time_res['year_gan_name'], 'wu_xing' => $year_gan_wu_xing, 'shen_name' => $gan_shi_shen[0], 'shen_style' => $gan_shi_shen_style[0], 'gan_zhi_style' => 0, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 1, 'gan_zhi_name' => $month_gan_name, 'wu_xing' => $month_gan_wu_xing, 'shen_name' => $gan_shi_shen[1], 'shen_style' => $gan_shi_shen_style[1], 'gan_zhi_style' => 0, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 2, 'gan_zhi_name' => $ri_gan_name, 'wu_xing' => $ri_gan_wu_xing, 'shen_name' => $gan_shi_shen[2], 'shen_style' => 2, 'gan_zhi_style' => 0, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 3, 'gan_zhi_name' => $time_gan_name, 'wu_xing' => $time_gan_wu_xing, 'shen_name' => $gan_shi_shen[3], 'shen_style' => $gan_shi_shen_style[3], 'gan_zhi_style' => 0, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 4, 'gan_zhi_name' => $da_yun['gan_name'] ?? '', 'wu_xing' => $da_yun['gan_wu_xing'] ?? '', 'shen_name' => $gan_shi_shen[4], 'shen_style' => $gan_shi_shen_style[4] ?? 0, 'gan_zhi_style' => 0, 'is_da_yun' => 1],
                ['record_id' => $record_id, 'shen_in' => 5, 'gan_zhi_name' => $time_res['year_zhi_name'], 'wu_xing' => $year_zhi_wu_xing, 'shen_name' => $zhi_shi_shen[0], 'shen_style' => $zhi_shi_shen_style[0], 'gan_zhi_style' => 1, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 6, 'gan_zhi_name' => $month_zhi_name, 'wu_xing' => $month_zhi_wu_xing, 'shen_name' => $zhi_shi_shen[1], 'shen_style' => $zhi_shi_shen_style[1], 'gan_zhi_style' => 1, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 7, 'gan_zhi_name' => $ri_zhi_name, 'wu_xing' => $ri_zhi_wu_xing, 'shen_name' => $zhi_shi_shen[2], 'shen_style' => $zhi_shi_shen_style[2], 'gan_zhi_style' => 1, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 8, 'gan_zhi_name' => $time_res['time_text'], 'wu_xing' => $time_zhi_wu_xing, 'shen_name' => $zhi_shi_shen[3], 'shen_style' => $zhi_shi_shen_style[3], 'gan_zhi_style' => 1, 'is_da_yun' => 0],
                ['record_id' => $record_id, 'shen_in' => 9, 'gan_zhi_name' => $da_yun['zhi_name'] ?? '', 'wu_xing' => $da_yun['zhi_wu_xing'] ?? '', 'shen_name' => $zhi_shi_shen[4] ?? '', 'shen_style' => $zhi_shi_shen_style[4] ?? 0, 'gan_zhi_style' => 1, 'is_da_yun' => 1],
            ];

            \think\Log::info("[RecordService] 准备插入record_shen, 数量: " . count($insert_data));
            Db::name('record_shen')->insertAll($insert_data);
            \think\Log::info("[RecordService] record_shen插入成功");
            return true;
        } catch (\Exception $e) {
            \think\Log::error("[RecordService] 异常: " . $e->getMessage());
            \think\Log::error("[RecordService] 堆栈: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 获取年月日时的干支信息
     */
    public static function getYearMonthDayTimeRes($yin_li_date, $hour, $minute)
    {
        $date_arr = explode('-', $yin_li_date);
        $lunar = Lunar::fromYmdHms(intval($date_arr[0]), intval($date_arr[1]), intval($date_arr[2]), intval($hour), intval($minute), 0);

        return [
            'year' => $lunar->getYearInGanZhi(),
            'year_gan_name' => $lunar->getYearGan(),
            'year_zhi_name' => $lunar->getYearZhi(),
            'month' => $lunar->getMonthInGanZhi(),
            'day' => $lunar->getDayInGanZhiExact(),
            'time' => $lunar->getTimeInGanZhi(),
            'time_text' => $lunar->getTimeZhi()
        ];
    }

    /**
     * 获取起运数据
     */
    public static function getQiYunData($record_id)
    {
        $record_res = Db::name('record')->where('id', $record_id)->find();
        if (empty($record_res)) {
            return [];
        }

        $date_arr = explode('-', $record_res['yin_li_date']);
        $lunar = Lunar::fromYmdHms(intval($date_arr[0]), intval($date_arr[1]), intval($date_arr[2]), intval($record_res['zhen_hour']), intval($record_res['zhen_minute']), 0);

        // 修复：先获取Yun对象，再获取大运数组
        $yun = $lunar->getEightChar()->getYun($record_res['gender'], 2);
        $da_yun_arr = $yun->getDaYun();
        $now_year = date('Y');

        $result = [
            'gan_name' => '',
            'zhi_name' => '',
            'gan_wu_xing' => '',
            'zhi_wu_xing' => '',
            'gan_shi_shen' => '',
            'zhi_shi_shen' => ''
        ];

        foreach ($da_yun_arr as $k => $v) {
            if ($k == 0)
                continue;
            $start_year = $v->getStartYear();
            $end_year = $v->getEndYear();
            if ($now_year >= $start_year && $now_year <= $end_year) {
                $gan_zhi = $v->getGanZhi();
                $result['gan_name'] = mb_substr($gan_zhi, 0, 1);
                $result['zhi_name'] = mb_substr($gan_zhi, -1);

                // 获取五行
                $result['gan_wu_xing'] = Db::name('tian_gan')->where('tian_gan_name', $result['gan_name'])->value('attribute') ?: '';
                $result['zhi_wu_xing'] = Db::name('month_zhi')->where('month_name', $result['zhi_name'])->value('attribute') ?: '';

                // 获取十神
                $ri_gan_name = mb_substr($lunar->getDayInGanZhiExact(), 0, 1);
                $result['gan_shi_shen'] = Db::name('si_zhu_shi_shen')
                    ->where('ri_gan_name', $ri_gan_name)
                    ->where('gan_name', $result['gan_name'])
                    ->value('shi_shen_name') ?: '';
                $result['zhi_shi_shen'] = Db::name('tian_gan_zhi')
                    ->where('gan_zhi_name', $ri_gan_name . $result['zhi_name'])
                    ->value('shi_shen') ?: '';
                break;
            }
        }

        return $result;
    }
}
