var app = getApp();
// 正式
var path = "https://zhixingbao.plzxb.com/";
var img_url = "https://zhixingbao.plzxb.com/"
// var path = "https://zhixingbao.jiangkukeji.cn/";
// var img_url = "https://zhixingbao.jiangkukeji.cn/"
var versionName = '1.0.0';
var version = 100;
var appType = 1; // 1=安卓 2=ios
function paths() {
	return path;
}
module.exports = {
	path: path,
	img_url:img_url,
	version: version,
	versionName: versionName,
	appType: appType,
	paths: paths
}
