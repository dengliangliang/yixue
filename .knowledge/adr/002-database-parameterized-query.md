# ADR-002: 数据库参数化查询规范

## 状态
已采纳并强制执行

## 上下文

项目使用 ThinkPHP 5.1 框架和自定义的数据库操作类。在数据库查询中，存在以下安全风险：

1. **SQL注入风险**: 直接拼接用户输入到SQL语句
2. **数据类型错误**: 未对输入进行类型验证
3. **特殊字符处理**: 引号、反斜杠等特殊字符未转义

传统的PDO参数绑定方式在项目的自定义数据库类中不适用，需要使用项目特定的参数化方法。

## 决策

**强制要求**所有数据库查询必须使用 `Db()->prepareParam()` 方法进行参数化查询，禁止直接拼接SQL语句。

## 理由

### 1. 安全性
参数化查询可以有效防止SQL注入攻击：

```php
// ❌ 危险：SQL注入风险
$username = $_POST['username'];
$sql = "SELECT * FROM fa_user WHERE username = '$username'";
// 如果 $username = "admin' OR '1'='1"，将绕过验证

// ✅ 安全：参数化查询
$params = Db::prepareParam([':username' => $username]);
$sql = "SELECT * FROM fa_user WHERE username = :username";
$result = Db::query($sql, $params);
```

### 2. 类型安全
`prepareParam()` 方法会自动处理数据类型：

```php
$params = Db::prepareParam([
    ':user_id' => 123,        // 整数
    ':amount' => 99.99,       // 浮点数
    ':username' => 'test',    // 字符串
    ':is_active' => true      // 布尔值
]);
```

### 3. 特殊字符处理
自动转义特殊字符，避免语法错误：

```php
$params = Db::prepareParam([
    ':content' => "It's a test with 'quotes' and \"double quotes\""
]);
// 自动转义，不会导致SQL语法错误
```

### 4. 代码可读性
参数化查询使SQL语句更清晰：

```php
// 清晰的参数占位符
$sql = "SELECT * FROM fa_record 
        WHERE user_id = :user_id 
        AND yang_li_date = :date 
        AND gender = :gender";

$params = Db::prepareParam([
    ':user_id' => $user_id,
    ':date' => $date,
    ':gender' => $gender
]);
```

### 5. 性能优化
数据库可以缓存执行计划，提高重复查询的性能。

## 备选方案

### 方案1: ThinkPHP ORM
**优点**:
- 框架原生支持
- 自动参数化
- 链式调用

**缺点**:
- 项目已有自定义数据库类
- 迁移成本高
- 复杂查询不够灵活

**结论**: 不采纳。项目已有成熟的数据库操作方式。

### 方案2: 标准PDO绑定
**优点**:
- PHP标准方式
- 文档丰富

**缺点**:
- 项目的自定义数据库类不支持
- 需要重构现有代码

**结论**: 不采纳。与项目架构不兼容。

### 方案3: 手动转义
**优点**:
- 简单直接

**缺点**:
- 容易遗漏
- 不同数据库转义规则不同
- 维护困难

**结论**: 不采纳。安全性无法保证。

## 实施细节

### 1. 基本用法

#### 单条件查询
```php
$params = Db::prepareParam([':user_id' => $user_id]);
$sql = "SELECT * FROM fa_user WHERE id = :user_id";
$result = Db::query($sql, $params);
```

#### 多条件查询
```php
$params = Db::prepareParam([
    ':user_id' => $user_id,
    ':start_date' => $start_date,
    ':end_date' => $end_date
]);

$sql = "SELECT * FROM fa_record 
        WHERE user_id = :user_id 
        AND yang_li_date BETWEEN :start_date AND :end_date";

$result = Db::query($sql, $params);
```

#### INSERT语句
```php
$params = Db::prepareParam([
    ':user_id' => $user_id,
    ':username' => $username,
    ':email' => $email,
    ':createtime' => time()
]);

$sql = "INSERT INTO fa_user (user_id, username, email, createtime) 
        VALUES (:user_id, :username, :email, :createtime)";

Db::execute($sql, $params);
```

#### UPDATE语句
```php
$params = Db::prepareParam([
    ':nickname' => $nickname,
    ':avatar' => $avatar,
    ':user_id' => $user_id
]);

$sql = "UPDATE fa_user 
        SET nickname = :nickname, avatar = :avatar 
        WHERE id = :user_id";

Db::execute($sql, $params);
```

#### DELETE语句
```php
$params = Db::prepareParam([':user_id' => $user_id]);
$sql = "DELETE FROM fa_user WHERE id = :user_id";
Db::execute($sql, $params);
```

### 2. 特殊场景处理

#### IN查询
```php
// 方法1: 使用多个占位符
$ids = [1, 2, 3, 4, 5];
$placeholders = [];
$params = [];

foreach ($ids as $index => $id) {
    $key = ":id_$index";
    $placeholders[] = $key;
    $params[$key] = $id;
}

$params = Db::prepareParam($params);
$sql = "SELECT * FROM fa_user WHERE id IN (" . implode(',', $placeholders) . ")";
$result = Db::query($sql, $params);

// 方法2: 使用 ThinkPHP 查询构造器
$result = Db::name('user')->where('id', 'in', $ids)->select();
```

