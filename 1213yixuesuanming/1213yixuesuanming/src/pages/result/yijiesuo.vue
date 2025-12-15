<script>
	export default {
		data() {
			return {
				record_id: '',
				record_res: {},
				qrCodeUrl: ''
			}
		},
		onLoad({
			record_id,
		}) {
			this.qrCodeUrl = uni.getStorageSync('app_parmas').qrCodeUrl;
			this.record_id = record_id;
			this.loadData();
		},
		methods: {
			async loadData() {
				const res = await this.$api.post('api/si_zhu/getResult', {
					record_id: this.record_id
				});
				if (res.code != 1) return this.$toast(res.msg);
				this.record_res = res.data.record_res;
			},
			useNext() {
				this.$api.post('api/si_zhu/notify', {
					record_id: this.record_id
				});
				this.$go(`/pages/result/share?record_id=${this.record_id}&qrCodeUrl=${this.qrCodeUrl}`)
				// if (res.code != 1) return this.$toast(res.msg);
			}
		}
	}
</script>

<template>
	<view class="max_page" :style="'min-height:'+$windowHeight+'px'">
		<view style="height: 454rpx;"> </view>
		<view class="w_100 pl-20 pr-38">
			<view class="boxs_center po_re round-t-4">
				<view class="w_100 flex a_top jc_b" style="padding: 28rpx 12rpx 30rpx 22rpx;">
					<image src="/static/left-shu.png" style="width: 20rpx;height: 116rpx;" mode="aspectFill"></image>
					<view class="flex_1 flex a_bom jc_b px-14">
						<view class="flex_1 fz_500" style="padding-top: 60rpx;">
							{{record_res.ju_ben}}
						</view>
						<image src="/static/right-shu.png" style="width: 20rpx;height: 116rpx;" mode="aspectFill">
						</image>
					</view>
				</view>
				<view style="height: 224rpx;"> </view>
				<image src="/static/2025-bom.png" style="height: 224rpx;" class="w_100 po_ab l-0 b--20"
					mode="aspectFill">
				</image>
			</view>
		</view>
		<view style="height: 200rpx;"> </view>
		<view class="w_100 flex_column po_fi b-0 pb-30 pt-15">
			<view @click="useNext()" class="bom_btn fz_32 po_re">
				下一页
			</view>
			<view class="bom_h"></view>
		</view>
	</view>
</template>

<style lang="scss">
	page {
		/* 使用红色祥云背景 */
		background-image: url(/static/beijing.jpg);
		background-size: cover;
		background-repeat: no-repeat;
		background-position: center center;
		background-color: #BF0000;
	}

	.max_page {
		width: 100%;
		min-height: 100vh;
	}

	.bom_btn {
		width: 690rpx;
		height: 96rpx;
		line-height: 96rpx;
		text-align: center;
		color: #fff;
		font-weight: bold;
		font-size: 36rpx;
		letter-spacing: 8rpx;
		border-radius: 48rpx;
		background: linear-gradient(135deg, #D0000F 0%, #C41E1E 25%, #A22823 50%, #8B0000 100%);
		box-shadow: 0 8rpx 24rpx rgba(208, 0, 15, 0.4),
					inset 0 2rpx 0 rgba(255, 255, 255, 0.2),
					inset 0 -2rpx 0 rgba(0, 0, 0, 0.2);
		position: relative;
		overflow: hidden;
		
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

	.boxs_center {
		width: 100%;
		color: #D0000F;
		line-height: 48rpx;
		background: rgba(255, 241, 241, 0.95);
		border-radius: 16rpx;
		border: 2rpx solid rgba(255, 190, 190, 0.5);
		box-shadow: 0 8rpx 24rpx rgba(208, 0, 15, 0.15);
	}
</style>