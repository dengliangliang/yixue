# ADR-001: lunar-php 库选型决策

## 状态
已采纳

## 上下文

项目需要实现四柱八字测算功能，核心需求包括：
1. 阳历与农历的相互转换
2. 根据出生日期计算年、月、日、时的天干地支
3. 计算八字（四柱）
4. 计算大运信息
5. 支持真太阳时修正

在选择农历计算库时，需要考虑以下因素：
- 计算准确性
- API易用性
- 文档完整性
- 维护活跃度
- 性能表现
- License兼容性

## 决策

选择使用 **6tail/lunar-php** 库（版本 1.x）作为项目的农历计算核心库。

## 理由

### 1. 功能完整性
`lunar-php` 提供了完整的农历计算功能：
- ✅ 阳历转农历
- ✅ 农历转阳历
- ✅ 八字计算
- ✅ 大运计算
- ✅ 流年计算
- ✅ 节气计算
- ✅ 支持1900-2100年范围

### 2. API设计优秀
库的API设计简洁直观：

```php
// 从阳历创建
$solar = Solar::fromYmdHms(2023, 1, 1, 12, 0, 0);
$lunar = $solar->getLunar();

// 从农历创建
$lunar = Lunar::fromYmd(2023, 1, 1);
$solar = $lunar->getSolar();

// 获取八字
$baZi = $lunar->getEightChar();
$yearGan = $baZi->getYearGan();  // 年干
$yearZhi = $baZi->getYearZhi();  // 年支

// 获取大运
$yun = $baZi->getYun(1, 2);  // 性别, 起运方式
$daYunList = $yun->getDaYun();
```

### 3. 计算准确性
- 基于《钦定协纪辨方书》等权威古籍
- 支持真太阳时修正
- 节气计算精确到分钟
- 经过大量测试验证

### 4. 文档完整
- 提供详细的中文文档
- 包含丰富的示例代码
- API说明清晰

### 5. 维护活跃
- GitHub Star数较高
- 持续更新维护
- 社区活跃，问题响应及时

### 6. 性能表现
- 纯PHP实现，无需额外扩展
- 计算速度快
- 内存占用低

### 7. License兼容
- MIT License
- 可商用
- 无版权风险

## 备选方案

### 方案1: 自行实现
**优点**:
- 完全可控
- 可定制化

**缺点**:
- 开发成本高
- 需要深入研究农历算法
- 容易出错
- 维护成本高

**结论**: 不采纳。农历计算涉及复杂的天文算法，自行实现风险高。

### 方案2: overtrue/chinese-calendar
**优点**:
- 知名开发者维护
- 功能较完整

**缺点**:
- 主要面向日历展示
- 八字计算功能不完整
- 缺少大运计算
- API不够直观

**结论**: 不采纳。功能不满足需求。

### 方案3: 调用第三方API
**优点**:
- 无需维护算法
- 可能更准确

**缺点**:
- 依赖网络
- 可能收费
- 响应速度慢
- 数据隐私风险

**结论**: 不采纳。不符合项目要求。

## 实施细节

### 1. 安装方式
通过 Composer 安装：

```bash
composer require 6tail/lunar-php
```

在 `composer.json` 中：

```json
{
    "require": {
        "6tail/lunar-php": "^1.0"
    }
}
```

### 2. 使用场景

#### 场景1: 阳历转农历
```php
use com\nlf\calendar\Solar;

$solar = Solar::fromYmdHms($year, $month, $day, $hour, $minute, 0);
$lunar = $solar->getLunar();

$yinLiDate = $lunar->getYear() . '-' . $lunar->getMonth() . '-' . $lunar->getDay();
```

#### 场景2: 计算八字
```php
use com\nlf\calendar\Lunar;

$lunar = Lunar::fromYmd($year, $month, $day);
$baZi = $lunar->getEightChar();

$yearGan = $baZi->getYearGan();   // 年干
$yearZhi = $baZi->getYearZhi();   // 年支
$monthGan = $baZi->getMonthGan(); // 月干
$monthZhi = $baZi->getMonthZhi(); // 月支
$dayGan = $baZi->getDayGan();     // 日干
$dayZhi = $baZi->getDayZhi();     // 日支
$timeGan = $baZi->getTimeGan();   // 时干
$timeZhi = $baZi->getTimeZhi();   // 时支
```

