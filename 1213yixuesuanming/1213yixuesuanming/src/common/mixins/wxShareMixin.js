/**
 * 全局微信分享 Mixin
 * 
 * 在每个页面的 onReady 生命周期初始化微信分享（只初始化一次）
 * 分享链接使用后端 /api/user/share 接口格式（无 # 号）
 * 确保朋友圈分享显示为卡片而非纯链接
 */
import wxSdk from '@/common/wechat-jssdk.js'

export default {
    data() {
        return {
            // 内部标志：防止重复初始化
            $_wxShareInitialized: false
        }
    },

    onReady() {
        // 页面渲染完成后初始化微信分享（只执行一次）
        if (!this.$_wxShareInitialized) {
            this.$_wxShareInitialized = true
            this.$_initWxShare()
        }
    },

    methods: {
        /**
         * 初始化微信分享（内部方法）
         * 使用 $_ 前缀避免与页面方法冲突
         */
        async $_initWxShare() {
            try {
                // 使用封装好的 initDefaultShare 方法
                await wxSdk.initDefaultShare()
            } catch (error) {
                console.warn('[WxShareMixin] 分享初始化失败:', error)
            }
        },

        /**
         * 手动设置分享信息（可在页面中覆盖默认配置）
         * @param {Object} options 分享配置
         * @param {string} options.title 分享标题
         * @param {string} options.desc 分享描述
         * @param {string} options.imgUrl 分享图片URL
         */
        async $setShareInfo(options = {}) {
            try {
                await wxSdk.initDefaultShare(options)
            } catch (error) {
                console.warn('[WxShareMixin] 设置分享信息失败:', error)
            }
        }
    }
}
