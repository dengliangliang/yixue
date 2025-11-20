<script>
	export default {
		data() {
			return {
				query: ''
			}
		},
		onLoad(op) {
			this.query = op;
			console.log(this.$use.loadImg(this.query.url));
		}
	}
</script>

<template>
	<view class="imgs" style="width: 100vw;height: 100vh;">
		<image show-menu-by-longpress :src="$use.loadImg(query.url)" class="box_100 imgs" mode=""></image>
	</view>
</template>

<style scoped lang="scss">
	.imgs {
		-webkit-user-select: text;
		-moz-user-select: text;
		-ms-user-select: text;
		user-select: text;
	}
</style>