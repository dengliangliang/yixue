let UASBridgeAgr = {
	appSystem: '',
	obj_fn: {},
	result: {}
}

if (/ANDROID/gi.test(navigator.userAgent)) {
	UASBridgeAgr.appSystem = 'Android'
} else if (/IOS/gi.test(navigator.userAgent)) {
	UASBridgeAgr.appSystem = 'IOS'
} else {
	UASBridgeAgr.appSystem = 'Brower'
}

// sucess回调， sucessBack是e行销的接受分享回调的地方
window.success = res => {
	let msg = JSON.parse(res[0])
	let interfaceName = res[1]
	let fn = UASBridgeAgr.obj_fn[interfaceName]
	UASBridgeAgr.result.success = true
	UASBridgeAgr.result.data = msg
	if (fn) fn(UASBridgeAgr.result)
}

// error回调
window.error = res => {
	let msg = JSON.parse(res[0])
	let interfaceName = res[1]
	let fn = UASBridgeAgr.obj_fn[interfaceName]
	UASBridgeAgr.result.success = false
	UASBridgeAgr.result.data = msg
	if (fn) fn(UASBridgeAgr.result)
}

// startBaiduAudio 成功回调
window.videoRecording = res => {
	let msg = JSON.parse(res[0])
	let interfaceName = res[1]
	let fn = UASBridgeAgr.obj_fn[interfaceName]
	UASBridgeAgr.result.success = true
	UASBridgeAgr.result.data = msg
	if (fn) fn(UASBridgeAgr.result)
}


/**
 * call原生
 * @param  {String} id 接口名称
 * @param  {Function} fn 回调
 * @param {Object} data 传输数据
 */
var AppJSInterface = (id, fn = function() {}, data) => {
	console.log(id, '-------类型==型号------', UASBridgeAgr.appSystem)
	let temp = []
	if (!data) {
		temp[0] = ''
	} else {
		temp[0] = data
	}
	temp[1] = id
	UASBridgeAgr.obj_fn[id] = fn
	try {
		if (UASBridgeAgr.appSystem === 'IOS') {
			window.webkit.messageHandlers[id].postMessage(temp)
		} else {
			AndroidNative.entryTunnel(id, JSON.stringify(data))
		}
	} catch (err) {
		console.log('失败----', err)
		uni.showModal({
			content: String(err)
		})
	}
}

/**
 * 打电话
 * @return {String} talktime 通话时间
 * @return {String} maketime 开始时间
 * @param {String} data.phoneNum 电话号码
 */
export const makeCall = (callback, data) => AppJSInterface('makeCall', callback, data)

/**
 * 定位
 * @return {String} latitude 纬度
 * @return {String} longitude 经度
 * @return {String} address 地址详细信息
 * @return {String} isSuccess 0成功/1失败
 * @return {String} message 错误信息
 */
export const getPosition = (callback, data) => AppJSInterface('getPosition', callback, data)

/**
 * url跳转
 * @param {String} data.xType openLocalURL:打开系统浏览器
 * @param {String} data.URL 打开系统浏览器的URL
 * @param {String} data.xType entryPreservation:信易通2.0->移动保全
 * @param {String} data.entryUrl 信易通2.0->移动保全的URL
 * @param {String} data.xType entryLocalSysFromOtherSys:移动保全->信易通2.0
 * @param {String} data.sessionTimeOut 移动保全->信易通2.0是否超时
 * @param {String} data.exitSysPage
 * @param {String} data.xType gotoPage:跳转页面
 * @param {String} data.URL 跳转URL
 * @param {String} data.redirectPath 重定向地址
 * @param {Object} data.params URL中的参数
 * @param {Number} data.showNavigationBar 新窗口是否显示导航栏 0/1
 */
export const gotoPage = (callback, data) => AppJSInterface('gotoPage', callback, data)

/**
 * OCR扫描
 * @param {String} data.xType scanIDCard:OCR扫描身份证
 * @param {String} data.xType scanVisitingCard:OCR扫描名片
 * @param {String} data.xType scanBankCard:OCR扫描银行卡
 * @return {String} cutBigPic/cutSmallPic/bigPic/smallPic base64图片
 */
export const OCRScan = (callback, data) => AppJSInterface('OCRScan', callback, data)

