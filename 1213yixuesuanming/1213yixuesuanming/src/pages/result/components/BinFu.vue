<template>
	<!-- P6: 禀赋组件 -->
	<view class="binfu-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image src="/static/xiuwen.jpg" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		<view class="section-title">
			<view class="title-bar"></view>
			<text class="fz_b fz_36">您的性格禀赋</text>
		</view>

		<!-- 天生禀赋(二期新增逻辑) -->
		<view v-if="binfuData.hasBinfu" class="binfu-box binfu-special mb-24">
			<view class="binfu-header">
				<image src="/static/icon-star.png" class="binfu-icon" mode="aspectFill"></image>
				<text class="fz_32 fz_b c_huo">天生禀赋</text>
			</view>
			<view class="binfu-content">
				<view class="binfu-wuxing" :class="`bg_${loadColor(binfuData.binfuWuXing)}`">
					{{ binfuData.binfuWuXing }}
				</view>
				<view class="binfu-desc fz_28">{{ binfuData.binfuDesc }}</view>
			</view>
		</view>

		<!-- 优势(最少的五行) -->
		<view class="binfu-box mb-24">
			<view class="binfu-label">
				<text class="fz_28">您性格中</text>
				<text class="fz_32 fz_b c_huo px-15">优势</text>
			</view>
			<view class="wuxing-content-box" :class="`${loadColor(minWuXing)}_back`">
				<view class="wuxing-icon-wrapper">
					<image :src="`/static/wuxing/${loadColor(minWuXing)}-@2x.png`" class="wuxing_img_new" mode="aspectFill"></image>
					<text class="wuxing-tag">最少</text>
				</view>
				<view :class="`c_${loadColor(minWuXing)}`" class="wuxing-desc">
					{{ minResult }}
				</view>
			</view>
		</view>

		<!-- 劣势(最多的五行) -->
		<view class="binfu-box mb-24">
			<view class="binfu-label">
				<text class="fz_28">您性格中</text>
				<text class="fz_32 fz_b px-15" style="color: #333;">劣势</text>
			</view>
			<view class="wuxing-content-box" :class="`${loadColor(maxWuXing)}_back`">
				<view class="wuxing-icon-wrapper">
					<image :src="`/static/wuxing/${loadColor(maxWuXing)}-@2x.png`" class="wuxing_img_new" mode="aspectFill"></image>
					<text class="wuxing-tag">最多</text>
				</view>
				<view :class="`c_${loadColor(maxWuXing)}`" class="wuxing-desc">
					{{ maxResult }}
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'BinFu',
	props: {
		// 最少的五行
		minWuXing: {
			type: String,
			default: ''
		},
		// 最少五行的结果描述
		minResult: {
			type: String,
			default: ''
		},
		// 最多的五行
		maxWuXing: {
			type: String,
			default: ''
		},
		// 最多五行的结果描述
		maxResult: {
			type: String,
			default: ''
		},
		// 禀赋数据(二期新增)
		binfuData: {
			type: Object,
			default: () => ({
				hasBinfu: false,
				binfuWuXing: '',
				binfuDesc: ''
			})
		}
	},
	methods: {
		loadColor(wuXing) {
			const colorMap = {
				'金': 'jin',
				'木': 'mu',
				'水': 'shui',
				'火': 'huo',
				'土': 'tu'
			};
			return colorMap[wuXing] || 'jin';
		}
	}
};
</script>

<style lang="scss" scoped>
.binfu-container {
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
.binfu-box {
	width: 100%;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.binfu-special {
	background: linear-gradient(135deg, #F5E6C8 0%, #EDD9B5 50%, #E5CCA0 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 
		0 4rpx 12rpx rgba(139, 105, 20, 0.2),
		inset 0 2rpx 4rpx rgba(255, 255, 255, 0.5);
}

.binfu-header {
	display: flex;
	align-items: center;
	margin-bottom: 16rpx;

	.binfu-icon {
		width: 40rpx;
		height: 40rpx;
		margin-right: 12rpx;
	}
}

.binfu-content {
	display: flex;
	align-items: flex-start;
}

.binfu-wuxing {
	width: 80rpx;
	height: 80rpx;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-size: 32rpx;
	font-weight: bold;
	margin-right: 20rpx;
	flex-shrink: 0;
}

.binfu-desc {
	flex: 1;
	line-height: 48rpx;
	color: #4A3728;
	font-weight: 500;
}

.binfu-label {
	display: flex;
	align-items: center;
	margin-bottom: 16rpx;
	height: 48rpx;
	color: #4A3728;
}

/* 新布局：五行内容盒子 */
.wuxing-content-box {
	display: flex;
	align-items: flex-start;
	padding: 24rpx;
	border-radius: 8rpx;
}

.wuxing-icon-wrapper {
	display: flex;
	flex-direction: column;
	align-items: center;
	margin-right: 20rpx;
	flex-shrink: 0;
}

.wuxing_img_new {
	width: 80rpx;
	height: 80rpx;
	margin-bottom: 8rpx;
}

.wuxing-tag {
	font-size: 24rpx;
	color: #8B4513;
	font-weight: 500;
}

.wuxing-desc {
	flex: 1;
	line-height: 50rpx;
	font-size: 28rpx;
}

/* 旧样式保留兼容 */
.wuxing_img {
	width: 96rpx;
	height: 96rpx;
	position: absolute;
	left: 20rpx;
	top: 20rpx;
}

// 五行背景色
.jin_back { background-color: rgba(184, 134, 11, 0.1); }
.mu_back { background-color: rgba(34, 139, 34, 0.1); }
.shui_back { background-color: rgba(30, 144, 255, 0.1); }
.huo_back { background-color: rgba(208, 0, 15, 0.1); }
.tu_back { background-color: rgba(139, 69, 19, 0.1); }

// 五行圆形背景
.bg_jin { background-color: #B8860B; }
.bg_mu { background-color: #228B22; }
.bg_shui { background-color: #1E90FF; }
.bg_huo { background-color: #D0000F; }
.bg_tu { background-color: #8B4513; }

// 五行文字颜色
.c_jin { color: #B8860B; }
.c_mu { color: #228B22; }
.c_shui { color: #1E90FF; }
.c_huo { color: #D0000F; }
.c_tu { color: #8B4513; }

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
