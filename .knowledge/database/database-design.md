# 数据库设计文档

## 数据库概述

### 基本信息
- **数据库名称**: yixue
- **字符集**: utf8mb4
- **排序规则**: utf8mb4_unicode_ci
- **存储引擎**: InnoDB
- **表前缀**: fa_

### 连接信息
- **主机**: 47.104.249.160
- **端口**: 3306
- **用户名**: yixue
- **密码**: JCpYZwjxmCDdHcCF

## 表分类

### 1. 核心业务表
- `fa_user` - 用户表
- `fa_record` - 测算记录表
- `fa_record_shen` - 十神记录表

### 2. 参考数据表
- `fa_tian_gan` - 天干表
- `fa_month_zhi` - 地支表
- `fa_si_zhu_shi_shen` - 四柱十神关系表
- `fa_tian_gan_zhi` - 天干地支组合表
- `fa_year_ju_ben` - 年柱剧本表
- `fa_xing_ge` - 性格表

### 3. 地区数据表
- `fa_area` - 地区表（包含真太阳时数据）

### 4. 系统管理表
- `fa_admin` - 管理员表
- `fa_admin_log` - 管理员日志表
- `fa_auth_group` - 权限组表
- `fa_auth_rule` - 权限规则表
- `fa_config` - 系统配置表
- `fa_attachment` - 附件表

## 核心业务表

### fa_user - 用户表

**用途**: 存储用户基本信息

```sql
CREATE TABLE `fa_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '组别ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `bio` varchar(100) NOT NULL DEFAULT '' COMMENT '格言',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '余额',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `successions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '连续登录天数',
  `maxsuccessions` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '最大连续登录天数',
  `prevtime` int(10) DEFAULT NULL COMMENT '上次登录时间',
  `logintime` int(10) DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `joinip` varchar(50) NOT NULL DEFAULT '' COMMENT '加入IP',
  `jointime` int(10) DEFAULT NULL COMMENT '加入时间',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) NOT NULL DEFAULT '' COMMENT 'Token',
  `status` varchar(30) NOT NULL DEFAULT '' COMMENT '状态',
  `verification` varchar(255) NOT NULL DEFAULT '' COMMENT '验证',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员表';