/**
 * 拍照
 * @param {String} data.xType signTakePhoto:签名拍照
 * @param {String} data.width 图片WIDTH
 * @param {String} data.height 图片HEIGHT
 * @param {String} data.mono
 * @param {String} data.quality
 * @param {String} data.openFromGallery 0-拍照，1-本地获取图片
 * @param {String} data.businessType
 * @return {String} businessType 投保人或被保人
 * @return {String} signImg 原图base64
 * @return {String} signMinImg 缩略图base64
 * @param {String} data.xType takePhoto:拍照
 * @param {String} data.imgID
 * @param {String} data.prtNum
 * @param {String} data.imgType
 * @param {String} data.width
 * @param {String} data.height
 * @param {String} data.picNum
 * @param {String} data.mono
 * @param {String} data.quality
 * @param {String} data.openFromGallery 0-拍照，1-本地获取图片
 * @param {String} data.businessType
 * @param {String} data.xType showCameraViewController:投保人、被保人拍照
 * @param {String} data.signImg 原图base64
 * @param {String} data.xType initPhotoView:电子投保页面初始化（查询本地库）
 * @return {String} images 图片数组
 * @return {String} signMinImg 图片数
 * @param {String} xType takePhotoOtherUpload:获取单张大图片
 * @return {String} photo 图片base64
 */
export const takePhotos = (callback, data) => AppJSInterface('takePhotos', callback, data)

/**
 * 电子投保页面图片上传
 * @param {String} data.prtNum 保单号
 * @param {String} data.xType imagesUpload
 * @return {String} status
 * @return {String} xType
 */
export const imagesUpload = (callback, data) => AppJSInterface('imagesUpload', callback, data)

/**
 * 打开电子签名控件 电子手写签名
 * @param {String} data.title 投保人（签名）
 * @param {String} data.titleSpanFromOffset
 * @param {String} data.titleSpanToOffset
 * @param {String} data.singleWidth
 * @param {String} data.singleHeight
 * @param {String} data.businessType
 * @param {String} data.Identitycardnbr
 * @param {String} data.username
 * @param {String} data.nessesary
 * @param {String} data.template_serial
 * @param {String} data.serverConfigRule
 * @param {String} data.contentHash
 * @param {String} data.xType showSignViewController
 * @return {String} currentSigner 类型（投保人或被投保人）
 * @return {String} signImg
 * @return {String} signatureData 加密报文
 * @return {String} exit
 * @return {String} xType showSignViewController
 */
export const signatureView = (callback, data) => AppJSInterface('signatureView', callback, data)

/**
 * 打开风险提示语
 * @param {String} data.title 风险提示语
 * @param {String} data.titleSpanFromOffset
 * @param {String} data.titleSpanToOffset
 * @param {String} data.singleWidth
 * @param {String} data.singleHeight
 * @param {String} data.businessType
 * @param {String} data.Identitycardnbr
 * @param {String} data.username
 * @param {String} data.nessesary
 * @param {String} data.template_serial
 * @param {String} data.serverConfigRule
 * @param {String} data.commitment
 * @param {String} data.xType showSignViewController
 * @return {String} currentSigner 类型（投保人或被投保人）
 * @return {String} signImg
 * @return {String} signatureData 加密报文
 * @return {String} exit
 * @return {String} xType showSignViewController
 */
export const gallaryView = (callback, data) => AppJSInterface('gallaryView', callback, data)

/**
 * 获取应用原生版本号
 * @return {String} currentSigner 空字符串
 * @return {String} AppVersion 原生版本号
 */
export const getNativeVersion = callback => AppJSInterface('getNativeVersion', callback)

/**
 * 获取应用前端资源包版本号
 * @return {String} currentSigner 空字符串
 * @return {String} ResourceVersion js版本号
 */
export const getOfflineH5Version = callback => AppJSInterface('getOfflineH5Version', callback)

/**
 * 检查APP版本信息
 */
export const checkUpdate = () => AppJSInterface('checkUpdate')

/**
 * 分享
 * @param {String} data.xType sendNonGifContent:微信分享_网页
 * @param {String} data.title 分享标题
 * @param {String} data.des 分享描述
 * @param {String} data.url 分享URL
 * @param {String} data.image 分享图片
 * @param {String} data.xType sendTencentContent:QQ分享
 * @param {String} data.title 分享标题
 * @param {String} data.des 分享描述
 * @param {String} data.url 分享URL
 * @param {String} data.image 分享图片
 */
export const share = (callback, data) => AppJSInterface('share', callback, data)

/**
 * 导航栏样式
 * @param {String} data.title 标题
 * @param {Boolean} data.showBack 是否显示返回按钮
 * @param {Boolean} data.showClose 是否显示关闭按钮
 * @param {Boolean} data.showShare 是否显示分享按钮
 * @param {String} data.xType 类型setNavigationStyle
 */
