<template>
	<!-- 二期生成页 - 整合P5-P10 -->
	<view class="page-bg">
		
		<view class="page-overlay min-h-screen">
			<view class="generate-page">
				<!-- 顶部背景 -->
				<image src="/static/top-text.png" style="height: 106rpx;" class="po_ab t-0 l-0 w_100 zIndex-1"
					mode="aspectFill"></image>

				<!-- Tab切换 - 使用新样式 -->
			<view class="tab-container animate__animated animate__fadeInDown">
				<view class="tab-bar mx-30">
					<template v-for="(tab, index) in tabs" :key="index">
						<view 
							v-if="isReview || index === currentTab"
							:class="['tab-item', currentTab === index ? 'tab-item-active' : '']"
							@click="switchTab(index)">
							{{ tab.name }}
						</view>
					</template>
				</view>
			</view>

		<!-- 内容区域 -->
		<swiper class="content-swiper" :current="currentTab" @change="onSwiperChange" :duration="300">
			<!-- P5: 排盘 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<PaiPan :userInfo="userInfo" :tableData="tableData" :gender="gender" :ganZhiDesc="ganZhiDesc" />
				</scroll-view>
			</swiper-item>

			<!-- P6: 禀赋 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<BinFu :minWuXing="xingGeMin.wu_xing" :minResult="xingGeMin.xing_result"
						:maxWuXing="xingGeMax.wu_xing" :maxResult="xingGeMax.xing_result" :binfuData="binfuData" />
				</scroll-view>
			</swiper-item>

			<!-- P7: 喜忌 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<XiJi :wangYun="wangYun" :xingGeMin="xingGeMin" />
				</scroll-view>
			</swiper-item>

			<!-- P8: 十神 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<ShiShen :liuNianShiShen="liuNianShiShen" :liuNianDesc="liuNianDesc"
						:liuNianShortDesc="liuNianShortDesc" :hasZuHe="hasZuHe" :zuHeName="zuHeName"
						:zuHeDesc="zuHeDesc" />
				</scroll-view>
			</swiper-item>

			<!-- P9: 星辰 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<ShenSha :shenShaList="shenShaList" />
				</scroll-view>
			</swiper-item>

			<!-- P10: 幸运位 -->
			<swiper-item>
				<scroll-view scroll-y class="swiper-content">
					<FangWei :fangWeiData="fangWeiData" />
				</scroll-view>
			</swiper-item>
		</swiper>

				<!-- 底部按钮 - 使用新样式 -->
				<view class="bottom-bar animate__animated animate__fadeInUp">
					<view class="flex gap-16 px-30">
						<view v-if="currentTab > 0" class="btn-secondary flex-1" @click="prevTab">
							<text class="btn-secondary-text">上一页</text>
						</view>
						<view v-if="currentTab < tabs.length - 1" class="btn-primary flex-1" @click="nextTab">
							<text class="btn-primary-text">下一页</text>
						</view>
						<view v-else class="btn-primary flex-1" @click="goShare">
							<text class="btn-primary-text">下一页</text>
						</view>
					</view>
					<view class="safe-area"></view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
import PaiPan from './components/PaiPan.vue';
import BinFu from './components/BinFu.vue';
import XiJi from './components/XiJi.vue';
import ShiShen from './components/ShiShen.vue';
import ShenSha from './components/ShenSha.vue';
import FangWei from './components/FangWei.vue';

