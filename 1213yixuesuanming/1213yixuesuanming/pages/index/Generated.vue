<template>
	<view class="flex_column pt-50">
		<view class="w_100 flex_between back_f fz_26" v-for="i,k in user_info" :key="k"
			:style="k!=3?{borderBottom: 'none'}:{}" style="border: 1rpx solid #BDBDBD;width: 692rpx;color: #333;">
			<view class="top_tr">
				{{i.name}}
			</view>
			<view class="flex_1 text-align-center h_100">
				{{i.text}}
			</view>
		</view>

		<view class="w_100 mt-16 table_box_title flex_between">
			<view class="t_item c_6">
				四柱
			</view>
			<view class="t_item">
				年柱
			</view>
			<view class="t_item">
				月柱
			</view>
			<view class="t_item">
				日柱
			</view>
			<view class="t_item" style="border: none;">
				时柱
			</view>
		</view>
		<view class="w_100 mb-44 table_box_data flex_between mb-44">
			<view v-if="user_info.length" class="td_item flex_column jc_a c_6">
				{{user_info[3].text=='男'?'乾造':'坤造'}}
			</view>
			<view v-else class="td_item flex_column jc_a c_6 opacity-0">坤造 </view>

			<view :style="k==3?{'border':'none'}:{}" v-for="i,k in table_data" :key="k"
				class="td_item flex_column jc_a py-10">
				<view class="w_100 flex_auto px-20">
					<text :class="loadClass(i.icon_top)">{{i.text_top}}</text>
					<image :src="loadImg(i.icon_top)" class="wuxing_icon" mode="aspectFill"></image>
				</view>
				<view class="w_100 flex_auto px-20">
					<text :class="loadClass(i.icon_bom)">{{i.text_bom}}</text>
					<image :src="loadImg(i.icon_bom)" class="wuxing_icon" mode="aspectFill"></image>
				</view>
			</view>
		</view>

		<view class="w_100 ganzhijinian po_re">
			<view style="color: #D0000F;" class="w_100 px-28 mb-10 pt-26 flex a_c fz_b fz_40">
				<view class="left_gun mr-12"></view>
				干支纪年
			</view>
			<view class="px-28 w_100">
				<text class="jinian_box">
					中国自上古以来就一直使用的纪年方法
				</text>
			</view>
			<view class="px-28 pb-15"
				style="line-height: 45rpx;color: #D0000F;background-image: url('/static/ganzhijinian-bj.png');background-size: 100% 100%;">
				干支纪年，是指中国传统纪年历法，自上古以来就一直使用的纪年方法。干支是天干和地支的总称。把干支顺序相配，正好六十为一周期，周而复始，
				循环记录。这就是“干支纪年”。干支纪年以每年农历正月初一进入下一年。<br />
				十天干:甲、乙、丙、丁、戊、己、庚、辛、壬、癸、十二地支:子、丑、寅、卯、辰、巳、午、未、申、酉、戌、亥<br />
				干支相配，组成每个中国人独有的生命密码
			</view>
		</view>

		<view style="height: 180rpx;"> </view>

		<view class="w_100 flex_column back_f zIndex-20 po_fi b-0 pb-30 pt-15">
			<view @click="$go('/pages/result/result?record_id='+record_id)" class="bom_btn fz_32 po_re">
				立即查看
			</view>
			<view class="bom_h"></view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				table_data: [],
				record_id: '',
				user_info: {}
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
				const res = await this.$api.post('api/si_zhu/getSiZhuRes', {
					record_id: this.record_id
				});
				if (res.code != 1) return this.$toast(res.msg);
				const {
					record_res,
					zao
				} = res.data;
				let loc_date = uni.getStorageSync('loc_date');
				console.log('loc_date---', loc_date);
				let time_text = record_res.yang_li_date.split('-');
				this.user_info = [{
					name: '生辰公历',
					text: time_text[0] + '年' + time_text[1] + '月' + time_text[2] + '日' +
						` ${record_res.hour}时${record_res.minute}分`
				}, {
					name: '生辰农历',
					text: `${loc_date.gzYear}年 ${loc_date.gzMonth}月 ${loc_date.gzDay}日 ${this.calculateShiChen(record_res.hour)}`
				}, {
					name: '出生地',
					text: `${record_res.province},${record_res.city}`
				}, {
					name: '性别',
					text: record_res.gender == 0 ? '女' : '男'
				}]
				this.table_data = zao;
			},
			calculateShiChen(hour) {
				if (hour >= 23 || hour <= 1) {
					return "子时";
				} else if (hour >= 1 && hour < 3) {
					return "丑时";
				} else if (hour >= 3 && hour < 5) {
					return "寅时";
				} else if (hour >= 5 && hour < 7) {
					return "卯时";
				} else if (hour >= 7 && hour < 9) {
					return "辰时";
				} else if (hour >= 9 && hour < 11) {
					return "巳时";
				} else if (hour >= 11 && hour < 13) {
					return "午时";
				} else if (hour >= 13 && hour < 15) {
					return "未时";
				} else if (hour >= 15 && hour < 17) {
					return "申时";
				} else if (hour >= 17 && hour < 19) {
					return "酉时";
				} else if (hour >= 19 && hour < 21) {
					return "戌时";
				} else if (hour >= 21 && hour < 23) {
					return "亥时";
				}
			},
			loadClass(type) {
				let class_;
				switch (type) {
					case '金':
						class_ = 'c_jin';
						break;
					case '木':
						class_ = 'c_mu';
						break;
					case '水':
						class_ = 'c_shui';
						break;
					case '火':
						class_ = 'c_huo';
						break;
					case '土':
						class_ = 'c_tu';
						break;
				}
				return class_
			},
			loadImg(type) {
				let img;
				switch (type) {
					case '金':
						img = 'jin';
						break;
					case '木':
						img = 'mu';
						break;
					case '水':
						img = 'shui';
						break;
					case '火':
						img = 'huo';
						break;
					case '土':
						img = 'tu';
						break;
				}
				return `/static/${img}@2x.png`
			}
		}
	}