export const setNavigationStyle = data => AppJSInterface('setNavigationStyle', function() {}, data)

/**
 * 控制子系统登出时自动关闭子系统
 * @param {String} data.system
 * @param {String} data.xType 类型closePage
 */
export const closePage = data => AppJSInterface('closePage', function() {}, data)

/**
 * 获取并设置设备的注册ID
 * @param {String} data.xType 类型setRegistrationId
 * @return {String} registrationId
 */
export const setRegistrationId = (callback, data) => AppJSInterface('setRegistrationId', callback, data)

/**
 * 唤起微信小程序
 * @param {String} data.userName 拉起的小程序的原始id
 * @param {String} data.path 拉起小程序页面的可带参路径，不填默认拉起小程序首页
 * @param {String} data.miniProgramType 拉起小程序的类型(正式版:0/开发版:1/体验版:2)
 * @param {String} data.xType 类型launchMiniProgram
 */
export const launchMiniProgram = data => AppJSInterface('launchMiniProgram', function() {}, data)

/**
 * 查询推送消息列表
 * @param {Number} data.pageNumber 第几页
 * @param {Number} data.pageLength 每页多少条数据
 * @param {String} data.xType 类型selectNotificationList
 * @return {Array} list 消息列表
 */
export const selectNotificationList = (callback, data) => AppJSInterface('selectNotificationList', callback, data)

/**
 * 文件下载
 * @param {String} data.docType 文件类型
 * @param {String} data.url 文件下载路径
 * @param {String} data.xType 类型download
 */
export const download = (callBack, data) => AppJSInterface('download', callBack, data)

/**
 * 登录IM
 * @param {String} data.userid
 * @param {String} data.password
 * @param {String} data.nickName 昵称
 */
export const loginIM = data => AppJSInterface('loginIM', function() {}, data)

/**
 * 获取会话列表
 * @param {String} data.userid
 * @return {Array} conversationList
 * @return {String} conversationList.conversationId 会话id
 * @return {String} conversationList.type 会话类型
 * @return {String} conversationList.groupName 群聊名称
 * @return {String} conversationList.unreadMessageCount 当前会话未读消息数
 * @return {String} conversationList.latestMessage.messageId 消息的唯一标识
 * @return {String} conversationList.latestMessage.direction 消息的方向（发送方、接收方）
 * @return {String} conversationList.latestMessage.from 发送方
 * @return {String} conversationList.latestMessage.to 接收方
 * @return {String} conversationList.latestMessage.fromName 本人昵称
 * @return {String} conversationList.latestMessage.toName 对方昵称
 * @return {String} conversationList.latestMessage.timestamp 服务器收到此消息的时间
 * @return {String} conversationList.latestMessage.localTime 客户端发送、接收到此消息的时间
 * @return {String} conversationList.latestMessage.text 消息内容
 * @return {Object} conversationList.ext 扩展消息的扩展内容
 */
export const getConversationList = (callback, data) => AppJSInterface('getConversationList', callback, data)

/**
 * 获取总未读消息数
 * @param {String} data.userid
 * @return {String} unReadCount
 */
export const unreadMessageCount = (callback, data) => AppJSInterface('unreadMessageCount', callback, data)

/**
 * 开始会话
 * @param {String} data.fromId 发送方
 * @param {String} data.fromName 发送方昵称
 * @param {String} data.toId 接收方
 * @param {String} data.toName 接收方昵称
 * @param {Number} data.conversationType 0/1/2 单聊/群聊/聊天室
 */
export const startContact = (callback, data) => AppJSInterface('startContact', callback, data)

/**
 * 语音识别
 * @return {String} result
 */
export const speechRecognition = (callback, data) => AppJSInterface('speechRecognition', callback, data)

/**
 * 创建群组
 * @param {String} data.groupName 群组名称
 * @param {String} data.groupDesc 群组描述
 * @param {Array} data.inviteList 邀请列表，用户id列表
 * @param {String} data.inviteMsg 邀请提示信息
 */
export const createGroup = data => AppJSInterface('createGroup', function() {}, data)

/**
 * 登出IM
 */
export const logoutIM = (callback, data) => AppJSInterface('logoutIM', callback, data)

/**
 * 获取群组列表
 * @return {String} groupId 群组id
 * @return {String} desc 群组描述
 * @return {String} groupName 群组名称
 */
export const getGroupList = callback => AppJSInterface('getGroupList', callback)

/**
 * 控制壳更新
 * @param {String} data.downLoadUrl 下载地址
 * @param {String} data.updateInfo 更新信息
 * @param {String} data.optional 是否可选择不更新
 * @param {String} data.type apk/'' 壳内更新/浏览器更新
 */
