<template>
	<view class="loading-page">
		<!-- WebGL 烟雾背景层 - 使用原生HTML canvas以支持WebGL -->
		<canvas canvas-id="particleCanvas" id="particleCanvas" class="smoke-canvas"></canvas>
        <canvas canvas-id="lightningCanvas" id="lightningCanvas" class="lightning-canvas"></canvas>
		
		<!-- 祥云装饰层 - 左右两边各一组 -->
		<view class="cloud-decor">
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="cloud cloud-left-top" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="cloud cloud-left-bottom" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun1.png'" class="cloud cloud-right-top" mode="widthFix"></image>
			<image :src="cdnBase + staticPrefix + 'yun2.png'" class="cloud cloud-right-bottom" mode="widthFix"></image>
		</view>
		
		<!-- 中心核心内容 -->
		<view class="center-core">
			<!-- 旋转加载图标 (放大2倍) -->
			<view class="loading-spinner-wrapper">
				<view class="loading-spinner"></view>
			</view>
			<!-- 奔马动画 - 移到环下方横向奔跑 -->
			<view class="horse-container">
				<view class="horse-sprite"></view>
			</view>
			<view class="loading-text-box">
				<text class="loading-text">{{ loadingText }}</text>
			</view>
		</view>
		
		<!-- 五行能量点 (由 GSAP 控制) -->
		<view v-for="(item, index) in elements" :key="index" 
			:ref="'element-' + index"
			class="energy-point"
			:class="'energy-' + item.type">
		</view>
	</view>
</template>

