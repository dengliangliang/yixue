<template>
	<view class="content" :class="{ 'page-fade-out': isLeaving }">
		<!-- ========== 吉祥点缀装饰 ========== -->
		<view class="decor-container">
			<!-- 马群 - 底部 -->
			<image :src="cdnBase + staticPrefix + 'ma3.png'" class="decor-horse animate__animated animate__fadeInUp animate__delay-1s" mode="widthFix"></image>
			<!-- 仙鹤 - 左下角、右中上方、右下方 -->
			<image :src="cdnBase + staticPrefix + 'he4.png'" class="decor-crane decor-crane-left animate__animated animate__fadeInLeft animate__delay-0-5s" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'he4.png'" class="decor-crane decor-crane-top animate__animated animate__fadeInRight animate__delay-0-5s" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'he5.png'" class="decor-crane decor-crane-right animate__animated animate__fadeInRight animate__delay-0-5s" mode="widthFix"></image>
		</view>
		
		<!-- 主题文案 - 解密2026 -->
		<view class="theme-container animate__animated animate__fadeInDown animate__delay-0-8s">
			<!-- 主题文案图片 -->
			<image :src="cdnBase + staticPrefix + 'jiemi.png'" class="theme-image" mode="widthFix"></image>
		</view>
		
		<!-- 九紫离火 - 单字篆体图片（支持独立动画） -->
		 <view class="floating-text animate__animated animate__fadeIn animate__delay-1s">
			<image :src="cdnBase + staticPrefix + '九.png'" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0s;"></image>
			<image :src="cdnBase + staticPrefix + '紫.png'" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.2s;"></image>
			<image :src="cdnBase + staticPrefix + '离.png'" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.4s;"></image>
			<image :src="cdnBase + staticPrefix + '火.png'" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.6s;"></image>
		</view>
		
		<!-- 按钮 - share 来源延迟渲染 -->
		 <view v-if="shouldShowButton" class="btn-container animate__animated animate__zoomIn animate__delay-1s">
			<!-- 按钮图片 -->
			<image :src="cdnBase + staticPrefix + 'anniu.png'" class="explore-btn-image" mode="widthFix" @click="navto"></image>
		</view>
	</view>
	
	<!-- 全局加载遮罩 -->
	<GlobalLoading :visible="showLoading" :text="loadingText" />
	
	<!-- 九紫离火运介绍弹窗 -->
	<uni-popup ref="introPopup" type="center" :mask-click="false">
		<view class="intro-popup-container">
			<!-- 透明暗纹图片 -->
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="intro-watermark watermark-top-left" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="intro-watermark watermark-bottom-right" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="intro-watermark watermark-title-under" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="intro-watermark watermark-center" mode="widthFix"></image>
			
			<!-- 标题 -->
			<view class="intro-title">九紫离火运</view>
			
			<!-- 介绍内容 -->
			<view class="intro-content">
				<view class="intro-paragraph">
					<text class="intro-text">九紫即九紫右弼星,被视为最吉利的当运之星,它代表着财富、贵人、喜庆等吉祥之意;"离"火,代表万物光明,一切符合离火元素的事物,都可能会"应运而发"。</text>
				</view>
				
				<view class="intro-list">
					<view class="intro-item">
						<text class="intro-bullet">•</text>
						<text class="intro-text">离火运会推动电力、通讯、新能源、强人际关系网经营等火属性的行业。</text>
					</view>
					<view class="intro-item">
						<text class="intro-bullet">•</text>
						<text class="intro-text">"火"是文明的起源,代表着文化,也将会带动文化产业出现大变革。</text>
					</view>
					<view class="intro-item">
						<text class="intro-bullet">•</text>
						<text class="intro-text">离者,丽也。其代表物是"朱雀"。在离火运的带动之下,让人变美的行业迎来突破性的发展。</text>
					</view>
					<view class="intro-item">
						<text class="intro-bullet">•</text>
						<text class="intro-text">离卦,代表着中年女性。离火运时期,那些思想活跃,热情有礼的中年女性,将大放异彩,受到重视。</text>
					</view>
				</view>
			</view>
			
			<!-- 确认按钮 -->
			<view class="intro-btn" @click="confirmIntro">
				<text class="intro-btn-text">开启探索之旅</text>
			</view>
		</view>
	</uni-popup>
	
	<!-- 返回用户弹窗 -->
	<uni-popup ref="returnUserPopup" type="center" :mask-click="false">
		<view class="intro-popup-container">
			<!-- 透明暗纹图片 -->
			<image :src="cdnBase + '/src/static/yun1.png'" class="intro-watermark watermark-top-left" mode="widthFix"></image>
			<image :src="cdnBase + '/src/static/yun2.png'" class="intro-watermark watermark-bottom-right" mode="widthFix"></image>
			<image :src="cdnBase + '/src/static/yun1.png'" class="intro-watermark watermark-title-under" mode="widthFix"></image>
			<image :src="cdnBase + '/src/static/yun2.png'" class="intro-watermark watermark-center" mode="widthFix"></image>
			
			<!-- 标题 -->
			<view class="intro-title">欢迎回来</view>
			
			<!-- 提示内容 -->
			<view class="intro-content">
				<view class="intro-paragraph">
					<text class="intro-text" style="text-align: center;">检测到您之前测算过</text>
				</view>
				<view class="intro-paragraph" style="margin-top: 20rpx;">
					<text class="intro-text" style="text-align: center;">是否查看上次的测算结果?</text>
				</view>
			</view>
			
			<!-- 按钮组 -->
			<view class="return-user-btns">
				<view class="intro-btn intro-btn-secondary" @click="newCalculation">
					<text class="intro-btn-text intro-btn-text-secondary">重新测算</text>
				</view>
				<view class="intro-btn" @click="viewLastResult">
					<text class="intro-btn-text">上次结果</text>
				</view>
			</view>
		</view>
	</uni-popup>
