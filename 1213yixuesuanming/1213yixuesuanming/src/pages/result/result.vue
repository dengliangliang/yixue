<template>
	<view class="page-bg">
		<view class="page-overlay min-h-screen">
			<view class="pt-50 px-30 pb-180">
				<!-- 顶部装饰图 -->
				<image src="/static/top-text.png" style="height: 106rpx;" class="po_ab t-0 l-0 w_100 zIndex-1" mode="aspectFill"></image>
				
				<!-- 标题区域 -->
				<view class="card-glass mb-24 animate__animated animate__fadeInDown">
					<view class="flex items-center">
						<image src="/static/jieguo/xingge.png" class="mr-16" style="width: 296rpx;height: 84rpx;" mode="aspectFit"></image>
					</view>
					<view class="mt-16">
						<text class="text-base text-gray-700">您的 </text>
						<text class="text-xl font-bold text-primary">性格剧本</text>
						<text class="text-base text-gray-700"> 已解码，根据推算</text>
					</view>
				</view>

				<!-- 描述文字 -->
				<view class="card-white mb-24 animate__animated animate__fadeInUp">
					<text class="text-sm text-gray-700">您性格中 </text>
					<text class="text-lg font-bold" :class="`c_${loadColor(xing_ge_min.wu_xing)}`">{{xing_ge_min.wu_xing}}</text>
					<text class="text-sm text-gray-700"> 元素数量最少，</text>
					<text class="text-lg font-bold" :class="`c_${loadColor(xing_ge_max.wu_xing)}`">{{xing_ge_max.wu_xing}}</text>
					<text class="text-sm text-gray-700"> 元素数量最多，由此推荐</text>
				</view>

				<!-- 优势卡片 -->
				<view class="mb-32 animate__animated animate__fadeInUp animate__delay-1s">
					<view class="flex items-center mb-16">
						<text class="title-secondary">您性格中 </text>
						<text class="title-secondary" style="color: #D4AF37;">优势</text>
					</view>
					
					<view class="card-gold po_re" :class="`${loadColor(xing_ge_min.wu_xing)}_back`">
						<image :src="`/static/wuxing/${loadColor(xing_ge_min.wu_xing)}-@2x.png`" class="wuxing_img"
							mode="aspectFill"></image>
						<view class="badge-gold mb-16" style="left: 110rpx;">最少</view>
						<text :class="`c_${loadColor(xing_ge_min.wu_xing)}`" class="text-base" style="line-height: 50rpx;">
							{{xing_ge_min.xing_result}}
						</text>
					</view>
				</view>

				<!-- 劣势卡片 -->
				<view class="animate__animated animate__fadeInUp animate__delay-2s">
					<view class="flex items-center mb-16">
						<text class="title-secondary">您性格中 </text>
						<text class="title-secondary" style="color: #666;">劣势</text>
					</view>
					
					<view class="card-gold po_re" :class="`${loadColor(xing_ge_max.wu_xing)}_back`">
						<image :src="`/static/wuxing/${loadColor(xing_ge_max.wu_xing)}-@2x.png`" class="wuxing_img"
							mode="aspectFill"></image>
						<view class="badge-outline mb-16" style="left: 110rpx;">最多</view>
						<text :class="`c_${loadColor(xing_ge_max.wu_xing)}`" class="text-base" style="line-height: 50rpx;">
							{{xing_ge_max.xing_result}}
						</text>
					</view>
				</view>
				
			</view>

			<!-- 底部按钮 -->
			<view class="po_fi b-0 l-0 w-full px-30 pb-30 pt-15 animate__animated animate__fadeInUp animate__delay-3s"
				style="background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,0.95) 20%, rgba(255,255,255,0.98) 100%);">
				<view class="btn-primary" @click="$go('/pages/result/miji?record_id='+record_id)">
					<text class="btn-primary-text">下一页</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				record_id: '',
				info: {},
				xing_ge_max: {},
				xing_ge_min: {},
			}
		},
		computed: {

		},
		onLoad({
			record_id
		}) {
			this.record_id = record_id;
			this.loadData();
		},
		methods: {
			async loadData() {
				// 优先使用预加载的缓存数据
				const cacheKey = 'preload_resultRes_' + this.record_id;
				let res = uni.getStorageSync(cacheKey);
				
				if (res && res.code == 1) {
					console.log('[result] 使用预加载缓存数据');
					// 不清除缓存，miji页面还需要用
				} else {
					console.log('[result] 缓存未命中，重新请求');
					res = await this.$api.post('api/si_zhu/getResult', {
						record_id: this.record_id
					});
				}
				
				if (res.code != 1) return this.$toast(res.msg);
				this.info = res.data;
				this.xing_ge_max = res.data.xing_ge_max;
				this.xing_ge_min = res.data.xing_ge_min;
			},
			loadColor(type) {
				let color_;
				switch (type) {
					case '金':
						color_ = 'jin';
						break;
					case '木':
						color_ = 'mu';
						break;
					case '水':
						color_ = 'shui';
						break;
					case '火':
						color_ = 'huo';
						break;
					case '土':
						color_ = 'tu';
						break;
				}
				return color_
			}
		}
	}
</script>

<style scoped>
	/* 五行图片样式 */
	.wuxing_img {
		position: absolute;
		top: 0;
		right: 0;
		width: 200rpx;
		height: 200rpx;
		opacity: 0.15;
		z-index: 1;
	}
	
	/* 五行背景色 - 保持原有颜色逻辑 */
	.jin_back {
		background: rgba(255, 215, 0, 0.1);
	}
	
	.mu_back {
		background: rgba(34, 139, 34, 0.1);
	}
	
	.shui_back {
		background: rgba(30, 144, 255, 0.1);
	}
	
	.huo_back {
		background: rgba(255, 69, 0, 0.1);
	}
	
	.tu_back {
		background: rgba(160, 82, 45, 0.1);
	}
</style>