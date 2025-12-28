-- =============================================
-- 易学二期数据库更新脚本
-- 执行前请备份数据库
-- =============================================

-- 1. 神煞配置表
DROP TABLE IF EXISTS `fa_shen_sha`;
CREATE TABLE `fa_shen_sha` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shen_sha_name` varchar(20) NOT NULL COMMENT '神煞名称',
  `condition_type` varchar(20) NOT NULL COMMENT '条件类型:year_gan,year_zhi,day_gan,day_zhi',
  `condition_value` varchar(50) NOT NULL COMMENT '条件值(逗号分隔,如:庚,辛)',
  `liu_nian_zhi` varchar(10) DEFAULT '午' COMMENT '流年地支(2026年为午)',
  `description` text COMMENT '神煞说明',
  `createtime` int(10) DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='神煞配置表';

-- 插入2026年丙午年神煞数据
INSERT INTO `fa_shen_sha` (`shen_sha_name`, `condition_type`, `condition_value`, `liu_nian_zhi`, `description`) VALUES
('贵人', 'year_gan', '庚,辛', '午', '年干为庚金和辛金，2026年为贵人年，主遇到贵人，尤其是财运和事业上会遇到贵人。'),
('食禄', 'year_gan', '丁,己', '午', '年干为丁与己禄在午，代表2026年有较好的财运，并且还可以存住钱。'),
('桃花', 'year_zhi', '巳,酉,丑', '午', '年支和日支为巳酉丑桃花在午，代表今年会有比较好的名声，比较好的人缘和比较好的人际关系，如果是未婚人士也代表今年有好的感情运势。'),
('羊刃', 'year_gan', '丙,戊', '午', '年干和日干为丙与戊刃在午，这一年代表会有比较激烈的事情发生，或者是当权得令，或者发生比较严峻的事情。'),
('文昌', 'year_gan', '乙', '午', '年干和日干为乙文昌在午，代表这一年很有机会学习到一种新的能力。'),
('将星', 'year_zhi', '寅,午,戌', '午', '年支和日支逢寅午戌将星在午，代表这一年中有可能在事业上有突出的表现，成为万众瞩目的一个。');

-- 2. 十神组合配置表
DROP TABLE IF EXISTS `fa_shi_shen_zuhe`;
CREATE TABLE `fa_shi_shen_zuhe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `liu_nian_shi_shen` varchar(10) NOT NULL COMMENT '流年十神',
  `yuan_ju_shi_shen` varchar(10) NOT NULL COMMENT '原局十神',
  `zuhe_name` varchar(30) NOT NULL COMMENT '组合名称',
  `description` text COMMENT '组合说明',
  `createtime` int(10) DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_zuhe` (`liu_nian_shi_shen`, `yuan_ju_shi_shen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='十神组合配置表';

