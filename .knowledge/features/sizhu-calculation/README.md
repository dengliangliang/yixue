# 四柱八字计算模块

## 模块概述

四柱八字计算是本项目的核心业务模块，负责根据用户的出生信息（阳历日期、时辰、地区）计算其命理八字信息。

### 核心功能
1. 阳历转农历日期
2. 真太阳时修正
3. 四柱（年柱、月柱、日柱、时柱）计算
4. 十神关系判定
5. 五行强弱分析
6. 起运时间计算

## 技术实现

### 主控制器
- **文件路径**: `application/api/controller/SiZhu.php`
- **命名空间**: `app\api\controller`
- **基类**: `app\common\controller\Api`

### 核心依赖
```php
use com\nlf\calendar\Lunar;  // 农历日期处理
use com\nlf\calendar\Solar;  // 阳历日期处理
use think\Db;                // 数据库操作
```

## 数据流程

### 1. 用户输入阶段
```
用户输入信息:
├── 阳历日期 (年-月-日)
├── 出生时辰 (小时:分钟)
├── 性别 (0=女, 1=男)
└── 出生地区 (area_id)
```

### 2. 数据转换阶段
```
阳历 → 真太阳时修正 → 农历
```

**真太阳时修正逻辑**:
```php
// 获取地区的真太阳时修正秒数
$zhen_second = $area_res['zhen_second'];

// 计算修正后的时间
$before_time = strtotime($yang_li_date . ' ' . $hour . ':' . $minute);
$now_time = $before_time + $zhen_second + 900; // +900秒(15分钟)作为缓冲

// 特殊处理: 如果修正后是23点，则进入下一天
if (date('H', $now_time) == '23') {
    $now_time += 86400; // +1天
}
```

### 3. 四柱计算阶段
```
农历日期 + 时辰 → lunar-php库 → 四柱干支
```

**四柱结构**:
- **年柱**: 年干 + 年支 (如: 甲子)
- **月柱**: 月干 + 月支 (如: 乙丑)
- **日柱**: 日干 + 日支 (如: 丙寅) - **日干为参照点**
- **时柱**: 时干 + 时支 (如: 丁卯)

### 4. 十神关系判定
```
日干 + 其他干支 → 查询 si_zhu_shi_shen 表 → 十神名称
```

**十神分类**:
- **正神**: 正官、正印、正财、食神、比肩
- **偏神**: 七杀、枭神、偏财、伤官、劫财

### 5. 五行分析
```
八个干支 → 提取五行属性 → 统计数量 → 判断强弱
```

## 核心方法说明

### getSiZhuRes($record_id)
获取四柱八字完整信息

**输入**: 测算记录ID
**输出**: 
```php
[
    'record_res' => [...],      // 测算记录信息
    'zao' => [...],             // 四柱干支数组
    'wu_xing_name' => '木',     // 最强五行
    'min_wu_xing_name' => '金'  // 最弱五行
]
```

**处理流程**:
1. 从 `record_shen` 表读取已保存的干支信息
2. 组装四柱数据结构
3. 执行五行强弱判断算法
4. 更新记录表的五行字段
5. 返回完整结果

### getYearMonthDayTimeRes($date, $hour, $minute)
静态方法，根据农历日期和时辰获取干支信息

**输入**:
- `$date`: 农历日期字符串 (格式: "年-月-日" 或 "年--闰月-日")
- `$hour`: 小时
- `$minute`: 分钟

**输出**:
```php
[
    'year_text' => '农历甲子年',
    'yang_li' => '阳历2024-01-01',
    'year' => '甲子',           // 年柱干支
    'year_gan_name' => '甲',    // 年干
    'year_zhi_name' => '子',    // 年支
    'month' => '乙丑',          // 月柱干支
    'day' => '丙寅',            // 日柱干支
    'time' => '丁卯',           // 时柱干支
    'time_text' => '卯',        // 时支
    'yin_li_month' => 1,        // 农历月
    'yin_li_day' => 1           // 农历日
]
```