</template>

<script>
	import websiteConfig from '@/config/website.js';
	import GlobalLoading from '@/components/GlobalLoading.vue';
	
	export default {
		components: {
			GlobalLoading
		},
		data() {
			return {
				title: 'Hello',
				windowHeight: '',
				pageLoadStart: 0, // 页面加载开始时间
				lastRecordId: '', // 上次测算的record_id
				pageLoadEnd: 0,   // 页面加载完成时间
				decorScheme: 'C',
				// 二次测算检测状态
				isCheckingUser: true, // 是否正在检测用户状态
				isReturnUser: false,  // 是否为二次测算用户
				hasCheckedOnce: false, // 是否已经检测过一次（用于左滑返回时重新检测）
				// 页面跳转过渡状态
				showLoading: false,   // 是否显示加载遮罩
				isLeaving: false,     // 是否正在淡出
				loadingText: '加载中...' // 加载提示文字
			}
		},
		computed: {
			// 统一的静态资源路径转换，通过 website.js 配置控制 CDN/本地切换
			cdnBase() {
				return websiteConfig.CDN.enabled ? websiteConfig.CDN.baseUrl : '';
			},
			// 静态资源路径前缀
			staticPrefix() {
				return websiteConfig.CDN.enabled ? '/src/static/' : '/static/';
			},
			// 控制按钮是否渲染：share 来源检测完成后才渲染，其他来源根据逻辑决定
			shouldShowButton() {
				const appParams = uni.getStorageSync('app_parmas') || {};
				
				// share 来源：检测完成后才显示按钮
				if (appParams.from === 'share') {
					return !this.isCheckingUser;
				}
				
				// jump 来源：始终显示
				if (appParams.from === 'jump') {
					return true;
				}
				
				// 其他来源：不显示
				return false;
			}
		},
		onLoad(op) {
			//  性能监控：记录页面加载开始时间
			this.pageLoadStart = Date.now();
			console.log('📊 [性能] 页面开始加载', new Date().toISOString());
			
			// 记录用户进入页面的开始时间，用于回传三方接口
			const now = new Date();
			const startTime = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
			uni.setStorageSync('activity_start_time', startTime);
			console.log('[index] 记录活动开始时间:', startTime);
			
			console.log('----', op);
			if (op.sign) {
				uni.setStorageSync('app_parmas', op)
			}
			
			// 检查是否需要重定向获取 code（微信浏览器中且无缓存OpenID且无code）
			// 如果需要重定向，其他初始化都不做，等重定向回来后再执行
			this.handleWxAuth().then(needRedirect => {
				if (!needRedirect) {
					// 不需要重定向，继续后续流程
					// share 来源：检查二次测算
					if (op.from === 'share') {
						this.checkReturnUser();
					} else if (op.from === 'jump') {
						// jump 来源：不检测，启用按钮
						this.isCheckingUser = false;
						this.isReturnUser = false;
						console.log('[index] jump来源，跳过二次测算检测，启用按钮');
					} else {
						// 其他来源：保持禁用状态，不检测不弹窗
						// isCheckingUser 保持 true，按钮始终禁用
						this.isReturnUser = false;
						console.log('[index] 其他来源，按钮始终禁用');
					}
					// 微信分享由全局 mixin 在 onShow 时自动初始化
				}
				// 如果需要重定向，页面会跳转，不执行后续代码
			});

			// 异步预加载省市数据
			setTimeout(() => {
				this.preloadAreaData();
			}, 100);
			

			// 如果来源是jump(第三方跳转),自动打开九紫离火弹窗
			if (op.from === 'jump') {
			console.log('[index] 检测到来自jump,延迟打开九紫离火弹窗');
			setTimeout(() => {
				this.$refs.introPopup.open();
			}, 1000);
			}
		},
		// 【修复】处理左滑返回场景：重新检测 share 来源的二次用户
		onShow() {
			const appParams = uni.getStorageSync('app_parmas') || {};
			
			// 仅对 share 来源处理左滑返回场景
			if (appParams.from === 'share') {
				// 重置过渡动画状态
				this.showLoading = false;
				this.isLeaving = false;
				
				// 如果已经检测过，说明是左滑返回，需要重新检测
				if (this.hasCheckedOnce) {
					console.log('[index] 左滑返回，重新检测 share 来源的二次用户');
					// 先禁用按钮
					this.isCheckingUser = true;
					// 重新检测
					this.checkReturnUser();
				}
			}
		},
		onReady() {
			this.getsy();
			
			//  性能监控：记录页面渲染完成时间
			this.pageLoadEnd = Date.now();
			const loadTime = this.pageLoadEnd - this.pageLoadStart;
			console.log(' [性能] 页面渲染完成', {
				耗时: loadTime + 'ms',
				时间戳: new Date().toISOString()
			});
			
			// 如果加载时间超过2秒，输出警告
			if (loadTime > 2000) {
				console.warn('⚠️ [性能警告] 页面加载超过2秒，耗时:', loadTime + 'ms');
			}
			
			// 注意：微信分享初始化已移到 handleWxAuth 完成后执行
			// 这样可以确保 URL 中的 code 参数已被移除，避免签名错误
		},
		methods: {
			// 获取系统信息
			getsy() {
				uni.getSystemInfo({
					success: (res) => {
						this.windowHeight = res.windowHeight;
					}
				});
			},
			// 异步预加载省市数据（不阻塞页面渲染）
			async preloadAreaData() {
				const startTime = Date.now();
				try {
					console.log('📦 [数据] 开始预加载省市数据');
					
					// 检查是否已有缓存
					const cachedProvince = uni.getStorageSync('cache_provinceList');
					if (cachedProvince && cachedProvince.length > 0) {
						console.log(' [数据] 省份数据已缓存，跳过', {
							数量: cachedProvince.length,
							耗时: (Date.now() - startTime) + 'ms'
						});
						return;
					}
					
					// 请求省份列表
					console.log('🌐 [API] 开始请求省份数据...');
					const apiStartTime = Date.now();
					
					const res = await this.$api.post('api/user/getProvinceList');
					
					const apiTime = Date.now() - apiStartTime;
					console.log('🌐 [API] 省份数据请求完成', {
						耗时: apiTime + 'ms',
						状态: res.code == 1 ? '成功' : '失败'
					});
					
					if (res.code == 1) {
						uni.setStorageSync('cache_provinceList', res.data);
						const totalTime = Date.now() - startTime;
						console.log(' [数据] 省份数据缓存完成', {
							数量: res.data.length,
							API耗时: apiTime + 'ms',
							总耗时: totalTime + 'ms'
						});
						
						// 如果API请求时间过长，输出警告
						if (apiTime > 1000) {
							console.warn('⚠️ [API警告] 省份数据请求耗时过长:', apiTime + 'ms');
							console.warn('💡 [建议] 检查服务器性能或网络状况');
						}
					} else {
						console.error('❌ [数据] 省份数据请求失败', res);
					}
				} catch (e) {
					const totalTime = Date.now() - startTime;
					console.error('❌ [数据] 预加载失败', {
						错误: e.message || e,
						耗时: totalTime + 'ms'
					});
				}
			},
			
			/**
			 * 处理微信授权流程
			 * 统一管理 OAuth 重定向和 OpenID 获取
			 * @returns {Promise<boolean>} true 表示需要重定向（页面将跳转），false 表示不需要
			 */
			async handleWxAuth() {
				console.log('[WxAuth] 🚀 开始处理微信授权流程...');
				console.log('[WxAuth] 🔗 当前URL:', window.location.href);
				
				// #ifdef H5
				try {
					// 1. 检查是否在微信浏览器中
					const ua = window.navigator.userAgent.toLowerCase();
					const isWechat = ua.indexOf('micromessenger') !== -1;
					if (!isWechat) {
						console.log('[WxAuth] 📱 非微信浏览器，跳过授权流程');
						return false; // 不需要重定向
					}
					
					// 2. 检查缓存的 OpenID
					const cachedOpenId = uni.getStorageSync('wx_openid');
					if (cachedOpenId) {
						console.log('[WxAuth] ✅ 已有缓存的 OpenID:', cachedOpenId.substring(0, 10) + '...');
						return false; // 不需要重定向
					}
					
					// 3. 检查 URL 中是否有 code
					const urlParams = new URL(window.location.href).searchParams;
					const code = urlParams.get('code');
					
					if (code) {
						// 有 code，换取 openid
						console.log('[WxAuth] 🔑 检测到 code，开始换取 OpenID...');
						console.log('[WxAuth] 📝 code:', code.substring(0, 15) + '...');
						
						try {
							const res = await this.$api.get('api/wechat/getOpenid', { code });
							console.log('[WxAuth] 📦 API返回:', JSON.stringify(res));
							
							if (res.code === 1 && res.data && res.data.openid) {
								const openid = res.data.openid;
								uni.setStorageSync('wx_openid', openid);
								console.log('[WxAuth] ✅ OpenID 获取成功:', openid.substring(0, 10) + '...');
							} else {
								console.warn('[WxAuth] ⚠️ OpenID 获取失败:', res.msg || '未知错误');
							}
						} catch (apiError) {
							console.error('[WxAuth] ❌ API 调用异常:', apiError);
						}
						
						// 无论成功失败，都移除 URL 中的 code 参数
						// 这样分享签名才能使用干净的 URL
						this.removeCodeFromUrl();
						console.log('[WxAuth] 🧹 已从 URL 中移除 code 参数');
						
						return false; // 不需要再重定向
					} else {
						// 无 code，无缓存，需要发起微信授权重定向
						console.log('[WxAuth] 🔄 需要获取授权，准备重定向到微信...');
						
						// 获取 AppID
						const signUrl = window.location.href.split('#')[0];
						const configRes = await this.$api.get('api/wechat/jsconfig', { url: signUrl });
						
						if (configRes.code === 1 && configRes.data && configRes.data.appId) {
							const appId = configRes.data.appId;
							const redirectUri = encodeURIComponent(window.location.href);
							const scope = 'snsapi_base'; // 静默授权
							const oauthUrl = `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirectUri}&response_type=code&scope=${scope}&state=STATE#wechat_redirect`;
							
							console.log('[WxAuth] 🌐 重定向到:', oauthUrl.substring(0, 100) + '...');
							window.location.href = oauthUrl;
							return true; // 需要重定向，页面会跳转
						} else {
							console.error('[WxAuth] ❌ 获取 AppID 失败');
						}
					}
				} catch (error) {
					console.error('[WxAuth] ❌ 授权流程异常:', error);
				}
				// #endif
				
				return false; // 默认不需要重定向
			},
			// 获取微信OpenID
			async getWxOpenId() {
				try {
					console.log('[OpenID] 🚀 开始获取流程...');
					console.log('[OpenID] 🔗 当前完整URL:', window.location.href);
					
					// 1. 检查缓存
					const cachedOpenId = uni.getStorageSync('wx_openid');
					if (cachedOpenId) {
						console.log('[OpenID] ✅ 使用缓存的OpenID:', cachedOpenId);
						return cachedOpenId;
					}
					console.log('[OpenID] 📝 无缓存OpenID');

					// 2. 环境判断：仅在 H5 环境且微信浏览器中执行
					// #ifdef H5
					const ua = window.navigator.userAgent.toLowerCase();
					const isWechat = ua.indexOf('micromessenger') !== -1;
					if (!isWechat) {
						console.log('[OpenID] ❌ 非微信浏览器，跳过获取');
						return '';
					}
					console.log('[OpenID] ✅ 检测到微信浏览器环境');

					// 3. 检查 URL 中是否带 code
					const urlParams = new URL(window.location.href).searchParams;
					const code = urlParams.get('code');
					console.log('[OpenID] 🔍 URL中的code:', code ? code.substring(0, 10) + '...' : '无');

					if (code) {
						// 4. 有 code，通过后端换取 openid
						console.log('[OpenID] 🌐 调用API换取OpenID，code:', code.substring(0, 10) + '...');
						console.log('[OpenID] ⏰ API调用开始时间:', new Date().toISOString());
						const res = await this.$api.get('api/wechat/getOpenid', { code });
						console.log('[OpenID] ⏰ API调用完成时间:', new Date().toISOString());
						console.log('[OpenID] 📦 API返回结果:', JSON.stringify(res));
						
						// 无论成功失败，都从 URL 中移除 code（code 只能使用一次）
						this.removeCodeFromUrl();
						
						if (res.code === 1 && res.data.openid) {
							const openid = res.data.openid;
							uni.setStorageSync('wx_openid', openid);
							console.log('[index] OpenID 获取成功:', openid);
							return openid;
						} else {
							console.error('[index] 换取 OpenID 失败:', res.msg);
						}
					} else {
						// 5. 无 code，且没有缓存，发起微信授权重定向
						console.log('[index] 发起微信静默授权重定向');
						// 获取 AppID
						const signUrl = window.location.href.split('#')[0];
						const res = await this.$api.get('api/wechat/jsconfig', { url: signUrl });
						if (res.code === 1 && res.data.appId) {
							const appId = res.data.appId;
							const redirectUri = encodeURIComponent(window.location.href);
							const scope = 'snsapi_base';
							const oauthUrl = `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirectUri}&response_type=code&scope=${scope}&state=STATE#wechat_redirect`;
							window.location.href = oauthUrl;
						}
					}
					// #endif
					return '';
				} catch (error) {
					console.error('[index] 获取OpenID异常:', error);
					return '';
				}
			},
			
			// 从 URL 中移除微信回调的 code 和 state 参数
			// 微信 code 只能使用一次，移除可避免刷新页面时报错
			removeCodeFromUrl() {
				try {
					const url = new URL(window.location.href);
					// 移除微信回调参数
					url.searchParams.delete('code');
					url.searchParams.delete('state');
					// 使用 replaceState 更新 URL，不会触发页面刷新
					window.history.replaceState({}, '', url.toString());
					console.log('[index] 已从 URL 中移除 code 参数');
				} catch (error) {
					console.warn('[index] 移除 URL 参数失败:', error);
				}
			},
			navto() {
				// 检测期间禁止点击（仅对 share 来源生效）
				if (this.isCheckingUser) {
					console.log('[index] 正在检测用户状态，点击无效');
					return;
				}
				
				// 获取来源参数
				const appParams = uni.getStorageSync('app_parmas') || {};
				
				// jump 来源：直接跳转 setInfo 页面
				if (appParams.from === 'jump') {
					console.log('[index] jump来源,跳转到setInfo页面');
					this.navigateWithTransition('/pages/login/setInfo');
					return;
				}
				
				// share 来源：根据是否二次测算决定跳转目标
				if (appParams.from === 'share') {
					if (this.isReturnUser) {
						// 二次测算用户 -> 跳转 setInfo（与"重新测算"按钮逻辑一致）
						console.log('[index] share来源+二次测算用户,跳转到setInfo页面');
						this.navigateWithTransition('/pages/login/setInfo');
					} else {
						// 新用户 -> 跳转三方链接
						console.log('[index] share来源+新用户,跳转到对方系统');
						this.redirectToThirdParty(appParams);
					}
					return;
				}
				
				// 其他来源（直进等）：始终禁用，不响应点击
				console.log('[index] 其他来源,按钮禁用,点击无效');
				return;
			},
			
			// 带过渡动画的页面跳转
			navigateWithTransition(url) {
				// 显示加载遮罩
				this.loadingText = '正在进入...';
				this.showLoading = true;
				this.isLeaving = true;
				
				// 延迟跳转，让动画播放
				setTimeout(() => {
					this.$go(url);
				}, 300);
			},
			async redirectToThirdParty(params) {
				// 【方案三】跳转前再次校验，防止竞态条件
				if (this.isCheckingUser) {
					console.log('[index] 跳转前检测未完成，拒绝跳转');
					return;
				}
				
				// 显示加载遮罩
				this.loadingText = '正在跳转...';
				this.showLoading = true;
				this.isLeaving = true;
				
				try {
					const res = await this.$api.get('api/user/getShareRedirectUrl', {
						merchantId: params.merchantId || '',
						activityCode: params.activityCode || '',
						agentCode: params.agentCode || '',
						sign: params.sign || ''
					});
					
					if (res.code === 1 && res.data.redirect_url) {
						console.log('[index] 跳转到:', res.data.redirect_url);
						// #ifdef H5
						// 延迟跳转，让遮罩完全显示
						setTimeout(() => {
							window.location.href = res.data.redirect_url;
						}, 200);
						// #endif
					} else {
						this.showLoading = false;
						this.isLeaving = false;
						this.$toast(res.msg || '获取跳转地址失败');
					}
				} catch (error) {
					this.showLoading = false;
					this.isLeaving = false;
					console.error('[index] 获取跳转地址失败:', error);
					this.$toast('网络请求失败');
				}
			},
			confirmIntro() {
				this.$refs.introPopup.close();
				this.$go('/pages/login/setInfo');
			},
			
			// 检查是否为返回用户
		async checkReturnUser() {
			// 开始检测，禁用按钮
			this.isCheckingUser = true;
			this.isReturnUser = false;
			
			try {
				// 从localStorage获取客户号和OpenID
				const customerId = uni.getStorageSync('customer_id');
				const openid = uni.getStorageSync('wx_openid');

				if (!customerId && !openid) {
					console.log('[index] 未找到客户号和OpenID,为新用户');
					this.isReturnUser = false;
					return;
				}
				console.log('[index] 检测信息 - 客户号:', customerId, 'OpenID:', openid);
				
				// 检查是否有测算记录
				const res = await this.$api.post('api/user/checkRecord', {
					customer_id: customerId || '',
					openid: openid || ''
				});
				
				if (res.code === 1 && res.data.has_record) {
					console.log('[index] 检测到测算记录,record_id:', res.data.last_record_id);
					this.lastRecordId = res.data.last_record_id;
					this.isReturnUser = true; // 标记为二次测算用户
					// 延迟弹窗,避免与页面加载动画冲突
					setTimeout(() => {
						this.$refs.returnUserPopup.open();
					}, 1500);
				} else {
					console.log('[index] 未找到测算记录');
					this.isReturnUser = false;
				}
			} catch (error) {
				console.error('[index] 检查返回用户失败:', error);
				// 静默失败,默认作为新用户处理
				this.isReturnUser = false;
			} finally {
				// 检测完成，启用按钮
				this.isCheckingUser = false;
				this.hasCheckedOnce = true; // 标记已检测过
				console.log('[index] 用户检测完成，isReturnUser:', this.isReturnUser);
			}
		},
			
			// 查看上次测算结果
			viewLastResult() {
				this.$refs.returnUserPopup.close();
				console.log('[index] 跳转到上次测算结果,record_id:', this.lastRecordId);
				// 跳转到结果页,传递isReview=true表示查看模式
				this.$go(`/pages/result/generate?record_id=${this.lastRecordId}&isReview=true`);
			},
			
			// 重新测算
			newCalculation() {
				this.$refs.returnUserPopup.close();
				console.log('[index] 用户选择重新测算');
			// 跳转到设置信息页
				this.$go('/pages/login/setInfo');
			}
		}
	}
