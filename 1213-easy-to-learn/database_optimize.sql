-- 数据库性能优化索引
-- 执行此SQL可显著提升API响应速度
-- 注意：所有表都有 fa_ 前缀

-- fa_record_shen表索引优化（最重要，原8次查询优化为1次）
ALTER TABLE `fa_record_shen` ADD INDEX `idx_record_shen_in` (`record_id`, `shen_in`);

-- fa_tian_gan表索引优化
ALTER TABLE `fa_tian_gan` ADD INDEX `idx_tian_gan_name` (`tian_gan_name`);

-- fa_month_zhi表索引优化
ALTER TABLE `fa_month_zhi` ADD INDEX `idx_month_name` (`month_name`);

-- fa_si_zhu_shi_shen表索引优化
ALTER TABLE `fa_si_zhu_shi_shen` ADD INDEX `idx_ri_gan_name` (`ri_gan_name`, `gan_name`);

-- fa_tian_gan_zhi表索引优化
ALTER TABLE `fa_tian_gan_zhi` ADD INDEX `idx_gan_zhi_name` (`gan_zhi_name`);

-- fa_record表索引优化
ALTER TABLE `fa_record` ADD INDEX `idx_record_user` (`user_id`, `yang_li_date`, `hour`, `minute`, `gender`, `area_id`);

-- fa_area表索引优化（如果不存在）
ALTER TABLE `fa_area` ADD INDEX `idx_area_pid` (`pid`);

-- fa_year_ju_ben表索引优化
ALTER TABLE `fa_year_ju_ben` ADD INDEX `idx_year_zhu` (`year_zhu`);
