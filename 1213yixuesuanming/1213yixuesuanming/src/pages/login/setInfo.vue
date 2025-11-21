<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<view class="box_100 ov_y flex_column">
			<view style="height: 428rpx;"> </view>
			<view class="from_box">
				<!-- $refs.calendar.isShowDateMask -->
				<view @click="is_show?$refs.calendar.isShowDateMask =true:''"
					class="input_box mb-16 flex a_c jc_b fz_30">
					<text>生日:</text>
					<view class="flex_1 flex_auto">
						<text class="c_6">{{nian}}</text>
						年
						<text class="c_6">{{yue}}</text>
						月
						<text class="c_6">{{ri}}</text>
						日
						<text class="c_6">{{shi}}</text>
						时
						<text class="c_6">{{fen}}</text>
						分
					</view>
					<image src="/static/right.png" class="left_icon" mode="aspectFill"></image>
				</view>

				<view @click="isCityShow=true" class="input_box mb-16 flex a_c fz_30">
					<text>出生地:</text>

					<view class="flex_1 flex_auto">
						<text class="c_6">{{province}}</text>
						省
						<text class="c_6">{{city}}</text>
						市
					</view>

					<image src="/static/right.png" class="left_icon" mode="aspectFill"></image>
				</view>

				<view class="input_box flex a_c fz_30">
					<text>性别:</text>

					<view class="flex_1 pl-30 flex a_c">
						<view @click="xb_che=i" v-for="i,k in xb_list" :key="k" class="flex a_c mr-46">
							<image :src="`/static/${xb_che==i?'che':'ches'}.png`" class="xb_che_box" mode="aspectFill">
							</image>
							{{i}}
						</view>
					</view>

					<image src="/static/right.png" class="left_icon" mode="aspectFill"></image>
				</view>

				<view @click="useSubmit()" class="submit_btn">
					立即获取
				</view>

			</view>

			<view class="w_100 flex_center fz_24 mt-10" style="color: #D67F48;">
				* 尊重传统文化，仅供娱乐，相信科学
			</view>

		</view>
		<myPicker ref="calendar" @chushihua="(e)=>is_show=e" @confirm="confirm" />
		<!-- <u-picker round="10" :show="isCityShow"></u-picker> -->
		<u-picker keyName="name" confirmColor="#A22823" round="10" :show="isCityShow" title="选择地址" ref="uPicker"
			:columns="columns" @confirm="city_confirm" @cancel="isCityShow=false" @change="changeHandler"
			closeOnClickOverlay @close="isCityShow=false"></u-picker>
	</view>
</template>

<script>
	import myPicker from '@/components/my-picker.vue';
	export default {
		components: {
			myPicker,
		},
		data() {
			return {
				is_show: false,
				isShowCalendar: false,
				isCityShow: false,
				windowHeight: '',
				nian: '',
				yue: '',
				ri: '',
				shi: '',
				fen: '',
				xb_list: ['男', '女'],
				xb_che: '男',
				province: '',
				city: '',
				province_list: [],
				city_list: [],
				columns: [],
				city_id: ''
			}
		},
		onReady() {
			this.loadData()
		},
		onLoad(op) {
			if (op.sign) uni.setStorageSync('app_parmas', op)
		},
		methods: {
			async loadData() {
				const {
					windowHeight
				} = await uni.getSystemInfo();
				this.windowHeight = windowHeight;
				const res = await this.$api.post('api/user/getProvinceList');
				// if (res.code != 1) return this.$toast(res.msg);
				this.province_list = res.data;
				// 添加空值检查，防止数据为空时报错
				if (res.data && res.data.length > 0) {
					this.loadCity(res.data[0].id);
				}
			},
			async loadCity(id) {
				const {
					data,
					code,
					msg
				} = await this.$api.post('api/user/getCityList', {
					id
				});
				// if (code != 1) return this.$toast(msg);
				this.columns = [this.province_list, data];
			},
			changeHandler(e) {
				if (e.value[0].id) this.loadCity(e.value[0].id);
			},
			city_confirm(e) {
				console.log('选择的地址是---', e);
				// this.location = e
				e = e.value;
				this.province = e[0].name;
				this.city = e[1].name;
				this.city_id = e[1].id;
				this.isCityShow = false;
			},
			/** 
			 * @param {Object} e
			 */
			confirm(e) {
				console.log(e);
				this.loc_date = e;
				this.nian = e.year;
				this.yue = e.month;
				this.ri = e.day;
				this.shi = e.hour;
				this.fen = e.minute;
			},
			async useSubmit() {
				// 参数验证
				if (!this.city_id) {
					return this.$toast('请选择出生地');
				}
				if (!this.nian || !this.yue || !this.ri) {
					return this.$toast('请选择出生日期');
				}
				
				let app_parmas = uni.getStorageSync('app_parmas')
				// 检查app_parmas是否存在必需参数
				if (!app_parmas || !app_parmas.customerNo) {
					return this.$toast('缺少必需参数，请从正确入口进入');
				}
				
				const {
					data,
					code,
					msg
				} = await this.$api.post('api/user/addRecord', {
					...app_parmas,
					hour: Number(this.shi),
					minute: Number(this.fen),
					gender: this.xb_che == '男' ? 1 : 0,
					area_id: this.city_id,
					date: `${this.nian}-${this.yue}-${this.ri}`,
				})
				if (code != 1) return this.$toast(msg);
				uni.setStorageSync('loc_date', this.loc_date);
				uni.navigateTo({
					url: "/pages/index/gossip?record_id=" + data.record_id
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #bf0f06;
	}

	.submit_btn {
		width: 570rpx;
		height: 90rpx;
		text-align: center;
		line-height: 90rpx;
		margin-top: 50rpx;
		font-size: 30rpx;
		font-weight: bold;
		color: #A22823;
	}

	.left_icon {
		width: 12rpx;
		height: 22rpx;
	}

	.xb_che_box {
		width: 32rpx;
		height: 32rpx;
		margin-right: 8rpx;
	}

	.from_box {
		background-image: url("/static/from_bj.png");
		background-repeat: no-repeat;
		background-size: cover;
		width: 670rpx;
		height: 651rpx;
		padding: 109rpx 36rpx 66rpx 40rpx;

		.input_box {
			width: 598rpx;
			height: 102rpx;
			line-height: 102rpx;
			padding: 0 39rpx 0 40rpx;
			// box-shadow: inset 0rpx 6rpx 12rpx 2rpx #FEC36B;
			border-radius: 100rpx;
			// border: 2rpx solid #A22823;
		}
	}

	.content {
		width: 100%;
		// height: 100%;
		background-image: url(/static/haoxiaoxi.png);
		overflow: hidden;
		background-repeat: no-repeat;
		background-size: cover;
		background-position: center;
		background-repeat: no-repeat;
	}
</style>