<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<view class="w_100 flex_center" style="position: fixed;bottom: 510rpx;">
			<image src="/static/botton.png" mode="" @click="navto"></image>
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
		onLoad(op) {
			console.log('----', op);
			if (op.sign) {
				uni.setStorageSync('app_parmas', op)
			} else {
				this.$toast('获取参数失败~')
			}
		},
		methods: {
			getsy() {
				uni.getSystemInfo({
					success: res => {
						// console.log(res)
						this.windowHeight = res.windowHeight
					}
				});
			},
			navto() {
				uni.navigateTo({
					url: "/pages/login/setInfo"
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #bf0f06;
	}

	.content {
		width: 100%;
		background-image: url(/static/jiesuobg.png);
		// overflow: hidden;
		background-size: cover;
		// background-position: center;
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
	}
</style>