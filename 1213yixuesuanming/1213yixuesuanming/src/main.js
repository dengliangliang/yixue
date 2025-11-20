import App from './App'
import { createSSRApp } from 'vue'
import uviewPlus from 'uview-plus'
import util from '@/common/util.js'
import API from "@/common/request/api.js"
import website from './config/website'

export function createApp() {
	const app = createSSRApp(App)
	
	// Vue3 使用 globalProperties 替代 prototype
	app.config.globalProperties.util = util;
	app.config.globalProperties.$api = API;
	app.config.globalProperties.$config = website;
	
	// 使用 uView Plus
	app.use(uviewPlus)
	
	return {
		app
	}
}