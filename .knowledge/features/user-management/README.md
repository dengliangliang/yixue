# 用户管理模块

## 模块概述

用户管理模块负责处理用户注册、登录、信息管理等功能，是系统的基础模块。

### 核心功能
1. 用户注册（自动注册）
2. 手机号登录
3. 测算记录创建
4. 用户信息管理
5. 第三方登录（预留）

## 技术实现

### 主控制器
- **文件路径**: `application/api/controller/User.php`
- **命名空间**: `app\api\controller`
- **基类**: `app\common\controller\Api`

### 认证库
- **文件路径**: `application/common/library/Auth.php`
- **命名空间**: `app\common\library`
- **单例模式**: 通过 `Auth::instance()` 获取实例

## 用户注册流程

### 自动注册机制
本系统采用**自动注册**策略，用户在创建测算记录时自动创建账号。

```php
// 检查用户是否存在
$chk_user = \app\common\model\User::get(['username' => $customerNo]);
if (empty($chk_user)) {
    // 自动注册
    $ret = $this->auth->register(
        $customerNo,           // 用户名（客户编号）
        Random::alnum(),       // 随机密码
        '',                    // 邮箱（空）
        '',                    // 手机号（空）
        []                     // 扩展字段
    );
    $user_id = $this->auth->id;
} else {
    $user_id = $chk_user['id'];
}
```

### 注册特点
- **无需密码**: 自动生成随机密码
- **客户编号作为用户名**: 使用 `customerNo` 作为唯一标识
- **无需验证**: 不需要手机号或邮箱验证
- **即注即用**: 注册后立即可用

## 测算记录创建

### addRecord() 方法
这是用户管理模块的核心方法，整合了用户创建和测算记录保存。

**方法签名**:
```php
public function addRecord(
    $customerNo = '',    // 客户编号
    $date,               // 阳历日期
    $hour = 0,           // 小时
    $minute = 0,         // 分钟
    $gender = 0,         // 性别 (0=女, 1=男)
    $area_id = 0,        // 地区ID
    $merchantId = '',    // 商户ID
    $activityCode = '',  // 活动代码
    $agentCode = '',     // 代理代码
    $sign = ''           // 签名
)
```

### 验签机制
```php
// 构建签名字符串
$agentCode = rawurlencode($agentCode);
$str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";

// MD5签名
$md5_str = md5($str . 'e8893507eba541628598ed6605bd42ca');

// 验证签名
if ($md5_str != $sign) {
    $this->error('验签错误');
}
```

**签名密钥**: `e8893507eba541628598ed6605bd42ca`

### 真太阳时修正
```php
// 获取地区的真太阳时修正秒数
$area_res = Db::name('area')->where('id', $area_id)->find();
$zhen_second = $area_res['zhen_second'];

// 如果城市没有修正值，使用省份的修正值
if (empty($area_res['zhen_second'])) {
    $zhen_second = Db::name('area')
        ->where('pid', $area_res['pid'])
        ->value('zhen_second');
}

// 计算修正后的时间
$before_time = strtotime($yang_li_arr[0].'-'.$yang_li_arr[1].'-'.$yang_li_arr[2].' '.$hour.':'.$minute);
$now_time = $before_time + $zhen_second + 900; // +900秒(15分钟)

// 23点特殊处理
if (date('H', $now_time) == '23') {
    $now_time += 86400; // 进入下一天
}
```

### 重复记录检查
```php
$chk_record = Db::name('record')
    ->where([
        'user_id' => $user_id,
        'yang_li_date' => $date,
        'hour' => $hour,
        'minute' => $minute,
        'gender' => $gender,
        'area_id' => $area_id
    ])->find();

if (!empty($chk_record)) {
    // 返回已存在的记录ID
    $this->success('添加成功', ['record_id' => $chk_record['id']]);
}
```

### 记录保存
```php
$add_id = Db::name('record')->insertGetId([
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
    'createtime' => time()
]);

// 自动生成四柱信息
$this->updRecordRes($add_id);
```

## updRecordRes() 方法

自动生成测算记录的详细信息（四柱、十神、五行）。

### 处理流程
1. **清理旧数据**: 如果存在不完整的记录，先删除
2. **计算四柱**: 调用 `getYearMonthDayTimeRes()` 获取干支
3. **计算起运**: 调用 `getQiYun()` 获取大运信息
4. **查询五行**: 从 `tian_gan` 和 `month_zhi` 表查询五行属性
5. **查询十神**: 从 `si_zhu_shi_shen` 和 `tian_gan_zhi` 表查询十神关系
6. **保存数据**: 插入10条记录到 `record_shen` 表

### 数据插入
```php
// 年干
Db::name('record_shen')->insert([
    'record_id' => $record_id,
    'shen_name' => $gan_shi_shen[0],      // 十神名称
    'shen_style' => $gan_shi_shen_style[0], // 0=偏, 1=正
    'gan_zhi_style' => 0,                  // 0=天干
    'shen_in' => 0,                        // 位置编码
    'is_da_yun' => 0,                      // 0=非大运
    'gan_zhi_name' => $time_res['year_gan_name'],
    'wu_xing' => $year_gan_wu_xing
]);

// ... 依次插入月干、日干、时干、大运天干
// ... 依次插入年支、月支、日支、时支、大运地支
```

