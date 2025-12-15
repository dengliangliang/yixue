<script>
	import { getCurrentInstance } from 'vue';
	export default {
		onLaunch: function() {
			const launchTime = Date.now();
			console.log(' [应用] App Launch', new Date().toISOString());
			
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
					
					const initTime = Date.now() - launchTime;
					console.log(' [应用] 初始化完成', {
						耗时: initTime + 'ms',
						平台: res.platform,
						系统: res.system,
						分辨率: res.windowWidth + 'x' + res.windowHeight
					});
				}
			});
		},
		onShow: function() {
			console.log('👀 [应用] App Show', new Date().toISOString())
		},
		onHide: function() {
			console.log('👋 [应用] App Hide', new Date().toISOString())
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
	/* @import "uview-plus/index.scss"; */  /* ❌ 未使用，已移除 */

	@font-face {
		font-family: 'YouSheBiaoTiHei';
		src: url('@/static/ttf/font2.ttf');
	}

	page {
		font-family: YouSheBiaoTiHei;
	}
</style>