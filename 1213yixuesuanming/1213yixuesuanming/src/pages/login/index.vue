<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<view class="" style="position: fixed;width: 100%;bottom: 250rpx;">
			<view class="richtext">
				<scroll-view scroll-y="true">
					<view style="padding: 20rpx 28rpx;color: #AC0202;line-height: 54rpx;">
						2025年大家过的如何， <br />
						2026年是不是有更多期待， <br />
						巳午交替之际， <br />
						中信保诚人寿推出《解密2026》， <br />
						带您了解每个人生日里隐藏的人生密码解锁2026年剧本，抢占人生先机。
						<!-- <rich-text :nodes="title"></rich-text> -->
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="botton">
			<button class="btn" @click="$go('/pages/login/login')">
				开始测评
			</button>
			<button class="btn">
				客户清单
			</button>
			<button class="btns">
				一键转发
			</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: 'Hello',
				windowHeight: ''
			}
		},
		onReady() {
			this.getsy()
		},
		methods: {
			getsy() {
				uni.getSystemInfo({
					success: res => {
						console.log(res)
						this.windowHeight = res.windowHeight
					}
				});
			}
		}
	}
</script>

<style lang="scss">
	.content {
		width: 100%;
		// height: 100%;
		background-image: url(/static/jiesuobg.png);
		overflow: hidden;
				background-repeat: no-repeat;
		background-size: cover;
		background-position: center;
		background-repeat: no-repeat;

		image {
			width: 400rpx;
			height: 102rpx;
		}

		.bottom {
			width: 100%;
			position: fixed;
			bottom: 510rpx;
			text-align: center;
		}

		.richtext {
			margin: 0 31rpx;
			height: 424rpx;
			background: linear-gradient(180deg, #FFF0D7 0%, #FEEAC7 100%);
			border-radius: 40rpx;
			border: 4rpx solid #E71C18;
		}

		.btn {
			width: 226rpx;
			height: 100rpx;
			// background: linear-gradient( 359deg, #D0000F 0%, #FFF354 100%);
			background: linear-gradient(359deg, #D0000F 0%, #FFF354 100%);
			// background: linear-gradient( 180deg, #D0000F 0%, #780702 100%);
			border-radius: 50rpx;
			font-size: 32rpx;
			color: #fff;
			text-align: center;
			line-height: 100rpx;
			box-shadow: 0rpx 0rpx 12rpx 2rpx rgba(208, 0, 15, 0.38);
			transition-duration: .3s;
			border: 2rpx solid #D0000F;
		}

		.btn:active {
			transform: translate(1px, 0px);
			box-shadow: 0px 3px 0px rgba(208, 0, 15, 0.38);
			padding-bottom: 3px;
		}

		.btns {
			width: 226rpx;
			height: 100rpx;
			background: linear-gradient(87deg, #D0000F 0%, #FFF354 100%);
			border-radius: 50rpx;
			font-size: 32rpx;
			color: #fff;
			text-align: center;
			line-height: 100rpx;
			box-shadow: 0rpx 0rpx 12rpx 2rpx rgba(208, 0, 15, 0.38);
			transition-duration: .3s;
			border: 2rpx solid #D0000F;
		}

		.btns:active {
			transform: translate(1px, 0px);
			box-shadow: 0px 1px 0px rgba(208, 0, 15, 0.38);
			padding-bottom: 1px;
		}

		.botton {
			width: 100%;
			display: flex;
			align-items: center;
			position: fixed;
			bottom: 72rpx;
		}
	}
</style>