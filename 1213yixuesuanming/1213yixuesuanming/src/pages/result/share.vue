<template>
	<view class="page-bg" @longpress="handleLongPress">
		<!-- 截图区域 -->
		<view id="poster-content" class="poster-area" :style="'min-height:'+windowHeight+'px'">
			<view class="poster-overlay">
				<!-- 二维码区域 (模拟数据) -->
				<view class="qr-section">
					<view class="qr-placeholder" id="qr-code-area">
						<!-- 隐藏真实的二维码组件，只用于生成图片 -->
						<view style="position: absolute; left: -9999px;">
							<l-qrcode 
								v-if="shareUrl"
								:value="shareUrl" 
								:size="200" 
								@success="onQrCodeSuccess"
							></l-qrcode>
						</view>
						
						<!-- 显示生成后的图片，方便 html2canvas 捕捉 -->
						<image 
							v-if="qrCodeBase64" 
							:src="qrCodeBase64" 
							mode="aspectFit" 
							class="qr-img"
						></image>
						<view v-else class="qr-loading flex ai_c jc_c">
							<text class="qr-loading-text">正在生成...</text>
						</view>
						
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
		
		<!-- 图片预览弹窗 - 用户可长按保存 -->
		<uni-popup ref="previewPopup" type="center" :is-mask-click="true" @maskClick="closePreview">
			<view class="preview-container" @click.stop>
				<view class="preview-tip">
					<text class="tip-text">📱 长按图片保存到相册</text>
				</view>
				<image 
					class="preview-image" 
					:src="previewImageUrl" 
					mode="widthFix"
					:show-menu-by-longpress="true"
				></image>
				<view class="preview-close" @click="closePreview">
					<text class="close-text">✕ 关闭</text>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
import html2canvas from 'html2canvas'
import wxSdk, { isWechat } from '@/common/wechat-jssdk.js'

export default {
	data() {
		return {
			record_id: '',
			windowHeight: 0,
			isGenerating: false,
			previewImageUrl: '', // 预览图片的 base64 URL
			shareUrl: '', // 动态生成的分享链接
			qrCodeBase64: '', // 生成的二维码图片
			// 微信分享配置
			shareConfig: {
				title: '我的2026旺运密码已解锁',
				desc: '点击一下，解密你的2026吧~',
				imgUrl: '' // 将在页面加载时设置为实际URL
			}
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
		
		// 构造分享链接
		this.initShareUrl();
		
		// 初始化微信 JS-SDK
		this.initWxSdk();
	},
	methods: {
		// 构造带 agentCode 的分享链接
		initShareUrl() {
			const app_parmas = uni.getStorageSync('app_parmas') || {};
			const agentCode = app_parmas.agentCode || '';
			
			// 获取当前页面基础路径 (去掉参数部分)
			// 注意：UniApp H5 默认可能是 hash 模式也可能是 history 模式
			let baseUrl = window.location.origin + window.location.pathname;
			
			// 如果是跳转到首页，通常是 /#/pages/index/index
			// 这里我们构造指向首页的链接
			const indexUrl = `${window.location.origin}/#/pages/index/index`;
			
			if (agentCode) {
				this.shareUrl = `${indexUrl}?agentCode=${agentCode}`;
			} else {
				this.shareUrl = indexUrl;
			}
			
			console.log('[share] 动态分享链接:', this.shareUrl);
		},

		// 二维码生成成功回调
		onQrCodeSuccess(path) {
			this.qrCodeBase64 = path;
			console.log('[share] 二维码生成成功');
		},

		// 初始化微信 JS-SDK
		async initWxSdk() {
			try {
				// 初始化 SDK
				const success = await wxSdk.init();
				
				if (success) {
					// ⚠️ 使用CDN完整路径,确保微信可以访问
					const shareImgUrl = 'https://cdn.yixuestatic.linqingkeji.com/src/static/beijing.jpg';
					
					console.log('[share] 🖼️ 分享图片URL:', shareImgUrl);
					
					// 设置分享内容
					wxSdk.setShareInfo({
						title: this.shareConfig.title,
						desc: this.shareConfig.desc,
						link: this.shareUrl || window.location.href,
						imgUrl: shareImgUrl,
						success: () => {
							console.log('[share] ✅ 分享设置成功');
						}
					});
				}
			} catch (error) {
				console.error('[share] ❌ 微信SDK初始化失败:', error);
			}
		},
		
		showInstruction() {
			// 微信环境：直接显示指引弹窗，让用户通过右上角分享
			// 非微信环境：显示指引弹窗，提示用户长按保存图片
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
				
				// 略微延时确保UI渲染 (特别是二维码组件)
				await new Promise(resolve => setTimeout(resolve, 500));
				
				const canvas = await html2canvas(posterElement, {
					backgroundColor: '#BF0000',
					scale: 2,
					useCORS: true,
					logging: false,
					width: posterElement.offsetWidth,
					height: posterElement.offsetHeight
				});
				
				const imageUrl = canvas.toDataURL('image/png', 0.9);
				
				// 存储图片URL并打开预览弹窗
				this.previewImageUrl = imageUrl;
				this.$refs.generatingPopup.close();
				
				// 延时打开预览弹窗，确保生成弹窗已关闭
				await this.$nextTick();
				this.$refs.previewPopup.open('center');
				
			} catch (error) {
				console.error('[share] 长按截图失败:', error);
				this.$toast('生成海报失败');
				this.$refs.generatingPopup.close();
			} finally {
				this.isGenerating = false;
			}
		},
		
		// 关闭预览弹窗
		closePreview() {
			this.$refs.previewPopup.close();
			this.previewImageUrl = ''; // 清理内存
		}
	}
}
</script>

<style lang="scss">
@font-face {
	font-family: 'QianTuXianMo';
	src: url('https://cdn.yixuestatic.linqingkeji.com/src/static/ttf/千图纤墨体.ttf') format('truetype');
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
	background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/beijing.jpg);
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
		
		.qr-loading {
			width: 200rpx;
			height: 200rpx;
			background: #f0f0f0;
			margin-bottom: 10rpx;
			
			.qr-loading-text {
				font-size: 24rpx;
				color: #999;
			}
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

/* 图片预览弹窗样式 */
.preview-container {
	width: 90vw;
	max-height: 85vh;
	background: #fff;
	border-radius: 20rpx;
	overflow: hidden;
	display: flex;
	flex-direction: column;
	align-items: center;
	box-shadow: 0 10rpx 40rpx rgba(0,0,0,0.4);
}

.preview-tip {
	width: 100%;
	padding: 24rpx;
	background: linear-gradient(135deg, #8B0000, #A22823);
	text-align: center;
	
	.tip-text {
		color: #FFD700;
		font-size: 28rpx;
		font-weight: bold;
	}
}

.preview-image {
	width: 100%;
	max-height: 65vh;
	object-fit: contain;
}

.preview-close {
	width: 100%;
	padding: 24rpx;
	background: #f5f5f5;
	text-align: center;
	border-top: 1rpx solid #eee;
	
	.close-text {
		color: #666;
		font-size: 28rpx;
	}
}
</style>