/**
 * 全局配置文件
 */

// 根据运行环境自动选择API地址
// H5开发环境使用相对路径，通过Vite代理访问
// 其他环境使用完整地址
const getBaseURL = () => {
	// #ifdef H5
	// H5环境：开发时使用空字符串（通过Vite代理），生产环境使用完整地址
	if (import.meta.env.DEV) {
		return ''; // 开发环境使用Vite代理
	}
	return 'https://yixueadmin.linqingkeji.com'; // 生产环境使用域名
	// #endif
	
	// #ifndef H5
	// 非H5环境（小程序、APP）直接使用完整地址
	return 'https://yixueadmin.linqingkeji.com';
	// #endif
}

export default {
	title: '来自2025的好消息',
	appHotVersion: '1.0.0.0', //热更新版本号
	URL: getBaseURL(), // 服务器API地址
	upFeilUrl: getBaseURL() + '/api/index/saveBase64', //上传图片地址
};