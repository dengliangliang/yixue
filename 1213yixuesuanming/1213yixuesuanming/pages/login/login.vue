<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<view class="w_100 h_100 ov_y">
			<view style="height: 700rpx;"></view>
			<view class="richtext">
				<scroll-view scroll-y="true">
					<view style="padding: 20rpx 28rpx;color: #AC0202;line-height: 54rpx;">
						2024年大家过的如何， <br />
						2025年是不是有更多期待， <br />
						辰巳交替之际， <br />
						中信保诚人寿推出《来自2025的好消息》， <br />
						带您了解每个人生日里隐藏的人生密码解锁2025年剧本，抢占人生先机。
						<!-- <rich-text :nodes="title"></rich-text> -->
					</view>
				</scroll-view>
			</view>

			<view class="login_box flex_column pt-40">
				<view class="title_">
					填写您的信息
				</view>

				<view class="input_box mt-42 flex_between pl-28 pr-10 mb-28">
					<image src="/static/nameicon.png" class="login_icon" mode="aspectFill"></image>
					<input placeholder-class="pl_color" type="text" class="pl-22 flex_1" placeholder="请输入姓名" />
				</view>
				<view class="input_box flex_between pl-28 pr-10 mb-20">
					<image src="/static/phoneicon.png" class="login_icon" mode="aspectFill"></image>
					<input placeholder-class="pl_color" type="number" class="pl-22 flex_1" placeholder="请输入手机号" />
				</view>
				<view class="input_box flex_between pl-28 pr-10 mb-32">
					<image src="/static/codeicon.png" class="login_icon" mode="aspectFill"></image>
					<input placeholder-class="pl_color" type="text" class="pl-22 flex_1" placeholder="请输入验证码" />
					<view class="code_btn">
						发送验证码
					</view>
				</view>

				<view @click="useLogin" class="login_btn mb-30">
					确定
				</view>
				<view class="w_100 fz_24 flex_center">
					<image :src="`/static/${che_act?'wx':'xz'}.png`" class="che_box" mode="aspectFill"
						@click="che_act=!che_act"></image>
					<text style="color: #8F8F8F;">勾选即表明同意</text>
					<text @click="$go('/pages/yinsi')" style="color: #D0000F;">《隐私保护声明》</text>
				</view>
			</view>

			<view style="height: 116rpx;"> </view>
		</view>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: 'Hello',
				windowHeight: '',
				che_act: false
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
			},
			useLogin() {
				if (!this.che_act) return this.$toast('请先勾选隐私保护声明~');
				uni.reLaunch({
					url: "/pages/login/setInfo"
				})
			}
		}
	}
</script>

<style lang="scss">
	.login_box {
		width: 688rpx;
		height: 754rpx;
		margin: 22rpx 31rpx 0;
		background: linear-gradient(180deg, #FFF0D7 0%, #FEEAC7 100%);
		border-radius: 40rpx 40rpx 40rpx 40rpx;
		border: 4rpx solid #E71C18;

		.pl_color {
			color: #D0000F;
		}

		.code_btn {
			width: 182rpx;
			height: 72rpx;
			color: #fff;
			text-align: center;
			line-height: 72rpx;
			font-size: 26rpx;
			background: #D0000F;
			border-radius: 36rpx 36rpx 36rpx 36rpx;
		}

		.che_box {
			width: 28rpx;
			height: 28rpx;
			margin-right: 12rpx;
		}

		.login_btn {
			width: 556rpx;
			height: 106rpx;
			line-height: 106rpx;
			text-align: center;
			color: #fff;
			font-size: 30rpx;
			background: #D0000F;
			box-shadow: 0rpx 4rpx 40rpx 2rpx rgba(244, 174, 2, 0.18);
			border-radius: 58rpx 58rpx 58rpx 58rpx;
		}

		.login_icon {
			width: 48rpx;
			height: 48rpx;
		}

		.input_box {
			width: 556rpx;
			height: 90rpx;
			font-size: 26rpx;
			border-radius: 58rpx 58rpx 58rpx 58rpx;
			border: 2rpx solid #D0000F;

			input {
				font-size: 26rpx;
				color: #D0000F;
			}
		}

		.title_ {
			width: 294rpx;
			text-align: center;
			font-family: Source Han Sans SC, Source Han Sans SC;
			font-weight: 500;
			font-size: 42rpx;
			color: #AC0202;
			font-style: normal;
			text-transform: none;
			padding-bottom: 2rpx;
			border-bottom: 6rpx solid #AC0202;
		}
	}

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