#### LIKE查询
```php
$keyword = $_GET['keyword'];

// 在参数中添加通配符
$params = Db::prepareParam([
    ':keyword' => '%' . $keyword . '%'
]);

$sql = "SELECT * FROM fa_user WHERE username LIKE :keyword";
$result = Db::query($sql, $params);
```

#### NULL值处理
```php
$params = Db::prepareParam([
    ':email' => $email,  // 可能为 null
    ':user_id' => $user_id
]);

// 如果 $email 为 null，SQL中会正确处理为 NULL
$sql = "UPDATE fa_user SET email = :email WHERE id = :user_id";
Db::execute($sql, $params);
```

### 3. 事务中使用

```php
Db::begin();
try {
    // 插入用户
    $params1 = Db::prepareParam([
        ':username' => $username,
        ':password' => $password
    ]);
    $sql1 = "INSERT INTO fa_user (username, password) VALUES (:username, :password)";
    Db::execute($sql1, $params1);
    $user_id = Db::getLastInsID();
    
    // 插入记录
    $params2 = Db::prepareParam([
        ':user_id' => $user_id,
        ':date' => $date
    ]);
    $sql2 = "INSERT INTO fa_record (user_id, yang_li_date) VALUES (:user_id, :date)";
    Db::execute($sql2, $params2);
    
    Db::commit();
} catch (\Throwable $e) {
    Db::rollback();
    throw $e;
}
```

### 4. 代码审查检查点

在代码审查时，必须检查以下内容：

✅ **必须做**:
- [ ] 所有用户输入都经过参数化
- [ ] 使用 `prepareParam()` 方法
- [ ] 参数名使用冒号前缀（`:param_name`）
- [ ] SQL语句中使用占位符

❌ **禁止做**:
- [ ] 直接拼接用户输入到SQL
- [ ] 使用字符串插值（`"... $var ..."`）
- [ ] 手动转义特殊字符
- [ ] 信任任何外部输入

### 5. 错误示例和修正

#### 错误示例1: 直接拼接
```php
// ❌ 错误
$username = $_POST['username'];
$sql = "SELECT * FROM fa_user WHERE username = '$username'";
$result = Db::query($sql);

// ✅ 正确
$params = Db::prepareParam([':username' => $_POST['username']]);
$sql = "SELECT * FROM fa_user WHERE username = :username";
$result = Db::query($sql, $params);
```

#### 错误示例2: 字符串插值
```php
// ❌ 错误
$user_id = $_GET['user_id'];
$sql = "SELECT * FROM fa_record WHERE user_id = $user_id";
$result = Db::query($sql);

// ✅ 正确
$params = Db::prepareParam([':user_id' => $_GET['user_id']]);
$sql = "SELECT * FROM fa_record WHERE user_id = :user_id";
$result = Db::query($sql, $params);
```

#### 错误示例3: 手动转义
```php
// ❌ 错误
$username = addslashes($_POST['username']);
$sql = "SELECT * FROM fa_user WHERE username = '$username'";
$result = Db::query($sql);

// ✅ 正确
$params = Db::prepareParam([':username' => $_POST['username']]);
$sql = "SELECT * FROM fa_user WHERE username = :username";
$result = Db::query($sql, $params);
```

## 影响

### 正面影响
1. **安全性大幅提升**: 消除SQL注入风险
2. **代码质量提高**: 统一的查询规范
3. **维护成本降低**: 减少安全漏洞修复
4. **性能优化**: 数据库可缓存执行计划

### 负面影响
1. **代码量增加**: 需要额外的参数准备代码
2. **学习成本**: 团队需要学习新规范
3. **历史代码**: 需要逐步重构旧代码

### 风险缓解
1. **代码审查**: 严格审查所有数据库查询
2. **静态分析**: 使用工具检测不安全的查询
3. **培训**: 对团队进行安全编码培训
4. **文档**: 提供详细的使用示例

## 强制执行

### 1. 代码审查清单
所有涉及数据库查询的代码必须通过以下检查：

- [ ] 使用 `prepareParam()` 方法
- [ ] 没有直接拼接用户输入
- [ ] 参数命名规范（`:param_name`）
- [ ] 特殊场景（IN, LIKE）正确处理

### 2. 自动化检查
可以使用静态分析工具检测：

```php
// 检测模式：直接拼接变量到SQL
// 正则表达式：Db::query\([^:]*\$
```

### 3. 培训要求
所有开发人员必须：
- 理解SQL注入原理
- 掌握 `prepareParam()` 用法
- 通过安全编码测试

## 经验教训

### 成功经验
1. 统一的参数化规范降低了安全风险
2. 代码审查是确保规范执行的有效手段
3. 详细的文档和示例加速了团队学习

### 改进建议
1. 应该在项目初期就建立安全编码规范
2. 可以开发IDE插件自动生成参数化代码
3. 应该定期进行安全审计

## 相关决策
- [ADR-003: 小程序样式选择器限制](./003-miniprogram-style-selector.md)

## 参考资料
- [OWASP SQL注入防护](https://owasp.org/www-community/attacks/SQL_Injection)
- [ThinkPHP 5.1 安全](https://www.kancloud.cn/manual/thinkphp5_1/354117)
- [PDO预处理语句](https://www.php.net/manual/zh/pdo.prepared-statements.php)

---

**决策日期**: 2024-01-10  
**决策人**: 项目技术负责人  
**审核人**: 安全负责人  
**最后更新**: 2025-01-19
