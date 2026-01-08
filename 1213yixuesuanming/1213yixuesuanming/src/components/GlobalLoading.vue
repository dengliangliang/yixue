<template>
	<!-- 全局加载遮罩组件 -->
	<view v-if="visible" class="global-loading-mask">
		<!-- 背景遮罩 -->
		<view class="loading-backdrop"></view>
		
		<!-- 加载内容 -->
		<view class="loading-content">
			<!-- 五行环动画 -->
			<view class="loading-ring">
				<view class="ring-segment segment-1"></view>
				<view class="ring-segment segment-2"></view>
				<view class="ring-segment segment-3"></view>
				<view class="ring-segment segment-4"></view>
				<view class="ring-segment segment-5"></view>
			</view>
			
			<!-- 加载文字 -->
			<text class="loading-text">{{ text }}</text>
		</view>
	</view>
</template>

<script>
export default {
	name: 'GlobalLoading',
	props: {
		// 是否显示
		visible: {
			type: Boolean,
			default: false
		},
		// 加载文字
		text: {
			type: String,
			default: '加载中...'
		}
	}
}
</script>

<style lang="scss" scoped>
.global-loading-mask {
	/* 全屏遮罩 */
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 99999;
	display: flex;
	justify-content: center;
	align-items: center;
}

.loading-backdrop {
	/* 半透明背景 */
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: linear-gradient(180deg, 
		rgba(245, 230, 211, 0.95) 0%, 
		rgba(220, 200, 170, 0.98) 100%
	);
	animation: backdropFadeIn 0.3s ease-out;
}

@keyframes backdropFadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}

.loading-content {
	/* 加载内容容器 */
	position: relative;
	z-index: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 40rpx;
	animation: contentFadeIn 0.4s ease-out;
}

@keyframes contentFadeIn {
	from {
		opacity: 0;
		transform: scale(0.9);
	}
	to {
		opacity: 1;
		transform: scale(1);
	}
}

/* 五行环加载动画 */
.loading-ring {
	width: 120rpx;
	height: 120rpx;
	position: relative;
	animation: ringRotate 2s linear infinite;
}

@keyframes ringRotate {
	from { transform: rotate(0deg); }
	to { transform: rotate(360deg); }
}

.ring-segment {
	position: absolute;
	width: 20rpx;
	height: 20rpx;
	border-radius: 50%;
	/* 五行对应颜色 */
}

.segment-1 {
	/* 金 - 白金色 */
	background: linear-gradient(135deg, #FFD700 0%, #FFF8DC 50%, #DAA520 100%);
	top: 0;
	left: 50%;
	transform: translateX(-50%);
	animation: segmentPulse 1s ease-in-out infinite;
	animation-delay: 0s;
	box-shadow: 0 0 12rpx rgba(255, 215, 0, 0.6);
}

.segment-2 {
	/* 木 - 青绿色 */
	background: linear-gradient(135deg, #228B22 0%, #90EE90 50%, #2E8B57 100%);
	top: 35%;
	right: 0;
	animation: segmentPulse 1s ease-in-out infinite;
	animation-delay: 0.2s;
	box-shadow: 0 0 12rpx rgba(34, 139, 34, 0.6);
}

.segment-3 {
	/* 水 - 深蓝色 */
	background: linear-gradient(135deg, #1E90FF 0%, #87CEEB 50%, #0000CD 100%);
	bottom: 10%;
	right: 15%;
	animation: segmentPulse 1s ease-in-out infinite;
	animation-delay: 0.4s;
	box-shadow: 0 0 12rpx rgba(30, 144, 255, 0.6);
}

.segment-4 {
	/* 火 - 朱红色 */
	background: linear-gradient(135deg, #FF4500 0%, #FF6347 50%, #DC143C 100%);
	bottom: 10%;
	left: 15%;
	animation: segmentPulse 1s ease-in-out infinite;
	animation-delay: 0.6s;
	box-shadow: 0 0 12rpx rgba(255, 69, 0, 0.6);
}

.segment-5 {
	/* 土 - 土黄色 */
	background: linear-gradient(135deg, #DAA520 0%, #F0E68C 50%, #B8860B 100%);
	top: 35%;
	left: 0;
	animation: segmentPulse 1s ease-in-out infinite;
	animation-delay: 0.8s;
	box-shadow: 0 0 12rpx rgba(218, 165, 32, 0.6);
}

@keyframes segmentPulse {
	0%, 100% {
		transform: scale(1);
		opacity: 0.8;
	}
	50% {
		transform: scale(1.3);
		opacity: 1;
	}
}

.loading-text {
	font-size: 28rpx;
	color: #8B4513;
	letter-spacing: 4rpx;
	text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
}
</style>
