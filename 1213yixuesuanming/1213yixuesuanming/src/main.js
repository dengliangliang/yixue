import App from './App'
import { createSSRApp } from 'vue'
import uviewPlus from 'uview-plus'
import util from '@/common/util.js'
import API from "@/common/request/api.js"
import website from './config/website'
import '@/common/hooks/index.js' // 导入全局钩子，包含$toast等方法

export function createApp() {
	const app = createSSRApp(App)
	
	// Vue3 使用 globalProperties 替代 prototype
	app.config.globalProperties.util = util;
	app.config.globalProperties.$api = API;
	app.config.globalProperties.$config = website;
	
	// 添加全局导航方法
	app.config.globalProperties.$go = (href, type = 0, time = 300) => uni.navigateTo({
		url: href,
		animationDuration: time,
		animationType: type == 0 ? "slide-in-right" : "zoom-fade-out"
	});
	app.config.globalProperties.$goBack = (num = 1) => {
		const pages = getCurrentPages();
		if (pages.length < 2) return uni.switchTab({
			url: "/pages/index/index"
		});
		uni.navigateBack({
			delta: num
		})
	};
	app.config.globalProperties.$goTab = (url = "/pages/index/index") => uni.switchTab({
		url
	});
	app.config.globalProperties.$toast = (title) => uni.showToast({
		title,
		icon: "none"
	});
	
	// 使用 uView Plus
	app.use(uviewPlus)
	
	return {
		app
	}
}