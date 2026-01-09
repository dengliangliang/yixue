<template>
	<view class="loading-page">
		<!-- WebGL 烟雾背景层 - 使用原生HTML canvas以支持WebGL -->
		<canvas canvas-id="particleCanvas" id="particleCanvas" class="smoke-canvas"></canvas>

		
		<!-- 祥云装饰层 - 左右两边各一组 -->
		<view class="cloud-decor">
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="cloud cloud-left-top" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="cloud cloud-left-bottom" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="cloud cloud-right-top" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="cloud cloud-right-bottom" mode="widthFix"></image>
		</view>
		
		<!-- 中心核心内容 - 五色环区域 (粒子渲染在 canvas 上) -->
		<view class="center-core">
			<!-- 五色环由 WebGL canvas 渲染，此处仅作为定位参考 -->
		</view>
		
		<!-- 文字 - 五色环正下方 -->
		<view class="loading-text-box">
			<text class="loading-text">{{ loadingText }}</text>
		</view>
		
		<!-- 奔马动画 - 移至末尾确保层级 -->
		<view class="horse-container">
			<view class="horse-viewport">
				<view class="horse-strip"></view>
			</view>
		</view>

	</view>
</template>

<script>
	import { initImageMorph } from '@/common/image_morph.js';
	import gsap from 'gsap';
	import websiteConfig from '@/config/website.js';

	export default {
		data() {
			return {
				loadingText: '正在解析命盘...',
				progress: 0,
				record_id: '',
				apiComplete: false,
				smokeApi: null,
                windowWidth: 0,
                windowHeight: 0,
                centerX: 0,
                centerY: 0
			};
		},
		computed: {
			cdnBase() {
				return websiteConfig.CDN.enabled ? websiteConfig.CDN.baseUrl : '';
			},
			staticPrefix() {
				return websiteConfig.CDN.enabled ? '/src/static/' : '/static/';
			}
		},
		async onLoad(options) {
			// 如果有 record_id 参数，直接使用
			if (options.record_id) {
				this.record_id = options.record_id;
				this.apiComplete = true; // 直接传入 record_id 也视为 API 完成
				console.log('[loading] 直接使用传入的 record_id:', this.record_id);
				return;
			}
			
			// 【优化】优先使用预请求的 Promise（由 setInfo 发起）
			if (uni.$apiPromise) {
				console.log('[loading] 使用预请求的 API Promise');
				try {
					const { data, code, msg } = await uni.$apiPromise;
					// 清理预请求 Promise
					uni.$apiPromise = null;
					
					if (code != 1) {
						this.$toast(msg);
						setTimeout(() => uni.navigateBack(), 1500);
						return;
					}
					
					this.record_id = data.record_id;
					this.apiComplete = true;
					console.log('[loading] 预请求完成, record_id:', this.record_id);
					
					// 异步保存，不阻塞
					const formData = uni.getStorageSync('pending_form_data');
					if (formData?.loc_date) {
						uni.setStorage({ key: 'loc_date', data: formData.loc_date });
					}
					if (data.customer_id) {
						uni.setStorage({ key: 'customer_id', data: data.customer_id });
					}
					uni.removeStorage({ key: 'pending_form_data' });
					return;
				} catch (e) {
					console.error('[loading] 预请求失败', e);
					uni.$apiPromise = null;
					this.$toast('网络请求失败');
					setTimeout(() => uni.navigateBack(), 1500);
					return;
				}
			}
			
			// 回退：从存储中获取表单数据并发起新的 API 请求
			const formData = uni.getStorageSync('pending_form_data');
			if (formData) {
				try {
					console.log('[loading] 无预请求，发起新请求');
					const { data, code, msg } = await this.$api.post('api/user/addRecord', formData);
					
					if (code != 1) {
						this.$toast(msg);
						setTimeout(() => uni.navigateBack(), 1500);
						return;
					}
					
						this.record_id = data.record_id;
					this.apiComplete = true; // 标记 API 已完成
					console.log('[loading] API完成, record_id:', this.record_id);
					
					// 异步保存，不阻塞
					if (formData.loc_date) {
						uni.setStorage({ key: 'loc_date', data: formData.loc_date });
					}
					if (data.customer_id) {
						uni.setStorage({ key: 'customer_id', data: data.customer_id });
					}
					
					// 清理临时数据
					uni.removeStorage({ key: 'pending_form_data' });
				} catch (e) {
					this.$toast('网络请求失败');
					setTimeout(() => uni.navigateBack(), 1500);
				}
			}
		},

		async onReady() {
            const { windowWidth, windowHeight } = uni.getSystemInfoSync();
            this.windowWidth = windowWidth;
            this.windowHeight = windowHeight;
            this.centerX = windowWidth / 2;
            this.centerY = windowHeight / 2;

			// #ifdef H5
			// 动态创建 canvas 并注入 DOM，完全绕过 uni-app 的 canvas 处理
			this.$nextTick(() => {
				const container = document.querySelector('.loading-page');
				if (!container) {
					console.warn('[loading] 未找到容器');
					this.simulateProgress();
					return;
				}
				
				// 创建原生 canvas 元素
				const canvas = document.createElement('canvas');
				canvas.id = 'smokeCanvas'; // 保持唯一 ID
				canvas.className = 'smoke-canvas';
				
				canvas.width = windowWidth;
				canvas.height = windowHeight;
				canvas.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;';
				
				// 插入到容器最前面
				container.insertBefore(canvas, container.firstChild);
				
				try {
					// --- 五色环粒子动画配置 (WebGL) ---
                    // Ring: 480rpx target width
                    const ringTargetPx = (480 / 750) * windowWidth;
                    const ringScale = ringTargetPx / 400; // 400是采样尺寸

					this.smokeApi = initImageMorph(canvas, {
                        imageUrl: this.cdnBase + this.staticPrefix + 'donhuajiazai.png',
                        scale: { x: ringScale, y: ringScale },
                        centerOffset: { x: 0, y: windowHeight * 0.05 },
                        rotationSpeed: 0.2,       
                        morphDuration: 2.5,       
                        particleStep: 2           
                    }, {
                        particleSize: 3.5         // 增大粒子尺寸 (更饱满)
                    });
				} catch (e) {
					console.warn('[loading] WebGL 初始化失败', e);
				}
				// 3. 同时启动马匹动画 (不再等待粒子聚合完成)
				this.startHorseAnimation();
				
				// 4. 重置进度模拟
				this.simulateProgress();
			});
			// #endif
			
			// #ifndef H5
            // For non-H5, the canvas is already in the template, so we can directly get it.
            // However, initSmoke is WebGL specific, so we only call simulateProgress.
			this.simulateProgress();
			// #endif
		},



		onUnload() {
			if (this.smokeApi) this.smokeApi.destroy();
            if (this.horseTimeline) this.horseTimeline.kill();
		},
		methods: {
		startHorseAnimation() {
			if (this._horseTimeline) {
				this._horseTimeline.kill();
				this._horseTimeline = null;
			}
			
			const sw = uni.getSystemInfoSync().windowWidth || 375;
			const hw = uni.upx2px(350);      // 马的物理宽度
			const stripOffset = uni.upx2px(1750); 
			const startY = -uni.upx2px(300); // 极远端高度
			const endY = -uni.upx2px(60);    // 近端高度
			
			console.log('[Horse] 物理位移重构 | 彻底消除残留');

			const viewport = ".horse-viewport";
			const strip = ".horse-strip";

			// 1. 初始化（彻底藏在左侧外，极小）
			gsap.set(viewport, { 
				x: -hw * 1.2, 
				y: startY,
				scale: 0.05,
				opacity: 0,
				visibility: 'visible'
			});
			gsap.set(strip, { x: 0 });

			// 2. 创建平滑时间轴
			this._horseTimeline = gsap.timeline({
				repeat: -1,
				repeatDelay: 0.2
			});

			this._horseTimeline
				// --- A. 横向完整跑穿屏幕 (从 -hw 到 sw + hw) ---
				.to(viewport, {
					x: sw + hw, 
					duration: 4.8, // 稍微拉长一点点，让跑动更自然
					ease: "none"
				}, 0)
				
				// --- B. 3D 纵深变换 (在前 2.8s 完成) ---
				.to(viewport, {
					y: endY, 
					scale: 1.0, 
					opacity: 1,
					duration: 2.8, 
					ease: "power2.out"
				}, 0)
				
				// --- C. 离场保护：在快跑出屏幕时提前淡出，防止“卡屁股” ---
				.to(viewport, {
					opacity: 0,
					duration: 0.4,
					ease: "power1.in"
				}, 4.4) // 在 4.4秒（接近终点）时淡出
				
				// --- D. 精灵图切帧 ---
				.to(strip, {
					x: -stripOffset, 
					duration: 0.6,
					ease: "steps(5)",
					repeat: 14 // 增加循环次数以覆盖更长的位移
				}, 0);

			console.log('[Horse] 离场保护逻辑已生效');
		},
            
			startAnimation() {},

			simulateProgress() {
				const interval = setInterval(() => {
					if (this.progress < 90) {
						this.progress += Math.random() * 15;
						if (this.progress > 90) this.progress = 90;
					}
					
					if (this.progress >= 90 && this.apiComplete) {
						clearInterval(interval);
						
						// 【新增】分裂成粒子四散开的效果
						if (this.smokeApi && this.smokeApi.scatter) {
							this.smokeApi.scatter(1.5); // 1.5秒散开
						}
						
						this.finishLoading();
					} else if (this.progress >= 90 && !this.apiComplete) {
						this.loadingText = '正在获取测算结果...';
					}
				}, 600);
			},
			finishLoading() {
				this.progress = 100;
				this.loadingText = '解析完成，正在开启...';
				setTimeout(() => {
					uni.redirectTo({
						url: `/pages/result/generate?record_id=${this.record_id}`
					});
				}, 1500); // 增加延时，确保消散动画播放完整
			},
			handleTouch(e) {
				// 额外的手动交互
			}
		}
	}
