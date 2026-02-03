<template>
	<view class="mask" v-if="isShowDateMask" @click.stop="isShowDateMask = false">
		<view class="calendar">
			<view class="content" @click.stop>
				<view class="w_100 flex_between px-38 pt-15 pb-20">
					<view @click="isShowDateMask=false" class="c_9 fz_30">
						取消
					</view>
					<view class="act_type flex_between">
						<view @click="switchBtn('solar')" :class="isActive?'type_act':'type_acts'">
							公历
						</view>
						<view @click="switchBtn('lunar')" :class="isActive?'type_acts':'type_act'">
							农历
						</view>
					</view>
					<view style="color:#D0000F ;" @click="confirmFun" class="fz_30">
						确定
					</view>
				</view>
				<picker-view class="px-24" :indicator-style="indicatorStyle" :value="selectValue" @change="bindChange">
					<picker-view-column>
						<view class="item" style="white-space:nowrap" v-for="(item,index) in years" :key="index">
							{{item}}年
						</view>
					</picker-view-column>
					<picker-view-column>
						<view class="item" v-for="(item,index) in months" :key="index">{{item}}</view>
					</picker-view-column>
					<picker-view-column>
						<view class="item" v-for="(item,index) in days" :key="index">{{item}}</view>
					</picker-view-column>
					<picker-view-column>
						<view class="item" v-for="(item, index) in hours" :key="index">{{ item }}时
						</view>
					</picker-view-column>
					<picker-view-column>
						<view class="item" v-for="(item, index) in minutes" :key="index">{{ item }}分
						</view>
					</picker-view-column>
				</picker-view>
			</view>
		</view>

	</view>
</template>