<script>
	import { initSmoke } from '@/common/smoke_motion.js';
	import gsap from 'gsap';
	import websiteConfig from '@/config/website.js';

	export default {
		data() {
			return {
				loadingText: '正在解析命盘...',
				progress: 0,
				record_id: '',
				smokeApi: null,
                // 闪电相关
                lightningCtx: null,
                lightningSeed: 0,
                isLightningActive: false,
                lightningTimer: null,
                windowWidth: 0,
                windowHeight: 0,
                centerX: 0,
                centerY: 0,
				elements: [
					// 1.木/绿 渐变: 萃绿 -> 深绿
					{ type: 'mu', colors: [[0.5, 0.8, 0.4], [0.3, 0.7, 0.2], [0.1, 0.5, 0.1], [0.05, 0.3, 0.05]], start: { x: -50, y: 20 } },
					// 2.土/黄 渐变: 亮黄 -> 琥珀
					{ type: 'tu', colors: [[1.0, 0.9, 0.4], [1.0, 0.7, 0.2], [0.9, 0.5, 0.1], [0.7, 0.4, 0.0]], start: { x: 150, y: 30 } },
					// 3.火/橙 渐变: 橙红 -> 深红
					{ type: 'huo', colors: [[1.0, 0.6, 0.3], [1.0, 0.4, 0.1], [0.8, 0.2, 0.05], [0.5, 0.1, 0.0]], start: { x: 50, y: -20 } },
					// 4.水/蓝 渐变: 天蓝 -> 深海
					{ type: 'shui', colors: [[0.4, 0.8, 1.0], [0.2, 0.6, 0.9], [0.05, 0.4, 0.8], [0.0, 0.2, 0.6]], start: { x: 50, y: 120 } },
					// 5.金/棕 渐变: 金色 -> 青铜
					{ type: 'jin', colors: [[1.0, 0.9, 0.6], [0.9, 0.7, 0.3], [0.7, 0.5, 0.1], [0.5, 0.3, 0.05]], start: { x: -20, y: 80 } }
				]
			};
		},
		computed: {
			// 统一的静态资源路径转换
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
				return;
			}
			
			// 否则从存储中获取表单数据并发起 API 请求
			const formData = uni.getStorageSync('pending_form_data');
			if (formData) {
				try {
					const { data, code, msg } = await this.$api.post('api/user/addRecord', formData);
					
					if (code != 1) {
						this.$toast(msg);
						setTimeout(() => uni.navigateBack(), 1500);
						return;
					}
					
					this.record_id = data.record_id;
					
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

            this.initLightning(); // 初始化闪电canvas

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
					// 优化流体参数:折中调整消散率和涡旋强度
					this.smokeApi = initSmoke(canvas, {
						DENSITY_DISSIPATION: 0.95,  // 折中值,平衡可见性和堆积
						CURL: 8,
						SPLAT_RADIUS: 0.003
					});
					this.startAnimation();
				} catch (e) {
					console.warn('[loading] WebGL 初始化失败，降级显示', e);
				}
				
				this.simulateProgress();
			});
			// #endif
			
			// #ifndef H5
            // For non-H5, the canvas is already in the template, so we can directly get it.
            // However, initSmoke is WebGL specific, so we only call simulateProgress.
			this.simulateProgress();
            this.startAnimation(); // Start animation even if smoke is not initialized
			// #endif
		},



		onUnload() {
			if (this.smokeApi) this.smokeApi.destroy();
            if (this.lightningTimer) clearTimeout(this.lightningTimer);
		},
		methods: {
			startAnimation() {
				const windowWidth = uni.getSystemInfoSync().windowWidth;
				const windowHeight = uni.getSystemInfoSync().windowHeight;
				const centerX = windowWidth / 2;
				const centerY = windowHeight / 2;
				// 五色环半径（根据 .loading-spinner-wrapper 的 480rpx 计算）
				const ringRadius = windowWidth * 0.32;
				
				// 颜色插值函数：根据进度从颜色数组中计算当前颜色
				const interpolateColor = (colors, progress) => {
					const p = Math.min(Math.max(progress, 0), 1) * (colors.length - 1);
					const idx = Math.floor(p);
					const t = p - idx;
					const c1 = colors[Math.min(idx, colors.length - 1)];
					const c2 = colors[Math.min(idx + 1, colors.length - 1)];
					return [
						c1[0] + (c2[0] - c1[0]) * t,
						c1[1] + (c2[1] - c1[1]) * t,
						c1[2] + (c2[2] - c1[2]) * t
					];
				};
				
				// 方案B:同步开始旋转 - 全局完成状态跟踪
				let completedPhase1Count = 0;  // 已完成阶段1的元素数量
				const elementObjects = [];     // 存储所有元素的obj引用
				const elementConfigs = [];     // 存储每个元素的配置
				
				this.elements.forEach((el, index) => {
					const baseAngle = (index / this.elements.length) * Math.PI * 2;
					
					// 初始状态：远离中心，带螺旋偏移
					const obj = { 
						r: windowWidth * 1.0,
						orbitAngle: baseAngle - Math.PI * 0.5,
						phase: 0
					};
					
					elementObjects.push(obj);
					elementConfigs.push({ el, index, baseAngle });
					
					// 阶段1：切入动画（仅执行一次，带延迟）
					gsap.to(obj, {
						duration: 2.5,
						r: ringRadius * 0.65,
						orbitAngle: baseAngle,  // 到达目标位置(0°, 72°, 144°...)
						ease: "power2.inOut",
						delay: index * 0.4,
						onUpdate: () => {
							const currX = centerX + Math.cos(obj.orbitAngle) * obj.r;
							const currY = centerY + Math.sin(obj.orbitAngle) * obj.r;
							const progress = Math.min(obj.r / (ringRadius * 0.65), 1);
							const currentColor = interpolateColor(el.colors, 1 - progress);
							
							if (this.smokeApi) {
					// Phase 1: 入场阶段也加入向心力，方向从当前点指向中心
					const inDirX = centerX - currX;
					const inDirY = centerY - currY;
					const inDist = Math.sqrt(inDirX * inDirX + inDirY * inDirY);
					const inNx = inDirX / (inDist || 1);
					const inNy = inDirY / (inDist || 1);

					this.smokeApi.splat(
						currX,
						currY,
						inNx * 30 + (Math.random() - 0.5) * 10,
						inNy * 30 + (Math.random() - 0.5) * 10,
						currentColor
					);
				}
						},
						onComplete: () => {
                            // 进入 Phase 2
                            // 开启随机闪电
                            this.triggerRandomLightning();
							// 阶段1完成,计数+1
							completedPhase1Count++;
							
							// 如果所有元素都完成了阶段1,启动统一的旋转动画
							if (completedPhase1Count === this.elements.length) {
								console.log('[loading] 所有元素已到位,开始同步旋转');
								
								// 为每个元素启动旋转动画,使用不同duration打破精确同步
								const durations = [5.0, 5.1, 5.2, 5.3, 5.4];
								
								elementObjects.forEach((elementObj, idx) => {
									const config = elementConfigs[idx];
									let frameCount = 0;
									
									gsap.to(elementObj, {
										duration: durations[idx],
										orbitAngle: `+=${Math.PI * 2}`,
										ease: "none",
										repeat: -1,
										onUpdate: () => {
											const orbitX = centerX + Math.cos(elementObj.orbitAngle) * ringRadius * 0.65;
											const orbitY = centerY + Math.sin(elementObj.orbitAngle) * ringRadius * 0.65;
											const rawColor = config.el.colors[config.el.colors.length - 1];
											
											// V4 核心修改：移除 frameCount % 6 节流记录，改为逐帧平滑喷射。
											// 为了补偿频率提升，将单次喷射的颜色强度降低为原来的约 1/3 (折中值，兼顾可见度)。
											const smoothColor = [rawColor[0] * 0.35, rawColor[1] * 0.35, rawColor[2] * 0.35];

											if (this.smokeApi) {
								// 计算向心分量：从当前点指向中心 (centerX, centerY)
								const dirX = centerX - orbitX;
								const dirY = centerY - orbitY;
								const dist = Math.sqrt(dirX * dirX + dirY * dirY);
								const nx = dirX / (dist || 1);
								const ny = dirY / (dist || 1);

								// 混合速度：70% 切向 + 30% 向心，确保烟云向内“卷入”
								const tangentialSpeed = 50;
								const radialSpeed = 25;
								
								const dx = (-Math.sin(elementObj.orbitAngle) * tangentialSpeed) + (nx * radialSpeed);
								const dy = (Math.cos(elementObj.orbitAngle) * tangentialSpeed) + (ny * radialSpeed);
								
								this.smokeApi.splat(
									orbitX,
									orbitY,
									dx,
									dy,
									smoothColor
								);
											}
										}
									});
								});
							}
						}
					});
				});
			},
            // --- 闪电逻辑 ---
            initLightning() {
                // #ifdef H5
                const wrapper = document.getElementById('lightningCanvas');
                if (wrapper) {
                    // Uni-app H5 会将 canvas 封装在 uni-canvas 标签内
                    const canvas = wrapper.getElementsByTagName('canvas')[0] || wrapper;
                    if (canvas && canvas.getContext) {
                        this.lightningCtx = canvas.getContext('2d');
                        canvas.width = this.windowWidth;
                        canvas.height = this.windowHeight;
                    }
                }
                // #endif

                // #ifndef H5
                const query = uni.createSelectorQuery().in(this);
                query.select('#lightningCanvas').fields({ node: true, size: true }, (res) => {
                    if (res && res.node) {
                        const canvas = res.node;
                        this.lightningCtx = canvas.getContext('2d');
                        canvas.width = this.windowWidth;
                        canvas.height = this.windowHeight;
                    }
                }).exec();
                // #endif
            },
            
            lightningRandom() {
                const x = Math.sin(this.lightningSeed += 1000) * 10000;
                return x - Math.floor(x);
            },
            
            createLightningPath(start, end, gameTime) {
                this.lightningSeed = gameTime;
                const variance = Math.max(Math.abs(start[0] - end[0]), Math.abs(start[1] - end[1]));
                const points = [start];
                const pointCount = Math.max(5, Math.floor(variance / 15));
                
                for (let i = 1; i <= pointCount; i++) {
                    const nextPoint = [0, 0]; // Initialize nextPoint
                    // Corrected nextPoint logic
                    nextPoint[0] = start[0] + (end[0] - start[0]) * (i / pointCount);
                    nextPoint[1] = start[1] + (end[1] - start[1]) * (i / pointCount);
                    
                    if (i < pointCount) {
                        nextPoint[0] += (this.lightningRandom() - 0.5) * Math.sqrt(variance) * 1.5;
                        nextPoint[1] += (this.lightningRandom() - 0.5) * Math.sqrt(variance) * 1.5;
                    } else {
                        nextPoint[0] = end[0];
                        nextPoint[1] = end[1];
                    }
                    points.push(nextPoint);
                }
                return points;
            },
            
            drawLightning(points, alpha = 1) {
                if (!this.lightningCtx) return;
                const ctx = this.lightningCtx;
                
                ctx.beginPath();
                ctx.moveTo(points[0][0], points[0][1]);
                for (let i = 1; i < points.length; i++) {
                    ctx.lineTo(points[i][0], points[i][1]);
                }
                
                // 外发光层
                ctx.globalAlpha = alpha * 0.3;
                ctx.strokeStyle = '#9e00ff';
                ctx.lineWidth = 6;
                ctx.stroke();
                
                // 核心层
                ctx.globalAlpha = alpha;
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 1.5;
                ctx.stroke();
                
                ctx.globalAlpha = 1;
            },
            
            triggerRandomLightning() {
                if (this.lightningTimer) return;
                
                const burst = () => {
                    // 随机生成 3-5 条闪电同时爆发
                    const count = 3 + Math.floor(Math.random() * 3);
                    const shots = [];
                    for(let i=0; i<count; i++) {
                        const side = Math.floor(Math.random() * 4);
                        let start = [0,0];
                        if (side === 0) start = [Math.random() * this.windowWidth, -20]; // Top
                        if (side === 1) start = [this.windowWidth + 20, Math.random() * this.windowHeight]; // Right
                        if (side === 2) start = [Math.random() * this.windowWidth, this.windowHeight + 20]; // Bottom
                        if (side === 3) start = [-20, Math.random() * this.windowHeight]; // Left
                        
                        const end = [this.centerX + (Math.random()-0.5)*100, this.centerY + (Math.random()-0.5)*100];
                        shots.push(this.createLightningPath(start, end, Date.now() + i*100));
                    }
                    
                    // GSAP 模拟闪烁效果 - 延长至 1.2s，增加余晖感
                const flashObj = { opacity: 1 };
                gsap.to(flashObj, {
                    opacity: 0,
                    duration: 0.8, // 调回 V6 的紧凑时长
                    ease: "expo.out",
                    onUpdate: () => {
                        if (!this.lightningCtx) return;
                        this.lightningCtx.clearRect(0, 0, this.windowWidth, this.windowHeight);
                        shots.forEach(path => this.drawLightning(path, flashObj.opacity));
                    },
                    onComplete: () => {
                        if (this.lightningCtx) this.lightningCtx.clearRect(0, 0, this.windowWidth, this.windowHeight);
                        // 随机下次爆发时间
                        this.lightningTimer = setTimeout(burst, 800 + Math.random() * 2000); // 调回 V6 的随机间隔
                    }
                });
                };
                
                this.lightningTimer = setTimeout(burst, 1000);
            },
			simulateProgress() {
				const interval = setInterval(() => {
					if (this.progress < 90) {
						this.progress += Math.random() * 15;
						if (this.progress > 90) this.progress = 90;
					}
					
					if (this.progress >= 90) {
						// 检查数据是否准备好，或达到一定时间
						clearInterval(interval);
						this.finishLoading();
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
				}, 800);
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

    .lightning-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        pointer-events: none;
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
		position: relative;
		z-index: 10;
		display: flex;
		flex-direction: column;
		align-items: center;
		width: 100%;  /* 确保占满父容器宽度，使子元素能够正确居中 */
		pointer-events: none; /* 让触摸事件穿透到canvas */
	}

	.loading-spinner-wrapper {
		width: 480rpx;
		height: 480rpx;
		position: relative;
		margin-bottom: 40rpx;
	}

	.loading-spinner {
		width: 100%;
		height: 100%;
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/donhuajiazai.png);
		background-size: contain;
		background-repeat: no-repeat;
		background-position: center;
		animation: rotate 3s linear infinite;
		border: none;
		box-shadow: none;
	}


	.horse-container {
		position: fixed; /* 改为固定定位实现全屏宽度 */
		left: 0;
		right: 0;
		bottom: 200rpx; /* 定位在屏幕下方 */
		height: 180rpx;
		overflow: visible; /* 允许小马动画溢出 */
		z-index: 15;
	}

	.horse-sprite {
		position: absolute;
		width: 180rpx;
		height: 180rpx;
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma6.png);
		background-size: 1440rpx 180rpx; /* 8帧 x 180rpx = 1440rpx 宽 */
		background-repeat: no-repeat;
		background-position: 0 0;
		animation: horseSpriteRun 0.8s steps(8) infinite, horseRunAcross 4s linear infinite;
	}

	.loading-text-box {
		text-align: center !important;
		color: #FFFACD !important;  /* 柠檬绸色(LemonChiffon) - 更浅更亮的金色 */
		width: 100% !important;  /* 确保容器占满父元素宽度 */
		display: flex !important;
		justify-content: center !important;  /* 使用flex居中 */
		
		.loading-text {
			display: inline-block !important;
			font-size: 32rpx !important;
			font-weight: bold !important;
			letter-spacing: 4rpx !important;
			margin-right: -4rpx !important;  /* 抵消letter-spacing在最后一个字符后的额外间距 */
			text-shadow: 0 2rpx 4rpx rgba(0,0,0,0.5) !important;
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
		opacity: 0; /* 仅作为逻辑参考点，视觉由渲染决定 */
	}

	@keyframes rotate {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}

	@keyframes horseRun {
		0% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma1.png); }
		16% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma2.png); }
		33% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma3.png); }
		50% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma4.png); }
		66% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma5.png); }
		83% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma6.png); }
		100% { background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/ma7.png); }
	}

	@keyframes horseRunAcross {
		0% { 
			left: -180rpx; 
		}
		100% { 
			left: 100%; 
		}
	}

	@keyframes horseSpriteRun {
		from { background-position: 0 0; }
		to { background-position: -1440rpx 0; }
	}
</style>