**位置编码 (shen_in)**:
- 0-3: 年干、月干、日干、时干
- 4: 大运天干
- 5-8: 年支、月支、日支、时支
- 9: 大运地支

## 手机号登录

### mobilelogin() 方法
支持手机号+验证码登录，如果用户不存在则自动注册。

```php
public function mobilelogin()
{
    $mobile = $this->request->post('mobile');
    $captcha = $this->request->post('captcha');
    
    // 验证手机号格式
    if (!Validate::regex($mobile, "^1\d{10}$")) {
        $this->error(__('Mobile is incorrect'));
    }
    
    // 验证短信验证码
    if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
        $this->error(__('Captcha is incorrect'));
    }
    
    // 查找用户
    $user = \app\common\model\User::getByMobile($mobile);
    if ($user) {
        // 已存在，直接登录
        $ret = $this->auth->direct($user->id);
    } else {
        // 不存在，自动注册
        $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
    }
    
    if ($ret) {
        Sms::flush($mobile, 'mobilelogin');
        $data = ['userinfo' => $this->auth->getUserinfo()];
        $this->success(__('Logged in successful'), $data);
    }
}
```

### 验证码机制
- **发送**: 调用 `Sms::send()` 发送验证码
- **验证**: 调用 `Sms::check()` 验证验证码
- **清除**: 调用 `Sms::flush()` 清除已使用的验证码

## 用户信息管理

### profile() - 修改个人信息
```php
public function profile()
{
    $user = $this->auth->getUser();
    $username = $this->request->post('username');
    $nickname = $this->request->post('nickname');
    $bio = $this->request->post('bio');
    $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
    
    // 检查用户名唯一性
    if ($username) {
        $exists = \app\common\model\User::where('username', $username)
            ->where('id', '<>', $this->auth->id)
            ->find();
        if ($exists) {
            $this->error(__('Username already exists'));
        }
        $user->username = $username;
    }
    
    // 检查昵称唯一性
    if ($nickname) {
        $exists = \app\common\model\User::where('nickname', $nickname)
            ->where('id', '<>', $this->auth->id)
            ->find();
        if ($exists) {
            $this->error(__('Nickname already exists'));
        }
        $user->nickname = $nickname;
    }
    
    $user->bio = $bio;
    $user->avatar = $avatar;
    $user->save();
}
```

### changeemail() - 修改邮箱
```php
public function changeemail()
{
    $user = $this->auth->getUser();
    $email = $this->request->post('email');
    $captcha = $this->request->post('captcha');
    
    // 验证邮箱格式
    if (!Validate::is($email, "email")) {
        $this->error(__('Email is incorrect'));
    }
    
    // 检查邮箱唯一性
    if (\app\common\model\User::where('email', $email)
        ->where('id', '<>', $user->id)
        ->find()) {
        $this->error(__('Email already exists'));
    }
    
    // 验证邮箱验证码
    $result = Ems::check($email, $captcha, 'changeemail');
    if (!$result) {
        $this->error(__('Captcha is incorrect'));
    }
    
    // 更新邮箱并标记为已验证
    $verification = $user->verification;
    $verification->email = 1;
    $user->verification = $verification;
    $user->email = $email;
    $user->save();
    
    Ems::flush($email, 'changeemail');
}
```

### changemobile() - 修改手机号
逻辑与 `changeemail()` 类似，使用短信验证码验证。

## 密码管理

### resetpwd() - 重置密码
支持手机号或邮箱重置密码。

```php
public function resetpwd()
{
    $type = $this->request->post("type", "mobile");
    $mobile = $this->request->post("mobile");
    $email = $this->request->post("email");
    $newpassword = $this->request->post("newpassword");
    $captcha = $this->request->post("captcha");
    
    // 验证密码格式
    if (!Validate::make()->check(
        ['newpassword' => $newpassword], 
        ['newpassword' => 'require|regex:\S{6,30}']
    )) {
        $this->error(__('Password must be 6 to 30 characters'));
    }
    
    if ($type == 'mobile') {
        // 手机号重置
        $user = \app\common\model\User::getByMobile($mobile);
        $ret = Sms::check($mobile, $captcha, 'resetpwd');
        Sms::flush($mobile, 'resetpwd');
    } else {
        // 邮箱重置
        $user = \app\common\model\User::getByEmail($email);
        $ret = Ems::check($email, $captcha, 'resetpwd');
        Ems::flush($email, 'resetpwd');
    }
    
    // 模拟登录并修改密码
    $this->auth->direct($user->id);
    $ret = $this->auth->changepwd($newpassword, '', true);
}
```

## 地区管理

### getProvinceList() - 获取省份列表
```php
public function getProvinceList()
{
    $province_list = Db::name('area')
        ->where('pid', 0)
        ->field('id, name')
        ->select();
    $this->success('获取成功', $province_list);
}
```

