# 项目上下文信息

- 活动编码：2026TSMHWL；商户ID：JWLY；盐值：SIT(a20b6f5a009745f08716935243fa476c), UAT(dbc4235b0d0c470c83966b6ea2a2e2f4), PRD(7323bd5e6f6644c7b846524a6e8017ea)。
- 系统流程需求：首页点击"开始探索"→获取微信OpenID→判断二次测算→跳转第三方→回跳验证customerNo→显示相应弹窗或页面。addRecord方法需要保存OpenID到数据库。
- Task 'Refine Feature Proposal' Phase 1: Analyzed `SiZhu.php` and confirmed `result` column in `fa_record` is updated to '已完成' upon successful calculation. 'Completed User' stat will use `count(distinct user_id)` where `result='已完成'`. Existing `fa_record` stores input data, while calculation results are derived on-the-fly or stored in `record_shen`. Backend record view currently shows inputs; result detail view would require re-calculation logic integration if requested.
- Task Completed: Redesigned loading animation. Removed lightning effect. Replaced fluid smoke with WebGL particle morphing that reconstructs the 5-element ring from 'donhuajiazai.png'. Removed legacy 'smoke_motion.js'.
- Updated manifest.json app name to '解密2026，探索美好未来'.
- User Preference: Prefer continuous particle rotation over switching to static image for loading animation.
- Implemented continuous particle rotation in image_morph.js. Fixed WebGL blending issues. Restored smoke_motion.js as deprecated.
- 用户要求恢复之前的五色环粒子旋转动画（基于 jiazaizhuangtai.png），同时保留新开发的奔马粒子动画（基于 ma/frame-*.png 序列帧）。需要同时展示这两个效果。