**闰月处理**:
```php
// 闰月用负数表示
// 例如: "2024--4-15" 表示 2024年闰四月十五
if (count($date_arr) == 4) {
    if (empty($date_arr[1])) {
        $date_arr[1] = -$date_arr[2];  // 转换为负数
        $date_arr[2] = $date_arr[3];
    }
}
```

### getQiYun($record_id, $del_key)
计算起运信息和大运周期

**输入**:
- `$record_id`: 测算记录ID
- `$del_key`: 大运偏移量 (默认0)

**输出**:
```php
[
    'da_yun' => ['甲子', '乙丑', ...],  // 大运干支数组
    'year' => [2024, 2034, ...],        // 大运起始年份
    'age' => [1, 11, ...],              // 大运起始年龄
    'gan_zhi' => '甲子',                // 当前大运干支
    'gan_xing' => '木',                 // 大运天干五行
    'zhi_xing' => '水',                 // 大运地支五行
    'gan_shi_shen' => '正官',           // 大运天干十神
    'zhi_shi_shen' => '偏财'            // 大运地支十神
]
```

**大运计算逻辑**:
1. 使用 lunar-php 的 `getYun()` 方法
2. 根据性别和排盘方式(顺/逆)计算
3. 遍历大运数组，找到当前年龄对应的大运
4. 查询天干地支的五行和十神属性

## 五行强弱判断算法

### 算法概述
五行强弱判断是一个多级决策算法，按优先级依次判断，直到找到唯一的最强五行。

### 判断流程

#### 第一级: 全局统计
统计八个干支中各五行出现的次数，选择出现最多的五行。

```php
$wu_xing_zao = [
    年干五行, 月干五行, 日干五行, 时干五行,
    年支五行, 月支五行, 日支五行, 时支五行
];
$all_wu_xing_num = array_count_values($wu_xing_zao);
$all_max = max($all_wu_xing_num);
$all_wu_xing_result = array_keys($all_wu_xing_num, $all_max);

if (count($all_wu_xing_result) == 1) {
    // 找到唯一最强五行
    return $all_wu_xing_result[0];
}
```

**特殊情况处理**:
如果满足以下条件，则需要特殊处理:
- 五行种类 < 5
- 最大出现次数 >= 4
- 最强五行 == 月支五行

此时调用 `getTeWuXingName()` 方法重新计算。

#### 第二级: 天干统计
如果第一级无法确定，则只统计四个天干的五行。

```php
$gan_wu_xing = array_slice($wu_xing_zao, 0, 4);
$gan_wu_xing_num = array_count_values($gan_wu_xing);
$gan_all_max = max($gan_wu_xing_num);
$gan_wu_xing_result = array_keys($gan_wu_xing_num, $gan_all_max);

if (count($gan_wu_xing_result) == 1) {
    return $gan_wu_xing_result[0];
}
```

#### 第三级: 偏神优先
统计天干中的偏神数量，选择偏神对应的五行。

**偏神定义**:
```php
$pian_shen = ['七杀', '枭神', '偏财', '伤官', '劫财'];
```

**判断逻辑**:
1. 如果只有1个偏神，直接返回该位置的五行
2. 如果有2-3个偏神，统计这些位置的五行，选择出现最多的

#### 第四级: 地支偏神
如果天干偏神无法确定，则统计地支中的偏神。

逻辑与第三级相同，但作用于四个地支。

#### 第五级: 引入大运
如果前四级都无法确定，则将当前大运的干支五行加入统计，重新执行第一级到第四级的判断。

```php
$get_qi_yun_gan_zhi_res = $this->getQiYun($record_id);
$new_te_wu_xing_name = $this->getNewWuXingName(
    $get_qi_yun_gan_zhi_res, 
    $wu_xing_zao, 
    $gan_shi_shen, 
    $zhi_shi_shen, 
    $min_wu_xing_name
);
```