### getCityList($id) - 获取城市列表
```php
public function getCityList($id)
{
    if (empty($id)) $this->error('未获取到省份');
    $city_list = Db::name('area')
        ->where('pid', $id)
        ->order('first asc')
        ->field('id, name')
        ->select();
    $this->success('获取成功', $city_list);
}
```

## API接口

### POST /api/user/addRecord
创建测算记录（自动注册用户）

**参数**:
- `customerNo`: 客户编号
- `date`: 阳历日期 (YYYY-MM-DD)
- `hour`: 小时 (0-23)
- `minute`: 分钟 (0-59)
- `gender`: 性别 (0=女, 1=男)
- `area_id`: 地区ID
- `merchantId`: 商户ID
- `activityCode`: 活动代码
- `agentCode`: 代理代码
- `sign`: 签名

**返回**:
```json
{
    "code": 1,
    "msg": "添加成功",
    "data": {
        "record_id": 123
    }
}
```

### POST /api/user/mobilelogin
手机号登录

**参数**:
- `mobile`: 手机号
- `captcha`: 验证码

**返回**:
```json
{
    "code": 1,
    "msg": "登录成功",
    "data": {
        "userinfo": {
            "id": 1,
            "username": "13800138000",
            "nickname": "",
            "mobile": "13800138000",
            "avatar": "",
            "token": "xxx"
        }
    }
}
```

### GET /api/user/getProvinceList
获取省份列表

**返回**:
```json
{
    "code": 1,
    "msg": "获取成功",
    "data": [
        {"id": 1, "name": "北京"},
        {"id": 2, "name": "上海"}
    ]
}
```

### GET /api/user/getCityList
获取城市列表

**参数**:
- `id`: 省份ID

**返回**:
```json
{
    "code": 1,
    "msg": "获取成功",
    "data": [
        {"id": 10, "name": "东城区"},
        {"id": 11, "name": "西城区"}
    ]
}
```

## 数据表结构

### fa_user 表
```sql
CREATE TABLE `fa_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `salt` varchar(30) NOT NULL COMMENT '密码盐',
  `email` varchar(100) DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(11) DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `bio` varchar(255) DEFAULT '' COMMENT '个人简介',
  `status` varchar(30) DEFAULT 'normal' COMMENT '状态',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';
```

### fa_record 表
```sql
CREATE TABLE `fa_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `user_name` varchar(50) DEFAULT '' COMMENT '用户名',
  `yang_li_date` varchar(20) DEFAULT '' COMMENT '阳历日期',
  `yin_li_date` varchar(20) DEFAULT '' COMMENT '农历日期',
  `hour` tinyint(2) DEFAULT '0' COMMENT '小时',
  `minute` tinyint(2) DEFAULT '0' COMMENT '分钟',
  `zhen_hour` tinyint(2) DEFAULT '0' COMMENT '真太阳时小时',
  `zhen_minute` tinyint(2) DEFAULT '0' COMMENT '真太阳时分钟',
  `zhen_yang_day` varchar(20) DEFAULT '' COMMENT '真太阳时阳历日期',
  `gender` tinyint(1) DEFAULT '0' COMMENT '性别 0=女 1=男',
  `area_id` int(10) DEFAULT '0' COMMENT '地区ID',
  `max_wu_xing` varchar(10) DEFAULT '' COMMENT '最强五行',
  `min_wu_xing` varchar(10) DEFAULT '' COMMENT '最弱五行',
  `ju_ben` text COMMENT '剧本文本',
  `ju_ben_gan_zhi` varchar(10) DEFAULT '' COMMENT '剧本干支',
  `merchantId` varchar(50) DEFAULT '' COMMENT '商户ID',
  `activityCode` varchar(50) DEFAULT '' COMMENT '活动代码',
  `agentCode` varchar(50) DEFAULT '' COMMENT '代理代码',
  `customerNo` varchar(50) DEFAULT '' COMMENT '客户编号',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `customerNo` (`customerNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='测算记录表';
```

## 注意事项

### 1. 自动注册机制
- 用户无需主动注册
- 首次创建测算记录时自动创建账号
- 使用客户编号作为用户名
- 密码随机生成，用户可后续修改

### 2. 签名验证
- 所有 `addRecord` 请求必须验签
- 签名密钥: `e8893507eba541628598ed6605bd42ca`
- 签名算法: MD5
- 参数顺序: merchantId, activityCode, agentCode, customerNo

### 3. 重复记录处理
- 相同用户、相同出生信息的记录只保存一次
- 重复请求返回已存在的记录ID
- 避免数据冗余

### 4. 真太阳时修正
- 不同地区有不同的修正值
- 城市无修正值时使用省份的修正值
- 23点特殊处理避免跨日问题

### 5. 数据完整性
- 创建记录后自动生成四柱信息
- 使用 `updRecordRes()` 方法确保数据完整
- 不完整的记录会被自动清理重建

## 相关文档
- [认证授权机制](./auth.md)
- [数据库设计](./database.md)
- [API接口文档](./api.md)
