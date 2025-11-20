<script>
	export default {
		data() {
			return {
				text_html: '后台编辑隐私政策文本内容本应用尊重并保护所有使用服务用户的个人隐私权。为了给您提供更准确、更有个性化的服务，本应用会按照本隐私权政策的规定使用和披露您的个人信息。但本应用将以高度的勤勉、审慎义务对待这些信息。除本隐私权政策另有规定外，在未征得您事先许可的情况下，本应用不会将这些信息对外披露或向第三方提供。本应用会不时更新本隐私权政策。您在同意本应用服务使用协议之时，即视为您已经同意本隐私权政策全部内容。本隐私权政策属于本应用服务使用协议不可分割的一部分。',
			}
		},
		onReady() {},
		methods: {

		}
	}
</script>

<template>
	<view v-html="text_html" class="box_100 bj_ py-50 pl-16 pr-44" style="line-height: 60rpx;">

	</view>
</template>

<style scoped lang="scss">
	page {
		background-image: url("/static/yinsi-bj.png");
				background-repeat: no-repeat;
		background-size: cover;
	}
</style>