export const updateNativeVersion = data => AppJSInterface('updateNativeVersion', function() {}, data)

/**
 * 应用内url和图片发送到APP内IM用户
 * @param {String} data.fromId 发送方
 * @param {String} data.fromName 发送方昵称
 * @param {String} data.toId 接收方
 * @param {String} data.toName 接收方昵称
 * @param {String} data.text 消息文本
 * @param {Number} data.conversationType 0/1/2 单聊/群聊/聊天室
 * @param {Number} data.ext.shareType 0/1 url/image
 * @param {String} data.ext.url 网页url或image下载地址
 */
export const sendShareMessage = data => AppJSInterface('sendShareMessage', function() {}, data)

/**
 * 监听底部标签栏tag值
 * @param {String} data.fromPage
 * @param {String} data.customSource
 * @param {String} data.eformLocked
 * @param {String} data.prtNum 保单号
 * @return {String} operator
 * @return {String} customerNo 客户号
 * @return {String} customFlag
 */
export const getTabBarTag = (callback, data) => AppJSInterface('getTabBarTag', callback, data)

/**
 * 删除客户信息
 * @param {String} data.customerNo
 * @return {Number} success 1/0 成功/失败
 */
export const deleteCustomerInfo = (callback, data) => AppJSInterface('deleteCustomerInfo', callback, data)

/**
 * 个人中心推送数据回传，获取每种类型消息数量
 * @return {Number} zhaohui
 * @return {Number} daijiaofei
 * @return {Number} daibuziliao
 * @return {Number} huizhi
 */
export const receiveMessageNumber = callback => AppJSInterface('receiveMessageNumber', callback)

/**
 * 点击个人中心四个模块，推送消息数量置0
 */
export const setMessageNumberIs0 = () => AppJSInterface('setMessageNumberIs0')

/**
 * 打开手势密码
 */
export const openLockGesture = () => AppJSInterface('openLockGesture')

/**
 * 修改手势密码
 */
export const modifyLockGesture = () => AppJSInterface('modifyLockGesture')

/**
 * 检测是否设置手势密码
 */
export const checkGesture = () => AppJSInterface('checkGesture')

/**
 * 活体检测
 * @param {String} data.xType LivenessDetection
 * @return {String} verificationData
 * @return {String} isSuccess  1/0 成功/失败
 * @return {String} xType LivenessDetection
 */
export const LivenessDetection = (callback, data) => AppJSInterface('LivenessDetection', callback, data)

/**
 * 截取webView长页面保存到相册
 */
export const getLongPicture = () => AppJSInterface('getLongPicture')

/**
 * 登录时网络请求客户数据，存到数据库_分页查询的方式
 */
export const requestCustomerData = (callback, data) => AppJSInterface('requestCustomerData', callback, data)

export const getCallLog = callback => AppJSInterface('getCallLog', callback)

/**
 * 保存客户信息
 */
export const saveCustomerInfo = data => AppJSInterface('saveCustomerInfo', function() {}, data)

/**
 * 清除系统缓存
 */
export const cleanSystemCache = () => AppJSInterface('cleanSystemCache')

/**
 * 获取一段时间内的日历事件列表
 * @param {String} startDate 开始日期
 * @param {String} endDate 结束日期
 */
export const getCalendarEventList = (callback, data) => AppJSInterface('getCalendarEventList', callback, data)

/**
 * 添加日历事件
 */
export const addCalendarEvent = data => AppJSInterface('addCalendarEvent', function() {}, data)

/**
 * 更新日历事件
 */
export const updateCalendarEvent = data => AppJSInterface('updateCalendarEvent', function() {}, data)

/**
 * 查看单个日历事件
 * @param {String} eventIdentifier 事件的唯一标识
 */
export const selectCalendarEvent = data => AppJSInterface('selectCalendarEvent', function() {}, data)

/**
 * 批量删除日历事件
 */
export const deleteCalendarEvent = data => AppJSInterface('deleteCalendarEvent', function() {}, data)

/**
 * 将所有消息标记为已读
 */
export const markAllMessagesAsRead = () => AppJSInterface('markAllMessagesAsRead')

/**
 * 删除会话
 */
export const deleteConversation = (callback, data) => AppJSInterface('deleteConversation', callback, data)

/**
 * 同步环信用户头像
 */
export const updateUserInfo = () => AppJSInterface('updateUserInfo')

export const remoteSendData = data => AppJSInterface('remoteSendData', function() {}, data)

