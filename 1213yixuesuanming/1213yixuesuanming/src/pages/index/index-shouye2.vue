<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<!-- ========== 吉祥点缀装饰（卷轴版-丰富版） ========== -->
		<view class="decor-container">
			<!-- 灯笼 - 顶部两侧（马灯笼） -->
			<image src="/static/denglong1.png" class="decor-lantern decor-left animate__animated animate__fadeInDown" mode="widthFix"></image>
			<image src="/static/denglong1.png" class="decor-lantern decor-right animate__animated animate__fadeInDown" mode="widthFix"></image>
			<!-- 祥云 - 左下角 -->
			<image src="/static/yun1.png" class="decor-cloud decor-cloud-left animate__animated animate__fadeIn animate__delay-0-5s" mode="widthFix"></image>
			<!-- 祥云 - 右下角 -->
			<image src="/static/yun2.png" class="decor-cloud decor-cloud-right animate__animated animate__fadeIn animate__delay-0-5s" mode="widthFix"></image>
			<!-- 祥云 - 左上部 -->
			<image src="/static/yun1.png" class="decor-cloud decor-cloud-top-left animate__animated animate__fadeIn animate__delay-0-5s" mode="widthFix"></image>
			<!-- 祥云 - 右上部 -->
			<image src="/static/yun2.png" class="decor-cloud decor-cloud-top-right animate__animated animate__fadeIn animate__delay-0-5s" mode="widthFix"></image>
			<!-- 仙鹤 - 右上角 -->
			<image src="/static/he3.png" class="decor-crane decor-crane-top animate__animated animate__fadeInRight animate__delay-0-5s" mode="widthFix"></image>
			<!-- 仙鹤 - 左下部（按钮左边） -->
			<image src="/static/he4.png" class="decor-crane decor-crane-left animate__animated animate__fadeInLeft animate__delay-0-5s" mode="widthFix"></image>
			<!-- 仙鹤 - 右下部（按钮右边，对称） -->
			<image src="/static/he4.png" class="decor-crane decor-crane-right animate__animated animate__fadeInRight animate__delay-0-5s" mode="widthFix"></image>
			<!-- 马群 - 底部中间 -->
			<image src="/static/ma3.png" class="decor-horse animate__animated animate__fadeInUp animate__delay-1s" mode="widthFix"></image>
		</view>
		
		<!-- 左上角双Logo布局 -->
		<view class="dual-logo-container animate__animated animate__fadeInLeft">
			<image src="/static/logo1.png" class="logo-item" mode="aspectFit"></image>
			<view class="logo-divider">|</view>
			<image src="/static/logo2.png" class="logo-item" mode="aspectFit"></image>
		</view>
		
		<!-- 主题文案 - 解密2026 -->
		<view class="theme-container animate__animated animate__fadeInDown animate__delay-0-8s">
			<view class="theme-title-v4">解密2026，探索美好未来</view>
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
			//  性能监控：记录页面加载开始时间
			this.pageLoadStart = Date.now();
			console.log(' [性能] 页面开始加载', new Date().toISOString());
			
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
		/* 统一logo尺寸 - shouye2卷轴版本缩小 */
		width: 100rpx !important;
		height: 40rpx !important;
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
		text-align: center;
		flex-shrink: 0;
		padding: 0 60rpx;
		margin-top: 18vh;
		margin-bottom: 30rpx;
	}

	/* 方案1：金色渐变 + 深色描边 */
	.theme-title {
		font-size: 48rpx;
		font-weight: 700;
		letter-spacing: 8rpx;
		line-height: 1.6;
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
		width: 200rpx;
		height: auto;
		/* 完全不透明 */
		opacity: 1;
		/* 金色光晕 */
		filter: drop-shadow(0 0 20rpx rgba(255, 215, 0, 0.6));
		/* 漂浮动画 - 保持光晕变化效果 */
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
			filter: drop-shadow(0 0 15rpx rgba(255, 215, 0, 0.5));
		}
		50% {
			transform: translateY(-10rpx) rotate(var(--rotate, 0deg));
			filter: drop-shadow(0 0 30rpx rgba(255, 215, 0, 0.9));
		}
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
	
	/* ========== 吉祥点缀装饰样式（卷轴版-丰富版） ========== */
	.decor-container {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		pointer-events: none;
		z-index: 10;
	}
	
	/* 灯笼装饰 - 贴着卷轴边缘 */
	.decor-lantern {
		position: absolute;
		width: 55rpx;
		top: 85rpx;
		opacity: 0.85;
		filter: drop-shadow(0 4rpx 8rpx rgba(0, 0, 0, 0.3));
	}
	.decor-left {
		left: 85rpx;
	}
	.decor-right {
		right: 85rpx;
		transform: scaleX(-1);
	}
	
	/* 祥云装饰 */
	.decor-cloud {
		position: absolute;
		opacity: 0.5;
		filter: drop-shadow(0 2rpx 4rpx rgba(0, 0, 0, 0.1));
	}
	.decor-cloud-left {
		width: 140rpx;
		left: -15rpx;
		bottom: 12%;
	}
	.decor-cloud-right {
		width: 120rpx;
		right: -10rpx;
		bottom: 18%;
		transform: scaleX(-1);
	}
	.decor-cloud-top-left {
		width: 120rpx;
		left: -10rpx;
		top: 22%;
	}
	.decor-cloud-top-right {
		width: 100rpx;
		right: -8rpx;
		top: 28%;
		transform: scaleX(-1);
	}
	
	/* 仙鹤装饰 */
	.decor-crane {
		position: absolute;
		opacity: 0.75;
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.2));
	}
	.decor-crane-top {
		width: 120rpx;
		right: 5rpx;
		top: 15%;
	}
	.decor-crane-left {
		width: 130rpx;
		left: 0rpx;
		bottom: 28%;
	}
	.decor-crane-right {
		width: 130rpx;
		right: 0rpx;
		bottom: 28%;
		transform: scaleX(-1);
	}
	
	/* 马群装饰 - 底部居中 */
	.decor-horse {
		position: absolute;
		width: 220rpx;
		bottom: 25rpx;
		left: 50%;
		transform: translateX(-50%);
		opacity: 0.7;
		filter: drop-shadow(0 2rpx 6rpx rgba(0, 0, 0, 0.3));
	}
</style>