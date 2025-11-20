<template>
	<view class="pt-50 px-30 w_100">
		<view class="w_100 my_color flex a_c fz_24 fz_500 po_re zIndex-11 mb-24" style="height: 84rpx;">
			<view style="width: 8rpx;height: 35rpx;background-color: #D0000F;border-radius: 30rpx;margin-right: 15rpx;">
			</view>
			<view class="flex_1 flex a_bom fz_b fz_36">
				个人旺运秘籍
			</view>
		</view>

		<view class="w_100 boxs_1 mb-24">
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
				const res = await this.$api.post('api/si_zhu/getResult', {
					record_id: this.record_id
				});
				if (res.code != 1) return this.$toast(res.msg);
				this.wang_yun = res.data.wang_yun;
				this.xing_ge_max = res.data.xing_ge_max;
				this.xing_ge_min = res.data.xing_ge_min;
				this.wang_yun.color = res.data.wang_yun.color.split(',');
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
		background-image: url("/static/miji/jieguo-bg@2x.png");
		background-repeat: no-repeat;
		background-size: cover;
		background-repeat: no-repeat;
	}

	.boxs_1 {
		height: 640rpx;
		background-image: url("/static/miji/miji-bj@2x.png");
		background-repeat: no-repeat;
		background-size: cover;
		padding: 22rpx 32rpx 46rpx 36rpx;
	}

	.boxs_2 {
		background-image: url("/static/miji/jieguo-content@2x.png");
		background-size: 100% 100%;
		padding: 36rpx 32rpx 44rpx 36rpx;
		width: 692rpx;
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
		width: 344rpx;
		height: 128rpx;
		line-height: 128rpx;
		text-align: center;
		color: #fff;
		background-image: url("/static/miji/jieguo-btn@2x.png");
		background-repeat: no-repeat;
		background-size: cover;
	}
</style>