export const shareMiniApp = data => AppJSInterface('shareMiniApp', function() {}, data)

// 获取联系人
export const getContacts = callback => AppJSInterface('getContacts', callback)

export const sendVRData = data => AppJSInterface('sendVRData', function() {}, data)

// 双录-开始录制
export const startCapture = (callback, data) => AppJSInterface('startCapture', callback, data)

// 双录-结束录制
export const stopCapture = (callback, data) => AppJSInterface('stopCapture', callback, data)

// 双录-双录列表
export const goToCaptureList = () => AppJSInterface('goToCaptureList')

// 双录-是否保存
export const isSaveCapture = data => AppJSInterface('isSaveCapture', function() {}, data)

// 跳转VR消息列表
export const goToVRMessage = data => AppJSInterface('goToVRMessage', function() {}, data)

export const startBaiduAudio = (callback, data) => AppJSInterface('startBaiduAudio', callback, data)

/**
 * 获取环信id
 * @param {String} data.xType getId
 * @return {String} "id":"客户的环信id"
 * @return {String} "confId","当前的会议id"
 */
export const getMessage = (callback, data) => AppJSInterface('getMessage', callback, data)

/**
 * 是否显示推送数据按钮
 * @param {Boolean} data.isShow true/false
 */
export const isSynchroData = data => AppJSInterface('isSynchroData', function() {}, data)

/**
 * 安卓特有
 */
export const checkIDCard = (callback, data) => AppJSInterface('checkIDCard', callback, data)

export const checkBankCard = (callback, data) => AppJSInterface('checkBankCard', callback, data)

export const checkVisitingCard = (callback, data) => AppJSInterface('checkVisitingCard', callback, data)

// 清除webview历史
export const cleanHistory = () => AppJSInterface('checkVisitingCard')

// 请求管理员账号
export const getManager = () => AppJSInterface('checkVisitingCard')

// 设置横屏
export const setUpLandscape = data => AppJSInterface('setUpLandscape', function() {}, data)

// 选择双录模式，本地/远程
export const selectRecordType = data => AppJSInterface('selectRecordType', function() {}, data)

// 给前端传本地已经录制的视频的单号
export const sendUploadList = callback => AppJSInterface('sendUploadList', callback)

// 开始录音
export const startAudioRecord = () => AppJSInterface('startAudioRecord')

// 结束录音
export const stopAudioRecord = callback => AppJSInterface('stopAudioRecord', callback)

// 保存图片
export const savePicture = data => AppJSInterface('savePicture', function() {}, data)

// 选择安全认证方式
export const securityAuthMode = data => AppJSInterface('securityAuthMode', function() {}, data)

// 保存当前用户登录凭证
export const saveLoginToken = (callback, data) => AppJSInterface('saveLoginToken', callback, data)

// 获取当前用户的登录凭证
export const getLoginToken = (callback, data) => AppJSInterface('getLoginToken', callback, data)

// 获取当前system
export const getUAsBridgeAgrSystem = () => UASBridgeAgr.appSystem

// 配置状态栏和底部安全区域
export const configStatusBar = data => AppJSInterface('configStatusBar', function() {}, data)

// 跳转系统设置
export const goToSystemSet = () => AppJSInterface('goToSystemSet')

// APP侧滑返回
export const appTouchBack = data => AppJSInterface('appTouchBack', function() {}, data)

// 扫描二维码
export const QRCodeScan = () => AppJSInterface('QRCodeScan')

// 扫描二维码
export const configWatermark = data => AppJSInterface('configWatermark', function() {}, data)

// 开始地图导航
export const startMapNavigation = data => AppJSInterface('startMapNavigation', function() {}, data)

// 检测会话是否过期
export const checkSessionExpired = data => AppJSInterface('checkSessionExpired', function() {}, data)

// 复制文字功能
export const CopyWord = data => AppJSInterface('CopyWord', function() {}, data)

// 清理浏览记录
export const clearHistory = data => AppJSInterface('clearHistory', function() {}, data)

// 获取在会人员
export const meetingAttendees = callback => AppJSInterface('meetingAttendees', callback)

// 仅拍照
export const onlyTakePhoto = callback => AppJSInterface('onlyTakePhoto', callback)

// 上传日志
export const logUpload = () => AppJSInterface('logUpload')

// 诸葛埋点
export const zhugeEventLog = data => AppJSInterface('zhugeEventLog', function() {}, data)

// 诸葛用户识别配置
export const zhugeIdentify = data => AppJSInterface('zhugeIdentify', function() {}, data)