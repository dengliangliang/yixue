# 四柱八字计算模块 - 代码地图

## 文件结构

```
application/api/controller/
└── SiZhu.php                    # 四柱八字控制器

application/common/model/
├── Record.php                   # 测算记录模型
└── RecordShen.php               # 十神记录模型

vendor/6tail/lunar-php/
└── src/                         # lunar-php 库
    ├── Lunar.php                # 农历类
    └── Solar.php                # 阳历类

database/
├── fa_record                    # 测算记录表
├── fa_record_shen               # 十神记录表
├── fa_tian_gan                  # 天干表
├── fa_month_zhi                 # 地支表
├── fa_si_zhu_shi_shen           # 四柱十神关系表
├── fa_tian_gan_zhi              # 天干地支组合表
└── fa_year_ju_ben               # 年柱剧本表
```

## 类图

### SiZhu 控制器

```
SiZhu extends Api
├── Properties
│   ├── $noNeedLogin: array      # 无需登录的方法
│   └── $noNeedRight: array      # 无需鉴权的方法
│
├── Public Methods
│   ├── getSiZhuRes($record_id)  # 获取四柱结果
│   ├── getQiYun($record_id)     # 获取起运信息
│   ├── getResult($record_id)    # 获取完整结果
│   └── notify()                 # 通知回调
│
├── Protected Methods
│   ├── getAllWuXing($wu_xing_zao) # 统计五行
│   ├── getMinWuXingName($record_id) # 获取最弱五行
│   ├── getTeWuXingName(...)     # 特殊情况五行判断
│   ├── getNewWuXingName(...)    # 引入大运后五行判断
│   └── updRecord($record_id, $max, $min) # 更新记录
│
└── Static Methods
    └── getYearMonthDayTimeRes($date, $hour, $minute) # 获取干支信息
```

## 方法调用链

### 1. 获取四柱结果流程

```
getSiZhuRes($record_id)
    ├── Db::name('record')->find()           # 查询记录
    ├── Db::name('area')->find()             # 查询地区
    ├── Lunar::fromDate()                    # 获取当前农历
    ├── Db::name('record_shen')->find()      # 查询干支（×9）
    ├── Db::name('si_zhu_shi_shen')->value() # 查询剧本干支
    ├── Db::name('year_ju_ben')->value()     # 查询剧本文本
    ├── getAllWuXing($wu_xing_zao)           # 统计五行
    │   └── array_count_values()             # 统计数量
    ├── 五行判断算法（多级）
    │   ├── 第一级: 全局统计
    │   ├── 第二级: 天干统计
    │   ├── 第三级: 偏神优先（天干）
    │   ├── 第四级: 偏神优先（地支）
    │   └── 第五级: 引入大运
    │       └── getQiYun($record_id)
    │           └── getNewWuXingName(...)
    ├── getMinWuXingName($record_id)         # 获取最弱五行
    └── updRecord($record_id, $max, $min)    # 更新记录
```

### 2. 起运计算流程

```
getQiYun($record_id, $del_key=0)
    ├── Db::name('record')->find()           # 查询记录
    ├── Lunar::fromYmd()                     # 创建农历对象
    ├── $lunar->getEightChar()               # 获取八字对象
    ├── $baZi->getYun($gender, 2)            # 获取运对象
    ├── $yun->getDaYun()                     # 获取大运数组
    ├── foreach 遍历大运
    │   ├── $dayun->getStartYear()           # 起始年份
    │   ├── $dayun->getStartAge()            # 起始年龄
    │   └── $dayun->getGanZhi()              # 大运干支
    ├── 计算当前年龄对应的大运
    ├── Db::name('tian_gan')->value()        # 查询天干五行
    ├── Db::name('month_zhi')->value()       # 查询地支五行
    ├── Db::name('si_zhu_shi_shen')->value() # 查询天干十神
    └── Db::name('tian_gan_zhi')->value()    # 查询地支十神
```

### 3. 完整结果流程

```
getResult($record_id)
    ├── Db::name('record')->find()           # 查询记录
    ├── Db::name('area')->find()             # 查询地区
    ├── Db::name('record_shen')->select()    # 查询所有干支
    ├── getQiYun($record_id)                 # 获取起运信息
    ├── Db::name('xing_ge')->where()->find() # 查询性格（×2）
    └── 组装返回数据
```

## 数据库查询模式

### 查询记录信息
```php
$record_res = Db::name('record')
    ->where('id', $record_id)
    ->find();
```

### 查询干支信息
```php
// 查询年干
$year_gan_res = Db::name('record_shen')
    ->where('record_id', $record_id)
    ->where('shen_in', 0)
    ->find();

// 查询月干
$month_gan_res = Db::name('record_shen')
    ->where('record_id', $record_id)
    ->where('shen_in', 1)
    ->find();

// ... 依次查询日干、时干、年支、月支、日支、时支
```

### 查询五行属性
```php
// 查询天干五行
$gan_wu_xing = Db::name('tian_gan')
    ->where('tian_gan_name', $gan_name)
    ->value('attribute');

// 查询地支五行
$zhi_wu_xing = Db::name('month_zhi')
    ->where('month_name', $zhi_name)
    ->value('attribute');
```

