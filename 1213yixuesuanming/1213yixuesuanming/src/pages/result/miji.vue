<template>
	<view class="pt-50 px-30 w_100">
		<!-- Loading遮罩 - 使用 Animate.css -->
		<view v-if="loading" class="loading-overlay">
			<view class="loading-content animate__animated animate__fadeIn">
				<view class="loading-box animate__animated animate__pulse animate__infinite">
					<view class="loading-icon">⚡</view>
				</view>
				<text class="loading-text animate__animated animate__fadeInUp animate__delay-1s">正在加载您的旺运秘籍...</text>
			</view>
		</view>
		
		<view class="w_100 my_color flex a_c fz_24 fz_500 po_re zIndex-11 mb-24" style="height: 84rpx;">
			<view style="width: 8rpx;height: 35rpx;background-color: #D0000F;border-radius: 30rpx;margin-right: 15rpx;">
			</view>
			<view class="flex_1 flex a_bom fz_b fz_36">
				个人旺运秘籍
			</view>
		</view>

		<view class="w_100 boxs_1 mb-24" :class="{'skeleton-box': loading}">
			<view class="w_100 flex_wrap pt-32">
				<view v-for="i,k in text_list" :key="k" class="item_box flex a_bom jc_b">
					<view class="flex_column h_100 flex_1">
						<view class="item_title">
							{{i.name}}
						</view>
						<view v-if="k==0" class="item_fangwei my_color flex_auto">
							{{wang_yun.fang_wei}}
						</view>
						<view v-if="k==1" class="flex_center w_100 mt-24 fz_22">
							<view v-for="v,b in wang_yun.color" :key="b" class="flex_column">
								<image :src="`/static/color/${loadColor(v)}.png`" class="color_img" mode="aspectFill">
								</image>
								{{v}}
							</view>
						</view>
						<view v-if="k==2" class="item_text">
							{{wang_yun.num}}
						</view>
						<view v-if="k==3" class="item_text">
							{{wang_yun.shi_pin}}
						</view>
						<view v-if="k==4" class="item_text">
							{{wang_yun.ji_jie}}
						</view>
						<view v-if="k==5" class="item_text">
							{{wang_yun.time}}
						</view>
						<view v-if="k==6" class="item_text">
							{{wang_yun.kou_wei}}
						</view>
					</view>
					<view v-if="i.border" class="item_border"> </view>
				</view>
			</view>
		</view>

		<view class="w_100 boxs_2 mb-24">
			<view class="box_100 pb-39 pt-24">
				<view class="my_color flex_center w_100 pb-28">
					旺运事业方向
				</view>
				<view class="fz_32 text-align-center fz_500 pb-25 px-20" style="line-height: 50rpx;">
					{{xing_ge_min.worker}}
				</view>
			</view>
		</view>
		<view class="w_100 boxs_2 mb-24">
			<view class="box_100 pb-39 pt-24">
				<view class="my_color flex_center w_100 pb-28">
					身体需注意的部位
				</view>
				<view class="fz_32 text-align-center fz_500 pb-25 px-20" style="line-height: 50rpx;">
					{{wang_yun.zhu_yi}}
				</view>
			</view>
		</view>

		<view style="height: 200rpx;"> </view>

		<view class="w_100 back_f flex_column po_fi b-0 l-0 pb-30 pt-15 round-t-10 shadow-10">
			<view class="w_100 flex_auto">
				<view @click="$goBack()" class="bom_btn fz_32 po_re">
					上一页
				</view>
				<view @click="$go('/pages/result/yijiesuo?record_id='+record_id)" class="bom_btn fz_32 po_re">
					下一页
				</view>
			</view>
			<view class="bom_h"></view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				loading: true,
				record_id: '',
				text_list: [{
					name: '旺运方位',
					border: true
				}, {
					name: '旺运颜色',
					border: true
				}, {
					name: '旺运数字'
				}, {
					name: '旺运饰品',
					border: true
				}, {
					name: '旺运月份(阳历)',
					border: true
				}, {
					name: '旺运时间'
				}, {
					name: '旺运口味',
					border: true
				}],
				wang_yun: {},
				xing_ge_max: {},
				xing_ge_min: {},
			}
		},
		onLoad({
			record_id
		}) {
			this.record_id = record_id;
			this.loadData();
		},
		methods: {
			async loadData() {
				this.loading = true;
				try {
					// 优先使用预加载的缓存数据
					const cacheKey = 'preload_resultRes_' + this.record_id;
					let res = uni.getStorageSync(cacheKey);
					
					if (res && res.code == 1) {
						console.log('[miji] 使用预加载缓存数据');
						uni.removeStorageSync(cacheKey); // 用完清除
					} else {
						console.log('[miji] 缓存未命中，重新请求');
						res = await this.$api.post('api/si_zhu/getResult', {
							record_id: this.record_id
						});
					}
					
					if (res.code != 1) return this.$toast(res.msg);
					this.wang_yun = res.data.wang_yun;
					this.xing_ge_max = res.data.xing_ge_max;
					this.xing_ge_min = res.data.xing_ge_min;
					this.wang_yun.color = res.data.wang_yun.color.split(',');
				} finally {
					this.loading = false;
				}
			},
			loadColor(type) {
				let color_;
				switch (type) {
					case '白':
						color_ = 'bai';
						break;
					case '金':
						color_ = 'jin';
						break;
					case '绿':
						color_ = 'lv';
						break;
					case '青':
						color_ = 'qing';
						break;
					case '黑':
						color_ = 'hei';
						break;
					case '蓝':
						color_ = 'lan';
						break;
					case '红':
						color_ = 'hong';
						break;
					case '紫':
						color_ = 'zi';
						break;
					case '黄':
						color_ = 'huang';
						break;
					case '棕':
						color_ = 'zong';
						break;
				}
				return color_
			}
		}
	}