-- 插入十神组合数据
INSERT INTO `fa_shi_shen_zuhe` (`liu_nian_shi_shen`, `yuan_ju_shi_shen`, `zuhe_name`, `description`) VALUES
('正财', '正官', '正财生正官', '这代表2026年有机会事业更进一步，这种进步来源于稳扎稳打，稳步提升，而不宜轻易冒进，不宜随性而为，尤其不宜从事冷门的行业。'),
('正财', '枭神', '正财克枭神', '这代表2026年中有机会事业陡然而起，一鸣惊人，当然这样好结果的前题是做事平稳、不感情用事，不投机取巧，这一年中关键时刻会遇贵人哦。'),
('偏财', '七杀', '偏财生七杀', '这代表2026年中是有机会博出一个机会，进而带来财运和事业的双丰收，关键时刻可以相信直觉拼一把，但是记住一定要见好就收，切忌贪心太重。'),
('偏财', '正印', '偏财克正印', '这代表2026年事业上有可能冒进，进而导致自己的人脉、身体出现问题，建议可以学一些其他的技艺提升自己，进而来消解坏印的不利，利用好枭神深入钻研和务实可靠的优势。'),
('正官', '枭神', '正官生枭神', '这代表2026年将会是奔波劳碌，但是劳而有得的一年，你的付出，一定会有回报，那么多一些付出、多一些投入又有何不可呢？'),
('正官', '劫财', '正官克劫财', '这代表在2026年你有可能成为出类拔萃的那一个，在事业上有可能陡然而起，但是一定要用好正官稳定、内敛、打好根基的特点，马步扎的稳，才能成为真正的武林高手，无往而不利。'),
('七杀', '正印', '七杀生正印', '这代表2026年很可能是名利双收、贵人关照的一年，但是一方面要注意自己的身体、另一方面要注意凡事要做的表里如一，毕竟有时候正印习惯于做表面文章，又好面子，这是可以提升的地方。'),
('七杀', '比肩', '七杀克比肩', '这代表2026年可能因为事业上的太过执着和付出，影响到了朋友间的关系，这时候请一定要收起自己的霸道和锋芒，事业很重要，好朋友也很重要。'),
('正印', '比肩', '正印生比肩', '这代表2026年将会收获好的名声、好的资历和好的人缘，讲通俗就是这一年将会有很多机会遇到贵人，即便遇到难处也会有贵人和朋友的帮助。'),
('正印', '伤官', '正印克伤官', '这代表2026年将会是既能收获好的名声，也能收获实实在在的利益，更重要的是一切都是在不用特别拼搏和努力的情况下而得到了。'),
('枭神', '劫财', '枭神生劫财', '这代表2026年将会是辛苦周折的一年，需要靠自己的努力拼搏，但是天道酬勤，枭神的勤劳可能会给你意想不到的收获，很多付出也许无法在当下开花结果，但是成功也许就是下一个时点。'),
('枭神', '食神', '枭神克食神', '枭神的年份一定要让自己忙碌起来，2026年绝对不能躺平，绝对不能让自己太安逸，努力把每一件事情做好，哪怕是反复多做几次也没有关系，而且今年头脑会特别灵光，学习和充实自己是不错的选择。'),
('比肩', '伤官', '比肩生伤官', '这代表2026年很可能过多关注于小的人脉圈子，而忽略的大环境对自己的影响，一定要努力让自己融入更大的集体，获得更多的前沿信息，保持自己认知的领先，这是非常重要的。'),
('比肩', '正财', '比肩克正财', '这代表2026年有可能通过朋友的关系和资讯获得一份意料之外的事业和财富，所以，适当的融入社交圈子，不要封闭自己，机会很可能就藏在其中。'),
('劫财', '食神', '劫财生食神', '这代表2026年可以通过投资获得丰厚回报，在获得物质收获的同时，经验的积累也非常重要，但同时容易出现花钱大手大脚以及小富即安的情况。'),
('劫财', '偏财', '劫财克偏财', '这代表2026年不能幻想依靠别人的帮助，而是要靠自己的努力和扎实拼搏，关键时刻咬牙坚持主，获得平稳向上的事业机会。'),
('食神', '偏财', '食神生偏财', '这代表2026年是有机会通过投资获得更大的回报，财运亨通可能就是这一年的代名词了，但是这一年可能出现冲动和感情用事的情况，这时候一定要保持平常心。'),
('食神', '七杀', '食神克七杀', '这代表2026年是事业攀登高峰的一年，很有可能在轻松和写意之间，完成职业的向上发展，勇敢行动起来，发现机会。'),
('伤官', '正财', '伤官生正财', '这代表2026年要利用好自己的知识和技术，构建自己的能力壁垒，通过你独有的优势，获得平稳向上的的财运及事业机会。');

