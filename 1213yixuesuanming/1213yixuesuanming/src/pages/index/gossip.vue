<template>
	<view>
		<!-- 顶部文字 -->
		<view class="pt-20">
			<view class="w_100 flex_right" style="text-align: right;height: 44rpx;">
				<image v-if="!donghua_show" class="top_text" src="/static/t0.png" mode="aspectFill"
					style="width: 480rpx;height: 44rpx;margin-right: -35rpx;"></image>
			</view>

			<view class="w_100 flex_right pr-32" style="height: 44rpx;">
				<image v-if="!donghua_show" class="top_texts" src="/static/t1.png" mode="aspectFill"
					style="width: 480rpx;height: 44rpx;z-index: 9999;"></image>
			</view>
		</view>

		<!-- 中间所有图片 -->
		<view class="w_100 flex_center">
			<view id="max_box" class="flex_column fade-element fade-0 po_re zIndex-12" style="padding-top: 184rpx;">
				<!-- 竖条条 -->
				<image v-if="donghua_show&&!niandu_show" src="/static/shu-text.png" style="width: 56rpx;left: 162rpx;"
					class="po_ab t-28 zIndex-1  shu_show0" mode="widthFix">
				</image>
				<image v-if="donghua_show&&!niandu_show" src="/static/shu-text.png"
					style="width: 56rpx;right: 196rpx;top: 64rpx;" class="po_ab zIndex-1  shu_show1" mode="widthFix">
				</image>
				<image v-if="donghua_show&&!niandu_show" src="/static/texiao-bj.png" style="width: 610rpx;"
					class="po_ab zIndex-1 t-0 l-0  shu_show2" mode="widthFix">
				</image>

				<view v-if="donghua_show==0">
					<view id="img0"></view>
					<view class="text-container fade-element fade-0 w_100 flex_column">
						<view class="text-item">
							<image src="/static/h2.png" mode="aspectFill" style="width: 572rpx;height: 50rpx;"></image>
						</view>
					</view>
				</view>
				<view v-else class="fade-element">
					<view class="fade-element" id="img1">
						<view class="fade-element fade-0" id="img2">
							<image src="/static/b2.png" mode="aspectFill" class=" fade-element fade-0" id="img3">
							</image>
						</view>
					</view>
				</view>

				<view v-if="!niandu_show" :class="{'fade-element':true}" class="text-container w_100 flex_column">
					<!-- <view class="text-item" v-for="(item, index) in textList" :key="index">
				<image :src="item" mode="aspectFill" :class="{ 'fade-element': showText[index] }" v-if="showText[index]"
					style="width: 572rpx;height: 50rpx;"></image>
			</view> -->
					<view class="text-item">
						<image :src="item" mode="aspectFill" :class="{ 'fade-element': showText[index] }"
							v-if="showText[index]" style="width: 572rpx;height: 50rpx;"
							v-for="(item, index) in textList" :key="index"></image>
					</view>
				</view>

				<view :class="{'fade-element':true}" v-else class="text-container w_100 flex_column">
					<view class="text-item" v-for="(item, index) in textList_niandu" :key="index">
						<image :src="item" mode="aspectFill" :class="{ 'fade-element': showText_niandu[index] }"
							v-if="showText_niandu[index]"
							:style="{width: `${index==0?510:418}rpx`,height: `${index==0?82:76}rpx`}">
						</image>
					</view>
				</view>
			</view>
		</view>

		<!-- 右下角 -->
		<image :src="`/static/${donghua_show==1?'right_boms':'right_bom'}.png`" class="po_fi r-0 b-0 zIndex-12"
			style="width: 182rpx;height: 256rpx;" mode="aspectFill"></image>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				isRotate: false,

				textList_niandu: ['/static/ninde2025.png', '/static/niandujuben.png'],
				textList: ['/static/h0.png', '/static/h1.png', '/static/h2.png', '/static/h3.png'],
				showText: [false, false, false, false],
				showText_niandu: [false, false],
				niandu_show: false,

				show_text: '', // /static/h2.png
				donghua_show: 0,
				record_id:''
			}
		},
		onLoad({
			record_id
		}) {
			console.log('[Gossip] onLoad 开始, record_id:', record_id, '时间:', new Date().toISOString());
			this.record_id = record_id;
			
			// 🚀 性能优化：去除动画，直接加载和跳转
			this.quickLoad();
		},
		methods: {
			// 🚀 快速加载：显示loading提示，后台加载数据，完成后跳转
			async quickLoad() {
				const startTime = Date.now();
				
				// 显示简单的加载提示
				uni.showLoading({
					title: '正在生成命盘...',
					mask: true
				});
				
				try {
					console.log('[Gossip] 开始加载数据');
					
					// 并行请求两个API
					const [siZhuRes, resultRes] = await Promise.all([
						this.$api.post('api/si_zhu/getSiZhuRes', { record_id: this.record_id }),
						this.$api.post('api/si_zhu/getResult', { record_id: this.record_id })
					]);
					
					console.log('[Gossip] 数据加载完成, 耗时:', Date.now() - startTime, 'ms');
					
					// 缓存数据供后续页面使用
					uni.setStorageSync('preload_siZhuRes_' + this.record_id, siZhuRes);
					uni.setStorageSync('preload_resultRes_' + this.record_id, resultRes);
					
					// 关闭loading
					uni.hideLoading();
					
					// 立即跳转到结果页
					uni.redirectTo({
						url: "/pages/index/Generated?record_id=" + this.record_id
					});
					
				} catch (e) {
					console.error('[Gossip] 加载失败', e);
					uni.hideLoading();
					uni.showToast({
						title: '加载失败，请重试',
						icon: 'none'
					});
				}
			},
			
			// ⚠️ 以下方法已废弃，保留以防回滚
			async preloadData() {
				try {
					console.log('[Gossip] 开始预加载数据');
					const [siZhuRes, resultRes] = await Promise.all([
						this.$api.post('api/si_zhu/getSiZhuRes', { record_id: this.record_id }),
						this.$api.post('api/si_zhu/getResult', { record_id: this.record_id })
					]);
					uni.setStorageSync('preload_siZhuRes_' + this.record_id, siZhuRes);
					uni.setStorageSync('preload_resultRes_' + this.record_id, resultRes);
					console.log('[Gossip] 预加载完成');
				} catch (e) {
					console.log('[Gossip] 预加载失败', e);
				}
			},
			startAnimation() {
				const startTime = Date.now();
				console.log('[Gossip] startAnimation 开始, 时间:', new Date().toISOString());
				this.isRotate = true;
				setTimeout(() => {
					console.log('[Gossip] 第一阶段动画完成, 耗时:', Date.now() - startTime, 'ms');
					this.donghua_show = 1;
					setTimeout(() => {
						console.log('[Gossip] 第二阶段动画开始');
						this.isRotate = false;
						this.textList.forEach((item, index) => {
							setTimeout(() => {
								console.log('[Gossip] 显示文字', index, '耗时:', Date.now() - startTime, 'ms');
								this.showText[index] = true;
								this.showText[index] = true;
								if (index != 0) this.showText[index - 1] = false;
								if (index == 3) setTimeout(() => this.showNiandu(), 2600)
								this.$forceUpdate()
							}, index * 2000);
						});
					}, 300);
				}, 3030)
			},
			showNiandu() {
				this.textList = this.textList.map(v => v = false);
				this.niandu_show = true;
				this.textList_niandu.forEach((item, index) => {
					setTimeout(() => {
						this.showText_niandu[index] = true;
						if (this.showText_niandu[0] && this.showText_niandu[1]) {
							setTimeout(() => uni.redirectTo({
								url: "/pages/index/Generated?record_id=" + this.record_id
							}), 3800)
						}
						this.$forceUpdate()
					}, index * 1500);
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.image.fade {
		opacity: 1;
	}

	.shu_show0 {
		opacity: 0;
		animation: topGuodu 5s .2s alternate forwards;
		animation-delay: .2s;
	}

	.shu_show1 {
		opacity: 0;
		animation: topGuodu 5s .4s alternate forwards;
		animation-delay: .4s;
	}

	.shu_show2 {
		opacity: 0;
		animation: topGuodu 5s .6s alternate forwards;
		animation-delay: .6s;
	}

	.top_text {
		opacity: 1;
		animation: topGuodu 5s 0s alternate forwards;
		animation-delay: 0s;
	}

	.top_texts {
		opacity: 0;
		animation: topGuodu 4s .7s alternate forwards;
		animation-delay: .7s;
	}

	@keyframes topGuodu {
		0% {
			opacity: 0.3;
		}

		20% {
			opacity: 1;
		}

		40% {
			opacity: 0.3;
		}

		60% {
			opacity: 1;
		}

		80% {
			opacity: 0.3;
		}

		100% {
			opacity: 1;
		}
	}

	.max_box {
		width: 610rpx;
	}

	#img0 {
		background: url(/static/0b.png);
				background-repeat: no-repeat;
		background-size: cover;
		width: 644rpx;
		height: 644rpx;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
		animation: chushi 7s alternate forwards;
		/* 总时长9秒，循环播放 */
		// animation-delay: 2.5s;
		animation-delay: .2s;
		/* 延迟3秒开始动画 */
	}

	@keyframes chushi {
		0% {
			opacity: 1;
		}

		20% {
			opacity: 0.3;
		}

		40% {
			opacity: 1;
		}

		60% {
			opacity: 0.3;
		}

		80% {
			opacity: 1;
		}

		100% {
			opacity: 0;
		}
	}

	#img1 {
		background: url(/static/b0.png);
				background-repeat: no-repeat;
		background-size: cover;
		width: 644rpx;
		height: 644rpx;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
		// margin: 114rpx 53rpx 0 53rpx;
		animation: fadeAndRotate 35s 0s alternate forwards;
		/* 总时长9秒，循环播放 */
		// animation-delay: 2.5s;
		animation-delay: 0s;
		/* 延迟3秒开始动画 */
	}

	#img2 {
		background: url(/static/b1.png);
				background-repeat: no-repeat;
		background-size: cover;
		width: 592rpx;
		height: 592rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		animation: fadeAndRotate1 35s .2s alternate forwards;
		// animation-delay: 1s;
		animation-delay: 0.1s;
		/* 延迟3秒开始动画 */
	}

	#img3 {
		width: 562rpx;
		height: 560rpx;
		animation: fadeAndRotate1 35s .4s alternate forwards;
		// animation-delay: 2s;
		animation-delay: 0.4s;
		/* 延迟6秒开始动画 */
	}

	@keyframes fadeAndRotate {
		0% {
			opacity: 1;
			transform: rotate(0deg);
		}

		40% {
			opacity: 1;
			transform: rotate(360deg);
		}

		90% {
			opacity: 1;
			transform: rotate(360deg);
		}

		100% {
			opacity: 1;
			transform: rotate(360deg);
		}
	}

	@keyframes fadeAndRotate1 {
		0% {
			opacity: 1;
			transform: rotate(0deg);
		}

		40% {
			opacity: 1;
			transform: rotate(-360deg);
		}

		90% {
			opacity: 1;
			transform: rotate(-360deg);
		}

		100% {
			opacity: 1;
			transform: rotate(-360deg);
		}
	}


	page {
		background: url('/static/ma2.jpg') no-repeat center top;
		background-size: 100% auto;
		background-color: #FDF5E6;
		min-height: 100vh;
		overflow: hidden;
	}

	.fade-0 {
		opacity: 1;
		animation: fadeOut 5s alternate forwards;
	}

	@keyframes fadeOut {
		100% {
			opacity: 0;
		}
	}

	.fade-element {
		opacity: 0;
		animation: fadeIn 5s alternate forwards;
	}

	@keyframes fadeIn {
		100% {
			opacity: 1;
		}
	}

	.text-container {
		text-align: center;
		margin-top: 50rpx;
	}

	.text-item {
		margin-bottom: 20rpx;
	}
</style>