</script>

<style scoped lang="scss">
	page {
		background-color: #f9f9f9;
	}

	.top_tr {
		width: 182rpx;
		height: 80rpx;
		text-align: center;
		line-height: 80rpx;
		background: #FFFFFF;
		border-right: 1rpx solid #BDBDBD;
	}

	.wuxing_icon {
		width: 48rpx;
		height: 48rpx;
	}

	.table_box_title {
		width: 692rpx;
		height: 90rpx;
		background: #FFF1F1;
		border: 1rpx solid #BDBDBD;
		border-bottom: none;

		.t_item {
			width: 24%;
			height: 100%;
			line-height: 90rpx;
			text-align: center;
			font-size: 26rpx;
			color: #CD2A2A;
			border-right: 1rpx solid #FFBEBE;
		}
	}

	.table_box_data {
		width: 692rpx;
		height: 136rpx;
		background: #FFF1F1;
		border: 1rpx solid #BDBDBD;
		border-top: 1rpx solid #fff;

		.td_item {
			width: 24%;
			height: 100%;
			font-size: 26rpx;
			color: #CD2A2A;
			border-right: 1rpx solid #FFBEBE;
		}
	}

	.bom_btn {
		width: 690rpx;
		height: 110rpx;
		line-height: 110rpx;
		text-align: center;
		color: #fff;
		background-image: url("/static/lijichakan.png");
		background-repeat: no-repeat;
		background-size: cover;
	}

	.jinian_box {
		// width: 440rpx;
		display: inline-block;
		height: 48rpx;
		line-height: 48rpx;
		background: rgba(208, 0, 15, 0.18);
		color: #D0000F;
		font-size: 24rpx;
		padding: 0rpx 16rpx;
		margin-bottom: 24rpx;
	}

	.left_gun {
		width: 8rpx;
		height: 28rpx;
		background: #D0000F;
		border-radius: 6rpx;
	}

	.ganzhijinian {
		width: 690rpx;
		// height: 576rpx;
		background: #FFF1F1;
		border-radius: 16rpx;
	}

	.table {
		margin-top: 34rpx;
	}
</style>