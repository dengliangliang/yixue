<template>
	<!-- P5: 排盘组件 - 参照专业排盘样式 -->
	<view class="paipan-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image :src="cdnBase + staticPrefix + 'xiuwen.jpg'" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		
		<!-- 用户信息卡片 -->
		<view class="user-info-card">
			<view class="info-row" v-for="(info, idx) in userInfo" :key="idx">
				<text class="info-label">{{ info.name }}</text>
				<text class="info-value">{{ info.text }}</text>
			</view>
		</view>
		
		<!-- 专业排盘表格 -->
		<view class="paipan-table">
			<!-- 表头 -->
			<view class="table-header">
				<view class="header-label"></view>
				<view class="header-item">年柱</view>
				<view class="header-item">月柱</view>
				<view class="header-item">日柱</view>
				<view class="header-item">时柱</view>
			</view>
			
			<!-- 十神行 -->
			<view class="table-row">
				<view class="row-label">十神</view>
				<view class="row-item" v-for="(item, index) in tableData" :key="'gs'+index">
					<text :class="getWuxingColorClass(item.icon_top)">{{ item.gan_shi_shen || '-' }}</text>
				</view>
			</view>
			
			<!-- 天干行 - 大字体 -->
			<view class="table-row tiangang-row">
				<view class="row-label">天干</view>
				<view class="row-item tiangang-item" v-for="(item, index) in tableData" :key="'tg'+index">
					<text class="tiangang-text" :class="getWuxingColorClass(item.icon_top)">{{ item.text_top }}</text>
				</view>
			</view>
			
			<!-- 地支行 - 大字体 -->
			<view class="table-row dizhi-row">
				<view class="row-label">地支</view>
				<view class="row-item dizhi-item" v-for="(item, index) in tableData" :key="'dz'+index">
					<text class="dizhi-text" :class="getWuxingColorClass(item.icon_bom)">{{ item.text_bom }}</text>
				</view>
			</view>
			
			<!-- 藏干行 -->
			<view class="table-row canggan-row">
				<view class="row-label">藏干</view>
				<view class="row-item canggan-item" v-for="(item, index) in tableData" :key="'cg'+index">
					<view class="canggan-list">
						<text v-for="(cg, cgIdx) in parseCangGan(item.cang_gan)" :key="cgIdx" 
							class="canggan-text" :class="getWuxingColorClass(cg.wuxing)">
							{{ cg.gan }}{{ cg.wuxing }}
						</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 干支纪年说明 -->
		<view class="ganzhijinian-card" v-if="ganZhiDesc">
			<view class="card-title">
				<view class="title-bar"></view>
				<text class="title-text">干支纪年</text>
			</view>
			<view class="card-subtitle">中国自上古以来就一直使用的纪年方法</view>
			<view class="card-content">{{ ganZhiDesc }}</view>
		</view>
	</view>
</template>

<script>
import websiteConfig from '@/config/website.js';

