// import Vue from 'vue'; // Vue3 不需要直接导入
import website from '@/config/website.js'

const URL = website.URL;

const use = {
	/**
	 * 小程序专用
	 * 获取胶囊大小
	 */
	getTopBox() {
		let leftbox = {
			width: '100rpx',
			height: '10rpx',
			top: '50rpx'
		}
		let box = uni.getMenuButtonBoundingClientRect();
		leftbox = {
			width: box.width + 0 + 'rpx',
			height: box.height + 'rpx',
			top: box.top + 0 + 'rpx',
		}
		return leftbox
	},
	/**
	 * 上一个页面栈赋值
	 * @param {string} str 参数名
	 * @param {any} val 赋值
	 * @param {any} nums 索引 （可选）
	 */
	setLastData(str, val, nums = "-1") {
		const pages = getCurrentPages();
		const lastPage = pages[pages.length - 2]; // 上一页实例
		nums == -1 ? lastPage.$vm[str] = val : lastPage.$vm[str][nums] = val;
		console.log(getCurrentPages(), 'setLastData===');
	},
	/** 
	 * 复制剪切板
	 * @param {Object} str 复制内容
	 * @param {Object} title 提示信息
	 */
	copy(str, title = '复制成功') {
		uni.setClipboardData({
			data: str + '',
			success(res) {
				console.log(res);
				uni.showToast({
					title,
					icon: 'none'
				})
			}
		})
	},
	/** 
	 * 拨打电话
	 * @param {Object} tel 电话号码
	 */
	call(tel) {
		uni.makePhoneCall({
			phoneNumber: tel
		})
	},
	/** 
	 * 保存图片到相册
	 * @param {Object} ImgUrl 图片地址
	 * @param {Object} callBack 回调函数
	 */
	saveImg(ImgUrl, callBack) {
		uni.downloadFile({
			ImgUrl,
			success: (res) => {
				console.log('downloadFile===', res);
				if (res.statusCode === 200) {
					uni.saveImageToPhotosAlbum({
						filePath: res.tempFilePath,
						success: function() {
							uni.showToast({
								title: "保存成功",
								icon: "none"
							});
						},
						fail: function() {
							uni.showToast({
								title: "保存失败，请稍后重试",
								icon: "none"
							});
						},
						complete: (complete) => {
							callBack(complete)
						}
					});
				}
			}
		})
	},
	/**
	 * 上传图片
	 */
	upImg(formData, num = 1) {
		return new Promise((resolve, reject) => {
			uni.chooseImage({
				count: num, //默认9
				sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
				sourceType: ['album'], //从相册选择
				success: (res) => {
					uni.showLoading({
						title: "加速上传中~"
					})
					uni.uploadFile({
						url: website.upFeilUrl, //仅为示例，非真实的接口地址
						filePath: res.tempFilePaths[0],
						name: 'file',
						header: {
							token: uni.getStorageSync('token')
						},
						formData,
						success: (uploadFileRes) => {
							uni.hideLoading()
							// console.log('上传图片=====', uploadFileRes);
							const {
								data
							} = uploadFileRes
							var imgfrllUrl = JSON.parse(data)
							return resolve(imgfrllUrl)
						},
						cache: (err) => {
							uni.hideLoading()
							uni.showToast({
								title: "上传失败",
								icon: "none"
							})
						}
					});
				}
			});
		})
	},
	/**
	 * 上传视频
	 */
	upVideo(formData) {
		return new Promise((resolve, reject) => {
			// uploadFile 存储需要上传的文件
			let uploadFile = ''
			uni.chooseVideo({
				maxDuration: 60, // 拍摄视频最长拍摄时间，单位秒。最长支持 60 秒。
				sourceType: ['album'],
				success: (res) => {
					uploadFile = res.tempFilePath;
					uni.uploadFile({
						url: website.upFeilUrl, //仅为示例，非真实的接口地址
						filePath: uploadFile,
						name: 'file',
						header: {
							token: uni.getStorageSync('token')
						},
						formData,
						success: (uploadFileRes) => {
							let data = JSON.parse(uploadFileRes.data)
							// console.log('data:===', data);
							if (data.code !== 1) { // 视频上传失败了
								uni.hideLoading()
								uni.use.toast(data.msg)
								return
							}
							// 上传成功(把上传成功后的文件路径 push 到页面需要显示的视频数据列表中)
							return resolve(data)
						}
					});
				}
			});
		})
	},
	/** 
	 * 预览图片
	 * @param {Array} ImgUrls 图片地址
	 * @param {Number} current 索引
	 */
	viewImg(ImgUrls, current = 0) {
		if (!ImgUrls) return uni.showToast({
			title: "图片错误,无法预览",
			icon: "none"
		});
		let urls = Array.isArray(ImgUrls) ? urls = [...ImgUrls] : [ImgUrls];
		urls = urls.map(v => {
			v = this.loadImg(v);
			return v
		})
		uni.previewImage({
			urls,
			current
		});
	},

	/** 
	 * 图片加载
	 * @param {Object} url 图片地址前缀拼接
	 */
	loadImg(url) {
		let ImgUrl = ''
		if (url === null || url === undefined || url === '') {
			ImgUrl = ''
		} else if (typeof(url) === "string") {
			if (url.includes('http://') || url.includes('https://') || url.includes('www') || url.includes(
					'data:image')) {
				ImgUrl = url;
			} else {
				ImgUrl = URL + url;
			}
		} else {
			ImgUrl = URL + url;
		}

		// if (url == '') ImgUrl =
		// 	'https://tse3-mm.cn.bing.net/th/id/OIP-C.OlkGrrQB4qI2UrcjINqGRwAAAA?rs=1&pid=ImgDetMain';
		return ImgUrl
	},
	/**
	 * 微信支付
	 */
	wxPay(orderInfo, callBack) {
		// let that = this
		let orderInfos = {};
		// #ifdef MP-WEIXIN
		orderInfos = {
			"appId": orderInfo.appId, // 微信开放平台 - 应用 - AppId，注意和微信小程序、公众号 AppId 可能不一致
			"nonceStr": orderInfo.nonceStr, // 随机字符串
			"package": orderInfo.package, // 固定值
			"partnerid": orderInfo.package.split('prepay_id=')[1], // 微信支付商户号
			"paySign": orderInfo.paySign, // 统一下单订单号
			"timeStamp": orderInfo.timeStamp, // 时间戳（单位：秒）
			"signType": orderInfo.signType, // 签名，这里用的 MD5/RSA 签名
		};
		// #endif

		// #ifdef APP
		orderInfos = {
			"appid": orderInfo.appId, // 微信开放平台 - 应用 - AppId，注意和微信小程序、公众号 AppId 可能不一致
			"noncestr": orderInfo.nonceStr, // 随机字符串
			"package": orderInfo.package, // 固定值
			"partnerid": orderInfo.package.split('prepay_id=')[1], // 微信支付商户号
			"paysign": orderInfo.paySign, // 统一下单订单号
			"timestamp": orderInfo.timeStamp, // 时间戳（单位：秒）
			"sign": orderInfo.signType // 签名，这里用的 MD5/RSA 签名
		};
		// #endif
		uni.requestPayment({
			provider: 'wxpay',
			...orderInfos,
			success: function(res) {
				console.log('微信成功', res);
				callBack(true)
				uni.showToast({
					title: '支付成功',
					icon: "none"
				})
			},
			fail: function(err) {
				console.log('支付失败---', err);
				let content = '订单支付失败';
				if (err.errMsg == "requestPayment:fail cancel") content = '用户取消支付';
				callBack(false)
				uni.showModal({
					title: '支付失败',
					content,
					showCancel: false,
					success: (res) => {}
				})
			},
			complete: (res) => {

			}
		})
	},
	/** 
	 * 遍历对象是否为空
	 * @param {Object} obj 目标对象
	 */
	objIfNull(obj) {
		let flag = true;
		for (var key in obj) {
			if (obj[key] != '0' && !obj[key]) {
				flag = false; // 终止程序
				// console.log('----', obj[key]);
			}
		}
		return flag;
	},
	/** 
	 * 置空对象(最多二级对象)
	 * @param {Object} obj 目标对象
	 */
	clearObj(obj) {
		for (let key in obj) {
			if (obj.hasOwnProperty(key)) {
				if (Array.isArray(obj[key])) {
					obj[key] = [];
				} else if (typeof(obj[key]) == 'object') {
					obj[key] = {};
					for (let keys in obj[key]) {
						if (Array.isArray(obj[key])) {
							obj[key][keys] = [];
						} else {
							obj[key][keys] = '';
						}
					}
				} else {
					obj[key] = '';
				}
			}
		}
		return obj
	},
	/** 
	 * 把二级对象转为一级
	 * @param {Object} obj 要处理的对象
	 */
	formatObj(obj) {
		obj = JSON.parse(JSON.stringify(obj))
		const flattened = {};
		for (const key in obj) {
			if (typeof obj[key] === 'object' && obj[key] !== null && !Array.isArray(obj[key])) {
				const subObj = obj[key];
				for (const subKey in subObj) {
					flattened[subKey] = subObj[subKey];
				}
			} else {
				flattened[key] = obj[key];
			}
		}
		// console.log('formatObj--', flattened);
		return flattened;
	},
	/** 
	 * 跳到企业微信打开文档
	 * @param {Object} item
	 * @param {Object} callBack
	 */
	async goWxWork(item, callBack) {
		uni.navigateToMiniProgram({
			appId: 'wxd45c635d754dbf59',
			path: `pages/detail/detail?url=${item.url}`,
			success: (res) => {callBack()}
		})
	},
	/** 
	 * 打开企业微信二维码
	 * @param {Object} callBack 回调函数
	 */
	async toWxWork(callBack) {
		const res = await uni.request({
			url: website.URL + '/api/order/consult',
			header: {
				token: uni.getStorageSync('token')
			}
		});
		// console.log('toWxWork===', res);
		let datas = res[1].data.data;
		if (!datas.url) datas.url = website.workUrl;
		if (!datas.wxwork_id) datas.wxwork_id = website.wxwork_id;
		uni.$emit('workImg', datas);
		// #ifdef MP-WEIXIN
		wx.openCustomerServiceChat({
			extInfo: {
				url: datas.url
			},
			corpId: datas.wxwork_id,
			success(res) {
				console.log('跳转企业微信成功---', res)
				callBack(res)
			},
			complete(e) {
				console.log('跳转企业微信---', e);
			}
		})
		// #endif
	},
	// 获取缓存
	getCache() {
		return new Promise((res, req) => {
			let cache = '';
			plus.cache.calculate(function(size) {
				let sizeCache = parseInt(size);
				if (sizeCache == 0) {
					cache = "0B";
				} else if (sizeCache < 1024) {
					cache = sizeCache + "B";
				} else if (sizeCache < 1048576) {
					cache = (sizeCache / 1024).toFixed(2) + "KB";
				} else if (sizeCache < 1073741824) {
					cache = (sizeCache / 1048576).toFixed(2) + "MB";
				} else {
					cache = (sizeCache / 1073741824).toFixed(2) + "GB";
				}
				resolve(cache)
			});
		})
	},
	/** 
	 * 清理缓存
	 */
	clear() {
		uni.showLoading({
			title: '加大马力清除中~'
		})
		let os = plus.os.name;
		if (os == 'Android') {
			let main = plus.android.runtimeMainActivity();
			let sdRoot = main.getCacheDir();
			let files = plus.android.invoke(sdRoot, "listFiles");
			let len = files.length;
			for (let i = 0; i < len; i++) {
				let filePath = '' + files[i]; // 没有找到合适的方法获取路径，这样写可以转成文件路径  
				plus.io.resolveLocalFileSystemURL(filePath, function(entry) {
					if (entry.isDirectory) {
						entry.removeRecursively(function(entry) { //递归删除其下的所有文件及子目录 
							uni.showToast({
								title: '缓存清理完成',
								duration: 2000
							});
						}, function(e) {
							console.log(e.message)
						});
					} else {
						entry.remove();
					}
					uni.hideLoading()
				}, function(e) {
					uni.hideLoading()
					console.log('文件路径读取失败')
				});
			}
		} else { // ios  
			uni.hideLoading()
			plus.cache.clear(() => {
				uni.showToast({
					title: '缓存清理完成',
					duration: 2000
				});
			});
		}
	},
	/** 
	 * 关闭应用
	 */
	close() {
		switch (uni.getSystemInfoSync().platform) {
			case 'android':
				plus.runtime.quit();
				break;
			case 'ios':
				plus.ios.import('UIApplication').sharedApplication().performSelector('exit');
				break;
		}
	}
};

const $goBack = (num = 1) => {
	const pages = getCurrentPages();
	if (pages.length < 2) return uni.switchTab({
		url: "/pages/index/index"
	});
	uni.navigateBack({
		delta: num
	})
}
const $go = (href, type = 0, time = 300) => uni.navigateTo({
	url: href,
	animationDuration: time,
	animationType: type == 0 ? "slide-in-right" : "zoom-fade-out"
})
const $goTab = (url = "/pages/index/index") => uni.switchTab({
	url
})
const $toast = (title) => uni.showToast({
	title,
	icon: "none"
});

uni.$goBack = $goBack;
uni.$go = $go;
uni.$goTab = $goTab;
uni.$toast = $toast;
uni.$use = use;

module.exports = {
	use,
	$goBack,
	$go,
	$goTab,
	$toast
}