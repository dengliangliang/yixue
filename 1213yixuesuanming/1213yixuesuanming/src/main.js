import App from './App'
import { createSSRApp } from 'vue'
import uView from 'uview-ui'
// 封装方法 - 使用 require 因为这些模块使用 CommonJS 导出
const util = require('@/common/util.js');
const USE = require('@/common/hooks/index.js');
const API = require("@/common/request/api.js");
const website = require('./config/website');

export function createApp() {
	const app = createSSRApp(App)
	
	// Vue3 使用 globalProperties 替代 prototype
	app.config.globalProperties.util = util;
	app.config.globalProperties.$api = API;
	app.config.globalProperties.$config = website;
	
	// 使用 uView
	app.use(uView)
	
	return {
		app
	}
}