export default {
	components: {
		PaiPan,
		BinFu,
		XiJi,
		ShiShen,
		ShenSha,
		FangWei
	},
	data() {
		return {
			record_id: '',
			isReview: false, // 是否为查看模式(二次进入)
			viewedPages: [0], // 已查看的页面索引(默认包含第一页)
			currentTab: 0,
			tabs: [
				{ name: '排盘', key: 'paipan' },
				{ name: '禀赋', key: 'binfu' },
				{ name: '喜忌', key: 'xiji' },
				{ name: '十神', key: 'shishen' },
				{ name: '星辰', key: 'shensha' }, // 原"神煞"改为"星辰"
				{ name: '幸运位', key: 'fangwei' } // 原"方位"改为"幸运位"
			],
			// P5 排盘数据
			userInfo: [],
			tableData: [],
			gender: 0,
			ganZhiDesc: '',
			// P6 禀赋数据
			xingGeMin: {},
			xingGeMax: {},
			binfuData: {
				hasBinfu: false,
				binfuWuXing: '',
			binfuDesc: ''
			},
			// P7 喜忌数据
			wangYun: {},
			// P8 十神数据
			liuNianShiShen: '',
			liuNianDesc: '',
			liuNianShortDesc: '',
			hasZuHe: false,
			zuHeName: '',
			zuHeDesc: '',
			// P9 神煞数据
			shenShaList: [],
			// P10 方位数据
			fangWeiData: {}
		};
	},
	onLoad({ record_id, isReview }) {
		this.record_id = record_id;
		this.isReview = isReview === 'true'; // 字符串转布尔
		console.log('[generate] 加载模式:', this.isReview ? '查看模式' : '首次测算');
		this.loadAllData();
	},
	methods: {
		async loadAllData() {
			const startTime = Date.now();
			console.log('[generate] 开始加载数据, record_id:', this.record_id);
			
			try {
				// 加载排盘数据
				const paiPanStart = Date.now();
				await this.loadPaiPanData();
				console.log('[generate] 排盘数据加载完成, 耗时:', Date.now() - paiPanStart, 'ms');
				
				// 加载结果数据(包含二期扩展数据)
				const resultStart = Date.now();
				await this.loadResultData();
				console.log('[generate] 结果数据加载完成, 耗时:', Date.now() - resultStart, 'ms');
				
				console.log('[generate] 全部数据加载完成, 总耗时:', Date.now() - startTime, 'ms');
			} catch (e) {
				console.error('[generate] 加载数据失败:', e);
				this.$toast('加载数据失败');
			}
		},
		async loadPaiPanData() {
			console.log('[generate] 开始请求 getSiZhuRes');
			const requestStart = Date.now();
			
			const res = await this.$api.post('api/si_zhu/getSiZhuRes', {
				record_id: this.record_id
			});
			
			console.log('[generate] getSiZhuRes 响应耗时:', Date.now() - requestStart, 'ms');
			console.log('[generate] getSiZhuRes 响应:', res?.code, res?.msg);
			
			if (res.code !== 1) {
				console.error('[generate] getSiZhuRes 失败:', res);
				return;
			}

			const { record_res, zao } = res.data;
			this.gender = record_res.gender;
			this.userInfo = [
				{ name: '阳历', text: record_res.yang_li_date },
				{ name: '出生地', text: `${record_res.province}${record_res.city}` },
				{ name: '性别', text: record_res.gender == 1 ? '男' : '女' }
			];
			this.tableData = zao;
			console.log('[generate] 排盘数据解析完成, tableData长度:', this.tableData?.length);
		},
		async loadResultData() {
			console.log('[generate] 开始请求 getResult');
			const requestStart = Date.now();
			
			// 使用version=2026获取二期扩展数据
			const res = await this.$api.post('api/si_zhu/getResult', {
				record_id: this.record_id,
				version: '2026'
			});
			
			console.log('[generate] getResult 响应耗时:', Date.now() - requestStart, 'ms');
			console.log('[generate] getResult 响应:', res?.code, res?.msg);
			
			if (res.code !== 1) {
				console.error('[generate] getResult 失败:', res);
				return;
			}

			const { record_res, wang_yun, xing_ge_max, xing_ge_min, binfu, shi_shen, shen_sha, fang_wei, cang_gan } = res.data;
			
			// 基础数据
			this.xingGeMin = xing_ge_min || {};
			this.xingGeMax = xing_ge_max || {};
			this.wangYun = wang_yun || {};
			
			// 禀赋数据
			if (binfu) {
				this.binfuData = binfu;
			}
			// 十神数据
			if (shi_shen) {
				this.liuNianShiShen = shi_shen.liu_nian_shi_shen || '';
				this.liuNianDesc = shi_shen.liu_nian_desc || '';
				this.liuNianShortDesc = shi_shen.liu_nian_short_desc || '';
				this.hasZuHe = shi_shen.has_zuhe || false;
				this.zuHeName = shi_shen.zuhe_name || '';
				this.zuHeDesc = shi_shen.zuhe_desc || '';
			}
			// 神煞数据
			if (shen_sha) {
				this.shenShaList = shen_sha || [];
			}
			// 方位数据
			if (fang_wei) {
				this.fangWeiData = fang_wei;
				console.log('[generate] 方位数据:', JSON.stringify(fang_wei));
				console.log('[generate] 事业位:', fang_wei.shiyeWei);
				console.log('[generate] 正财位:', fang_wei.zhengcaiWei);
				console.log('[generate] 偏财位:', fang_wei.piancaiWei);
				console.log('[generate] 贵人位:', fang_wei.guirenWei);
			} else {
				console.warn('[generate] 方位数据为空!');
			}
			// 藏干数据(用于排盘组件)
			if (cang_gan && this.tableData.length > 0) {
				cang_gan.forEach((item, index) => {
					if (this.tableData[index]) {
						this.tableData[index].cang_gan = item.cang_gan;
					}
				});
			}
			console.log('[generate] 结果数据解析完成');
		},
		switchTab(index) {
		// 查看模式可以自由切换,首次模式不允许跳转
		if (!this.isReview && index !== this.currentTab) {
			// 检查是否已查看过该页面
			if (!this.viewedPages.includes(index)) {
				this.$toast('请按顺序查看');
				return;
			}
		}
		this.currentTab = index;
	},
		onSwiperChange(e) {
		const targetIndex = e.detail.current;
		// 首次模式:防止用户通过滑动跳过页面
		if (!this.isReview && !this.viewedPages.includes(targetIndex)) {
			// 恢复到当前页面
			this.currentTab = this.currentTab;
			this.$toast('请按顺序查看');
			return;
		}
		this.currentTab = targetIndex;
	},
		prevTab() {
			if (this.currentTab > 0) {
				this.currentTab--;
			}
		},
		nextTab() {
		if (this.currentTab < this.tabs.length - 1) {
			// 记录已查看的页面
			if (!this.viewedPages.includes(this.currentTab)) {
				this.viewedPages.push(this.currentTab);
			}
			// 下一页自动标记为已查看
			const nextIndex = this.currentTab + 1;
			if (!this.viewedPages.includes(nextIndex)) {
				this.viewedPages.push(nextIndex);
			}
			this.currentTab++;
			console.log('[generate] 已查看页面:', this.viewedPages);
		}
	},
		goShare() {
		// 保存所有组件数据到localStorage
		const shareData = {
			// P5 排盘数据
			userInfo: this.userInfo,
			tableData: this.tableData,
			gender: this.gender,
			ganZhiDesc: this.ganZhiDesc,
			// P6 禀赋数据
			xingGeMin: this.xingGeMin,
			xingGeMax: this.xingGeMax,
			binfuData: this.binfuData,
			// P7 喜忌数据
			wangYun: this.wangYun,
			// P8 十神数据
			liuNianShiShen: this.liuNianShiShen,
			liuNianDesc: this.liuNianDesc,
			liuNianShortDesc: this.liuNianShortDesc,
			hasZuHe: this.hasZuHe,
			zuHeName: this.zuHeName,
			zuHeDesc: this.zuHeDesc,
			// P9 神煞数据
			shenShaList: this.shenShaList,
			// P10 方位数据
			fangWeiData: this.fangWeiData
		};
		
		uni.setStorageSync('share_result_data', shareData);
		console.log('[generate] 已保存分享数据到localStorage');
		
		const qrCodeUrl = uni.getStorageSync('app_parmas')?.qrCodeUrl || '';
		this.$go(`/pages/result/share?record_id=${this.record_id}&qrCodeUrl=${qrCodeUrl}`);
	}
}
};
</script>