### 查询十神关系
```php
// 查询天干十神
$gan_shi_shen = Db::name('si_zhu_shi_shen')
    ->where('ri_gan_name', $ri_gan_name)
    ->where('gan_name', $gan_name)
    ->value('shi_shen_name');

// 查询地支十神
$zhi_shi_shen = Db::name('tian_gan_zhi')
    ->where('gan_zhi_name', $gan_zhi)
    ->value('shi_shen');
```

## 算法实现

### 五行统计算法
```php
protected function getAllWuXing($wu_xing_zao, $type = 1)
{
    // 分离天干和地支
    $gan_wu_xing = array_slice($wu_xing_zao, 0, 4);
    $zhi_wu_xing = array_slice($wu_xing_zao, 4, 4);
    
    // 统计全部五行
    $all_wu_xing_num = array_count_values($wu_xing_zao);
    
    // 统计天干五行
    $gan_wu_xing_num = array_count_values($gan_wu_xing);
    
    // 统计地支五行
    $zhi_wu_xing_num = array_count_values($zhi_wu_xing);
    
    return [
        'all_wu_xing_num' => $all_wu_xing_num,
        'gan_wu_xing_num' => $gan_wu_xing_num,
        'zhi_wu_xing_num' => $zhi_wu_xing_num
    ];
}
```

### 五行判断决策树
```
判断最强五行:
├── 第一级: 全局统计
│   ├── 统计8个干支的五行
│   ├── 找出现次数最多的
│   └── 如果唯一 → 返回
│
├── 特殊情况处理
│   ├── 五行种类 < 5
│   ├── 最大次数 >= 4
│   ├── 最强五行 == 月支五行
│   └── 调用 getTeWuXingName()
│
├── 第二级: 天干统计
│   ├── 只统计4个天干
│   ├── 找出现次数最多的
│   └── 如果唯一 → 返回
│
├── 第三级: 天干偏神
│   ├── 找出天干中的偏神
│   ├── 如果只有1个 → 返回该位置五行
│   └── 如果有2-3个 → 统计这些位置的五行
│
├── 第四级: 地支偏神
│   ├── 找出地支中的偏神
│   ├── 如果只有1个 → 返回该位置五行
│   └── 如果有2-4个 → 统计这些位置的五行
│
└── 第五级: 引入大运
    ├── 获取当前大运干支
    ├── 将大运五行加入统计
    └── 重新执行第一级到第四级
```

## 依赖关系

### 外部依赖
```
SiZhu Controller
├── ThinkPHP Framework
│   ├── think\Db                 # 数据库操作
│   ├── think\Config             # 配置读取
│   └── think\Request            # 请求处理
│
├── lunar-php Library
│   ├── com\nlf\calendar\Lunar   # 农历类
│   └── com\nlf\calendar\Solar  # 阳历类
│
└── Common Components
    ├── app\common\controller\Api # API基类
    └── app\common\model\*        # 模型类
```

### 数据库依赖
```
SiZhu Controller
├── fa_record                    # 测算记录表（主表）
├── fa_record_shen               # 十神记录表（关联表）
├── fa_area                      # 地区表（关联表）
├── fa_tian_gan                  # 天干表（参考表）
├── fa_month_zhi                 # 地支表（参考表）
├── fa_si_zhu_shi_shen           # 十神关系表（参考表）
├── fa_tian_gan_zhi              # 干支组合表（参考表）
├── fa_year_ju_ben               # 年柱剧本表（参考表）
└── fa_xing_ge                   # 性格表（参考表）
```

## 性能考虑

### 查询优化
1. **批量查询**: 一次查询所有干支记录
   ```php
   $all_shen = Db::name('record_shen')
       ->where('record_id', $record_id)
       ->select();
   ```

2. **索引使用**: 
   - `record_id` 字段有索引
   - `shen_in` 字段有索引
   - 组合索引 `(record_id, shen_in)`

3. **缓存策略**:
   - 参考表数据可缓存（天干、地支、十神关系）
   - 测算结果可缓存（避免重复计算）

### 算法优化
1. **早期返回**: 每一级判断成功后立即返回
2. **避免重复计算**: 统计结果复用
3. **减少数据库查询**: 合并查询

## 错误处理

### 异常场景
1. 记录不存在
   ```php
   if (empty($record_res)) $this->error('未获取到测算记录');
   ```

2. 日期为空
   ```php
   if (empty($record_res['yin_li_date'])) $this->error('日期不能为空');
   ```

3. 数据不完整
   ```php
   if (empty($year_gan_res)) {
       // 重新生成数据
   }
   ```

## 扩展点

### 可扩展的功能
1. **新增五行判断规则**: 在五行判断算法中添加新级别
2. **自定义剧本**: 扩展 `fa_year_ju_ben` 表
3. **新增十神关系**: 扩展 `fa_si_zhu_shi_shen` 表
4. **支持其他历法**: 扩展 lunar-php 库

### 建议的改进
1. 使用缓存减少数据库查询
2. 批量查询优化性能
3. 添加日志记录
4. 添加性能监控

## 相关文档
- [功能说明](./README.md)
- [数据库设计](./database.md)
- [测试文档](./tests.md)