### 最弱五行判断
调用 `getMinWuXingName($record_id)` 方法，逻辑与最强五行判断类似，但选择出现次数最少的五行。

## 数据持久化

### record 表
存储测算记录的基本信息:
```sql
INSERT INTO fa_record (
    user_id,           -- 用户ID
    yang_li_date,      -- 阳历日期
    yin_li_date,       -- 农历日期
    hour,              -- 原始小时
    minute,            -- 原始分钟
    zhen_hour,         -- 修正后小时
    zhen_minute,       -- 修正后分钟
    zhen_yang_day,     -- 修正后阳历日期
    gender,            -- 性别
    area_id,           -- 地区ID
    max_wu_xing,       -- 最强五行
    min_wu_xing,       -- 最弱五行
    ju_ben,            -- 剧本文本
    ju_ben_gan_zhi     -- 剧本干支
) VALUES (...)
```

### record_shen 表
存储四柱和大运的详细信息:
```sql
INSERT INTO fa_record_shen (
    record_id,         -- 测算记录ID
    shen_name,         -- 十神名称
    shen_style,        -- 十神样式 (0=偏, 1=正, 2=日元)
    gan_zhi_style,     -- 干支样式 (0=天干, 1=地支)
    shen_in,           -- 位置 (0=年干, 1=月干, ..., 8=时支)
    is_da_yun,         -- 是否大运 (0=否, 1=是)
    gan_zhi_name,      -- 干支名称
    wu_xing            -- 五行属性
) VALUES (...)
```

**shen_in 位置编码**:
- 0: 年干
- 1: 月干
- 2: 日干
- 3: 时干
- 4: 大运天干 (未使用)
- 5: 年支
- 6: 月支
- 7: 日支
- 8: 时支
- 9: 大运地支 (未使用)

## API接口

### GET /api/sizhu/getSiZhuRes
获取四柱八字结果

**参数**:
- `record_id`: 测算记录ID

**返回**:
```json
{
    "code": 1,
    "msg": "获取成功",
    "data": {
        "record_res": {
            "id": 1,
            "user_id": 1,
            "yang_li_date": "2024-01-01",
            "yin_li_date": "2023-11-20",
            "city": "北京",
            "province": "北京市"
        },
        "zao": [
            {
                "text_top": "甲",
                "icon_top": "木",
                "text_bom": "子",
                "icon_bom": "水"
            },
            // ... 其他三柱
        ],
        "wu_xing_name": "木",
        "min_wu_xing_name": "金"
    }
}
```

### GET /api/sizhu/getQiYun
获取起运信息

**参数**:
- `record_id`: 测算记录ID
- `del_key`: 大运偏移量 (可选, 默认0)

**返回**:
```json
{
    "code": 1,
    "msg": "获取成功",
    "data": {
        "da_yun": ["甲子", "乙丑", "丙寅"],
        "year": [2024, 2034, 2044],
        "age": [1, 11, 21],
        "gan_zhi": "甲子",
        "gan_xing": "木",
        "zhi_xing": "水",
        "gan_shi_shen": "正官",
        "zhi_shi_shen": "偏财"
    }
}
```

## 注意事项

### 1. 闰月处理
- 闰月用负数月份表示
- 例如: 闰四月 = -4
- 数据库存储格式: "2024--4-15"

### 2. 真太阳时修正
- 不同地区有不同的修正秒数
- 存储在 `fa_area` 表的 `zhen_second` 字段
- 如果城市没有修正值，使用省份的修正值

### 3. 23点特殊处理
- 如果修正后时间为23点，自动进入下一天
- 这是为了避免子时跨日的问题

### 4. 数据一致性
- 创建记录时必须同时创建 record_shen 记录
- 删除记录时需级联删除 record_shen
- 更新五行信息时使用 `updRecord()` 静态方法

## 相关文档
- [数据库设计](./database.md)
- [代码地图](./code-map.md)
- [测试用例](./tests.md)
