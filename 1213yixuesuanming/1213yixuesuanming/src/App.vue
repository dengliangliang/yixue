<script>
	import { getCurrentInstance } from 'vue';
	export default {
		onLaunch: function() {
			console.log('App Launch')
			if (this.isWeChatBrowser()) {
				const pages = getCurrentPages();
				pages.forEach((page) => {
					page.$vm.$page.options.navigationStyle = 'custom';
				});
			}

			uni.getSystemInfo({
				success: res => {
					// Vue3 使用 globalProperties
					const app = getCurrentInstance();
					if (app) {
						app.appContext.config.globalProperties.$windowHeight = res.windowHeight
					}
				}
			});
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		},
		methods: {
			isWeChatBrowser() {
				let userAgent = navigator.userAgent.toLowerCase();
				return userAgent.indexOf('micromessenger') !== -1;
			}
		}
	}
</script>

<style lang="scss">
	/*每个页面公共css */
	@import url("common/style/index.css");
	/* 注意要写在第一行，同时给style标签加入lang="scss"属性 */
	@import "uview-plus/index.scss";

	@font-face {
		font-family: 'YouSheBiaoTiHei';
		src: url('@/static/ttf/font2.ttf');
	}

	page {
		font-family: YouSheBiaoTiHei;
	}
</style>