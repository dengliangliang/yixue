<template>
	<view class="page-bg" @longpress="handleLongPress">
		<!-- 截图区域 -->
		<view id="poster-content" class="poster-area" :style="'min-height:'+windowHeight+'px'">
			<view class="poster-overlay">
				<!-- 二维码区域 (模拟数据) -->
				<view class="qr-section">
					<view class="qr-placeholder">
						<image src="/static/xz.png" mode="aspectFit" class="qr-img"></image>
						<text class="qr-tip">长按识别二维码</text>
					</view>
				</view>
				
				<!-- 文案区域 -->
				<view class="w_100 px-30 mt-60">
					<view class="card-glass text-center">
						<view class="text-base text-gray-700 flex_column jc_c ai_c">
							<text class="mb-8 font-qx">我的2026旺运密码已解锁</text>
							<text class="mb-8 font-qx">点击一下</text>
							<text class="font-qx">解密你的2026吧~</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<!-- 底部操作区域 (不包含在截图中) -->
		<view class="bom_actions flex_column jc_b animate__animated animate__fadeInUp">
			<!-- 底部按钮 -->
			<view class="w_100 px-30 pb-30">
				<view class="flex gap-16">
					<view class="btn-secondary flex-1" @click="$go('/pages/login/setInfo')">
						<text class="btn-secondary-text">重新测算</text>
					</view>
					<view class="btn-primary flex-1" @click="showInstruction">
						<text class="btn-primary-text">分享</text>
					</view>
				</view>
				<view class="bom_h"></view>
			</view>
		</view>

		<!-- 指引弹窗 (图片1效果) -->
		<uni-popup ref="instructionPopup" type="center">
			<view class="instruction-container">
				<view class="ins-content">
					<view class="ins-text-box">
						<text class="ins-text">您可长按保存此图</text>
						<text class="ins-text">片在朋友圈转发，</text>
						<text class="ins-text">或点击右上角“...”</text>
						<text class="ins-text">在朋友圈转发此链</text>
						<text class="ins-text">接。</text>
					</view>
					<view class="ins-btn" @click="$refs.instructionPopup.close()">
						<text class="ins-btn-text">知道了</text>
					</view>
				</view>
			</view>
		</uni-popup>
		
		<!-- 预览/生成中弹窗 -->
		<uni-popup ref="generatingPopup" :is-mask-click="false">
			<view class="generating-box">
				<text class="loading-text">正在捕捉灵感...</text>
			</view>
		</uni-popup>
	</view>
</template>

<script>
import html2canvas from 'html2canvas'

export default {
	data() {
		return {
			record_id: '',
			windowHeight: 0,
			isGenerating: false
		}
	},
	onReady() {
		uni.getSystemInfo({
			success: res => {
				this.windowHeight = res.windowHeight
			}
		});
	},
	onLoad({ record_id }) {
		this.record_id = record_id;
	},
	methods: {
		showInstruction() {
			this.$refs.instructionPopup.open();
		},
		
		async handleLongPress() {
			if (this.isGenerating) return;
			
			// 震动反馈 (如果平台支持)
			uni.vibrateShort();
			
			this.isGenerating = true;
			this.$refs.generatingPopup.open('center');
			
			try {
				// 获取海报区域
				const posterElement = document.getElementById('poster-content');
				if (!posterElement) throw new Error('未找到内容区域');
				
				// 略微延时确保UI渲染
				await new Promise(resolve => setTimeout(resolve, 300));
				
				const canvas = await html2canvas(posterElement, {
					backgroundColor: '#BF0000',
					scale: 2,
					useCORS: true,
					logging: false,
					width: posterElement.offsetWidth,
					height: posterElement.offsetHeight
				});
				
				const imageUrl = canvas.toDataURL('image/png', 0.9);
				
				// H5端下载图片
				const link = document.createElement('a');
				link.download = `解密2026_${Date.now()}.png`;
				link.href = imageUrl;
				link.click();
				
				this.$toast('海报已准备就绪');
			} catch (error) {
				console.error('[share] 长按截图失败:', error);
				this.$toast('生成海报失败');
			} finally {
				this.isGenerating = false;
				this.$refs.generatingPopup.close();
			}
		}
	}
}
</script>

<style lang="scss">
@font-face {
	font-family: 'QianTuXianMo';
	src: url('~@/static/ttf/千图纤墨体.ttf') format('truetype');
	font-weight: normal;
	font-style: normal;
}

page {
	background-color: #BF0000;
	overflow: hidden;
}

.page-bg {
	width: 100%;
	position: relative;
}

.poster-area {
	width: 100%;
	background-image: url(/static/beijing.jpg);
	background-size: cover;
	background-repeat: no-repeat;
	background-position: center center;
}

.poster-overlay {
	width: 100%;
	min-height: 100%;
	background: rgba(0, 0, 0, 0.1);
	display: flex;
	flex-direction: column;
	align-items: center;
	padding-top: 100rpx;
}

.qr-section {
	margin-top: 60rpx;
	.qr-placeholder {
		width: 300rpx;
		height: 300rpx;
		background: #FFFFFF;
		padding: 20rpx;
		border-radius: 12rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		box-shadow: 0 8rpx 20rpx rgba(0,0,0,0.2);
		
		.qr-img {
			width: 200rpx;
			height: 200rpx;
			margin-bottom: 10rpx;
		}
		
		.qr-tip {
			font-size: 20rpx;
			color: #666;
		}
	}
}

.font-qx {
	font-family: 'QianTuXianMo', sans-serif;
}

.card-glass {
	background: rgba(245, 230, 200, 0.9);
	padding: 40rpx 30rpx;
	border-radius: 16rpx;
	border: 2rpx solid #DAA520;
	width: 85%;
	margin: 0 auto;
}

.bom_actions {
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	z-index: 100;
}

/* 按钮样式保持一致 */
.btn-primary {
	background: linear-gradient(135deg, #8B0000 0%, #A22823 50%, #8B0000 100%);
	border: 3rpx solid #DAA520;
	border-radius: 12rpx;
	padding: 24rpx 40rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 4rpx 12rpx rgba(139, 0, 0, 0.4);
}

.btn-primary-text {
	color: #FFD700;
	font-size: 30rpx;
	font-weight: bold;
}

.btn-secondary {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border: 3rpx solid #DAA520;
	border-radius: 12rpx;
	padding: 24rpx 40rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.btn-secondary-text {
	color: #8B0000;
	font-size: 30rpx;
	font-weight: bold;
}

/* 指引弹窗样式 (完全复刻图片1) */
.instruction-container {
	width: 500rpx;
	background: #F5E6C8;
	border-radius: 20rpx;
	overflow: hidden;
	box-shadow: 0 10rpx 30rpx rgba(0,0,0,0.3);
}

.ins-content {
	padding: 60rpx 40rpx 40rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
}

.ins-text-box {
	margin-bottom: 60rpx;
	text-align: center;
}

.ins-text {
	display: block;
	font-size: 32rpx;
	color: #333;
	line-height: 1.8;
	font-weight: 500;
}

.ins-btn {
	width: 100%;
	height: 88rpx;
	background: #8B0000;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 4rpx 10rpx rgba(139, 0, 0, 0.4);
}

.ins-btn-text {
	color: #FFD700;
	font-size: 32rpx;
	font-weight: bold;
}

.generating-box {
	background: rgba(0,0,0,0.7);
	padding: 40rpx 60rpx;
	border-radius: 20rpx;
	
	.loading-text {
		color: #fff;
		font-size: 28rpx;
	}
}
</style>