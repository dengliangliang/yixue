-- ====================================================
-- 易学小程序 - 数据库性能优化SQL
-- 执行时间：2025-12-14
-- 目标：将数据库查询从447ms降至50ms以下
-- ====================================================

USE yixue;

-- ====================================================
-- 第一步：检查现有索引
-- ====================================================
SHOW INDEX FROM fa_record;
SHOW INDEX FROM fa_record_shen;
SHOW INDEX FROM fa_tian_gan;
SHOW INDEX FROM fa_month_zhi;
SHOW INDEX FROM fa_si_zhu_shi_shen;
SHOW INDEX FROM fa_tian_gan_zhi;

-- ====================================================
-- 第二步：添加关键索引
-- ====================================================

-- 1. record表索引（最高优先级）
-- 查询场景：WHERE id = ? 或 WHERE id = ? AND user_id = ?
ALTER TABLE fa_record ADD INDEX idx_id (id);
ALTER TABLE fa_record ADD INDEX idx_user_id (user_id);
ALTER TABLE fa_record ADD INDEX idx_id_user (id, user_id);
ALTER TABLE fa_record ADD INDEX idx_createtime (createtime);

-- 2. record_shen表索引
-- 查询场景：WHERE record_id = ? 或 WHERE record_id = ? AND shen_in = ?
ALTER TABLE fa_record_shen ADD INDEX idx_record_id (record_id);
ALTER TABLE fa_record_shen ADD INDEX idx_record_shen_in (record_id, shen_in);
ALTER TABLE fa_record_shen ADD INDEX idx_is_da_yun (is_da_yun);

-- 3. 天干表索引
-- 查询场景：WHERE tian_gan_name = ? 或 WHERE tian_gan_name IN (...)
ALTER TABLE fa_tian_gan ADD INDEX idx_tian_gan_name (tian_gan_name);

-- 4. 地支表索引
-- 查询场景：WHERE month_name = ? 或 WHERE month_name IN (...)
ALTER TABLE fa_month_zhi ADD INDEX idx_month_name (month_name);

-- 5. 四柱十神表索引
-- 查询场景：WHERE ri_gan_name = ? AND gan_name = ?
ALTER TABLE fa_si_zhu_shi_shen ADD INDEX idx_ri_gan (ri_gan_name);
ALTER TABLE fa_si_zhu_shi_shen ADD INDEX idx_gan_name (gan_name);
ALTER TABLE fa_si_zhu_shi_shen ADD INDEX idx_ri_gan_name (ri_gan_name, gan_name);

-- 6. 天干地支表索引
-- 查询场景：WHERE gan_zhi_name = ? 或 WHERE gan_zhi_name IN (...)
ALTER TABLE fa_tian_gan_zhi ADD INDEX idx_gan_zhi_name (gan_zhi_name);

-- 7. area表索引（省市数据）
ALTER TABLE fa_area ADD INDEX idx_pid (pid);
ALTER TABLE fa_area ADD INDEX idx_level (level);

-- ====================================================
-- 第三步：验证索引创建结果
-- ====================================================
SHOW INDEX FROM fa_record;
SHOW INDEX FROM fa_record_shen;
SHOW INDEX FROM fa_tian_gan;
SHOW INDEX FROM fa_month_zhi;
SHOW INDEX FROM fa_si_zhu_shi_shen;
SHOW INDEX FROM fa_tian_gan_zhi;

-- ====================================================
-- 第四步：分析表并优化（可选）
-- ====================================================
ANALYZE TABLE fa_record;
ANALYZE TABLE fa_record_shen;
ANALYZE TABLE fa_tian_gan;
ANALYZE TABLE fa_month_zhi;
ANALYZE TABLE fa_si_zhu_shi_shen;
ANALYZE TABLE fa_tian_gan_zhi;

-- ====================================================
-- 第五步：测试查询性能
-- ====================================================

-- 测试1：record查询（修复前：447ms，目标：<50ms）
EXPLAIN SELECT * FROM fa_record WHERE id = 64;

-- 测试2：record_shen批量查询
EXPLAIN SELECT * FROM fa_record_shen WHERE record_id = 64;

-- 测试3：天干批量查询
EXPLAIN SELECT attribute, tian_gan_name FROM fa_tian_gan 
WHERE tian_gan_name IN ('甲', '乙', '丙', '丁');

-- 测试4：十神查询
EXPLAIN SELECT shi_shen_name FROM fa_si_zhu_shi_shen 
WHERE ri_gan_name = '甲' AND gan_name = '乙';

-- ====================================================
-- 性能监控查询
-- ====================================================

-- 查看慢查询设置
SHOW VARIABLES LIKE 'slow_query%';
SHOW VARIABLES LIKE 'long_query_time';

-- 启用慢查询日志（如果需要）
-- SET GLOBAL slow_query_log = 'ON';
-- SET GLOBAL long_query_time = 1; -- 记录超过1秒的查询

-- 查看当前连接数和状态
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads%';

-- ====================================================
-- 预期效果
-- ====================================================
/*
优化前：
- record查询：447ms
- 总API时间：3-6秒

优化后：
- record查询：<50ms（快9倍）
- 总API时间：0.5-1秒（快6倍）

用户体验：
- 页面加载从38秒降至5-8秒
*/