</script>

<style lang="scss" scoped>
	.loading-page {
		width: 100%;
		height: 100vh;
		background-color: #F5E6D3;
		position: relative;
		overflow: hidden;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}


    
	.smoke-canvas {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: 1;
	}

	/* 祥云装饰层 - 左右两边各一组 */
	.cloud-decor {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		pointer-events: none;
		z-index: 0; /* 在烟雾canvas下方 */
		
		.cloud {
			position: absolute;
			width: 200rpx;
			opacity: 0.7;
			filter: sepia(0.3) brightness(0.95);
		}
		
		/* 左侧祥云 */
		.cloud-left-top {
			top: 80rpx;
			left: -30rpx;
			transform: rotate(15deg);
		}
		.cloud-left-bottom {
			bottom: 120rpx;
			left: -20rpx;
			transform: rotate(-10deg);
		}
		
		/* 右侧祥云 */
		.cloud-right-top {
			top: 100rpx;
			right: -30rpx;
			transform: rotate(-15deg) scaleX(-1);
		}
		.cloud-right-bottom {
			bottom: 150rpx;
			right: -20rpx;
			transform: rotate(10deg) scaleX(-1);
		}
	}

	.center-core {
		position: absolute;
		top: 45%; /* 上移，减少上方空白 */
		left: 50%;
		transform: translate(-50%, -50%);
		z-index: 10;
		width: 480rpx;
		height: 480rpx;
		pointer-events: none;
	}

	.loading-text-box {
		position: absolute;
		top: calc(45% + 250rpx); /* 同步上移缩短间距 */
		left: 0;
		right: 0;
		z-index: 10;
		text-align: center;
		
		.loading-text {
			font-size: 40rpx; /* 增大字体 */
			font-weight: bold;
			letter-spacing: 4rpx;
			/* 亮金色发光字体 */
			color: #f5f5dc;
			text-shadow: 
				0 0 10rpx rgba(251, 229, 182, 0.8),
				0 0 20rpx rgba(218, 165, 32, 0.6),
				0 0 30rpx rgba(218, 165, 32, 0.4),
				0 2rpx 4rpx rgba(0,0,0,0.3);
		}
	}

	/* 进度条已移除 */

	.energy-point {
		position: absolute;
		width: 10rpx;
		height: 10rpx;
		border-radius: 50%;
		pointer-events: none;
		z-index: 5;
		opacity: 0;
	}

	/* 奔马动画容器 - 全屏位移层 */
	.horse-container {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 80rpx; /* 调整离底部的距离 */
		height: 400rpx;
		overflow: visible;
		z-index: 15;
		pointer-events: none;
		will-change: transform;
	}

	/* 方案 1 核心：切帧视口 */
	.horse-viewport {
		position: absolute;
		left: 0;
		bottom: 0;
		width: 350rpx;  /* 单帧宽度 */
		height: 280rpx; /* 单帧高度 */
		overflow: hidden; /* 关键：裁剪长条 */
		will-change: transform;
	}

	/* 方案 1 核心：精灵图长条 */
	.horse-strip {
		position: absolute;
		left: 0;
		top: 0;
		width: 2100rpx; /* 6帧总宽: 350rpx * 6 */
		height: 100%;
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma/sprite-sheet.png);
		background-size: 100% 100%; /* 整个长条填充 */
		background-repeat: no-repeat;
		will-change: transform;
		transform: translateZ(0);
		backface-visibility: hidden;
	}
</style>