-- 3. 流年十神内容表
DROP TABLE IF EXISTS `fa_liu_nian_shi_shen`;
CREATE TABLE `fa_liu_nian_shi_shen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shi_shen_name` varchar(10) NOT NULL COMMENT '十神名称',
  `description` text COMMENT '十神说明',
  `createtime` int(10) DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`shi_shen_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='流年十神内容表';

-- 插入流年十神数据
INSERT INTO `fa_liu_nian_shi_shen` (`shi_shen_name`, `description`) VALUES
('劫财', '当年内会遇到很多花钱的事情，也会交到很多朋友，但是朋友往往是利益朋友，不必交心，开心就够了。如果遇事情得不到他们的帮助，也不必伤心和失望，不必太放心上。今年无谓的花费会比较多，所以要注意投资方向，尽量不做金融投资，可以多实体投资，不要向外借钱。'),
('比肩', '当年会有很好的人际交往，会体现为朋友的层面很高、或者朋友的范围很广泛、或者朋友可以带给实质性的帮助。所以今年如果有好朋友给你项目合机会，不要错过，可以放手一搏，这一年你会非常好面子，但是有可能做事不能够脚踏实地，天下大事，必作于细，这是需要注意的地方。'),
('伤官', '这一年在外要注意和领导的关系，尽量不要产生直接冲突，在家要注意和长辈说话的方式，不要顶撞长辈，家有一老，如有一宝。同时这一年有可能感觉有些人和事让自己气炸了，请一定要克制，可以尝试培养自己的一些兴趣爱好，并深入钻研，让它成为自己的一项特长也是很不错的。'),
('食神', '今年是很好的一年，轻松、安逸、逍遥，如果你想要享受生活，今年一定是非常好的年份，而且今年大概率可以有所积蓄。但是凡事有利则有弊，如果您正在拼事业的上升期就要注意了，这一年恐怕机会如镜花水月，看得见摸不着，即便已经近在眼前却又失之交臂，那不妨放松下来，明年再战。'),
('正财', '今年是你的财运年，财运集中体现为稳定，可控，意料之内，不会太有大的波动。如果您是上班族，恭喜您收入还是稳定可靠的，如果您是企业主，恭喜您事业不会出现明显滑坡，但是也不会特别大的暴利。今年最大的特点就是稳定，因此这一年中做事一定要求稳，要顺势而为。'),
('偏财', '今年是获得意外之财的年份，人人爱偏财和横财，但古语有云：君子爱财，取之有道。请谨记，意外之财是不易被控制的，得到之前和得到之后都容易节外生枝。这一年谨防感情用事，招来祸端，养个好身体，交些好朋友，保持好心情，有空的时候，不妨多回家看看父母吧，今年会对你很有帮助。'),
('正官', '今年的事业以平稳向上发展为主，无波无澜。如果你想火箭式上升，大概率今年是不行的，但是如果你是扎实稳健的工作风格，今年非常有可能受到领导的赏识，更进一步。如果您从事的行业为主流的、正统的行业，这会是非常好的一年，事业安顺遂。如果您正在求如意郎君，今年也是不错的一年，有很大机会遇到如意郎君。'),
('七杀', '今年的事业容易出现不稳定的情况，做事一定要小心谨慎，三思而行，千万不要冲动。今年确实会有一些不利的信息，但是也不必特别担心，如果可以与人为善，懂的退一步海阔天空的道理，很多不利就可以消弭于无形，同时今年可以去献血。出门在外安全是第一位的，女性今年要注意感情，平平淡淡才是真。'),
('正印', '今年是你的贵人年，工作里和生活中会经常遇到有人帮助，好上加好，坏事不坏，都是因为有贵人帮助的原因。同时今年会比往年都要爱美，爱面子，但是要注意做人做事，面子和里子都要兼顾。同时今年特别适合考试和自我提升，如果有时间和精力，多多学习，有可能会有意外惊喜。'),
('枭神', '今年容易出现种种周折，进而导致身心疲惫，因此可能对于表面功夫的事情不再上心，今年的人脉关系不太好，凡事更多要靠自己撑，靠自己解决，越是艰难处，越是修心时，培养自己做事情稳扎稳打、踏实务实、做精做细的工作风格对你非常有好处，今年可以多多运动，锻炼一个好体魄。');

-- 4. 禀赋配置表(二期新逻辑)
DROP TABLE IF EXISTS `fa_binfu`;
CREATE TABLE `fa_binfu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wu_xing` varchar(4) NOT NULL COMMENT '五行',
  `type` tinyint(1) NOT NULL COMMENT '类型:0=劣势(最多),1=优势(最少)',
  `is_binfu` tinyint(1) DEFAULT '0' COMMENT '是否天生禀赋:0=普通,1=天生禀赋',
  `description` text COMMENT '描述',
  `createtime` int(10) DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_wuxing_type` (`wu_xing`, `type`, `is_binfu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='禀赋配置表';

-- 插入天生禀赋数据(优势-最少的五行)
INSERT INTO `fa_binfu` (`wu_xing`, `type`, `is_binfu`, `description`) VALUES
('金', 1, 1, '您义气大气、内敛拘谨、做事果断、有军人一般的纪律感，但是控制欲也比较强，适合从事军事、行政机关，尤其是金属行业和金融行业会让本人如鱼得水。'),
('木', 1, 1, '您仁慈、谦让、奉献、讲道理、善总结、逻辑思维强、协调力强，适合从事教育、培训、体育和医护，善于管理和策划，尤其适合从事条理性强，和工作性质包含为别人奉献和付出的行业，这会让你得到精神上的极大满足。'),
('火', 1, 1, '您热情有礼、善于交际，是天生的社牛，非常容易在交朋友中得到信任和赞扬，同时喜潮流、重仪表。适合从事传媒工作、与社交、公关有关的工作、与美丽事物有关的工作，尤其是适合从事销售工作，不要辜负自己的社牛体质。'),
('土', 1, 1, '您非常珍视信誉，言必信，行必果，不说则以，说到就做到，同时做事善于变通，擅长转换思维，遇事冷静。适合从事实业、农业、畜牧养殖业，房地产行业，尤其擅长资吸收资源、整合资源进而创造价值的行业。'),
('水', 1, 1, '您对数字敏感，创意十足、聪明伶俐，头脑非常灵活，同时做事情内敛、细腻、有耐性，并且忍受能力非常强。适合从事创作、创意、设计类工作，同时适合从事服务型行业，与数字打交道的行业。');

-- 插入待改善数据(劣势-最多的五行)
INSERT INTO `fa_binfu` (`wu_xing`, `type`, `is_binfu`, `description`) VALUES
('金', 0, 0, '您非常理性和务实，甚至有些小气，同时存在做事犹豫、对人霸道的可能。建议培养自己做事大气、果断，与人相互要更多讲道理和气，而不能太霸道，要培养自己圆滑的性格。'),
('木', 0, 0, '您有条理性比较差，做事比较容易固执、冲动，与人相处比较麻木，做事缺乏逻辑感，建议培养自己做事条理性、与人为善，凡事不要轻易陷到自己的思维定式里，做事情多站在对方角度思考问题。'),
('火', 0, 0, '您不是特别注重个人仪表，不太善于交际、与人相处容易火爆脾气、容易冲动，同业也容易盲从，也有做事浮夸、暴躁的可能，建议加强自己社交方面的培养，同时更多注重个人言谈举止，不要让自己的爆脾气伤害到别人，也要更多注重外在形象，毕竟人都是视觉动物哦。'),
('土', 0, 0, '您不太善于处理突发事件，应变能力较差，同时心里很难藏着很多事情，不擅长遇事灵活转变思维，换一个角度突围，同时容易冲动许下诺言，但是又难以实现。建议不要轻易许下承诺，同时要培养自己灵活应变，转化思路的能力。'),
('水', 0, 0, '您做事情一板一眼，缺乏一些灵感、同时容易犯粗心和鲁莽的问题，遇事也比较容易张扬，缺乏耐心，建议养成自己的城府，做事严密，培养自己的涵养、可以练习做事情的创意思维。');

-- 5. 干支方位表
DROP TABLE IF EXISTS `fa_gan_zhi_fang_wei`;
CREATE TABLE `fa_gan_zhi_fang_wei` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gan_zhi` varchar(4) NOT NULL COMMENT '干支名称',
  `gan_zhi_type` tinyint(1) NOT NULL COMMENT '类型:0=天干,1=地支',
  `fang_wei` varchar(20) NOT NULL COMMENT '方位',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ganzhi` (`gan_zhi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='干支方位表';

-- 插入天干方位
INSERT INTO `fa_gan_zhi_fang_wei` (`gan_zhi`, `gan_zhi_type`, `fang_wei`) VALUES
('甲', 0, '东北偏东'),
('乙', 0, '东南偏东'),
('丙', 0, '西南偏南'),
('丁', 0, '东南偏南'),
('戊', 0, '东南、西北'),
('己', 0, '东北、西南'),
('庚', 0, '西南偏西'),
('辛', 0, '西北偏西'),
('壬', 0, '东北偏北'),
('癸', 0, '西北偏北');

-- 插入地支方位
INSERT INTO `fa_gan_zhi_fang_wei` (`gan_zhi`, `gan_zhi_type`, `fang_wei`) VALUES
('子', 1, '正北'),
('丑', 1, '东北偏北'),
('寅', 1, '东北偏东'),
('卯', 1, '正东'),
('辰', 1, '东南偏东'),
('巳', 1, '东南偏南'),
('午', 1, '正南'),
('未', 1, '西南偏南'),
('申', 1, '西南偏西'),
('酉', 1, '正西'),
('戌', 1, '西北偏西'),
('亥', 1, '西北偏北');

-- 6. 扩展fa_record表
ALTER TABLE `fa_record` ADD COLUMN `binfu_wu_xing` varchar(4) DEFAULT NULL COMMENT '禀赋五行(天生禀赋)' AFTER `min_wu_xing`;
ALTER TABLE `fa_record` ADD COLUMN `liu_nian_shi_shen` varchar(10) DEFAULT NULL COMMENT '流年十神' AFTER `ju_ben_gan_zhi`;
ALTER TABLE `fa_record` ADD COLUMN `shi_shen_zuhe` varchar(50) DEFAULT NULL COMMENT '十神组合名称' AFTER `liu_nian_shi_shen`;
ALTER TABLE `fa_record` ADD COLUMN `shen_sha_list` text DEFAULT NULL COMMENT '神煞列表(JSON)' AFTER `shi_shen_zuhe`;
ALTER TABLE `fa_record` ADD COLUMN `fang_wei_info` text DEFAULT NULL COMMENT '方位信息(JSON:事业位/财位/贵人位)' AFTER `shen_sha_list`;
ALTER TABLE `fa_record` ADD COLUMN `version` varchar(10) DEFAULT '2025' COMMENT '版本:2025,2026' AFTER `fang_wei_info`;

-- 7. 地支藏干表
DROP TABLE IF EXISTS `fa_di_zhi_cang_gan`;
CREATE TABLE `fa_di_zhi_cang_gan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `di_zhi` varchar(4) NOT NULL COMMENT '地支',
  `cang_gan` varchar(20) NOT NULL COMMENT '藏干(逗号分隔)',
  `zhu_qi` varchar(4) NOT NULL COMMENT '主气',
  `zhong_qi` varchar(4) DEFAULT NULL COMMENT '中气',
  `yu_qi` varchar(4) DEFAULT NULL COMMENT '余气',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_dizhi` (`di_zhi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='地支藏干表';

INSERT INTO `fa_di_zhi_cang_gan` (`di_zhi`, `cang_gan`, `zhu_qi`, `zhong_qi`, `yu_qi`) VALUES
('子', '癸', '癸', NULL, NULL),
('丑', '己,癸,辛', '己', '癸', '辛'),
('寅', '甲,丙,戊', '甲', '丙', '戊'),
('卯', '乙', '乙', NULL, NULL),
('辰', '戊,乙,癸', '戊', '乙', '癸'),
('巳', '丙,戊,庚', '丙', '戊', '庚'),
('午', '丁,己', '丁', '己', NULL),
('未', '己,丁,乙', '己', '丁', '乙'),
('申', '庚,壬,戊', '庚', '壬', '戊'),
('酉', '辛', '辛', NULL, NULL),
('戌', '戊,辛,丁', '戊', '辛', '丁'),
('亥', '壬,甲', '壬', '甲', NULL);
