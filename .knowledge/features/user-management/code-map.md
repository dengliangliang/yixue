# 用户管理模块 - 代码地图

## 文件结构

```
application/api/controller/
└── User.php                     # 用户控制器

application/common/
├── controller/
│   └── Api.php                  # API基类
├── library/
│   ├── Auth.php                 # 认证库
│   ├── Sms.php                  # 短信库
│   └── Ems.php                  # 邮件库
└── model/
    └── User.php                 # 用户模型

database/
├── fa_user                      # 用户表
├── fa_record                    # 测算记录表
└── fa_area                      # 地区表
```

## 类图

### User 控制器

```
User extends Api
├── Properties
│   ├── $noNeedLogin: array      # 无需登录的方法
│   └── $noNeedRight: string     # 无需鉴权（全部）
│
├── Public Methods
│   ├── index()                  # 会员中心
│   ├── getProvinceList()        # 获取省份列表
│   ├── getCityList($id)         # 获取城市列表
│   ├── addRecord(...)           # 添加测算记录
│   ├── mobilelogin()            # 手机号登录
│   ├── register()               # 注册会员
│   ├── logout()                 # 退出登录
│   ├── profile()                # 修改个人信息
│   ├── changeemail()            # 修改邮箱
│   ├── changemobile()           # 修改手机号
│   ├── third()                  # 第三方登录
│   └── resetpwd()               # 重置密码
│
├── Protected Methods
│   └── updRecordRes($record_id) # 更新测算记录详情
│
└── Static Methods
    └── getYearMonthDayTimeRes($date, $hour, $minute) # 获取干支信息
```

### Auth 认证库

```
Auth (Singleton)
├── Properties
│   ├── $instance: Auth          # 单例实例
│   ├── $_error: string          # 错误信息
│   ├── $_logined: bool          # 登录状态
│   ├── $_user: User             # 用户对象
│   └── $_token: string          # Token
│
├── Public Methods
│   ├── instance()               # 获取单例
│   ├── init($token)             # 初始化
│   ├── isLogin()                # 是否登录
│   ├── getUser()                # 获取用户
│   ├── getUserinfo()            # 获取用户信息
│   ├── register(...)            # 注册
│   ├── login($username, $password) # 登录
│   ├── direct($user_id)         # 直接登录
│   ├── logout()                 # 退出
│   ├── changepwd(...)           # 修改密码
│   └── getError()               # 获取错误
│
└── Protected Methods
    └── setError($error)         # 设置错误
```

## 方法调用链

### 1. 添加测算记录流程

```
addRecord($customerNo, $date, ...)
    ├── 验签
    │   ├── rawurlencode($agentCode)
    │   ├── md5($str . 'secret_key')
    │   └── 比较签名
    │
    ├── 真太阳时修正
    │   ├── Db::name('area')->find()         # 查询地区
    │   ├── 获取 zhen_second
    │   ├── strtotime() + zhen_second + 900
    │   └── 23点特殊处理
    │
    ├── 阳历转农历
    │   ├── Solar::fromYmdHms()
    │   └── $solar->getLunar()
    │
    ├── 用户处理
    │   ├── User::get(['username' => $customerNo])
    │   ├── 如果不存在
    │   │   └── $this->auth->register()      # 自动注册
    │   └── 获取 user_id
    │
    ├── 检查重复记录
    │   └── Db::name('record')->where()->find()
    │
    ├── 插入记录
    │   └── Db::name('record')->insertGetId()
    │
    └── 生成详情
        └── updRecordRes($add_id)
```

### 2. 更新测算记录详情流程

```
updRecordRes($record_id)
    ├── 检查并清理不完整数据
    │   └── Db::name('record_shen')->delete()
    │
    ├── 查询记录
    │   └── Db::name('record')->find()
    │
    ├── 计算四柱
    │   └── getYearMonthDayTimeRes()
    │       └── Lunar::fromYmd()
    │
    ├── 计算起运
    │   └── getQiYun($record_id)
    │
    ├── 查询五行属性
    │   ├── Db::name('tian_gan')->value()    # 天干五行（×4）
    │   └── Db::name('month_zhi')->value()   # 地支五行（×4）
    │
    ├── 查询十神关系
    │   ├── Db::name('si_zhu_shi_shen')->value() # 天干十神（×4）
    │   └── Db::name('tian_gan_zhi')->value()    # 地支十神（×4）
    │
    ├── 判断十神样式
    │   └── in_array($shen, $pian_shen) ? 0 : 1
    │
    └── 插入10条记录
        └── Db::name('record_shen')->insert() # ×10
```

### 3. 手机号登录流程

