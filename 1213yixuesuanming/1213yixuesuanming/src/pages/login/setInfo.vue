<template>
	<view class="content" :style="'height:'+windowHeight+'px'">
		<!-- 祥云动效背景 -->
		<view class="cloud-container">
			<view class="cloud cloud-1"></view>
			<view class="cloud cloud-2"></view>
			<view class="cloud cloud-3"></view>
		</view>
		
		<!-- 动态背景粒子效果 -->
		<view class="particles-bg">
			<view v-for="i in 20" :key="i" class="particle" :style="getParticleStyle(i)"></view>
		</view>
		
		<view class="box_100 ov_y">
			<!-- 古典卷轴风格表单卡片 -->
			<view class="scroll-card animate-fade-in">
				<!-- 卷轴顶部装饰 -->
				<view class="scroll-top"></view>
				<!-- 卷轴内容区 -->
				<view class="scroll-content">
					<!-- 角花装饰 -->
					<view class="corner-decor corner-tl"></view>
					<view class="corner-decor corner-tr"></view>
					<view class="corner-decor corner-bl"></view>
					<view class="corner-decor corner-br"></view>
					
					<!-- 生辰选择 -->
					<view @click="is_show?$refs.calendar.isShowDateMask =true:''"
						class="guofeng-row animate-slide-up" style="animation-delay: 0.1s;">
						<view class="guofeng-icon">
							<text class="icon-text">辰</text>
						</view>
						<view class="guofeng-content">
							<text class="guofeng-label">出生时间</text>
							<view class="guofeng-value">
								<text class="value-num">{{nian || '————'}}</text>
								<text class="value-char">年</text>
								<text class="value-num">{{yue || '——'}}</text>
								<text class="value-char">月</text>
								<text class="value-num">{{ri || '——'}}</text>
								<text class="value-char">日</text>
								<text class="value-num">{{shi || '——'}}</text>
								<text class="value-char">时</text>
								<text class="value-num">{{fen || '——'}}</text>
								<text class="value-char">分</text>
							</view>
						</view>
						<view class="guofeng-arrow">❯</view>
					</view>

					<!-- 出生地选择 -->
					<view @click="isCityShow=true" class="guofeng-row animate-slide-up" style="animation-delay: 0.2s;">
						<view class="guofeng-icon">
							<text class="icon-text">地</text>
						</view>
						<view class="guofeng-content">
							<text class="guofeng-label">出生地区</text>
							<view class="guofeng-value">
								<text class="value-num">{{province || '请选择'}}</text>
								<text class="value-char" v-if="province">省</text>
								<text class="value-num" v-if="city">{{city}}</text>
								<text class="value-char" v-if="city">市</text>
							</view>
						</view>
						<view class="guofeng-arrow">❯</view>
					</view>

					<!-- 性别选择 -->
					<view class="guofeng-row gender-row animate-slide-up" style="animation-delay: 0.3s;">
						<view class="guofeng-icon">
							<text class="icon-text">乾坤</text>
						</view>
						<view class="guofeng-content">
							<text class="guofeng-label">性别</text>
							<view class="guofeng-gender">
								<view v-for="i,k in xb_list" :key="k" 
									@click="xb_che=i" 
									class="gender-seal"
									:class="{'seal-active': xb_che==i}">
									<text class="seal-text">{{i}}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
				<!-- 卷轴底部装饰 -->
				<view class="scroll-bottom"></view>
				
				<!-- 提交按钮 - 印章风格 -->
				<view @click="useSubmit()" class="seal-btn animate-slide-up" style="animation-delay: 0.4s;">
					<view class="seal-inner">
						<text class="seal-btn-text">立即获取</text>
					</view>
				</view>
			</view>

			<view class="disclaimer animate-fade-in" style="animation-delay: 0.5s;">
				* 尊重传统文化，仅供娱乐，相信科学
			</view>

		</view>
		
		<myPicker ref="calendar" @chushihua="(e)=>is_show=e" @confirm="confirm" />
		
		<!-- 自定义省市选择弹窗（替代u-picker） -->
		<view v-if="isCityShow" class="city-picker-mask" @click="isCityShow=false">
			<view class="city-picker-container" @click.stop>
				<!-- 标题栏 -->
				<view class="city-picker-header">
					<text class="city-picker-cancel" @click="isCityShow=false">取消</text>
					<text class="city-picker-title">选择地址</text>
					<text class="city-picker-confirm" @click="confirmCity">确定</text>
				</view>
				<!-- 选择器 -->
				<view class="city-picker-body">
					<picker-view :value="pickerValue" @change="onPickerChange" class="city-picker-view">
						<!-- 省份列 -->
						<picker-view-column>
							<view v-for="(item, index) in province_list" :key="index" class="picker-item">
								{{item.name}}
							</view>
						</picker-view-column>
						<!-- 城市列 -->
						<picker-view-column>
							<view v-for="(item, index) in city_list" :key="index" class="picker-item">
								{{item.name}}
							</view>
						</picker-view-column>
					</picker-view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import myPicker from '@/components/my-picker.vue';
	export default {
		components: {
			myPicker,
		},
		data() {
			return {
				is_show: false,
				isShowCalendar: false,
				isCityShow: false,
				isLoading: false,
				windowHeight: '',
				nian: '',
				yue: '',
				ri: '',
				shi: '',
				fen: '',
				xb_list: ['男', '女'],
				xb_che: '男',
				province: '',
				city: '',
				province_list: [],
				city_list: [],
				columns: [],
				city_id: '',
				pickerValue: [0, 0]
			}
		},
		onReady() {
			this.loadData()
		},
		onLoad(op) {
			if (op.sign) uni.setStorageSync('app_parmas', op)
		},
		methods: {
			async loadData() {
				const {
					windowHeight
				} = await uni.getSystemInfo();
				this.windowHeight = windowHeight;
				
				// 优先使用预加载的省份缓存
				let provinceData = uni.getStorageSync('cache_provinceList');
				if (provinceData && provinceData.length > 0) {
					console.log('[setInfo] 使用预加载的省份缓存');
					this.province_list = provinceData;
				} else {
					console.log('[setInfo] 缓存未命中，重新请求省份');
					const res = await this.$api.post('api/user/getProvinceList');
					this.province_list = res.data;
					provinceData = res.data;
				}
				
				// 添加空值检查，防止数据为空时报错
				if (provinceData && provinceData.length > 0) {
					this.loadCity(provinceData[0].id);
				}
			},
			async loadCity(id) {
				// 优先使用城市缓存
				const cacheKey = 'cache_cityList_' + id;
				let cityData = uni.getStorageSync(cacheKey);
				
				if (cityData && cityData.length > 0) {
					console.log('[setInfo] 使用城市缓存');
					this.city_list = cityData;
					return;
				}
				
				const {
					data,
					code,
					msg
				} = await this.$api.post('api/user/getCityList', {
					id
				});
				// 缓存城市数据
				if (data && data.length > 0) {
					uni.setStorageSync(cacheKey, data);
				}
				this.city_list = data || [];
			},
			async onPickerChange(e) {
				const val = e.detail.value || [];
				const provinceIndex = val[0] || 0;
				const cityIndex = val[1] || 0;
				
				// 如果省份变化，重新加载城市并重置城市索引
				if (provinceIndex !== this.pickerValue[0]) {
					const selectedProvince = this.province_list[provinceIndex];
					if (selectedProvince) {
						this.loadCity(selectedProvince.id);
						this.pickerValue = [provinceIndex, 0];
					}
				} else {
					this.pickerValue = [provinceIndex, cityIndex];
				}
			},
			confirmCity() {
				const provinceIndex = this.pickerValue[0];
				const cityIndex = this.pickerValue[1];
				
				const selectedProvince = this.province_list[provinceIndex];
				const selectedCity = this.city_list[cityIndex];
				
				if (selectedProvince && selectedCity) {
					this.province = selectedProvince.name;
					this.city = selectedCity.name;
					this.city_id = selectedCity.id;
					console.log('[setInfo] 选择地址:', this.province, this.city);
				}
				this.isCityShow = false;
			},
			changeHandler(e) {
				if (e.value[0].id) this.loadCity(e.value[0].id);
			},
			city_confirm(e) {
				console.log('选择的地址是---', e);
				// this.location = e
				e = e.value;
				this.province = e[0].name;
				this.city = e[1].name;
				this.city_id = e[1].id;
				this.isCityShow = false;
			},
			/** 
			 * @param {Object} e
			 */
			confirm(e) {
				console.log(e);
				this.loc_date = e;
				this.nian = e.year;
				this.yue = e.month;
				this.ri = e.day;
				this.shi = e.hour;
				this.fen = e.minute;
			},
			async useSubmit() {
				// 参数验证
				if (!this.city_id) {
					return this.$toast('请选择出生地');
				}
				if (!this.nian || !this.yue || !this.ri) {
					return this.$toast('请选择出生日期');
				}
				
				// 准备表单数据
				let app_parmas = uni.getStorageSync('app_parmas') || {}
				const formData = {
					...app_parmas,
					hour: Number(this.shi),
					minute: Number(this.fen),
					gender: this.xb_che == '男' ? 1 : 0,
					area_id: this.city_id,
					date: `${this.nian}-${this.yue}-${this.ri}`,
					loc_date: this.loc_date
				};
				
				// 保存表单数据到临时存储，由 loading.vue 发起请求
				uni.setStorageSync('pending_form_data', formData);
				console.log('[setInfo] 表单数据已保存，跳转到加载页面');
				
				// 立即跳转到加载页面（烟雾动画），API 请求由 loading.vue 完成
				uni.navigateTo({
					url: "/pages/login/loading"
				});
			},
			// 计算并缓存结果（等待完成，避免重复请求）
			//  计算并缓存结果（等待完成，避免重复请求）
			async calcAndCache(record_id) {
				const calcStart = Date.now();
				console.log('[setInfo] 开始计算, record_id:', record_id);
				
				try {
					// 并行请求两个API
					const [siZhuRes, resultRes] = await Promise.all([
						this.$api.post('api/si_zhu/getSiZhuRes', { record_id }),
						this.$api.post('api/si_zhu/getResult', { record_id })
					]);
					
					console.log('[setInfo] 计算完成, 耗时:', Date.now() - calcStart, 'ms');
					
					// 缓存到本地存储，5分钟有效
					uni.setStorageSync('calc_cache_' + record_id, {
						record_id,
						siZhuData: siZhuRes.data,
						resultData: resultRes.data,
						timestamp: Date.now()
					});
					
					console.log('[setInfo] 结果已缓存');
					return true;
				} catch (e) {
					console.error('[setInfo] 计算失败', e);
					// 计算失败也继续跳转，gossip页面会重新请求
					return false;
				}
			},
			
			// ⚠️ 已废弃：后台触发计算（不阻塞UI）
			triggerBackgroundCalc(record_id) {
				console.log('[setInfo] 触发后台计算, record_id:', record_id);
				this.$api.post('api/si_zhu/getSiZhuRes', { record_id }).then(res => {
					console.log('[setInfo] 后台计算完成 getSiZhuRes');
				}).catch(e => {
					console.log('[setInfo] 后台计算失败', e);
				});
			},
			// 生成粒子样式
			getParticleStyle(index) {
				const size = Math.random() * 8 + 4;
				const left = Math.random() * 100;
				const delay = Math.random() * 5;
				const duration = Math.random() * 10 + 10;
				return {
					width: size + 'rpx',
					height: size + 'rpx',
					left: left + '%',
					animationDelay: delay + 's',
					animationDuration: duration + 's'
				};
			}
		}
	}
