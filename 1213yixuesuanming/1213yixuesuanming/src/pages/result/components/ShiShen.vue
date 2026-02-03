<template>
	<!-- P8: 十神组件 -->
	<view class="shishen-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image src="/static/xiuwen.jpg" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		<view class="section-title">
			<view class="title-bar"></view>
			<text class="fz_b fz_36">2026年流年十神</text>
		</view>

		<!-- 流年十神 -->
		<view class="shishen-box mb-24">
			<view class="shishen-header">
				<view class="shishen-badge" :class="shiShenClass">{{ liuNianShiShen }}</view>
				<text class="fz_30 fz_b">2026丙午年是您的{{ liuNianShiShen }}年</text>
			</view>
			<view class="shishen-desc fz_28">{{ liuNianDesc }}</view>
		</view>

		<!-- 十神组合(二期新增) -->
		<view v-if="hasZuHe" class="zuhe-box">
			<view class="zuhe-header">
				<image src="/static/icon-combine.png" class="zuhe-icon" mode="aspectFill"></image>
				<text class="fz_30 fz_b c_huo">流年与原局组合</text>
			</view>
			<view class="zuhe-summary fz_26">
				<text class="c_9">根据您八字原局和丙午流年组合，则变化为：</text>
			</view>
			<view class="zuhe-content">
				<view class="zuhe-name">{{ zuHeName }}</view>
				<view class="zuhe-desc fz_28">{{ zuHeDesc }}</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'ShiShen',
	props: {
		// 流年十神名称
		liuNianShiShen: {
			type: String,
			default: ''
		},
		// 流年十神描述
		liuNianDesc: {
			type: String,
			default: ''
		},
		// 流年简短描述(用于组合说明)
		liuNianShortDesc: {
			type: String,
			default: ''
		},
		// 是否有十神组合
		hasZuHe: {
			type: Boolean,
			default: false
		},
		// 组合名称
		zuHeName: {
			type: String,
			default: ''
		},
		// 组合描述
		zuHeDesc: {
			type: String,
			default: ''
		}
	},
	computed: {
		shiShenClass() {
			// 正神用红色样式,偏神用灰色样式
			const zhengShen = ['正官', '正印', '正财', '比肩', '食神'];
			return zhengShen.includes(this.liuNianShiShen) ? 'badge-zheng' : 'badge-pian';
		}
	}
};
</script>

<style lang="scss" scoped>
.shishen-container {
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

/* 国风卡片盒子 */
.shishen-box {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.shishen-header {
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.shishen-badge {
	width: 80rpx;
	height: 80rpx;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-size: 28rpx;
	font-weight: bold;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
	margin-right: 20rpx;
}

.badge-zheng {
	background: linear-gradient(135deg, #D0000F 0%, #FF4444 100%);
}

.badge-pian {
	background: linear-gradient(135deg, #666 0%, #999 100%);
}

.shishen-desc {
	line-height: 48rpx;
	color: #4A3728;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

/* 国风组合盒子 */
.zuhe-box {
	background: linear-gradient(135deg, #F5E6C8 0%, #EDD9B5 50%, #E5CCA0 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	margin-top: 24rpx;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.zuhe-header {
	display: flex;
	align-items: center;
	margin-bottom: 16rpx;

	.zuhe-icon {
		width: 40rpx;
		height: 40rpx;
		margin-right: 12rpx;
	}
}

.zuhe-content {
	margin-bottom: 20rpx;
}

.zuhe-name {
	font-size: 32rpx;
	font-weight: bold;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
	color: #D0000F;
	margin-bottom: 12rpx;
}

.zuhe-desc {
	line-height: 48rpx;
	color: #4A3728;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

.zuhe-summary {
	line-height: 44rpx;
	padding: 16rpx;
	background-color: rgba(255, 254, 249, 0.6);
	border-radius: 8rpx;
	margin-bottom: 16rpx;
	border: 1rpx solid rgba(218, 165, 32, 0.3);
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

.zuhe-result {
	line-height: 48rpx;
	color: #8B0000;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

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
</style>