```
mobilelogin()
    ├── 获取参数
    │   ├── $mobile
    │   └── $captcha
    │
    ├── 验证手机号格式
    │   └── Validate::regex($mobile, "^1\d{10}$")
    │
    ├── 验证短信验证码
    │   └── Sms::check($mobile, $captcha, 'mobilelogin')
    │
    ├── 查找用户
    │   └── User::getByMobile($mobile)
    │
    ├── 如果用户存在
    │   └── $this->auth->direct($user->id)   # 直接登录
    │
    ├── 如果用户不存在
    │   └── $this->auth->register()          # 自动注册
    │
    ├── 清除验证码
    │   └── Sms::flush($mobile, 'mobilelogin')
    │
    └── 返回用户信息
        └── $this->auth->getUserinfo()
```

### 4. 修改个人信息流程

```
profile()
    ├── 获取当前用户
    │   └── $this->auth->getUser()
    │
    ├── 获取参数
    │   ├── $username
    │   ├── $nickname
    │   ├── $bio
    │   └── $avatar
    │
    ├── 检查用户名唯一性
    │   └── User::where('username', $username)
    │       ->where('id', '<>', $this->auth->id)
    │       ->find()
    │
    ├── 检查昵称唯一性
    │   └── User::where('nickname', $nickname)
    │       ->where('id', '<>', $this->auth->id)
    │       ->find()
    │
    ├── 更新用户信息
    │   ├── $user->username = $username
    │   ├── $user->nickname = $nickname
    │   ├── $user->bio = $bio
    │   ├── $user->avatar = $avatar
    │   └── $user->save()
    │
    └── 返回成功
```

### 5. 重置密码流程

```
resetpwd()
    ├── 获取参数
    │   ├── $type (mobile/email)
    │   ├── $mobile / $email
    │   ├── $newpassword
    │   └── $captcha
    │
    ├── 验证密码格式
    │   └── Validate::make()->check()
    │
    ├── 如果是手机号重置
    │   ├── Validate::regex($mobile)
    │   ├── User::getByMobile($mobile)
    │   ├── Sms::check($mobile, $captcha)
    │   └── Sms::flush($mobile)
    │
    ├── 如果是邮箱重置
    │   ├── Validate::is($email, "email")
    │   ├── User::getByEmail($email)
    │   ├── Ems::check($email, $captcha)
    │   └── Ems::flush($email)
    │
    ├── 模拟登录
    │   └── $this->auth->direct($user->id)
    │
    ├── 修改密码
    │   └── $this->auth->changepwd($newpassword, '', true)
    │
    └── 返回成功
```

## 数据库查询模式

### 查询用户
```php
// 按用户名查询
$user = User::get(['username' => $username]);

// 按手机号查询
$user = User::getByMobile($mobile);

// 按邮箱查询
$user = User::getByEmail($email);

// 检查唯一性
$exists = User::where('username', $username)
    ->where('id', '<>', $user_id)
    ->find();
```

### 查询地区
```php
// 查询省份列表
$province_list = Db::name('area')
    ->where('pid', 0)
    ->field('id, name')
    ->select();

// 查询城市列表
$city_list = Db::name('area')
    ->where('pid', $province_id)
    ->order('first asc')
    ->field('id, name')
    ->select();

// 查询地区详情
$area_res = Db::name('area')
    ->where('id', $area_id)
    ->find();
```

### 查询测算记录
```php
// 检查重复记录
$chk_record = Db::name('record')
    ->where([
        'user_id' => $user_id,
        'yang_li_date' => $date,
        'hour' => $hour,
        'minute' => $minute,
        'gender' => $gender,
        'area_id' => $area_id
    ])->find();

// 插入记录
$add_id = Db::name('record')->insertGetId([
    'user_id' => $user_id,
    'user_name' => $customerNo,
    // ... 其他字段
]);
```

### 操作十神记录
```php
// 检查记录数量
$chk = Db::name('record_shen')
    ->where('record_id', $record_id)
    ->count();

// 删除不完整记录
Db::name('record_shen')
    ->where('record_id', $record_id)
    ->delete();

// 插入记录
Db::name('record_shen')->insert([
    'record_id' => $record_id,
    'shen_name' => $shen_name,
    // ... 其他字段
]);
```

## 认证流程

### 注册流程
```
register($username, $password, $email, $mobile, $extend)
    ├── 验证参数
    │   ├── 用户名不能为空
    │   ├── 密码不能为空
    │   └── 检查用户名是否存在
    │
    ├── 生成密码盐
    │   └── Random::alnum()
    │
    ├── 加密密码
    │   └── md5(md5($password) . $salt)
    │
    ├── 创建用户
    │   └── User::create([...])
    │
    ├── 生成Token
    │   └── Random::uuid()
    │
    ├── 保存Token
    │   └── $user->token = $token
    │
    └── 设置登录状态
        ├── $_logined = true
        ├── $_user = $user
        └── $_token = $token
```

