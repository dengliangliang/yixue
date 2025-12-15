import website from '@/config/website.js'

const URL = website.URL + '/';

export const get = (url, data, timeout = 100000) => {
	const header = {
		'token': uni.getStorageSync('token')
	}
	return new Promise((resolve, reject) => {
		uni.request({
			url: URL + url,
			data,
			header,
			timeout,
			method: 'GET',
			success: (res) => {
				// console.log(
				// 	`%c${url}==========`,
				// 	'background-color:#bffaff;color:#000;border-radius: 4px;padding:3px 10px;',
				// 	res.data
				// );
				if (res.data.code == 401) {
					const pages = getCurrentPages();
					const currentRoute = pages[pages.length - 1].route;
					// console.log('当前页面路径：', currentRoute);
					if (currentRoute.indexOf('login') == -1) return uni.showToast({
						title: "请先登录",
						icon: "none",
						success() {
							setTimeout(() => uni.navigateTo({
								url: '/pages_sub/login/login'
							}), 900)
						}
					})
				} else if (res.data.code != 1) {
					// uni.showToast({
					// 	title: res.data.msg,
					// 	icon: "none",
					// 	duration: 1500
					// })
				}
				resolve(res.data);
			},
			fail: (error) => {
				uni.showToast({
					title: error,
					icon: "none",
					duration: 1500
				})
				reject(error)
			},
			complete: (fail) => {
				resolve(fail)
			}
		})

	}).catch(error => {
		// let [err, res] = error
		uni.showToast({
			title: '网络开了小差1',
			icon: "none",
			duration: 1500
		})
		reject(error)
	})
}

export const post = (url, data, timeout = 100000) => {
	const header = {
		'token': uni.getStorageSync('token')
	}
	const requestStart = Date.now();
	console.log(`[API] POST 请求开始: ${url}`, '时间:', new Date().toISOString());
	
	return new Promise((resolve, reject) => {
		uni.request({
			url: URL + url,
			data,
			header,
			timeout,
			method: 'POST',
			success: (res) => {
				const duration = Date.now() - requestStart;
				console.log(`[API] POST 响应完成: ${url}`, '耗时:', duration, 'ms', '状态:', res.data?.code);
				
				if (res.data.code == 401) {
					const pages = getCurrentPages();
					const currentRoute = pages[pages.length - 1].route;
					// console.log('当前页面路径：', currentRoute);
					if (currentRoute.indexOf('login') == -1) return uni.showToast({
						title: "请先登录",
						icon: "none",
						success() {
							setTimeout(() => uni.navigateTo({
								url: '/pages_sub/login/login'
							}), 900)
						}
					})
				} else if (res.data.code != 1) {
					// uni.showToast({
					// 	title: res.data.msg,
					// 	icon: "none",
					// 	duration: 1500
					// })
				}
				resolve(res.data);
			},
			fail: (error) => {
				resolve(error)
				uni.showToast({
					title: error,
					icon: "none",
					duration: 1500
				})
			},
			complete: (fail) => {
				resolve(fail)
			}
		})
	}).catch(error => {
		reject(error)
		uni.showToast({
			title: '网络开了小差',
			icon: "none",
			duration: 1500
		})
	})
}

export const file = (url, filePath, formData, timeout = 100000) => {
	const header = {
		'token': uni.getStorageSync('token')
	}
	return new Promise((resolve, reject) => {
		uni.uploadFile({
			url,
			filePath,
			name: 'file',
			header,
			formData,
			success: (uploadFileRes) => {
				uni.hideLoading()
				console.log('上传接口=====', uploadFileRes);
				const {
					data
				} = uploadFileRes
				var fileData = JSON.parse(data)
				return resolve(fileData)
			},
			cache: (err) => {
				uni.hideLoading()
				uni.showToast({
					title: "上传失败",
					icon: "none"
				})
				reject(err)
			}
		});

	}).catch(error => {
		uni.showToast({
			title: '网络开了小差1',
			icon: "none",
			duration: 1500
		})
		reject(error)
	})
}

export default { get, post, file }