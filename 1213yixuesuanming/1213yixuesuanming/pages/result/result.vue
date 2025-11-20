<template>
	<view class="pt-50 px-30 w_100">
		<image src="/static/top-text.png" style="height: 106rpx;" class="po_ab t-0 l-0 w_100" mode="aspectFill"></image>
		<view class="w_100 my_color flex a_c fz_24 fz_500 po_re zIndex-11" style="height: 84rpx;">
			<image src="/static/jieguo/xingge.png" class="zIndex-10 po_ab l-0 t-0" style="width: 296rpx;height: 84rpx;"
				mode=""></image>
			<view style="width: 8rpx;height: 35rpx;background-color: #D0000F;border-radius: 30rpx;margin-right: 15rpx;">
			</view>
			<view class="flex_1 flex a_bom">
				您的 <text class="fz_b fz_36">性格剧本</text> 已解码，根据推算
			</view>
		</view>

		<view class="flex mb-16 c_0 a_bom fz_24 w_100" style="height: 48rpx;">
			您性格中 <text class="fz_32 px-15" :class="`c_${loadColor(xing_ge_min.wu_xing)}`">{{xing_ge_min.wu_xing}}</text>
			元素数量最少， <text class="fz_32 px-15" :class="`c_${loadColor(xing_ge_max.wu_xing)}`">{{xing_ge_max.wu_xing}}
			</text>元素数量最多，由此推荐
		</view>

		<view class="flex mb-38 a_bom fz_24 w_100 mt-25" style="height: 48rpx;">
			您性格中 <text class="fz_32 fz_b c_huo px-15">优势</text>
		</view>
		<view class="w_100 round-4 mb-24 po_re" :class="`${loadColor(xing_ge_min.wu_xing)}_back`">
			<image :src="`/static/wuxing/${loadColor(xing_ge_min.wu_xing)}-@2x.png`" class="wuxing_img"
				mode="aspectFill"></image>
			<text class="po_ab zIndex-10 fz_24" style="left: 110rpx;top: 46rpx;">最少</text>
			<view class="w_100 px-28 " style="line-height: 48rpx;opacity: 0;">
				{{xing_ge_min.xing_result}}
			</view>
			<view :class="`c_${loadColor(xing_ge_min.wu_xing)}`" class="w_100 po_ab l-0 px-28 py-24 "
				style="top: 96rpx;line-height: 50rpx;">
				{{xing_ge_min.xing_result}}
			</view>
		</view>

		<view class="flex mb-38 a_bom fz_24 w_100" style="height: 48rpx;">
			您性格中 <text style="color: #333333;" class="fz_32 fz_b px-15">劣势</text>
		</view>
		<view class="w_100 round-4 mb-24 po_re" :class="`${loadColor(xing_ge_max.wu_xing)}_back`">
			<image :src="`/static/wuxing/${loadColor(xing_ge_max.wu_xing)}-@2x.png`" class="wuxing_img"
				mode="aspectFill"></image>
			<text class="po_ab zIndex-10 fz_24" style="left: 110rpx;top: 46rpx;">最多</text>
			<view class="w_100 px-28" style="line-height: 48rpx;opacity: 0;">
				{{xing_ge_max.xing_result}}
			</view>
			<view :class="`c_${loadColor(xing_ge_max.wu_xing)}`" class="w_100 po_ab l-0 px-28 py-24"
				style="top: 96rpx;line-height: 50rpx;">
				{{xing_ge_max.xing_result}}
			</view>
		</view>

		<view style="height: 180rpx;"> </view>

		<view class="w_100 back_f flex_column po_fi b-0 l-0 pb-30 pt-15 round-t-10 shadow-10">
			<view @click="$go('/pages/result/miji?record_id='+record_id)" class="bom_btn fz_32 po_re">
				下一页
			</view>
			<view class="bom_h"></view>
		</view>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				record_id: '',
				info: {},
				xing_ge_max: {},
				xing_ge_min: {},
			}
		},
		computed: {

		},
		onLoad({
			record_id
		}) {
			this.record_id = record_id;
			this.loadData();
		},
		methods: {
			async loadData() {
				const res = await this.$api.post('api/si_zhu/getResult', {
					record_id: this.record_id
				});
				if (res.code != 1) return this.$toast(res.msg);
				this.info = res.data;
				this.xing_ge_max = res.data.xing_ge_max;
				this.xing_ge_min = res.data.xing_ge_min;
			},
			loadColor(type) {
				let color_;
				switch (type) {
					case '金':
						color_ = 'jin';
						break;
					case '木':
						color_ = 'mu';
						break;
					case '水':
						color_ = 'shui';
						break;
					case '火':
						color_ = 'huo';
						break;
					case '土':
						color_ = 'tu';
						break;
				}
				return color_
			}
		}
	}
</script>

<style scoped>
	.bom_btn {
		width: 690rpx;
		height: 110rpx;
		line-height: 110rpx;
		text-align: center;
		color: #fff;
		background-image: url("/static/jieguo/xiayiye@2x.png");
		background-repeat: no-repeat;
		background-size: cover;
	}

	page {
		background-image: url("/static/jieguo/jieguo-bj@2x.png");
		background-repeat: no-repeat;
		background-size: cover;
	}
</style>