export default {
	name: 'PaiPan',
	computed: {
		cdnBase() {
			return websiteConfig.CDN.enabled ? websiteConfig.CDN.baseUrl : '';
		},
		staticPrefix() {
			return websiteConfig.CDN.enabled ? '/src/static/' : '/static/';
		}
	},
	props: {
		// 用户信息数组 [{name: '阳历', text: '2000-01-01'}, ...]
		userInfo: {
			type: Array,
			default: () => []
		},
		// 四柱数据
		tableData: {
			type: Array,
			default: () => []
		},
		// 性别 0女 1男
		gender: {
			type: Number,
			default: 0
		},
		// 干支纪年说明
		ganZhiDesc: {
			type: String,
			default: ''
		},
		// 天干相冲
		tianGanChong: {
			type: String,
			default: ''
		},
		// 地支相冲
		diZhiChong: {
			type: String,
			default: ''
		}
	},
	methods: {
		// 根据五行获取颜色类名
		getWuxingColorClass(wuXing) {
			const colorMap = {
				'金': 'wuxing-jin',
				'木': 'wuxing-mu',
				'水': 'wuxing-shui',
				'火': 'wuxing-huo',
				'土': 'wuxing-tu'
			};
			return colorMap[wuXing] || 'wuxing-default';
		},
		// 根据纳音获取颜色类名
		getNayinColorClass(nayin) {
			if (!nayin) return 'wuxing-default';
			// 根据纳音名称判断五行
			if (nayin.includes('金')) return 'wuxing-jin';
			if (nayin.includes('木')) return 'wuxing-mu';
			if (nayin.includes('水')) return 'wuxing-shui';
			if (nayin.includes('火')) return 'wuxing-huo';
			if (nayin.includes('土')) return 'wuxing-tu';
			return 'wuxing-default';
		},
		// 解析藏干字符串
		parseCangGan(cangGanStr) {
			if (!cangGanStr) return [];
			// 格式: "戊·土,辛·金,丁·火" 或 "癸·水"
			return cangGanStr.split(',').map(item => {
				const parts = item.trim().split('·');
				return { gan: parts[0] || '', wuxing: parts[1] || '' };
			});
		},
		// 解析支神字符串
		parseZhiShen(zhiShenStr) {
			if (!zhiShenStr) return [];
			return zhiShenStr.split(',').map(s => s.trim());
		},
		// 解析神煞字符串
		parseShenSha(shenShaStr) {
			if (!shenShaStr) return [];
			return shenShaStr.split(',').map(s => s.trim());
		}
	}
};
</script>

<style lang="scss" scoped>
@font-face {
	font-family: 'QianTuXianMo';
	src: url('https://cdn.yixuestatic.linqingkeji.com/src/static/ttf/千图纤墨体.ttf') format('truetype');
	font-weight: normal;
	font-style: normal;
}

.paipan-container {
	width: 100%;
	padding: 24rpx;
	background: rgba(245, 230, 200, 0.85); /* 增加背景颜色衬托文字 */
	border-radius: 20rpx;
	box-sizing: border-box;
}


/* 祥云装饰样式 */
.xiuwen-decoration {
	width: 100%;
	height: 100rpx;
	overflow: hidden;
	margin-bottom: 20rpx;
	border-radius: 12rpx;
	border: 2rpx solid #DAA520;
}

.xiuwen-img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center;
}

/* 国风用户信息卡片 - 木色纸张风格 */
.user-info-card {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx 32rpx;
	margin-bottom: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 
		0 4rpx 12rpx rgba(139, 105, 20, 0.2),
		inset 0 2rpx 4rpx rgba(255, 255, 255, 0.5);
}

.info-row {
	display: flex;
	align-items: center;
	padding: 16rpx 0;
	border-bottom: 1rpx solid rgba(218, 165, 32, 0.3);
	
	&:last-child {
		border-bottom: none;
	}
}

.info-label {
	width: 140rpx;
	font-size: 28rpx;
	color: #8B0000;
	font-weight: 600;
	font-family: 'font2', 'Microsoft YaHei', sans-serif; /* font2字体 */
}

.info-value {
	flex: 1;
	font-size: 28rpx;
	color: #4A3728;
	text-align: center;
	/* font-weight: 500 移除避免合成粗体 */
}

