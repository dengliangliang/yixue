<template>
	<!-- P10: 重要方位组件 -->
	<view class="fangwei-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image src="/static/xiuwen.jpg" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		<view class="section-title">
			<view class="title-bar"></view>
			<text class="fz_b fz_36">2026年重要方位</text>
		</view>

		<!-- 方位说明 -->
		<view class="fangwei-intro fz_28 mb-24">
			您2026年丙午年的重要方位如下：
		</view>

		<!-- 方位卡片 -->
		<view class="fangwei-cards">
			<!-- 事业位 -->
			<view class="fangwei-card">
				<view class="card-icon shiye-icon">
					<image src="/static/icon-career.png" mode="aspectFill"></image>
				</view>
				<view class="card-content">
					<view class="card-title fz_28">事业位</view>
					<view class="card-value fz_32 fz_b">{{ fangWeiData.shiyeWei || '待计算' }}</view>
				</view>
			</view>

			<!-- 财位 -->
			<view class="fangwei-card">
				<view class="card-icon cai-icon">
					<image src="/static/icon-money.png" mode="aspectFill"></image>
				</view>
				<view class="card-content">
					<view class="card-title fz_28">财位</view>
					<view class="card-value fz_32 fz_b">{{ formatCaiWei }}</view>
				</view>
			</view>

			<!-- 贵人位 -->
			<view class="fangwei-card">
				<view class="card-icon guiren-icon">
					<image src="/static/icon-noble.png" mode="aspectFill"></image>
				</view>
				<view class="card-content">
					<view class="card-title fz_28">贵人位</view>
					<view class="card-value fz_32 fz_b">{{ fangWeiData.guirenWei || '待计算' }}</view>
				</view>
			</view>
		</view>

		<!-- 方位详解 -->
		<view class="fangwei-detail mt-24">
			<view class="detail-title fz_28 fz_b mb-16">方位详解</view>
			<view class="detail-item" v-if="fangWeiData.shiyeWei">
				<text class="detail-label">事业位</text>
				<text class="detail-desc">{{ fangWeiData.shiyeDesc || '正官所在方位，利于事业发展' }}</text>
			</view>
			<view class="detail-item" v-if="fangWeiData.zhengcaiWei || fangWeiData.piancaiWei">
				<text class="detail-label">财位</text>
				<text class="detail-desc">
					<text v-if="fangWeiData.zhengcaiWei">正财位{{ fangWeiData.zhengcaiWei }}，利于稳定收入；</text>
					<text v-if="fangWeiData.piancaiWei">偏财位{{ fangWeiData.piancaiWei }}，利于意外之财</text>
				</text>
			</view>
			<view class="detail-item" v-if="fangWeiData.guirenWei">
				<text class="detail-label">贵人位</text>
				<text class="detail-desc">{{ fangWeiData.guirenDesc || '正印所在方位，利于遇贵人相助' }}</text>
			</view>
		</view>

		<!-- 罗盘图示 -->
		<view class="luopan-box mt-24">
			<view class="luopan-title fz_26 c_9 mb-16">方位罗盘参考</view>
			<view class="luopan">
				<view class="luopan-center">中</view>
				<view class="luopan-direction north">北</view>
				<view class="luopan-direction south">南</view>
				<view class="luopan-direction east">东</view>
				<view class="luopan-direction west">西</view>
				<view class="luopan-direction northeast">东北</view>
				<view class="luopan-direction northwest">西北</view>
				<view class="luopan-direction southeast">东南</view>
				<view class="luopan-direction southwest">西南</view>
				<!-- 标记点 -->
				<view v-if="shiyePosition" class="marker shiye-marker" :style="shiyePosition">业</view>
				<view v-if="caiPosition" class="marker cai-marker" :style="caiPosition">财</view>
				<view v-if="guirenPosition" class="marker guiren-marker" :style="guirenPosition">贵</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'FangWei',
	props: {
		// 方位数据
		fangWeiData: {
			type: Object,
			default: () => ({
				shiyeWei: '',      // 事业位
				shiyeDesc: '',     // 事业位说明
				zhengcaiWei: '',   // 正财位
				piancaiWei: '',    // 偏财位
				guirenWei: '',     // 贵人位
				guirenDesc: ''     // 贵人位说明
			})
		}
	},
	computed: {
		formatCaiWei() {
			const { zhengcaiWei, piancaiWei } = this.fangWeiData;
			if (zhengcaiWei && piancaiWei) {
				return `正财${zhengcaiWei}、偏财${piancaiWei}`;
			}
			return zhengcaiWei || piancaiWei || '待计算';
		},
		shiyePosition() {
			return this.getMarkerPosition(this.fangWeiData.shiyeWei);
		},
		caiPosition() {
			return this.getMarkerPosition(this.fangWeiData.zhengcaiWei || this.fangWeiData.piancaiWei);
		},
		guirenPosition() {
			return this.getMarkerPosition(this.fangWeiData.guirenWei);
		}
	},
	methods: {
		getMarkerPosition(fangwei) {
			if (!fangwei) return null;
			// 根据方位返回CSS定位
			const positionMap = {
				'正北': { top: '10%', left: '45%' },
				'正南': { bottom: '10%', left: '45%' },
				'正东': { top: '45%', right: '10%' },
				'正西': { top: '45%', left: '10%' },
				'东北偏北': { top: '20%', right: '30%' },
				'东北偏东': { top: '30%', right: '20%' },
				'西北偏北': { top: '20%', left: '30%' },
				'西北偏西': { top: '30%', left: '20%' },
				'东南偏东': { bottom: '30%', right: '20%' },
				'东南偏南': { bottom: '20%', right: '30%' },
				'西南偏西': { bottom: '30%', left: '20%' },
				'西南偏南': { bottom: '20%', left: '30%' }
			};
			return positionMap[fangwei] || null;
		}
	}
};
</script>