#### 场景3: 计算大运
```php
$baZi = $lunar->getEightChar();
$yun = $baZi->getYun($gender, 2);  // $gender: 1=男, 0=女

$daYunList = $yun->getDaYun();
foreach ($daYunList as $daYun) {
    $startYear = $daYun->getStartYear();  // 起始年份
    $startAge = $daYun->getStartAge();    // 起始年龄
    $ganZhi = $daYun->getGanZhi();        // 大运干支
}
```

### 3. 真太阳时修正
项目中的真太阳时修正流程：

```php
// 1. 获取地区的真太阳时修正秒数
$area = Db::name('area')->where('id', $area_id)->find();
$zhen_second = $area['zhen_second'];

// 2. 修正时间（加上修正秒数和15分钟）
$timestamp = strtotime($date . ' ' . $hour . ':' . $minute . ':00');
$zhen_timestamp = $timestamp + $zhen_second + 900;

// 3. 23点特殊处理（跨日）
if ($hour == 23) {
    $zhen_timestamp = $zhen_timestamp + 86400;
}

// 4. 使用修正后的时间计算八字
$zhen_date = date('Y-m-d', $zhen_timestamp);
$zhen_hour = date('H', $zhen_timestamp);
$zhen_minute = date('i', $zhen_timestamp);

$solar = Solar::fromYmdHms($zhen_year, $zhen_month, $zhen_day, $zhen_hour, $zhen_minute, 0);
$lunar = $solar->getLunar();
```

### 4. 注意事项

#### 时辰边界处理
```php
// 23点-1点为子时，需要特殊处理
if ($hour == 23) {
    // 23点算作次日子时
    $zhen_timestamp = $zhen_timestamp + 86400;
}
```

#### 性别参数
```php
// 大运计算需要性别参数
// 男命和女命的大运顺逆不同
$gender = 1;  // 1=男, 0=女
$yun = $baZi->getYun($gender, 2);
```

#### 年份范围
```php
// lunar-php 支持 1900-2100 年
// 超出范围会抛出异常
if ($year < 1900 || $year > 2100) {
    throw new \Exception('年份超出支持范围');
}
```

## 影响

### 正面影响
1. **开发效率提升**: 无需自行实现复杂的农历算法
2. **准确性保证**: 使用经过验证的成熟库
3. **维护成本降低**: 库的更新和Bug修复由社区负责
4. **功能完整**: 满足项目所有农历计算需求

### 负面影响
1. **外部依赖**: 项目依赖第三方库
2. **版本锁定**: 需要锁定版本避免不兼容更新
3. **学习成本**: 团队需要学习库的API

### 风险缓解
1. **版本锁定**: 在 `composer.json` 中锁定版本号
2. **本地缓存**: Composer 会缓存依赖包
3. **备份方案**: 如果库停止维护，可以 fork 自行维护
4. **测试覆盖**: 编写充分的测试用例验证计算结果

## 经验教训

### 成功经验
1. 选择成熟的开源库可以大幅降低开发成本
2. API设计的易用性对开发效率影响很大
3. 完整的文档和示例对快速上手很重要

### 改进建议
1. 在项目初期就应该评估和选择核心依赖库
2. 应该编写更多的单元测试验证计算结果
3. 可以考虑对库的核心功能进行二次封装

## 相关决策
- [ADR-004: 真太阳时修正方案](./004-true-solar-time-correction.md)

## 参考资料
- [lunar-php GitHub](https://github.com/6tail/lunar-php)
- [lunar-php 文档](https://6tail.cn/calendar/api.html)
- [钦定协纪辨方书](https://zh.wikisource.org/wiki/%E6%AC%BD%E5%AE%9A%E5%8D%94%E7%B4%80%E8%BE%A8%E6%96%B9%E6%9B%B8)

---

**决策日期**: 2024-01-15  
**决策人**: 项目技术负责人  
**审核人**: 项目经理  
**最后更新**: 2025-01-19