### 登录流程
```
login($username, $password)
    ├── 查找用户
    │   └── User::get(['username' => $username])
    │
    ├── 验证密码
    │   └── md5(md5($password) . $user->salt) == $user->password
    │
    ├── 检查状态
    │   └── $user->status == 'normal'
    │
    ├── 生成Token
    │   └── Random::uuid()
    │
    ├── 更新登录信息
    │   ├── $user->logintime = time()
    │   ├── $user->loginip = request()->ip()
    │   └── $user->token = $token
    │
    └── 设置登录状态
        ├── $_logined = true
        ├── $_user = $user
        └── $_token = $token
```

### 直接登录流程
```
direct($user_id)
    ├── 查找用户
    │   └── User::get($user_id)
    │
    ├── 检查状态
    │   └── $user->status == 'normal'
    │
    ├── 生成Token
    │   └── Random::uuid()
    │
    ├── 更新Token
    │   └── $user->token = $token
    │
    └── 设置登录状态
        ├── $_logined = true
        ├── $_user = $user
        └── $_token = $token
```

## 验签机制

### 签名生成
```php
// 参数顺序: merchantId, activityCode, agentCode, customerNo
$agentCode = rawurlencode($agentCode);
$str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";

// MD5签名
$sign = md5($str . 'e8893507eba541628598ed6605bd42ca');
```

### 签名验证
```php
// 构建签名字符串
$agentCode = rawurlencode($agentCode);
$str = "merchantId=$merchantId&activityCode=$activityCode&agentCode=$agentCode&customerNo=$customerNo";

// 计算签名
$md5_str = md5($str . 'e8893507eba541628598ed6605bd42ca');

// 验证签名
if ($md5_str != $sign) {
    $this->error('验签错误');
}
```

## 依赖关系

### 外部依赖
```
User Controller
├── ThinkPHP Framework
│   ├── think\Db                 # 数据库操作
│   ├── think\Config             # 配置读取
│   ├── think\Validate           # 数据验证
│   └── think\Request            # 请求处理
│
├── lunar-php Library
│   ├── com\nlf\calendar\Lunar   # 农历类
│   └── com\nlf\calendar\Solar  # 阳历类
│
├── Common Libraries
│   ├── app\common\library\Auth  # 认证库
│   ├── app\common\library\Sms   # 短信库
│   └── app\common\library\Ems   # 邮件库
│
└── Common Components
    ├── app\common\controller\Api # API基类
    ├── app\common\model\User     # 用户模型
    └── fast\Random               # 随机字符串
```

### 数据库依赖
```
User Controller
├── fa_user                      # 用户表（主表）
├── fa_record                    # 测算记录表（关联表）
├── fa_record_shen               # 十神记录表（关联表）
├── fa_area                      # 地区表（关联表）
├── fa_tian_gan                  # 天干表（参考表）
├── fa_month_zhi                 # 地支表（参考表）
├── fa_si_zhu_shi_shen           # 十神关系表（参考表）
└── fa_tian_gan_zhi              # 干支组合表（参考表）
```

## 性能考虑

### 查询优化
1. **索引使用**:
   - `username` 唯一索引
   - `mobile` 索引
   - `email` 索引
   - `token` 索引

2. **批量操作**:
   - 批量插入 `record_shen` 记录
   - 使用事务保证一致性

3. **缓存策略**:
   - 用户信息缓存
   - Token缓存
   - 地区列表缓存

### 安全考虑
1. **密码安全**:
   - 使用盐值加密
   - 双重MD5加密
   - 密码强度验证

2. **Token安全**:
   - UUID生成
   - 定期更新
   - 服务端验证

3. **验签安全**:
   - 参数顺序固定
   - 密钥保密
   - URL编码处理

## 错误处理

### 常见错误
1. 参数错误
   ```php
   if (!$customerNo || !$date || !$area_id) {
       $this->error('参数不能为空');
   }
   ```

2. 验签错误
   ```php
   if ($md5_str != $sign) {
       $this->error('验签错误');
   }
   ```

3. 用户不存在
   ```php
   if (!$user) {
       $this->error(__('User not found'));
   }
   ```

4. 验证码错误
   ```php
   if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
       $this->error(__('Captcha is incorrect'));
   }
   ```

## 扩展点

### 可扩展的功能
1. **第三方登录**: 扩展 `third()` 方法
2. **多因素认证**: 添加二次验证
3. **社交账号绑定**: 绑定微信、QQ等
4. **用户等级系统**: 添加会员等级

### 建议的改进
1. 使用Redis缓存Token
2. 添加登录日志
3. 实现单点登录
4. 添加账号安全检查

## 相关文档
- [功能说明](./README.md)
- [认证机制](./auth.md)
- [API文档](./api.md)