```

**字段说明**:
- `id`: 主键，自增
- `username`: 用户名，唯一索引
- `password`: MD5加密密码
- `salt`: 密码盐值
- `mobile`: 手机号，索引
- `token`: 登录令牌，用于API认证
- `status`: 用户状态（normal/hidden）
- `logintime`: 登录时间戳
- `createtime`: 创建时间戳

**索引**:
- PRIMARY KEY (`id`)
- KEY `username` (`username`)
- KEY `email` (`email`)
- KEY `mobile` (`mobile`)

### fa_record - 测算记录表

**用途**: 存储用户的测算记录

```sql
CREATE TABLE `fa_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `yang_li_date` date NOT NULL COMMENT '阳历日期',
  `yin_li_date` date NOT NULL COMMENT '阴历日期',
  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '时',
  `minute` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '分',
  `zhen_hour` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '真太阳时-时',
  `zhen_minute` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '真太阳时-分',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别:0=女,1=男',
  `area_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '地区ID',
  `area_name` varchar(100) NOT NULL DEFAULT '' COMMENT '地区名称',
  `max_wu_xing` varchar(10) NOT NULL DEFAULT '' COMMENT '最强五行',
  `min_wu_xing` varchar(10) NOT NULL DEFAULT '' COMMENT '最弱五行',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `yang_li_date` (`yang_li_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='测算记录表';
```

**字段说明**:
- `id`: 主键，自增
- `user_id`: 关联用户ID
- `yang_li_date`: 阳历出生日期
- `yin_li_date`: 阴历出生日期
- `hour/minute`: 出生时间
- `zhen_hour/zhen_minute`: 真太阳时修正后的时间
- `gender`: 性别（0=女，1=男）
- `area_id`: 出生地区ID
- `max_wu_xing`: 最强五行（喜用神）
- `min_wu_xing`: 最弱五行（忌神）

**索引**:
- PRIMARY KEY (`id`)
- KEY `user_id` (`user_id`)
- KEY `yang_li_date` (`yang_li_date`)

### fa_record_shen - 十神记录表

**用途**: 存储测算记录的详细干支和十神信息

```sql
CREATE TABLE `fa_record_shen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `record_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录ID',
  `shen_in` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '位置:0=年干,1=月干,2=日干,3=时干,4=年支,5=月支,6=日支,7=时支,8=大运干,9=大运支',
  `shen_name` varchar(10) NOT NULL DEFAULT '' COMMENT '干支名称',
  `shen_wu_xing` varchar(10) NOT NULL DEFAULT '' COMMENT '五行',
  `shen_shi_shen` varchar(10) NOT NULL DEFAULT '' COMMENT '十神',
  `shen_style` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '样式:0=偏神,1=正神',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `shen_in` (`shen_in`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='十神记录表';
```

**字段说明**:
- `id`: 主键，自增
- `record_id`: 关联测算记录ID
- `shen_in`: 干支位置
  - 0=年干, 1=月干, 2=日干, 3=时干
  - 4=年支, 5=月支, 6=日支, 7=时支
  - 8=大运干, 9=大运支
- `shen_name`: 干支名称（如"甲"、"子"）
- `shen_wu_xing`: 五行属性（金/木/水/火/土）
- `shen_shi_shen`: 十神名称（如"比肩"、"食神"）
- `shen_style`: 0=偏神，1=正神

**索引**:
- PRIMARY KEY (`id`)
- KEY `record_id` (`record_id`)
- KEY `shen_in` (`shen_in`)

## 参考数据表

### fa_tian_gan - 天干表

**用途**: 存储十天干的基本信息

```sql
CREATE TABLE `fa_tian_gan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tian_gan_name` varchar(10) NOT NULL DEFAULT '' COMMENT '天干名称',
  `attribute` varchar(10) NOT NULL DEFAULT '' COMMENT '五行属性',
  `yin_yang` varchar(10) NOT NULL DEFAULT '' COMMENT '阴阳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tian_gan_name` (`tian_gan_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='天干表';
```

**数据内容**:
- 甲（木-阳）、乙（木-阴）
- 丙（火-阳）、丁（火-阴）
- 戊（土-阳）、己（土-阴）
- 庚（金-阳）、辛（金-阴）
- 壬（水-阳）、癸（水-阴）

### fa_month_zhi - 地支表

**用途**: 存储十二地支的基本信息

```sql
CREATE TABLE `fa_month_zhi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `month_name` varchar(10) NOT NULL DEFAULT '' COMMENT '地支名称',
  `attribute` varchar(10) NOT NULL DEFAULT '' COMMENT '五行属性',
  `yin_yang` varchar(10) NOT NULL DEFAULT '' COMMENT '阴阳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `month_name` (`month_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地支表';
```

**数据内容**:
- 子（水-阳）、丑（土-阴）、寅（木-阳）
- 卯（木-阴）、辰（土-阳）、巳（火-阴）
- 午（火-阳）、未（土-阴）、申（金-阳）
- 酉（金-阴）、戌（土-阳）、亥（水-阴）

### fa_si_zhu_shi_shen - 四柱十神关系表

**用途**: 存储日干与其他天干的十神关系

```sql
CREATE TABLE `fa_si_zhu_shi_shen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `ri_gan_name` varchar(10) NOT NULL DEFAULT '' COMMENT '日干',
  `gan_name` varchar(10) NOT NULL DEFAULT '' COMMENT '天干',
  `shi_shen_name` varchar(10) NOT NULL DEFAULT '' COMMENT '十神名称',
  PRIMARY KEY (`id`),
  KEY `ri_gan_name` (`ri_gan_name`),
  KEY `gan_name` (`gan_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='四柱十神关系表';
```

**十神类型**:
- 比肩、劫财（比劫）
- 食神、伤官（食伤）
- 偏财、正财（财星）
- 偏官（七杀）、正官（官星）
- 偏印（枭神）、正印（印星）

### fa_tian_gan_zhi - 天干地支组合表

**用途**: 存储干支组合的十神关系

```sql
CREATE TABLE `fa_tian_gan_zhi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `gan_zhi_name` varchar(20) NOT NULL DEFAULT '' COMMENT '干支组合',
  `shi_shen` varchar(10) NOT NULL DEFAULT '' COMMENT '十神',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gan_zhi_name` (`gan_zhi_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='天干地支组合表';
```

**说明**: 存储日干+地支的组合，用于查询地支藏干的十神关系

### fa_year_ju_ben - 年柱剧本表

**用途**: 存储年柱干支对应的性格描述

```sql
CREATE TABLE `fa_year_ju_ben` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `gan_zhi` varchar(20) NOT NULL DEFAULT '' COMMENT '年柱干支',
  `ju_ben_text` text COMMENT '剧本内容',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gan_zhi` (`gan_zhi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='年柱剧本表';
```

### fa_xing_ge - 性格表

**用途**: 存储五行对应的性格描述

```sql
CREATE TABLE `fa_xing_ge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `wu_xing` varchar(10) NOT NULL DEFAULT '' COMMENT '五行',
  `xing_result` text COMMENT '性格描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `wu_xing` (`wu_xing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='性格表';
```

**数据内容**: 金、木、水、火、土五行的性格描述

## 地区数据表

### fa_area - 地区表

**用途**: 存储全国省市县地区数据，包含真太阳时修正值

```sql
CREATE TABLE `fa_area` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) DEFAULT NULL COMMENT '父ID',
  `shortname` varchar(100) DEFAULT NULL COMMENT '简称',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) DEFAULT NULL COMMENT '全称',
  `level` tinyint(4) DEFAULT NULL COMMENT '层级:1=省,2=市,3=区县',
  `pinyin` varchar(100) DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) DEFAULT NULL COMMENT '区号',
  `zip` varchar(100) DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) DEFAULT NULL COMMENT '纬度',
  `zhen_second` varchar(100) DEFAULT NULL COMMENT '真太阳时修正秒数',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地区表';
