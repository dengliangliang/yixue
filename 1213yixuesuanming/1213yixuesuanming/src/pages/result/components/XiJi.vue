<template>
	<!-- P7: 喜忌/旺运秘籍组件 -->
	<view class="xiji-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image src="/static/xiuwen.jpg" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		<view class="section-title">
			<view class="title-bar"></view>
			<text class="fz_b fz_36">个人旺运秘籍</text>
		</view>

		<!-- 旺运信息网格 -->
		<view class="wangyun-grid mb-24">
			<view v-for="(item, index) in gridItems" :key="index" class="grid-item">
				<view class="item-title">{{ item.name }}</view>
				<view class="item-content">
					<!-- 方位 -->
					<view v-if="item.key === 'fangwei'" class="fangwei-text c_huo">
						{{ wangYun.fang_wei || '-' }}
					</view>
					<!-- 颜色 -->
					<view v-else-if="item.key === 'color'" class="color-list">
						<view v-for="(color, idx) in wangYun.color" :key="idx" class="color-item">
							<view class="color-dot" :class="`bg_${loadColor(color)}`"></view>
							<text class="fz_24">{{ color }}</text>
						</view>
					</view>
					<!-- 其他文本 -->
					<view v-else class="item-text">
						{{ getItemValue(item.key) }}
					</view>
				</view>
			</view>
		</view>

		<!-- 旺运事业方向 -->
		<view class="career-box mb-24">
			<view class="box-title c_huo">旺运事业方向</view>
			<view class="box-content fz_32 fz_500">{{ xingGeMin.worker || '-' }}</view>
		</view>

		<!-- 身体注意部位 -->
		<view class="health-box">
			<view class="box-title c_huo">身体需注意的部位</view>
			<view class="box-content fz_32 fz_500">{{ healthParts }}</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'XiJi',
	props: {
		// 旺运数据
		wangYun: {
			type: Object,
			default: () => ({
				fang_wei: '',
				color: [],
				num: '',
				shi_pin: '',
				ji_jie: '',
				time: '',
				kou_wei: '',
				zhu_yi: ''
			})
		},
		// 性格最少五行数据
		xingGeMin: {
			type: Object,
			default: () => ({
				worker: ''
			})
		}
	},
	data() {
		return {
			gridItems: [
				{ name: '旺运方位', key: 'fangwei' },
				{ name: '旺运颜色', key: 'color' },
				{ name: '旺运数字', key: 'num' },
				{ name: '旺运饰品', key: 'shipin' },
				{ name: '旺运月份(阳历)', key: 'jijie' },
				{ name: '旺运时间', key: 'time' },
				{ name: '旺运口味', key: 'kouwei' }
			]
		};
	},
	computed: {
		healthParts() {
			return this.wangYun.zhu_yi || '-';
		}
	},
	methods: {
		loadColor(colorName) {
			const colorMap = {
				'金': 'jin',
				'木': 'mu',
				'水': 'shui',
				'火': 'huo',
				'土': 'tu',
				'黄': 'huang',
				'棕': 'zong',
				'白': 'bai',
				'银': 'yin',
				'绿': 'lv',
				'青': 'qing',
				'黑': 'hei',
				'蓝': 'lan',
				'红': 'hong',
				'紫': 'zi',
				'橙': 'cheng'
			};
			return colorMap[colorName] || 'default';
		},
		getItemValue(key) {
			const keyMap = {
				'num': this.wangYun.num,
				'shipin': this.wangYun.shi_pin,
				'jijie': this.wangYun.ji_jie,
				'time': this.wangYun.time,
				'kouwei': this.wangYun.kou_wei
			};
			return keyMap[key] || '-';
		}
	}
};
</script>

<style lang="scss" scoped>
.xiji-container {
	width: 100%;
	padding: 24rpx;
	background: rgba(245, 230, 200, 0.85); /* 增加背景颜色衬托文字 */
	border-radius: 20rpx;
	box-sizing: border-box;
}

/* 国风标题样式 */
.section-title {
	display: flex;
	align-items: center;
	margin-bottom: 24rpx;
	padding: 16rpx 24rpx;
	background: linear-gradient(90deg, rgba(139, 0, 0, 0.1) 0%, transparent 100%);
	border-left: 6rpx solid #8B0000;
	border-radius: 0 8rpx 8rpx 0;

	.title-bar {
		display: none;
	}
}

/* 国风网格 */
.wangyun-grid {
	display: flex;
	flex-wrap: wrap;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	overflow: hidden;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.grid-item {
	width: 33.33%;
	padding: 20rpx 16rpx;
	border-bottom: 1rpx solid rgba(218, 165, 32, 0.3);
	border-right: 1rpx solid rgba(218, 165, 32, 0.3);
	box-sizing: border-box;

	&:nth-child(3n) {
		border-right: none;
	}

	&:nth-last-child(-n+3) {
		border-bottom: none;
	}
}

.item-title {
	font-size: 24rpx;
	color: #8B4513;
	margin-bottom: 8rpx;
	text-align: center;
	font-weight: 500;
}

.item-content {
	min-height: 50rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.fangwei-text {
	font-size: 28rpx;
	font-weight: bold;
	padding: 8rpx 20rpx;
	border: 2rpx solid #8B0000;
	border-radius: 8rpx;
	background: rgba(139, 0, 0, 0.05);
}

.color-list {
	display: flex;
	flex-wrap: wrap;
	gap: 8rpx;
	justify-content: center;
}

.color-item {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.color-dot {
	width: 36rpx;
	height: 36rpx;
	border-radius: 50%;
	margin-bottom: 4rpx;
	border: 2rpx solid rgba(218, 165, 32, 0.5);
}

.item-text {
	font-size: 26rpx;
	color: #4A3728;
	font-weight: 500;
	text-align: center;
}

/* 国风卡片盒子 */
.career-box,
.health-box {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	text-align: center;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.box-title {
	font-size: 28rpx;
	margin-bottom: 16rpx;
}

.box-content {
	line-height: 50rpx;
}

// 五行颜色
.bg_jin { background-color: #FFD700; }
.bg_mu { background-color: #228B22; }
.bg_shui { background-color: #1E90FF; }
.bg_huo { background-color: #D0000F; }
.bg_tu { background-color: #8B4513; }
// 实际颜色
.bg_huang { background-color: #FFD700; }
.bg_zong { background-color: #8B4513; }
.bg_bai { background-color: #FFFFFF; border: 2rpx solid #ccc !important; }
.bg_yin { background-color: #C0C0C0; }
.bg_lv { background-color: #228B22; }
.bg_qing { background-color: #00CED1; }
.bg_hei { background-color: #333333; }
.bg_lan { background-color: #1E90FF; }
.bg_hong { background-color: #D0000F; }
.bg_zi { background-color: #800080; }
.bg_cheng { background-color: #FF8C00; }
.bg_default { background-color: #999; }

/* 祥云装饰样式 */
.xiuwen-decoration {
	width: 100%;
	height: 120rpx;
	overflow: hidden;
	margin-bottom: 20rpx;
	border-radius: 12rpx;
	border: 2rpx solid #DAA520;
}

.xiuwen-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center;
}

.box-content {
	color: #4A3728;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}
</style>
