import App from './App'
// 封装方法
import util from '@/common/util.js';
import * as USE from '@/common/hooks/index.js';
import * as API from "@/common/request/api.js";
import website from './config/website';
// main.js，注意要在use方法之后执行
import uView from 'uview-ui'
import VueCompositionAPI from '@vue/composition-api'
Vue.use(VueCompositionAPI)
Vue.use(uView)
// #ifndef VUE3
import Vue from 'vue'
import './uni.promisify.adaptor'
Vue.config.productionTip = false
Vue.prototype.util = util;
Vue.prototype.$api = API;
Vue.prototype.$config = website;
App.mpType = 'app'
const app = new Vue({
	...App
})
app.$mount()
// #endif

// #ifdef VUE3
import {
	createSSRApp
} from 'vue'
export function createApp() {
	const app = createSSRApp(App)
	return {
		app
	}
}
// #endif