<style lang="scss" scoped>
.page-bg {
	/* 使用红色祥云背景 */
	background-image: url(/static/beijing.jpg);
	background-size: cover;
	background-repeat: no-repeat;
	background-position: center center;
	background-color: #BF0000;
}

.page-overlay {
	background: rgba(0, 0, 0, 0.1);
}

.generate-page {
	min-height: 100vh;
	padding-top: 106rpx;
	position: relative;
}

/* 国风Tab栏 */
.tab-container {
	position: sticky;
	top: 0;
	z-index: 100;
	padding: 16rpx 0;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-bottom: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.3);
}

.tab-bar {
	display: flex;
	justify-content: flex-start; /* 改为左对齐,避免单个Tab时居中 */
	align-items: center;
	gap: 40rpx; /* 添加Tab之间的间距 */
	padding-left: 20rpx; /* 添加左侧padding */
}

.tab-item {
	padding: 12rpx 20rpx;
	font-size: 26rpx;
	color: #8B4513;
	font-weight: 500;
	position: relative;
	transition: all 0.3s ease;
}

.tab-item-active {
	color: #8B0000;
	font-weight: bold;
	
	&::after {
		content: '';
		position: absolute;
		bottom: -4rpx;
		left: 50%;
		transform: translateX(-50%);
		width: 60%;
		height: 4rpx;
		background: linear-gradient(90deg, transparent, #DAA520, transparent);
		border-radius: 2rpx;
	}
}

/* 禁用状态的Tab(首次模式下未查看的Tab) */
.tab-item-disabled {
	opacity: 0.4;
	pointer-events: none;
}

.content-swiper {
	height: calc(100vh - 106rpx - 100rpx - 140rpx);
}

.swiper-content {
	height: 100%;
	padding: 24rpx 30rpx;
}

/* 国风底部栏 */
.bottom-bar {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	padding: 20rpx 30rpx;
	background: linear-gradient(180deg, rgba(245, 230, 200, 0.95) 0%, #F5E6C8 100%);
	border-top: 2rpx solid #DAA520;
	box-shadow: 0 -4rpx 16rpx rgba(139, 105, 20, 0.25);
	z-index: 100;
}

/* 国风主按钮 - 印章风格 */
.btn-primary {
	background: linear-gradient(135deg, #8B0000 0%, #A22823 50%, #8B0000 100%);
	border: 3rpx solid #DAA520;
	border-radius: 12rpx;
	padding: 24rpx 60rpx;
	box-shadow: 
		0 4rpx 12rpx rgba(139, 0, 0, 0.4),
		inset 0 2rpx 4rpx rgba(255, 215, 0, 0.2);
	position: relative;
	/* 文字居中 */
	display: flex;
	align-items: center;
	justify-content: center;
	
	&::before {
		content: '';
		position: absolute;
		top: 4rpx;
		left: 4rpx;
		right: 4rpx;
		bottom: 4rpx;
		border: 1rpx solid rgba(255, 215, 0, 0.3);
		border-radius: 8rpx;
		pointer-events: none;
	}
	
	&:active {
		transform: scale(0.98);
		box-shadow: 0 2rpx 6rpx rgba(139, 0, 0, 0.3);
	}
}

.btn-primary-text {
	color: #FFD700;
	font-size: 32rpx;
	font-weight: bold;
	text-shadow: 1rpx 1rpx 2rpx rgba(0, 0, 0, 0.3);
}

/* 国风次要按钮 */
.btn-secondary {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border: 3rpx solid #DAA520;
	border-radius: 12rpx;
	padding: 24rpx 60rpx;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
	/* 文字居中 */
	display: flex;
	align-items: center;
	justify-content: center;
}

.btn-secondary-text {
	color: #8B0000;
	font-size: 32rpx;
	font-weight: bold;
}

.safe-area {
	height: env(safe-area-inset-bottom);
}
</style>
