<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<!-- 左上角双Logo布局 -->
		<view class="dual-logo-container animate__animated animate__fadeInLeft">
			<image src="/static/logo1.png" class="logo-item" mode="aspectFit"></image>
			<view class="logo-divider">|</view>
			<image src="/static/logo2.png" class="logo-item" mode="aspectFit"></image>
		</view>
		
		<!-- 主题文案 - 解密2026 -->
		<view class="theme-container animate__animated animate__fadeInDown animate__delay-0-8s">
			<view class="theme-title">解密2026，探索美好未来</view>
		</view>
		
		<!-- 九紫离火 - 单字篆体图片（支持独立动画） -->
		<view class="floating-text animate__animated animate__fadeIn animate__delay-1s">
			<image src="/static/九.png" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0s;"></image>
			<image src="/static/紫.png" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.2s;"></image>
			<image src="/static/离.png" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.4s;"></image>
			<image src="/static/火.png" class="zhuanti-char-img" mode="widthFix" style="animation-delay: 0.6s;"></image>
		</view>
		
		<!-- 按钮 -->
		<view class="btn-container animate__animated animate__zoomIn animate__delay-1s">
			<view class="explore-btn" @click="navto">
				<text class="btn-text">探索 2026</text>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: 'Hello',
				windowHeight: '',
				pageLoadStart: 0, // 页面加载开始时间
				pageLoadEnd: 0    // 页面加载完成时间
			}
		},
		onLoad(op) {
			// 🚀 性能监控：记录页面加载开始时间
			this.pageLoadStart = Date.now();
			console.log('🚀 [性能] 页面开始加载', new Date().toISOString());
			
			console.log('----', op);
			if (op.sign) {
				uni.setStorageSync('app_parmas', op)
			}
			
			// ⚡ 异步预加载省市数据（不阻塞页面渲染）
			// 使用 setTimeout 确保页面渲染完成后再加载
			setTimeout(() => {
				this.preloadAreaData();
			}, 100);
		},
		onReady() {
			this.getsy();
			
			// 🚀 性能监控：记录页面渲染完成时间
			this.pageLoadEnd = Date.now();
			const loadTime = this.pageLoadEnd - this.pageLoadStart;
			console.log('✅ [性能] 页面渲染完成', {
				耗时: loadTime + 'ms',
				时间戳: new Date().toISOString()
			});
			
			// 如果加载时间超过2秒，输出警告
			if (loadTime > 2000) {
				console.warn('⚠️ [性能警告] 页面加载超过2秒，耗时:', loadTime + 'ms');
			}
		},
		methods: {
			// 异步预加载省市数据（不阻塞页面渲染）
			async preloadAreaData() {
				const startTime = Date.now();
				try {
					console.log('📦 [数据] 开始预加载省市数据');
					
					// 检查是否已有缓存
					const cachedProvince = uni.getStorageSync('cache_provinceList');
					if (cachedProvince && cachedProvince.length > 0) {
						console.log('✅ [数据] 省份数据已缓存，跳过', {
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
						console.log('✅ [数据] 省份数据缓存完成', {
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
			getsy() {
				uni.getSystemInfo({
					success: res => {
						this.windowHeight = res.windowHeight;
						console.log('📱 [系统] 设备信息', {
							高度: res.windowHeight + 'px',
							平台: res.platform,
							系统: res.system
						});
					}
				});
			},
			navto() {
				console.log('🔗 [导航] 跳转到信息填写页');
				uni.navigateTo({
					url: "/pages/login/setInfo"
				})
			}
		}
	}
</script>

<style lang="scss">
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
		height: 100%;
		/* 使用卷轴背景图 shouye1.jpg */
		background-image: url(/static/shouye2.jpg);
		background-size: 100% 100%;
		background-repeat: no-repeat;
		background-position: center center;
		background-color: #F5E6D3;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		padding: 0;
		box-sizing: border-box;
		position: relative;
		overflow: hidden;
	}
	
	/* 双Logo容器 - 位于左上角，无背景框 */
	.dual-logo-container {
		position: absolute;
		top: 80rpx;
		left: 40rpx;
		z-index: 100;
		display: flex;
		align-items: center;
		gap: 30rpx;
		/* 无背景，直接透明 */
		background: transparent;
		padding: 0;
		box-sizing: border-box;
		animation-delay: 0.5s;
	}
	
	.logo-item {
		/* 固定宽高，强制统一大小 */
		width: 200rpx !important;
		height: 80rpx !important;
		display: block;
		/* 使用contain保持比例完整显示 */
		object-fit: contain;
		/* 已抠好背景，无需混合模式 */
		/* 添加金色光晕效果，与背景融合 */
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.3))
				drop-shadow(0 0 12rpx rgba(218, 165, 32, 0.3));
		/* 轻微透明度使其更融入背景 */
		opacity: 0.95;
	}
	
	.logo-divider {
		font-size: 80rpx;
		font-weight: 100;
		/* 使用深色系分隔线，与背景融合 */
		color: rgba(139, 105, 20, 0.5);
		line-height: 0.9;
		margin: 0 8rpx;
		text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
	}
	
	/* 主题文案容器 - 解密2026（移到九紫离火上方） */
	.theme-container {
		width: 100%;
		text-align: center;
		flex-shrink: 0;
		padding: 0 60rpx;
		margin-top: 18vh;
		margin-bottom: 30rpx;
	}

	.theme-title {
		font-size: 44rpx;
		font-weight: 600;
		/* 使用指定的金色 */
		color: #faeac2;
		/* 简洁阴影 - 无金粉效果 */
		text-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.5);
		letter-spacing: 8rpx;
		line-height: 1.6;
	}
	
	/* 九紫离火 - 单字篆体图片效果 */
	.floating-text {
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 15rpx;
		flex-shrink: 0;
		margin-bottom: 6vh;
	}
	
	.zhuanti-char-img {
		width: 120rpx;
		height: auto;
		/* 纯正金色发光效果 - 增强金属质感 */
		filter: drop-shadow(0 0 25rpx rgba(255, 215, 0, 1)) 
				drop-shadow(0 0 50rpx rgba(218, 165, 32, 0.8))
				drop-shadow(0 0 80rpx rgba(184, 134, 11, 0.5))
				drop-shadow(4rpx 4rpx 10rpx rgba(0, 0, 0, 0.8));
		/* 每个字独立的漂浮动画 */
		animation: float 3s ease-in-out infinite, goldPulse 2s ease-in-out infinite;
		display: inline-block;
	}

	/* 按钮容器 */
	.btn-container {
		width: 100%;
		display: flex;
		justify-content: center;
		flex-shrink: 0;
		margin: 30vh 0 40rpx 0;
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
</style>