</script>

<style lang="scss">
	/* ========== 祥云动效 - 引入外部样式（必须在最开头） ========== */
	@import '@/styles/cloud-animation.scss';

	/* ========== 自定义字体 ========== */
	@font-face {
		font-family: 'QianTuXianMo';
		src: url('https://cdn.yixuestatic.linqingkeji.com/src/static/ttf/千图纤墨体.ttf') format('truetype');
		font-weight: normal;
		font-style: normal;
	}

	page {
		/* 使用设置信息页专属背景 */
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/setinfo.png);
		background-size: cover;
		background-repeat: no-repeat;
		background-position: center;
		background-attachment: fixed;
		/* 背景色改为更贴合新背景的颜色 */
		background-color: #f7f3e8;
		
		/* 应用自定义字体 */
		font-family: 'QianTuXianMo', sans-serif;
	}

	.content {
		width: 100%;
		overflow: hidden;
		position: relative;
		
		/* 自适应布局容器 - 覆盖 box_100 默认样式 */
		.box_100 {
			display: flex !important;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			min-height: 100%;
			padding: 40rpx 0;
			box-sizing: border-box;
		}
	}

	/* 动画类 */
	.animate-fade-in {
		animation: fadeIn 0.8s ease-out forwards;
		opacity: 0;
	}

	.animate-slide-up {
		animation: slideUp 0.6s ease-out forwards;
		z-index: 10;
	}

	@keyframes fadeIn {
		from { opacity: 0; }
		to { opacity: 1; }
	}

	@keyframes slideUp {
		from { opacity: 0; transform: translateY(30rpx); }
		to { opacity: 1; transform: translateY(0); }
	}

	/* ========== 古典卷轴风格 ========== */
	.scroll-card {
		width: 680rpx;
		position: fixed; /* 固定定位 */
		top: 50%; /* 垂直居中 */
		left: 50%; /* 水平居中 */
		transform: translate(-50%, -50%); /* 精确居中 */
		z-index: 10;
		overflow: hidden; /* 防止内容溢出导致滚动 */
	}
	
	/* 卷轴顶部轴杆 - 圆柱形 */
	.scroll-top {
		height: 32rpx;
		background: linear-gradient(180deg, 
			#D4A574 0%, 
			#C4956A 20%, 
			#8B6914 50%, 
			#C4956A 80%, 
			#D4A574 100%
		);
		border-radius: 16rpx;
		box-shadow: 
			0 6rpx 12rpx rgba(0, 0, 0, 0.4),
			inset 0 4rpx 8rpx rgba(255, 255, 255, 0.3),
			inset 0 -2rpx 4rpx rgba(0, 0, 0, 0.2);
		position: relative;
		z-index: 2;
		
		/* 轴杆两端圆头 */
		&::before, &::after {
			content: '';
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			width: 48rpx;
			height: 48rpx;
			background: radial-gradient(circle at 35% 35%, 
				#FFD700 0%, 
				#DAA520 40%, 
				#B8860B 70%, 
				#8B6914 100%
			);
			border-radius: 50%;
			box-shadow: 
				2rpx 2rpx 6rpx rgba(0, 0, 0, 0.4),
				inset 2rpx 2rpx 4rpx rgba(255, 255, 255, 0.4);
		}
		&::before { left: -8rpx; }
		&::after { right: -8rpx; }
	}
	
	/* 卷轴底部轴杆 - 圆柱形 */
	.scroll-bottom {
		height: 32rpx;
		background: linear-gradient(180deg, 
			#D4A574 0%, 
			#C4956A 20%, 
			#8B6914 50%, 
			#C4956A 80%, 
			#D4A574 100%
		);
		border-radius: 16rpx;
		box-shadow: 
			0 6rpx 12rpx rgba(0, 0, 0, 0.4),
			inset 0 4rpx 8rpx rgba(255, 255, 255, 0.3);
		position: relative;
		z-index: 2;
		
		&::before, &::after {
			content: '';
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			width: 48rpx;
			height: 48rpx;
			background: radial-gradient(circle at 35% 35%, 
				#FFD700 0%, 
				#DAA520 40%, 
				#B8860B 70%, 
				#8B6914 100%
			);
			border-radius: 50%;
			box-shadow: 
				2rpx 2rpx 6rpx rgba(0, 0, 0, 0.4),
				inset 2rpx 2rpx 4rpx rgba(255, 255, 255, 0.4);
		}
		&::before { left: -8rpx; }
		&::after { right: -8rpx; }
	}
	
	/* 卷轴内容区 - 使用卷轴图片背景 */
	.scroll-content {
		/* 使用下载的卷轴背景 - scroll-bg-6.jpg (由用户指定) */
		background-image: url(https://cdn.yixuestatic.linqingkeji.com/src/static/scroll-bg-6.jpg);
		background-size: 100% 100%;
		background-repeat: no-repeat;
		background-position: center;
		
		/* 减少宽度,让木棍露出更多 */
		width: 90%; /* 宽度为父容器的90% */
		margin: 0 auto; /* 居中显示 */
		
		/* 调整内边距,确保内容在卷轴正中的纸张区域 */
		padding: 100rpx 60rpx 100rpx;
		position: relative;
		z-index: 15;
		/* 确保完全不透明以遮挡底层内容 */
		background-color: #F5E6C8;
		opacity: 1;
		
		/* 保持原有的外部阴影,增强立体感 */
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.3);
		
		/* 移除旧的CSS渐变和纹理,改用图片实现 */
	}
	
	/* 角花装饰 - 金色 */
	.corner-decor {
		position: absolute;
		width: 40rpx;
		height: 40rpx;
		border: 3rpx solid #DAA520;
		z-index: 5;
		
		&.corner-tl { top: 16rpx; left: 16rpx; border-right: none; border-bottom: none; }
		&.corner-tr { top: 16rpx; right: 16rpx; border-left: none; border-bottom: none; }
		&.corner-bl { bottom: 16rpx; left: 16rpx; border-right: none; border-top: none; }
		&.corner-br { bottom: 16rpx; right: 16rpx; border-left: none; border-top: none; }
	}
	
	/* 国风输入行 */
	.guofeng-row {
		display: flex;
		align-items: center;
		padding: 24rpx 20rpx;
		margin-bottom: 20rpx;
		/* 使用不透明背景确保可见 */
		background: #FFFEF9;
		border: 2rpx solid #D4A574;
		border-radius: 8rpx;
		transition: all 0.3s ease;
		position: relative;
		z-index: 5;
		
		&:active {
			background: #FFF8DC;
		}
	}
	
	/* 国风图标 */
	.guofeng-icon {
		width: 64rpx;
		height: 64rpx;
		background: linear-gradient(135deg, #8B0000 0%, #A22823 100%);
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 20rpx;
		
		.icon-text {
			font-size: 24rpx;
			color: #FFD700;
			font-weight: bold;
		}
	}
	
	.guofeng-content { flex: 1; }
	
	.guofeng-label {
		font-size: 28rpx; /* 增大字体 */
		color: #8B4513;
		display: block;
		margin-bottom: 6rpx;
	}
	
	.guofeng-value {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
	}
	
	.value-num {
		font-size: 30rpx;
		font-weight: 600;
		color: #4A3728;
		margin-right: 2rpx;
	}
	
	.value-char {
		font-size: 24rpx;
		color: #8B4513;
		margin-right: 12rpx;
	}
	
	.guofeng-arrow {
		font-size: 32rpx;
		color: #B8860B;
	}
	
	/* 性别选择 */
	.gender-row { margin-bottom: 20rpx; }
	
	.guofeng-gender {
		display: flex;
		gap: 90rpx;
	}
	
	.gender-seal {
		width: 80rpx;
		height: 80rpx;
		border: 3rpx solid #8B4513;
		border-radius: 8rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		background: #FFF8DC;
		transition: all 0.3s ease;
		
		.seal-text {
			font-size: 32rpx;
			color: #8B4513;
			font-weight: bold;
		}
		
		&.seal-active {
			background: linear-gradient(135deg, #8B0000 0%, #A22823 100%);
			border-color: #8B0000;
			
			.seal-text { color: #FFD700; }
		}
	}
	
	/* 印章按钮 */
	.seal-btn {
		width: 320rpx;
		height: 100rpx;
		margin: 30rpx auto 0;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #8B0000 0%, #A22823 50%, #8B0000 100%);
		border-radius: 12rpx;
		border: 4rpx solid #DAA520;
		box-shadow: 0 8rpx 20rpx rgba(139, 0, 0, 0.5);
		transition: all 0.3s ease;
		
		&:active {
			transform: scale(0.96);
		}
	}
	
	.seal-inner {
		border: 2rpx solid rgba(255, 215, 0, 0.5);
		border-radius: 8rpx;
		padding: 12rpx 40rpx;
	}
	
	.seal-btn-text {
		font-size: 34rpx;
		font-weight: bold;
		color: #FFD700;
		letter-spacing: 8rpx;
	}
	
	/* 免责声明 */
	.disclaimer {
		position: fixed; /* 固定定位 */
		bottom: 30rpx; /* 距离底部30rpx */
		left: 50%; /* 水平居中 */
		transform: translateX(-50%); /* 精确居中 */
		text-align: center;
		font-size: 24rpx;
		color: #FFF1F1;
		padding: 16rpx 32rpx;
		background: rgba(0, 0, 0, 0.2);
		border-radius: 40rpx;
		z-index: 20; /* 确保在最上层 */
	}

	/* 省市选择弹窗样式 */
	.city-picker-mask {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 9999;
		display: flex;
		align-items: flex-end;
		justify-content: center;
	}

	.city-picker-container {
		width: 100%;
		background: #fff;
		border-radius: 24rpx 24rpx 0 0;
		overflow: hidden;
		animation: slideUpPicker 0.3s ease-out;
	}

	@keyframes slideUpPicker {
		from {
			transform: translateY(100%);
		}
		to {
			transform: translateY(0);
		}
	}

	.city-picker-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 24rpx 32rpx;
		border-bottom: 1rpx solid #eee;
		background: #FFF1F1;
	}

	.city-picker-cancel {
		font-size: 28rpx;
		color: #999;
		padding: 10rpx 20rpx;
	}

	.city-picker-title {
		font-size: 32rpx;
		font-weight: 600;
		color: #333;
	}

	.city-picker-confirm {
		font-size: 28rpx;
		color: #D0000F;
		font-weight: 600;
		padding: 10rpx 20rpx;
	}

	.city-picker-body {
		height: 480rpx;
		background: #fff;
	}

	.city-picker-view {
		height: 100%;
	}

	.picker-item {
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 32rpx;
		color: #333;
		height: 80rpx;
		line-height: 80rpx;
	}
	
	/* ========== 自定义加载动画样式 ========== */
	.custom-loading-mask {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #fefdf8;
		z-index: 9999;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	
	/* 祥云点缀 */
	.loading-cloud {
		position: absolute;
		opacity: 0.6;
		filter: drop-shadow(0 2rpx 4rpx rgba(0, 0, 0, 0.1));
	}
	.loading-cloud-tl {
		width: 200rpx;
		top: 60rpx;
		left: -30rpx;
	}
	.loading-cloud-tr {
		width: 180rpx;
		top: 80rpx;
		right: -20rpx;
		transform: scaleX(-1);
	}
	.loading-cloud-bl {
		width: 220rpx;
		bottom: 100rpx;
		left: -40rpx;
	}
	.loading-cloud-br {
		width: 200rpx;
		bottom: 80rpx;
		right: -30rpx;
		transform: scaleX(-1);
	}
	
	/* 加载内容区 */
	.loading-content {
		position: relative;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		width: 280rpx;
		/* 移除固定高度,让图片自然显示 */
	}
	
	/* 旋转加载图标 - 参考b0.png方法 */
	.loading-spinner {
		background: url(https://cdn.yixuestatic.linqingkeji.com/src/static/donhuajiazai.png);
		background-repeat: no-repeat;
		background-size: cover; /* 关键:cover模式填充容器 */
		width: 280rpx;
		height: 280rpx; /* 正方形容器 */
		animation: spin-precise 2s linear infinite;
		/* 不设置transform-origin和background-position,默认就是中心旋转 */
		border: none; /* 去除边框 */
		box-shadow: none; /* 去除阴影 */
		outline: none; /* 去除轮廓 */
	}
	
	@keyframes spin-precise {
		from {
			transform: rotate(0deg);
		}
		to {
			transform: rotate(360deg);
		}
	}
	
	/* 加载文字 */
	.loading-text {
		position: absolute;
		bottom: -60rpx;
		font-size: 32rpx;
		color: #8B6914;
		letter-spacing: 4rpx;
		font-weight: 500;
	}
</style>