```

**字段说明**:
- `id`: 主键
- `pid`: 父级ID（0=省级，其他=市级或区县级）
- `level`: 层级（1=省，2=市，3=区县）
- `lng/lat`: 经纬度坐标
- `zhen_second`: **真太阳时修正秒数**（关键字段）

**真太阳时修正**:
- 根据地区经度计算与北京时间的时差
- 单位：秒
- 用于修正出生时辰，确保八字计算准确

**索引**:
- PRIMARY KEY (`id`)
- KEY `pid` (`pid`)
- KEY `level` (`level`)

## 系统管理表

### fa_admin - 管理员表

```sql
CREATE TABLE `fa_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(30) NOT NULL DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` int(10) DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) DEFAULT NULL COMMENT '登录IP',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) NOT NULL DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';
```

### fa_admin_log - 管理员日志表

```sql
CREATE TABLE `fa_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '管理员名字',
  `url` varchar(1500) NOT NULL DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '日志标题',
  `content` text NOT NULL COMMENT '内容',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `createtime` int(10) DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员日志表';
```

## 表关系图

```
fa_user (用户表)
    ↓ 1:N
fa_record (测算记录表)
    ↓ 1:10
fa_record_shen (十神记录表)

fa_record
    ↓ N:1
fa_area (地区表)

fa_record_shen
    ↓ N:1
fa_tian_gan (天干表)
fa_month_zhi (地支表)
fa_si_zhu_shi_shen (十神关系表)
fa_tian_gan_zhi (干支组合表)

fa_record
    ↓ N:1
fa_year_ju_ben (年柱剧本表)
fa_xing_ge (性格表)
```