</script>

<style scoped lang="scss">
	page {
		/* 使用红色祥云背景 */
		background-image: url(/static/beijing.jpg);
		background-size: cover;
		background-repeat: no-repeat;
		background-position: center center;
		background-color: #BF0000;
	}

	.boxs_1 {
		height: 640rpx;
		background: rgba(255, 241, 241, 0.95);
		border-radius: 16rpx;
		padding: 22rpx 32rpx 46rpx 36rpx;
		border: 2rpx solid rgba(255, 190, 190, 0.5);
		box-shadow: 0 8rpx 24rpx rgba(208, 0, 15, 0.15);
	}

	.boxs_2 {
		background: rgba(255, 241, 241, 0.95);
		border-radius: 16rpx;
		padding: 36rpx 32rpx 44rpx 36rpx;
		width: 692rpx;
		border: 2rpx solid rgba(255, 190, 190, 0.5);
		box-shadow: 0 8rpx 24rpx rgba(208, 0, 15, 0.15);
	}

	.item_box {
		// width: 196rpx;
		width: 32%;
		height: 132rpx;
		margin-bottom: 48rpx;

		.item_title {
			color: #A22823;
			font-size: 24rpx;
		}

		.item_fangwei {
			width: 100rpx;
			height: 38rpx;
			line-height: 34rpx;
			margin-top: 26rpx;
			font-size: 24rpx;
			background-image: url("/static/miji/fangxiang@2x.png");
			background-repeat: no-repeat;
			// background-size: cover;
			background-size: 100% 100%;
		}

		.item_text {
			margin-top: 26rpx;
			color: #D0000F;
			font-size: 24rpx;
		}
	}

	.item_border {
		height: 104rpx;
		width: 1rpx;
		background-color: #FFC2C7;
	}

	.bom_btn {
		width: 320rpx;
		height: 88rpx;
		line-height: 88rpx;
		text-align: center;
		color: #fff;
		font-weight: bold;
		border-radius: 44rpx;
		background: linear-gradient(135deg, #D0000F 0%, #A22823 50%, #8B0000 100%);
		box-shadow: 0 4rpx 12rpx rgba(208, 0, 15, 0.3);
		
		&:first-child {
			background: linear-gradient(135deg, #fff 0%, #f5f5f5 100%);
			border: 2rpx solid #D0000F;
			color: #D0000F;
		}
	}

	// Loading遮罩样式 - 使用 Animate.css
	.loading-overlay {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(245, 230, 211, 0.95);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 9999;
		backdrop-filter: blur(10px);
	}

	.loading-content {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.loading-box {
		width: 160rpx;
		height: 160rpx;
		background: linear-gradient(135deg, #FFF1F1, #FFE0E0);
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		box-shadow: 0 8rpx 32rpx rgba(208, 0, 15, 0.3);
		border: 4rpx solid rgba(208, 0, 15, 0.2);
	}

	.loading-icon {
		font-size: 80rpx;
		animation: bounce 1s ease-in-out infinite;
	}

	@keyframes bounce {
		0%, 100% {
			transform: translateY(0) scale(1);
		}
		50% {
			transform: translateY(-10rpx) scale(1.1);
		}
	}

	.loading-text {
		margin-top: 40rpx;
		font-size: 32rpx;
		color: #D0000F;
		font-weight: 500;
		text-shadow: 0 2rpx 8rpx rgba(208, 0, 15, 0.2);
	}

	// 骨架屏效果
	.skeleton-box {
		opacity: 0.6;
	}
</style>