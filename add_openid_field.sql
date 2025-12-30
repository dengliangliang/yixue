-- 为fa_record表添加openid字段
ALTER TABLE `fa_record` 
ADD COLUMN `openid` varchar(100) DEFAULT '' COMMENT '微信OpenID' AFTER `customerNo`;

-- 为openid字段添加索引以提升查询性能
ALTER TABLE `fa_record` 
ADD INDEX `idx_openid` (`openid`);
