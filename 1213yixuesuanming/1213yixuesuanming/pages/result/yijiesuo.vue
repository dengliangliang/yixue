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

<style>
	page {
		background-color: #bf0f06;
	}

	.max_page {
		width: 100%;
		background-image: url("@/static/yijiesuo-bj.png");
		background-repeat: no-repeat;
		background-size: 100% 100%;
	}

	.bom_btn {
		width: 690rpx;
		height: 110rpx;
		line-height: 110rpx;
		text-align: center;
		color: #fff;
		background-image: url("/static/jiesuo-btn.png");
		background-repeat: no-repeat;
		background-size: cover;
	}

	.boxs_center {
		width: 100%;
		/* height: 664rpx; */
		color: #D0000F;
		line-height: 48rpx;
		background-color: #FFF1F1;
	}
</style>