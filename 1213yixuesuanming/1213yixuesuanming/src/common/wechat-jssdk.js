/**
 * 微信 JS-SDK 封装模块
 * 
 * 用于在 H5 页面中调用微信公众号的 JS-SDK 能力
 * 包括：自定义分享、预览图片等
 * 
 * 使用方式：
 * import wxSdk from '@/common/wechat-jssdk.js'
 * 
 * // 初始化SDK
 * await wxSdk.init()
 * 
 * // 设置分享内容
 * wxSdk.setShareInfo({
 *   title: '分享标题',
 *   desc: '分享描述',
 *   link: '分享链接',
 *   imgUrl: '分享图片URL'
 * })
 */

// 使用默认导入,api.js导出了 { get, post, file } 作为默认对象
import API from '@/common/request/api.js'

// 判断是否在微信浏览器中
export const isWechat = () => {
    const ua = navigator.userAgent.toLowerCase()
    return ua.indexOf('micromessenger') !== -1
}

// 微信 JS-SDK 封装对象
const wxSdk = {
    // SDK 是否已初始化
    isReady: false,

    // 配置信息缓存
    config: null,

    /**
     * 初始化微信 JS-SDK
     * 会自动检测环境，非微信浏览器中直接返回成功
     * 
     * @param {string} url 可选，当前页面URL，默认使用 location.href
     * @returns {Promise<boolean>} 初始化是否成功
     */
    async init(url) {
        // 非微信浏览器，直接返回
        if (!isWechat()) {
            console.log('[WxSdk] 非微信浏览器，跳过初始化')
            return true
        }

        // 如果已经初始化过，直接返回
        if (this.isReady) {
            console.log('[WxSdk] 已初始化，跳过')
            return true
        }

        try {
            // 确保微信 JS 文件已加载
            // 注意: 微信 JS-SDK 同时暴露 wx 和 jWeixin 两个全局变量
            // UniApp 会覆盖 wx 对象,因此我们使用 jWeixin
            if (typeof window.jWeixin === 'undefined') {
                console.warn('[WxSdk] 微信 JS 文件未加载 (jWeixin undefined)')
                return false
            }

            // 🔧 关键：先清理浏览器 URL 中的微信参数
            // 微信 JS-SDK 验证签名时使用的是浏览器实际 URL，而不是我们传递的 signUrl
            // 因此必须先更新浏览器地址栏，再调用 wx.config
            try {
                const currentUrl = new URL(window.location.href);
                const hasCode = currentUrl.searchParams.has('code');
                const hasState = currentUrl.searchParams.has('state');

                if (hasCode || hasState) {
                    currentUrl.searchParams.delete('code');
                    currentUrl.searchParams.delete('state');
                    window.history.replaceState({}, '', currentUrl.toString());
                    console.log('[WxSdk] 🧹 已从浏览器URL中移除 code/state 参数');
                }
            } catch (urlError) {
                console.warn('[WxSdk] ⚠️ 清理URL参数失败:', urlError);
            }

            // 获取签名用的 URL（不含 # 及其后面的部分）
            // 移除微信二次分享时添加的参数
            let signUrl = url || window.location.href.split('#')[0]

            // 移除微信自动添加的参数(from, isappinstalled, code, state等)
            signUrl = signUrl.replace(/[?&](from|isappinstalled|code|state)=[^&]*/gi, '')
            // 移除末尾的?或&
            signUrl = signUrl.replace(/[?&]$/, '')

            console.log('[WxSdk] 🔑 签名URL:', signUrl)
            console.log('[WxSdk] 🌐 当前完整URL:', window.location.href)

            // 调用后端接口获取签名配置
            const res = await API.get('api/wechat/jsconfig', { url: signUrl })

            if (res.code !== 1 || !res.data) {
                console.error('[WxSdk] ❌ 获取签名配置失败:', res.msg)
                console.error('[WxSdk] 📦 响应数据:', res)
                return false
            }

            const config = res.data
            this.config = config

            console.log('[WxSdk] ✅ 签名配置获取成功:')
            console.log('[WxSdk] 📋 AppID:', config.appId)
            console.log('[WxSdk] 🕐 Timestamp:', config.timestamp)
            console.log('[WxSdk] 🎲 NonceStr:', config.nonceStr)
            console.log('[WxSdk] ✍️ Signature:', config.signature)
            console.log('[WxSdk] 📝 JSApiList:', config.jsApiList)

            // 配置微信 JS-SDK (使用 jWeixin 避免与 UniApp 的 wx 冲突)
            const jWeixin = window.jWeixin;
            return new Promise((resolve) => {
                jWeixin.config({
                    debug: false, // 生产环境关闭调试模式
                    appId: config.appId,
                    timestamp: config.timestamp,
                    nonceStr: config.nonceStr,
                    signature: config.signature,
                    jsApiList: [
                        'updateAppMessageShareData',  // 分享给朋友
                        'updateTimelineShareData',    // 分享到朋友圈
                        'onMenuShareAppMessage',      // 旧版分享接口
                        'onMenuShareTimeline',        // 旧版分享接口
                        'previewImage',               // 预览图片
                        'downloadImage',              // 下载图片
                        'chooseImage',                // 选择图片
                    ]
                })

                console.log('[WxSdk] 🚀 jWeixin.config 已调用,等待验证...')

                jWeixin.ready(() => {
                    console.log('[WxSdk] ✅✅✅ 初始化成功! 可以调用分享接口了')
                    console.log('[WxSdk] 📱 当前环境:', navigator.userAgent)
                    this.isReady = true
                    resolve(true)
                })

                jWeixin.error((err) => {
                    console.error('[WxSdk] ❌❌❌ 初始化失败!')
                    console.error('[WxSdk] 错误详情:', err)
                    console.error('[WxSdk] 错误描述:', err.errMsg || '未知错误')

                    // 常见错误提示
                    if (err.errMsg) {
                        if (err.errMsg.includes('invalid signature')) {
                            console.error('[WxSdk] 💡 签名错误! 请检查:')
                            console.error('  1. JS接口安全域名是否已配置')
                            console.error('  2. URL是否正确(不含#及之后的部分)')
                            console.error('  3. AppID/AppSecret是否正确')
                        } else if (err.errMsg.includes('invalid url domain')) {
                            console.error('[WxSdk] 💡 域名未授权! 请在微信公众平台配置JS接口安全域名')
                        }
                    }

                    this.isReady = false
                    resolve(false)
                })
            })

        } catch (error) {
            console.error('[WxSdk] 初始化异常:', error)
            return false
        }
    },

    /**
     * 设置分享信息
     * 
     * @param {Object} options 分享配置
     * @param {string} options.title 分享标题
     * @param {string} options.desc 分享描述
     * @param {string} options.link 分享链接
     * @param {string} options.imgUrl 分享图片URL
     * @param {Function} options.success 成功回调
     * @param {Function} options.cancel 取消回调
     */
    setShareInfo(options) {
        // 非微信浏览器，直接返回
        if (!isWechat()) {
            console.log('[WxSdk] 非微信环境，跳过设置分享')
            return
        }

        if (!this.isReady) {
            console.warn('[WxSdk] SDK未初始化，无法设置分享')
            return
        }

        const shareData = {
            title: options.title || document.title,
            desc: options.desc || '',
            link: options.link || window.location.href,
            imgUrl: options.imgUrl || '',
            success: options.success || (() => { }),
            cancel: options.cancel || (() => { })
        }

        console.log('[WxSdk] 📤 设置分享信息:')
        console.log('[WxSdk]   📌 标题:', shareData.title)
        console.log('[WxSdk]   📝 描述:', shareData.desc)
        console.log('[WxSdk]   🔗 链接:', shareData.link)
        console.log('[WxSdk]   🖼️ 图片:', shareData.imgUrl)

        // 新版接口 - 分享给朋友
        window.jWeixin.updateAppMessageShareData({
            title: shareData.title,
            desc: shareData.desc,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: () => {
                console.log('[WxSdk] ✅ 分享给朋友成功')
                shareData.success()
            },
            fail: (err) => {
                console.error('[WxSdk] ❌ 分享给朋友失败:', err)
            }
        })

        // 新版接口 - 分享到朋友圈
        window.jWeixin.updateTimelineShareData({
            title: shareData.title,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: () => {
                console.log('[WxSdk] ✅ 分享到朋友圈成功')
                shareData.success()
            },
            fail: (err) => {
                console.error('[WxSdk] ❌ 分享到朋友圈失败:', err)
            }
        })

        // 旧版接口兼容 - 分享给朋友
        window.jWeixin.onMenuShareAppMessage({
            title: shareData.title,
            desc: shareData.desc,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: shareData.success,
            cancel: shareData.cancel
        })

        // 旧版接口兼容 - 分享到朋友圈
        window.jWeixin.onMenuShareTimeline({
            title: shareData.title,
            link: shareData.link,
            imgUrl: shareData.imgUrl,
            success: shareData.success,
            cancel: shareData.cancel
        })

        console.log('[WxSdk] 🎉 分享接口已调用(新/旧版本兼容)')
    },

    /**
     * 预览图片
     * 
     * @param {string} current 当前显示的图片URL
     * @param {Array<string>} urls 所有图片URL列表
     */
    previewImage(current, urls) {
        if (!isWechat() || !this.isReady) {
            // 非微信环境，使用 uni-app 的预览
            uni.previewImage({
                current,
                urls
            })
            return
        }

        window.jWeixin.previewImage({
            current,
            urls
        })
    },

    /**
     * 选择图片
     * 
     * @param {Object} options 配置
     * @param {number} options.count 最多选择数量
     * @returns {Promise<Array<string>>} 选中的图片本地ID列表
     */
    chooseImage(options = {}) {
        return new Promise((resolve, reject) => {
            if (!isWechat() || !this.isReady) {
                // 非微信环境，使用 uni-app 的选择
                uni.chooseImage({
                    count: options.count || 9,
                    success: (res) => resolve(res.tempFilePaths),
                    fail: reject
                })
                return
            }

            window.jWeixin.chooseImage({
                count: options.count || 9,
                sizeType: ['original', 'compressed'],
                sourceType: ['album', 'camera'],
                success: (res) => resolve(res.localIds),
                fail: reject
            })
        })
    },

    /**
     * 初始化默认分享配置
     * 使用后端 /api/user/shareInternal 接口格式的链接，参数从缓存获取
     * 确保朋友圈分享显示为卡片而非纯链接
     * 
     * @param {Object} options 可选的覆盖配置
     * @param {string} options.title 分享标题
     * @param {string} options.desc 分享描述
     * @param {string} options.imgUrl 分享图片URL
     * @returns {Promise<boolean>} 是否初始化成功
     */
    async initDefaultShare(options = {}) {
        try {
            // 先初始化 SDK
            const success = await this.init();
            if (!success) {
                console.warn('[WxSdk] SDK 初始化失败，跳过分享设置');
                return false;
            }

            // 从缓存获取业务参数
            const app_parmas = uni.getStorageSync('app_parmas') || {};
            const merchantId = app_parmas.merchantId || '';
            const activityCode = app_parmas.activityCode || '';
            const agentCode = app_parmas.agentCode || '';
            const sign = app_parmas.sign || '';

            // 构造分享链接 - 使用后端 shareInternal 接口（专门处理我们自己的分享）
            // shareInternal 会对 agentCode 进行双重编码，确保传给三方时编码层次正确
            const baseUrl = 'https://yixueadmin.linqingkeji.com/api/user/shareInternal';
            const params = ['isShare=true'];
            if (merchantId) params.push(`merchantId=${merchantId}`);
            if (activityCode) params.push(`activityCode=${activityCode}`);
            if (agentCode) params.push(`agentCode=${agentCode}`);
            if (sign) params.push(`sign=${sign}`);

            const shareLink = `${baseUrl}?${params.join('&')}`;

            // 设置分享内容
            this.setShareInfo({
                title: options.title || '我的2026旺运密码已解锁',
                desc: options.desc || '点击一下,解密你的2026吧~',
                link: shareLink,
                imgUrl: options.imgUrl || 'https://cdn.yixuestatic.linqingkeji.com/src/static/beijing.jpg',
                success: () => {
                    console.log('[WxSdk] ✅ 默认分享设置成功');
                }
            });

            console.log('[WxSdk] 📤 默认分享链接:', shareLink);
            return true;
        } catch (error) {
            console.error('[WxSdk] ❌ 初始化默认分享失败:', error);
            return false;
        }
    }
}

export default wxSdk