<script>
	import conversion from "./calendar.js"
	export default {
		name: "calendar",
		data() {
			return {
				isShowDateMask: false, //是否显示模板
				oldYear: 1900, //从哪一年开始 最小是1900年
				years: [], //年
				months: [], //月
				days: [], //日
				minutes: [],
				hours: [],
				isActive: true, //true公历
				selectValue: [], //默认 2000-01-01
				indicatorStyle: `height: ${Math.round(uni.getSystemInfoSync().screenWidth/(750/100))}px;`,
				data: {}, //数据
				type: "solar", //lunar农历，solar公历
				isSwitch: true,
				showData: "", //显示数据
			};
		},
		props: {},
		components: {},
		created() {
			this.getTime();
		},
		mounted() {
			this.selectValue = [100, 0, 0]
			let val = this.selectValue
			let year = val[0] + this.oldYear
			let month = val[1] + 1
			let day = val[2] + 1
			if (this.isActive) {
				this.setDateGL(year, month, day, '00', '00')
			} else {
				this.setDateYL(year, month, day, '00', '00')
			}
		},
		methods: {
			getTime() { //初始化时间
				let date = new Date()
				let year = date.getFullYear()
				let month = date.getMonth() + 1
				let day = date.getDate()

				this.years = []
				this.months = []
				this.days = []


				if (this.isActive) { //公历
					for (let i = this.oldYear; i <= date.getFullYear(); i++) { // 年
						this.years.push(i);
					}
					for (let i = 1; i <= 12; i++) { //月
						this.months.push(i + '月');
					}
					for (let i = 1; i <= conversion.solarDays(year, month); i++) { //日
						this.days.push(i + '日');
					}

				} else { //阴历
					for (let i = this.oldYear; i <= date.getFullYear(); i++) { // 年
						this.years.push(conversion.toChinaYear(i));
					}

					let leap_month = conversion.leapMonth(year);
					//返回农历 闰月没有就返回0
					for (let i = 1; i <= 12; i++) {
						this.months.push(conversion.toChinaMonth(i));
						if (i == leap_month) {
							this.months.push("闰" + conversion.toChinaMonth(i));
						}
					}

					// 农历返回天数
					// **leapMonth 返回闰月 conversion.leapMonth(year)
					// **monthDays 返回非闰月的天数
					// **leapDays 返回闰月的天数
					if (conversion.leapMonth(year) != 0 && (conversion.leapMonth(year) == month || (month - 1) ==
							conversion.leapMonth(year))) {
						for (let i = 1; i <= conversion.leapDays(year, conversion.leapMonth(year)); i++) {
							this.days.push(conversion.toChinaDay(i));
						}
					} else {
						let lunarMonth = '';
						if (conversion.leapMonth(year)) {
							lunarMonth = month < conversion.leapMonth(year) ? month : (month - 1);
						} else {
							lunarMonth = month;
						}
						for (let i = 1; i <= conversion.monthDays(year, lunarMonth); i++) {
							this.days.push(conversion.toChinaDay(i));
						}
					}
				}

				let minutes = [];
				let hours = [];
				let values = [0, 0];
				let minute = 0;
				for (let i = 0; i < 24; i++) {
					// if (hour == i) {
					// 	values[0] = parseInt(i);
					// }
					hours.push(this.formatData(i));
				}

				let step = this.step > 1 ? parseInt(this.step) : 1;
				for (let i = 0; i < 60; i++) {
					if (minute == i && minute <= 60 - step) {
						values[4] = Math.ceil(minute / step);
					} else if (minute == i && minute > 60 - step) {
						values[4] = Math.floor(minute / step);
					}

					if (i % step == 0) {
						minutes.push(this.formatData(i));
					}
				}

				minute = minutes[values[4]]; // 分钟默认值要匹配到跟步长相关的分钟值

				this.hours = hours;
				this.minutes = minutes;

				this.$emit('chushihua', true);
			},
			formatData(value) { // 日期时间的初始化
				return value < 10 ? '0' + value : value + '';
			},
			bindChange: function(e) {
				let val = e.detail.value;
				console.log('bindChange--', val);
				let year = val[0] + this.oldYear
				let month, day, daysCount;
				let hour = val[3] || '00';
				let minute = val[4] || '00';
				let isLeapMonth = false;

				if (this.isActive) {
					// 公历处理
					month = val[1] + 1;
					// 动态更新公历天数
					daysCount = conversion.solarDays(year, month);
					if (this.days.length != daysCount) {
						let daysList = [];
						for (let i = 1; i <= daysCount; i++) {
							daysList.push(i + '日');
						}
						this.days = daysList;
					}
					// 修正天数索引防止越界（如31日切到2月）
					if (val[2] >= daysCount) val[2] = daysCount - 1;
					day = val[2] + 1;

					this.selectValue = val;
					this.setDateGL(year, month, day, hour, minute);
				} else {
					// 农历处理
					// 1. 根据年份更新月份列表（处理闰月）
					let leap = conversion.leapMonth(year);
					let newMonths = [];
					for (let i = 1; i <= 12; i++) {
						newMonths.push(conversion.toChinaMonth(i));
						if (i == leap) newMonths.push("闰" + conversion.toChinaMonth(i));
					}
					
					// 只有当月份列表长度变化或内容不一致时才更新，避免闪烁
					if (JSON.stringify(this.months) !== JSON.stringify(newMonths)) {
						this.months = newMonths;
						if (val[1] >= newMonths.length) val[1] = newMonths.length - 1;
					}

					// 2. 解析当前选中的实际月份和是否闰月
					let monthIndex = val[1];
					if (leap > 0 && monthIndex == leap) {
						month = leap;
						isLeapMonth = true;
					} else if (leap > 0 && monthIndex > leap) {
						month = monthIndex;
					} else {
						month = monthIndex + 1;
					}

					// 3. 动态更新农历天数
					if (isLeapMonth) {
						daysCount = conversion.leapDays(year);
					} else {
						daysCount = conversion.monthDays(year, month);
					}

					if (this.days.length != daysCount) {
						let daysList = [];
						for (let i = 1; i <= daysCount; i++) {
							daysList.push(conversion.toChinaDay(i));
						}
						this.days = daysList;
					}
					
					// 修正天数索引
					if (val[2] >= daysCount) val[2] = daysCount - 1;
					day = val[2] + 1;

					this.selectValue = val;
					this.setDateYL(year, month, day, hour, minute, isLeapMonth);
				}

			},
			setDateGL(year, month, day, hour, minute) { //设置公历数据
				let dt = conversion.solar2lunar(year, month, day)
				let data = {
					hour: hour.toString().padStart(2, '0'),
					minute: minute.toString().padStart(2, '0'),
					year: dt.cYear,
					month: dt.cMonth,
					day: dt.cDay,
					dateGL: dt.date,
					dateYL: `${dt.gzYear}年${dt.IMonthCn}${dt.IDayCn}`,
					Animal: dt.Animal,
					astro: dt.astro,
					ncWeek: dt.ncWeek,
					gzYear: dt.gzYear,
					gzMonth: dt.gzMonth,
					gzDay: dt.gzDay,
					IMonthCn: dt.IMonthCn,
					IDayCn: dt.IDayCn,
					festival: dt.festival,
					lunarDate: dt.lunarDate,
					type: '阳历'
				}
				this.data = data;
				console.log('setDateGL--', data);
			},
			setDateYL(year, month, day, hour, minute, isLeapMonth) { //设置阴历数据
				let dt = conversion.lunar2solar(year, month, day, isLeapMonth)
				let data = {
					hour: hour.toString().padStart(2, '0'),
					minute: minute.toString().padStart(2, '0'),
					year: dt.cYear,
					month: dt.cMonth,
					day: dt.cDay,
					dateGL: dt.date,
					dateYL: `${dt.gzYear}年${dt.IMonthCn}${dt.IDayCn}`,
					Animal: dt.Animal,
					astro: dt.astro,
					ncWeek: dt.ncWeek,
					gzYear: dt.gzYear,
					gzMonth: dt.gzMonth,
					gzDay: dt.gzDay,
					IMonthCn: dt.IMonthCn,
					IDayCn: dt.IDayCn,
					festival: dt.festival,
					lunarDate: dt.lunarDate,
					type: '阴历'
				}
				this.data = data;
			},
			confirmFun() { // 确定
				let data = this.data
				console.log('data---', data);
				this.$emit("confirm", data);
				this.isShowDateMask = false
			},

			switchBtn(type) { //切换
				this.isActive = type == 'solar' ? true : false;
				this.type = type;
				let val = this.selectValue
				let year = val[0] + this.oldYear
				let month = val[1] + 1
				let day = val[2] + 1
				if (this.isActive) {
					this.setDateGL(year, month, day, '00', '00')
				} else {
					this.setDateYL(year, month, day, '00', '00')
				}
				this.getTime();
			}
		}
	}