/* 国风专业排盘表格 - 木色背景 */
.paipan-table {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	overflow: hidden;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.table-header {
	display: flex;
	background: linear-gradient(180deg, #D4A574 0%, #C4956A 100%);
	border-bottom: 2rpx solid #DAA520;
}

.header-label {
	width: 100rpx;
	height: 70rpx;
}

.header-item {
	flex: 1;
	height: 70rpx;
	line-height: 70rpx;
	text-align: center;
	font-size: 28rpx;
	color: #FFFEF9;
	font-weight: 600;
	border-left: 1rpx solid rgba(218, 165, 32, 0.5);
	text-shadow: 1rpx 1rpx 2rpx rgba(0, 0, 0, 0.2);
}

.table-row {
	display: flex;
	border-bottom: 1rpx solid rgba(218, 165, 32, 0.3);
	
	&:last-child {
		border-bottom: none;
	}
}

.row-label {
	width: 100rpx;
	min-height: 70rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #8B0000;
	font-weight: 600;
	font-family: 'font2', 'Microsoft YaHei', sans-serif; /* font2字体 */
	background: linear-gradient(90deg, #E5CCA0 0%, #EDD9B5 100%);
}

.row-item {
	flex: 1;
	min-height: 70rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #4A3728;
	border-left: 1rpx solid rgba(218, 165, 32, 0.3);
	padding: 12rpx 8rpx;
	text-align: center;
	background: rgba(255, 254, 249, 0.5);
}

.normal-text {
	color: #4A3728;
	font-size: 26rpx;
}

/* 天干地支大字体样式 */
.tiangang-row, .dizhi-row {
	.row-item {
		min-height: 140rpx;
	}
}

.tiangang-text, .dizhi-text {
	font-size: 80rpx;
	font-weight: bold;
	font-family: 'QianTuXianMo', "KaiTi", "楷体", "STKaiti", serif;
}

/* 藏干样式 */
.canggan-row .row-item {
	min-height: 110rpx;
	align-items: flex-start;
	padding-top: 16rpx;
}

.canggan-list {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 6rpx;
}

.canggan-text {
	font-size: 24rpx;
	line-height: 34rpx;
}

/* 支神样式 */
.zhishen-row .row-item {
	min-height: 100rpx;
	align-items: flex-start;
	padding-top: 12rpx;
}

.zhishen-list {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 6rpx;
}

.zhishen-text {
	font-size: 26rpx;
	line-height: 34rpx;
}

/* 纳音样式 */
.nayin-text {
	font-size: 26rpx;
}

/* 神煞样式 */
.shensha-row .row-item {
	min-height: 180rpx;
	align-items: flex-start;
	padding-top: 16rpx;
}

.shensha-list {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 6rpx;
}

.shensha-text {
	font-size: 24rpx;
	line-height: 32rpx;
	color: #666;
}

/* 天干地支关系 */
.relation-section {
	background-color: #FAFAF5;
	border-radius: 16rpx;
	padding: 24rpx 32rpx;
	margin-top: 24rpx;
	border: 1rpx solid #E8E5D8;
}

.relation-row {
	display: flex;
	align-items: center;
	padding: 12rpx 0;
	
	&:last-child {
		padding-bottom: 0;
	}
}

.relation-label {
	font-size: 28rpx;
	color: #999;
	margin-right: 24rpx;
	min-width: 80rpx;
}

.relation-value {
	font-size: 28rpx;
	color: #333;
}

/* 干支纪年卡片 */
.ganzhijinian-card {
	background: linear-gradient(135deg, #FFF5F5 0%, #FFFAF0 100%);
	border-radius: 16rpx;
	padding: 32rpx;
	margin-top: 24rpx;
	border: 1rpx solid #F0E0D0;
}

.card-title {
	display: flex;
	align-items: center;
	margin-bottom: 12rpx;
}

.title-bar {
	width: 8rpx;
	height: 40rpx;
	background: linear-gradient(180deg, #D0000F 0%, #FF6B6B 100%);
	border-radius: 4rpx;
	margin-right: 16rpx;
}

.title-text {
	font-size: 40rpx;
	font-weight: bold;
	color: #D0000F;
}

.card-subtitle {
	font-size: 24rpx;
	color: #D0000F;
	margin-bottom: 20rpx;
	padding-left: 24rpx;
}

.card-content {
	font-size: 28rpx;
	color: #666;
	line-height: 48rpx;
	text-align: justify;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

/* 五行颜色 - 参照图片样式 */
.wuxing-jin { color: #B8860B; }  /* 金 - 土黄/金色 */
.wuxing-mu { color: #228B22; }   /* 木 - 绿色 */
.wuxing-shui { color: #1E90FF; } /* 水 - 蓝色 */
.wuxing-huo { color: #D0000F; }  /* 火 - 红色 */
.wuxing-tu { color: #8B4513; }   /* 土 - 棕色 */
.wuxing-default { color: #333; }
</style>