</script>

<style lang="scss" scoped>
	/* 引入篆体字体 - 多种路径尝试 */
	@font-face {
		font-family: 'JFZSKSealScript';
		src: url('~@/static/ttf/JFZSKSealScript_V2.0.ttf') format('truetype'),
		     url('/static/ttf/JFZSKSealScript_V2.0.ttf') format('truetype'),
		     url('../static/ttf/JFZSKSealScript_V2.0.ttf') format('truetype');
		font-weight: normal;
		font-style: normal;
		font-display: block;
		unicode-range: U+4E5D, U+7D2B, U+79BB, U+706B; /* 九紫离火的Unicode */
	}
	
	page {
		background-color: #1a1a2e;
	}

	.content {
		width: 100%;
		/* 使用100vh确保占满整个视口高度 */
		height: 100vh;
		min-height: 100vh;
		max-height: 100vh;
		/* 使用卷轴背景图 shouye3.jpg */
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/shouye3.jpg);
		background-size: 100% 100%;
		background-repeat: no-repeat;
		background-position: center center;
		background-color: #F5E6D3;
		/* flex布局自适应 */
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: space-evenly;
		padding: 0;
		box-sizing: border-box;
		position: relative;
		/* 禁止滚动 */
		overflow: hidden;
	}
	
	/* 双Logo容器 - 顶部居中 */
	.dual-logo-container {
		position: absolute;
		top: 60rpx;
		left: 50%;
		transform: translateX(-50%);
		z-index: 100;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 16rpx;
		/* 无背景，直接透明 */
		background: transparent;
		padding: 0;
		box-sizing: border-box;
		animation-delay: 0.5s;
	}
	
	.logo-item {
		/* 统一logo尺寸 */
		width: 140rpx !important;
		height: 56rpx !important;
		display: block;
		/* 使用contain保持比例完整显示 */
		object-fit: contain;
		/* 白色背景衬托 */
		background: rgba(255, 255, 255, 0.9);
		border-radius: 8rpx;
		padding: 8rpx 12rpx;
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.3));
		/* 确保box-sizing包含padding */
		box-sizing: content-box;
	}
	
	.logo-divider {
		font-size: 56rpx;
		font-weight: 100;
		/* 使用深色系分隔线，与背景融合 */
		color: rgba(139, 105, 20, 0.5);
		line-height: 0.9;
		margin: 0 4rpx;
		text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
	}
	
	/* 主题文案容器 - 解密2026（移到九紫离火上方） */
	.theme-container {
		width: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-shrink: 0;
		padding: 0 60rpx;
		/* 移除固定margin,使用flex自动分配空间 */
		margin: 0;
	}
	
	/* 主题文案图片样式 */
	.theme-image {
		/* 放大图片尺寸 */
		max-width: 95%;
		height: auto;
		/* 长度(高度)增加50%,宽度增加20%,向下移动 */
		transform: scaleY(1.5) scaleX(1.2) translateY(110rpx);
	}

	/* 方案1：金色渐变 + 深色描边 */
	.theme-title {
		font-size: 48rpx;
		font-weight: 700;
		letter-spacing: 8rpx;
		line-height: 1.6;
		/* 竖排显示 */
		writing-mode: vertical-rl;
		text-orientation: upright;
		/* 金属渐变效果 */
		background: linear-gradient(180deg, #FFD700 0%, #DAA520 50%, #B8860B 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
		/* 深色描边增强对比 */
		-webkit-text-stroke: 1rpx rgba(92, 64, 51, 0.5);
		/* 阴影增强立体感 */
		filter: drop-shadow(0 4rpx 8rpx rgba(0, 0, 0, 0.8));
	}
	
	/* 方案2：金色 + 黑色外发光 */
	.theme-title-v2 {
		font-size: 48rpx;
		font-weight: 700;
		letter-spacing: 8rpx;
		line-height: 1.6;
		/* 竖排显示 */
		writing-mode: vertical-rl;
		text-orientation: upright;
		color: #FFD700;
		text-shadow: 
			0 0 10rpx #000,
			0 0 20rpx #000,
			0 0 30rpx rgba(0,0,0,0.8),
			0 2rpx 4rpx rgba(0,0,0,0.9);
	}
	
	/* 方案3：金色 + 深红色背景条 */
	.theme-title-v3 {
		font-size: 48rpx;
		font-weight: 700;
		letter-spacing: 8rpx;
		line-height: 1.6;
		/* 竖排显示 */
		writing-mode: vertical-rl;
		text-orientation: upright;
		color: #FFD700;
		background: linear-gradient(90deg, transparent 0%, rgba(139,0,0,0.7) 20%, rgba(139,0,0,0.7) 80%, transparent 100%);
		padding: 20rpx 60rpx;
		text-shadow: 0 2rpx 8rpx rgba(0,0,0,0.8);
	}
	
	/* 方案4：双层金色立体感 - 浅金渐变 */
	.theme-title-v4 {
		font-size: 36rpx;
		font-weight: 700;
		letter-spacing: 4rpx;
		line-height: 1.6;
		/* 竖排显示 */
		writing-mode: vertical-rl;
		text-orientation: upright;
		/* 浅金渐变 */
		background: linear-gradient(180deg, #fefdf8 0%, #faeac2 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
		/* 立体阴影 */
		filter: drop-shadow(2rpx 2rpx 0 #B8860B)
				drop-shadow(4rpx 4rpx 0 #8B6914)
				drop-shadow(0 6rpx 12rpx rgba(0,0,0,0.9));
	}
	
	/* 九紫离火 - 水印风格散落效果 */
	.floating-text {
		position: absolute;
		top: 30%;
		left: 0;
		right: 0;
		height: 40%;
		pointer-events: none;
		z-index: 1;
	}
	
	.zhuanti-char-img {
		position: absolute;
		/* 缩小尺寸以适配一屏显示 */
		width: 120rpx;
		height: auto;
		/* 水印透明效果 */
		opacity: 0.25;
		/* 金色光晕 */
		filter: drop-shadow(0 0 20rpx rgba(255, 215, 0, 0.6));
		/* 漂浮动画 */
		animation: floatWatermark 4s ease-in-out infinite;
	}
	
	/* 四个字随意散落位置 */
	.zhuanti-char-img:nth-child(1) {
		top: 5%;
		left: 8%;
		transform: rotate(-15deg);
		animation-delay: 0s;
	}
	.zhuanti-char-img:nth-child(2) {
		top: 15%;
		right: 12%;
		left: auto;
		transform: rotate(10deg);
		animation-delay: 0.5s;
	}
	.zhuanti-char-img:nth-child(3) {
		bottom: 20%;
		top: auto;
		left: 15%;
		transform: rotate(8deg);
		animation-delay: 1s;
	}
	.zhuanti-char-img:nth-child(4) {
		bottom: 10%;
		top: auto;
		right: 8%;
		left: auto;
		transform: rotate(-12deg);
		animation-delay: 1.5s;
	}
	
	@keyframes floatWatermark {
		0%, 100% {
			transform: translateY(0) rotate(var(--rotate, 0deg));
			opacity: 0.2;
		}
		50% {
			transform: translateY(-10rpx) rotate(var(--rotate, 0deg));
			opacity: 0.3;
		}
	}
	
	/* 按钮容器 */
	.btn-container {
		width: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-shrink: 0;
		/* 移除固定margin,使用flex自动分配空间 */
		margin: 0;
		/* 调整padding使按钮向上移动 */
		padding: 5rpx 0 50rpx 0;
		/* 提升层级,高于装饰元素 */
		position: relative;
		z-index: 20;
	}
	
	/* 按钮图片样式 */
	.explore-btn-image {
		max-width: 520rpx;
		width: 80%;
		height: auto;
		cursor: pointer;
		/* 向上移动25rpx */
		transform: translateY(-25rpx);
	}
	
	.explore-btn-image:active {
		/* 点击时保留位置,同时缩放 */
		transform: translateY(-25rpx) scale(0.95);
	}

	.explore-btn {
		width: 520rpx;
		height: 96rpx;
		/* 统一红色渐变按钮样式 */
		background: linear-gradient(135deg, #D0000F 0%, #C41E1E 25%, #A22823 50%, #8B0000 100%);
		border-radius: 48rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		box-shadow: 
			0 8rpx 24rpx rgba(208, 0, 15, 0.4),
			inset 0 2rpx 0 rgba(255, 255, 255, 0.2),
			inset 0 -2rpx 0 rgba(0, 0, 0, 0.2);
		position: relative;
		overflow: hidden;
		z-index: 20;
		
		/* 按钮光泽效果 */
		&::before {
			content: '';
			position: absolute;
			top: -50%;
			left: -50%;
			width: 200%;
			height: 200%;
			background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
			transform: rotate(45deg);
			animation: shimmer 3s infinite;
		}
		
		&:active {
			transform: scale(0.98);
			box-shadow: 0 4rpx 12rpx rgba(208, 0, 15, 0.3);
		}
	}
	
	@keyframes shimmer {
		0% {
			transform: translateX(-100%) rotate(45deg);
		}
		100% {
			transform: translateX(100%) rotate(45deg);
		}
	}
	
	.btn-text {
		font-size: 36rpx;
		font-weight: bold;
		color: #fff;
		letter-spacing: 8rpx;
		text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.2);
		position: relative;
		z-index: 1;
	}
	
	/* 漂浮动画 */
	@keyframes float {
		0%, 100% {
			transform: translateY(0) scale(1);
			opacity: 0.85;
		}
		25% {
			transform: translateY(-15rpx) scale(1.05);
			opacity: 0.95;
		}
		50% {
			transform: translateY(-8rpx) scale(1.02);
			opacity: 0.9;
		}
		75% {
			transform: translateY(-20rpx) scale(1.08);
			opacity: 1;
		}
	}
	
	/* 金色光泽脉冲动画 */
	@keyframes goldPulse {
		0%, 100% {
			filter: drop-shadow(0 0 25rpx rgba(255, 215, 0, 1)) 
					drop-shadow(0 0 50rpx rgba(218, 165, 32, 0.8))
					drop-shadow(0 0 80rpx rgba(184, 134, 11, 0.5))
					drop-shadow(4rpx 4rpx 10rpx rgba(0, 0, 0, 0.8));
		}
		50% {
			filter: drop-shadow(0 0 35rpx rgba(255, 215, 0, 1)) 
					drop-shadow(0 0 70rpx rgba(255, 215, 0, 0.9))
					drop-shadow(0 0 100rpx rgba(218, 165, 32, 0.6))
					drop-shadow(4rpx 4rpx 10rpx rgba(0, 0, 0, 0.8));
		}
	}
	
	/* 金色文字光泽动画 */
	@keyframes goldShine {
		0%, 100% {
			filter: drop-shadow(0 4rpx 12rpx rgba(0, 0, 0, 0.9))
					drop-shadow(0 2rpx 4rpx rgba(0, 0, 0, 0.7))
					drop-shadow(0 0 20rpx rgba(255, 215, 0, 0.8))
					drop-shadow(0 0 40rpx rgba(218, 165, 32, 0.5));
		}
		50% {
			filter: drop-shadow(0 4rpx 12rpx rgba(0, 0, 0, 0.9))
					drop-shadow(0 2rpx 4rpx rgba(0, 0, 0, 0.7))
					drop-shadow(0 0 30rpx rgba(255, 215, 0, 1))
					drop-shadow(0 0 60rpx rgba(255, 215, 0, 0.7));
		}
	}
	
	/* ========== 吉祥点缀装饰样式 ========== */
	.decor-container {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		pointer-events: none;
		z-index: 10;
	}
	
	/* 灯笼装饰 */
	.decor-lantern {
		position: absolute;
		width: 70rpx;
		top: 60rpx;
		opacity: 0.9;
		filter: drop-shadow(0 4rpx 8rpx rgba(0, 0, 0, 0.3));
	}
	.decor-left {
		left: 15rpx;
	}
	.decor-right {
		right: 15rpx;
		transform: scaleX(-1);
	}
	
	/* 方案A：马群装饰 - 底部居中 */
	.decor-horse {
		position: absolute;
		width: 280rpx;
		bottom: 30rpx;
		left: 50%;
		margin-left: -140rpx; /* 使用margin-left代替transform,确保居中 */
		opacity: 0.75;
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.3));
	}
	
	/* 方案B：双马装饰 - 底部 */
	.decor-horse-b {
		position: absolute;
		width: 240rpx;
		bottom: 25rpx;
		left: 50%;
		transform: translateX(-50%);
		opacity: 0.8;
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.3));
	}
	
	/* 祥云装饰 */
	.decor-cloud {
		position: absolute;
		opacity: 0.7;
		filter: drop-shadow(0 2rpx 4rpx rgba(0, 0, 0, 0.2));
	}
	.decor-cloud-left {
		width: 200rpx;
		left: -20rpx;
		bottom: 18%;
	}
	.decor-cloud-right {
		width: 180rpx;
		right: -20rpx;
		top: 25%;
		transform: scaleX(-1);
	}
	
	/* 仙鹤装饰 */
	.decor-crane {
		position: absolute;
		opacity: 0.85;
		filter: drop-shadow(0 2rpx 8rpx rgba(0, 0, 0, 0.3));
	}
	.decor-crane-left {
		width: 160rpx;
		left: 10rpx;
		bottom: 15%;
	}
	.decor-crane-top {
		width: 160rpx;
		right: 20rpx;
		top: 18%;
	}
	.decor-crane-right {
		width: 100rpx;
		right: 15rpx;
		bottom: 22%;
		transform: scaleX(-1);
	}
	
	/* ========== 九紫离火运介绍弹窗样式 ========== */
	/* 弹窗容器 - 古典卷轴风格 */
	.intro-popup-container {
		width: 550rpx; /* 缩小宽度,避免超出屏幕 */
		max-width: 90vw;
		background: linear-gradient(180deg, #FFF8E7 0%, #F5E6C8 100%);
		border-radius: 24rpx;
		padding: 50rpx 40rpx;
		box-shadow: 
			0 8rpx 24rpx rgba(139, 69, 19, 0.3),
			inset 0 2rpx 8rpx rgba(255, 255, 255, 0.5);
		border: 4rpx solid #DAA520;
		position: relative;
		overflow: hidden;
	}
	
	/* 弹窗暗纹图片样式 */
	.intro-watermark {
		position: absolute;
		width: 320rpx;
		height: auto;
		opacity: 0.12;
		pointer-events: none;
		z-index: 1; /* 确保在背景之上 */
	}
	
	.watermark-top-left {
		top: -40rpx;
		left: -60rpx;
		transform: rotate(-15deg);
	}
	
	.watermark-bottom-right {
		bottom: -20rpx;
		right: -60rpx;
		transform: rotate(10deg);
	}
	
	.watermark-title-under {
		top: 60rpx;
		left: 50%;
		transform: translateX(-50%) rotate(5deg);
		width: 400rpx;
		opacity: 0.08;
	}
	
	.watermark-center {
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%) rotate(-10deg);
		width: 450rpx;
		opacity: 0.06;
	}
	
	/* 调整原内容的层级，确保在暗纹之上 */
	.intro-title, .intro-content, .intro-btn, .return-user-btns {
		position: relative;
		z-index: 5;
	}
	
	/* 弹窗标题 - 与解密图片相同的浅金色渐变和立体阴影 */
	.intro-title {
		font-size: 48rpx;
		font-weight: bold;
		text-align: center;
		margin-bottom: 40rpx;
		/* 浅金渐变 - 与theme-title-v4一致 */
		background: linear-gradient(180deg, #fefdf8 0%, #faeac2 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
		background-clip: text;
		/* 立体阴影 - 与theme-title-v4一致 */
		filter: drop-shadow(2rpx 2rpx 0 #B8860B)
				drop-shadow(4rpx 4rpx 0 #8B6914)
				drop-shadow(0 6rpx 12rpx rgba(0,0,0,0.9));
	}
	
	/* 介绍内容区域 */
	.intro-content {
		margin-bottom: 40rpx;
		line-height: 1.8;
	}
	
	/* 段落 */
	.intro-paragraph {
		margin-bottom: 30rpx;
	}
	
	/* 列表 */
	.intro-list {
		margin-top: 20rpx;
	}
	
	/* 列表项 */
	.intro-item {
		display: flex;
		align-items: flex-start;
		margin-bottom: 20rpx;
	}
	
	/* 列表项目符号 */
	.intro-bullet {
		color: #8B0000;
		font-size: 32rpx;
		margin-right: 12rpx;
		flex-shrink: 0;
		line-height: 1.6;
	}
	
	/* 介绍文字 - 使用楷体风格 */
	.intro-text {
		font-size: 28rpx;
		color: #5C4033;
		line-height: 1.8;
		text-align: justify;
		/* 使用系统楷体或宋体 */
		font-family: "KaiTi", "STKaiti", "SimSun", serif;
	}
	
	/* 确认按钮 - 复用项目主按钮样式 */
	.intro-btn {
		width: 100%;
		background: linear-gradient(135deg, #8B0000 0%, #A22823 50%, #8B0000 100%);
		border: 3rpx solid #DAA520;
		border-radius: 12rpx;
		padding: 28rpx 40rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		box-shadow: 
			0 4rpx 12rpx rgba(139, 0, 0, 0.4),
			inset 0 2rpx 4rpx rgba(255, 215, 0, 0.2);
		transition: transform 0.2s;
		
		&:active {
			transform: scale(0.98);
		}
	}
	
	.intro-btn-text {
		color: #FFD700;
		font-size: 32rpx;
		font-weight: bold;
		text-shadow: 1rpx 1rpx 2rpx rgba(0, 0, 0, 0.3);
	}
	
	/* 返回用户弹窗按钮组 */
	.return-user-btns {
		display: flex;
		gap: 20rpx;
		margin-top: 40rpx;
	}
	
	/* 次要按钮样式 */
	.intro-btn-secondary {
		background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
		border: 3rpx solid #DAA520;
		box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
	}
	
	.intro-btn-text-secondary {
		color: #8B0000;
	}
</style>