## 数据字典

### 性别枚举
- `0`: 女
- `1`: 男

### 用户状态
- `normal`: 正常
- `hidden`: 禁用

### 干支位置（shen_in）
- `0`: 年干
- `1`: 月干
- `2`: 日干
- `3`: 时干
- `4`: 年支
- `5`: 月支
- `6`: 日支
- `7`: 时支
- `8`: 大运干
- `9`: 大运支

### 十神样式（shen_style）
- `0`: 偏神（偏财、偏官、偏印、劫财、伤官）
- `1`: 正神（正财、正官、正印、比肩、食神）

### 五行
- 金
- 木
- 水
- 火
- 土

### 阴阳
- 阳
- 阴

## 数据库操作规范

### 1. 参数化查询
**必须使用** `prepareParam()` 方法进行参数化查询：

```php
// 正确示例
$params = Db::prepareParam([
    ':user_id' => $user_id,
    ':date' => $date
]);
$result = Db::query("SELECT * FROM fa_record WHERE user_id = :user_id AND yang_li_date = :date", $params);

// 错误示例（禁止）
$result = Db::query("SELECT * FROM fa_record WHERE user_id = $user_id");
```

### 2. 事务操作
**必须使用静态方法**调用事务：

```php
// 正确示例
Db::begin();
try {
    // 数据库操作
    Db::commit();
} catch (\Throwable $e) {
    Db::rollback();
    throw $e;
}

// 错误示例（禁止）
$db->begin();  // 不要使用实例方法
```

### 3. 异常捕获
**必须捕获** `\Throwable` 异常：

```php
// 正确示例
try {
    // 数据库操作
} catch (\Throwable $e) {
    // 错误处理
}

// 错误示例（不完整）
catch (\Exception $e) {  // 无法捕获 Error
    // 错误处理
}
```

### 4. 软删除
用户表使用软删除，不物理删除记录：

```php
// 软删除
$user->status = 'hidden';
$user->save();

// 查询时排除已删除
$users = User::where('status', 'normal')->select();
```

## 性能优化

### 1. 索引策略
- 主键：所有表都有自增主键
- 外键字段：`user_id`, `record_id`, `area_id` 等
- 查询字段：`username`, `mobile`, `email`, `yang_li_date`
- 组合索引：`(record_id, shen_in)` 用于查询干支

### 2. 查询优化
- 避免 `SELECT *`，只查询需要的字段
- 使用 `LIMIT` 限制结果集
- 合理使用 `JOIN`，避免 N+1 查询
- 参考表数据可缓存（天干、地支、十神关系）

### 3. 分页查询
```php
$list = Db::name('record')
    ->where('user_id', $user_id)
    ->order('createtime', 'desc')
    ->paginate(10);
```

## 数据备份

### 备份策略
1. **每日全量备份**: 凌晨2点执行
2. **增量备份**: 每4小时一次
3. **备份保留**: 30天

### 备份命令
```bash
# 全量备份
mysqldump -h 47.104.249.160 -u yixue -p yixue > yixue_backup_$(date +%Y%m%d).sql

# 恢复
mysql -h 47.104.249.160 -u yixue -p yixue < yixue_backup_20250119.sql
```

## 数据迁移

### 增量SQL脚本规范
1. 文件命名：`YYYYMMDD_HHMMSS_description.sql`
2. 包含回滚语句（注释）
3. 测试后再执行

### 示例
```sql
-- 增量脚本：添加用户备注字段
-- 日期：2025-01-19
-- 作者：开发者

-- 执行
ALTER TABLE `fa_user` 
ADD COLUMN `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `bio`;

-- 回滚（如需）
-- ALTER TABLE `fa_user` DROP COLUMN `remark`;
```

## 相关文档
- [Constitution](../../.specify/memory/constitution.md)
- [用户管理模块](../features/user-management/README.md)
- [四柱计算模块](../features/sizhu-calculation/README.md)