<style lang="scss" scoped>
.fangwei-container {
	width: 100%;
	padding: 0;
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

.fangwei-intro {
	line-height: 48rpx;
	color: #4A3728;
}

.fangwei-cards {
	display: flex;
	flex-wrap: wrap;
	gap: 20rpx;
}

/* 国风方位卡片 */
.fangwei-card {
	flex: 1;
	min-width: 200rpx;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	display: flex;
	align-items: center;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.card-icon {
	width: 80rpx;
	height: 80rpx;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 16rpx;

	image {
		width: 48rpx;
		height: 48rpx;
	}
}

.shiye-icon { background-color: rgba(30, 144, 255, 0.15); }
.cai-icon { background-color: rgba(255, 215, 0, 0.15); }
.guiren-icon { background-color: rgba(208, 0, 15, 0.15); }

.card-title {
	color: #8B4513;
	margin-bottom: 8rpx;
}

.card-value {
	color: #4A3728;
}

/* 国风详情盒子 */
.fangwei-detail {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.detail-item {
	display: flex;
	margin-bottom: 16rpx;

	&:last-child {
		margin-bottom: 0;
	}
}

.detail-label {
	width: 120rpx;
	color: #8B0000;
	font-weight: bold;
	flex-shrink: 0;
}

.detail-desc {
	flex: 1;
	color: #4A3728;
	line-height: 44rpx;
}

/* 国风罗盘盒子 */
.luopan-box {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.luopan {
	width: 400rpx;
	height: 400rpx;
	border-radius: 50%;
	border: 4rpx solid #8B0000;
	margin: 0 auto;
	position: relative;
	background: radial-gradient(circle, #FFFEF9 0%, #F5E6C8 100%);
}

.luopan-center {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 60rpx;
	height: 60rpx;
	background-color: #D0000F;
	border-radius: 50%;
	color: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24rpx;
}

.luopan-direction {
	position: absolute;
	font-size: 22rpx;
	color: #666;

	&.north { top: 10rpx; left: 50%; transform: translateX(-50%); }
	&.south { bottom: 10rpx; left: 50%; transform: translateX(-50%); }
	&.east { right: 10rpx; top: 50%; transform: translateY(-50%); }
	&.west { left: 10rpx; top: 50%; transform: translateY(-50%); }
	&.northeast { top: 50rpx; right: 50rpx; }
	&.northwest { top: 50rpx; left: 50rpx; }
	&.southeast { bottom: 50rpx; right: 50rpx; }
	&.southwest { bottom: 50rpx; left: 50rpx; }
}

.marker {
	position: absolute;
	width: 40rpx;
	height: 40rpx;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20rpx;
	color: #fff;
	font-weight: bold;
}

.shiye-marker { background-color: #1E90FF; }
.cai-marker { background-color: #FFD700; color: #333; }
.guiren-marker { background-color: #D0000F; }

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