</script>

<style lang="scss">
	.type_acts {
		width: 50%;
		height: 68rpx;
		line-height: 68rpx;
		text-align: center;
		background: #FFFFFF;
		color: #D0000F;
	}

	.type_act {
		width: 50%;
		height: 68rpx;
		line-height: 68rpx;
		text-align: center;
		background: #D0000F;
		color: #fff;
	}

	.act_type {
		font-size: 30rpx;
		width: 360rpx;
		height: 72rpx;
		background: #D0000F;
		border-radius: 8rpx;
		border: 1rpx solid #D0000F;
	}

	.mask {
		position: fixed;
		left: 0;
		right: 0;
		top: 0;
		bottom: 0;
		width: 750rpx;
		box-sizing: border-box;
		background: rgba(0, 0, 0, 0.3);
		z-index: 9999;
	}

	.u-mask-zoom {
		transform: scale(1, 1);
	}

	.calendar {
		display: flex;
		align-items: flex-end;
		justify-content: center;
		height: 100%;

		.content {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			width: 100%;
			background-color: #fff;
			border-top-left-radius: 24rpx;
			border-top-right-radius: 24rpx;

			.title {
				display: flex;
				flex-direction: row;
				align-items: center;
				height: 86rpx;
				border-bottom: 2rpx solid #E6E6E6;
				width: 100%;
				justify-content: space-between;
				background-color: #FFFFFF;
				border-top-left-radius: 24rpx;
				border-top-right-radius: 24rpx;
				/* #ifdef MP-WEIXIN */
				margin-top: 0;

				/* #endif */
				.left {
					color: #666666;
					font-size: 30rpx;
					padding-left: 30rpx;
				}

				.sure {
					color: #EB344A;
					font-size: 30rpx;
					padding-right: 30rpx;
				}
			}

			::v-deep picker-view {
				width: 100%;
				height: 480rpx;
				margin-top: 20rpx;

				.item {
					display: flex;
					align-items: center;
					justify-content: center;
					color: #333333;
					font-size: 32rpx;
					font-weight: 500;
				}

				.uni-picker-view-wrapper {
					::v-deep uni-picker-view-column {
						display: flex;
						align-items: center;
						justify-content: center;
						color: #333333;
						font-size: 32rpx;
						font-weight: 500;

						.uni-picker-view-group {
							.uni-picker-view-content {
								text-align: center;
								line-height: 110rpx;

								.item {
									display: flex;
									align-items: center;
									justify-content: center;
									color: #333333;
									font-size: 32rpx;
									font-weight: 500;
								}
							}
						}
					}
				}

				.uni-picker-view-indicator {
					// text-align: center;
					// line-height: 110rpx;
				}
			}

			.bottom {
				height: 110rpx;
				width: calc(100% - 31rpx);
				display: flex;
				align-items: center;
				justify-content: flex-end;
				padding-right: 31rpx;
				background-color: #FFFFFF;

				.switch {
					background-color: #E6E6E6;
					width: 180rpx;
					height: 70rpx;
					display: flex;
					flex-direction: row;
					border-radius: 35rpx;
					justify-content: space-around;
					align-items: center;
					position: relative;

					.left {
						position: absolute;
						z-index: 1;
						left: 25%;
						font-size: 28rpx;
						transform: translateX(-50%);
					}

					.right {
						color: #333333;
						font-size: 28rpx;
						position: absolute;
						right: 0;
						transform: translateX(-25%);
						color: #333333;
						z-index: 1;
					}

					.bg {
						background-color: #EB344A;
						color: #333333;
						height: 70rpx;
						border-radius: 40rpx;
						width: 90rpx;
						position: absolute;
						// right: 0;
						top: 0;
						z-index: 0;

					}

					.active {
						color: #FFFFFF;
					}

					.lunar {
						left: 90rpx;
						animation: switchsolar 500ms;
					}

					@keyframes switchsolar {
						0% {
							left: 0;
						}

						100% {
							left: 90rpx;
						}
					}

					.solar {
						left: 0;
						animation: switchlunar 500ms;
					}

					@keyframes switchlunar {
						0% {
							left: 90rpx;
						}

						100% {
							left: 0;
						}
					}
				}
			}
		}
	}
</style>