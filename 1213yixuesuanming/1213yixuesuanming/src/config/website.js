/**
 * 全局配置文件
 */

// 七牛云CDN配置
const CDN_CONFIG = {
	enabled: true, // 是否启用CDN
	baseUrl: 'https://cdn.yixuestatic.linqingkeji.com', // CDN域名
	bucket: 'yixue1016', // 存储空间名
};

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
};

/**
 * 获取CDN加速后的资源URL
 * @param {string} path - 资源路径，如 '/static/ttf/font.ttf' 或 '/static/image.png'
 * @returns {string} CDN加速后的完整URL
 */
const getCdnUrl = (path) => {
	if (!CDN_CONFIG.enabled) {
		return path; // CDN未启用时返回原路径
	}
	// 移除开头的斜杠，并添加src前缀（七牛云上传时保持了src/static/xxx的目录结构）
	let cleanPath = path.replace(/^\//, '');
	// 如果路径以static/开头，添加src/前缀
	if (cleanPath.startsWith('static/')) {
		cleanPath = 'src/' + cleanPath;
	}
	return `${CDN_CONFIG.baseUrl}/${cleanPath}`;
};

export default {
	title: '来自2025的好消息',
	appHotVersion: '1.0.0.0', //热更新版本号
	URL: getBaseURL(), // 服务器API地址
	upFeilUrl: getBaseURL() + '/api/index/saveBase64', //上传图片地址
	CDN: CDN_CONFIG, // 导出CDN配置
	getCdnUrl, // 导出CDN URL获取函数
};