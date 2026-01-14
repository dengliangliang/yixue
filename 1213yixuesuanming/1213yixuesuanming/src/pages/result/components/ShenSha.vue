<template>
	<!-- P9: 神煞组件 -->
	<view class="shensha-container">
		<!-- 顶部祥云装饰 -->
		<view class="xiuwen-decoration">
			<image src="/static/xiuwen.jpg" mode="aspectFill" class="xiuwen-img"></image>
		</view>
		<view class="section-title">
			<view class="title-bar"></view>
			<text class="fz_b fz_36">2026年神煞</text>
		</view>

		<!-- 神煞列表 -->
		<view v-if="shenShaList.length > 0" class="shensha-list">
			<view class="shensha-intro fz_28 mb-24">
				2026年是您的
				<text v-for="(item, index) in shenShaList" :key="index" class="shensha-tag">
					{{ item.name }}<text v-if="index < shenShaList.length - 1">、</text>
				</text>
				年
			</view>

			<view v-for="(item, index) in shenShaList" :key="index" class="shensha-item mb-20">
				<view class="shensha-header">
					<view class="shensha-badge" :class="getBadgeClass(item.name)">
						{{ item.name }}
					</view>
					<view class="shensha-type fz_24">{{ getTypeText(item.name) }}</view>
				</view>
				<view class="shensha-desc fz_28">{{ item.description }}</view>
			</view>
		</view>

		<!-- 无神煞 -->
		<view v-else class="no-shensha">
			<image src="/static/icon-empty.png" class="empty-icon" mode="aspectFill"></image>
			<text class="fz_28 c_9">2026年您暂无特殊神煞</text>
		</view>
	</view>
</template>

<script>
export default {
	name: 'ShenSha',
	props: {
		// 神煞列表 [{name: '贵人', description: '...'}, ...]
		shenShaList: {
			type: Array,
			default: () => []
		}
	},
	methods: {
		getBadgeClass(name) {
			// 根据神煞类型返回不同样式
			const goodSha = ['贵人', '禄神', '桃花', '文昌', '将星'];
			const badSha = ['羊刃'];
			if (goodSha.includes(name)) return 'badge-good';
			if (badSha.includes(name)) return 'badge-caution';
			return 'badge-normal';
		},
		getTypeText(name) {
			const typeMap = {
				'贵人': '财运事业贵人',
				'禄神': '财运亨通',
				'桃花': '人缘名声',
				'羊刃': '注意事项',
				'文昌': '学习提升',
				'将星': '事业突出'
			};
			return typeMap[name] || '';
		}
	}
};
</script>

<style lang="scss" scoped>
.shensha-container {
	width: 100%;
	padding: 24rpx;
	background: rgba(245, 230, 200, 0.85); /* 增加背景颜色衬托文字 */
	border-radius: 20rpx;
	box-sizing: border-box;
}

/* 国风标题样式 */
.section-title {
	display: flex;
	align-items: center;
	margin-bottom: 24rpx;
	padding: 16rpx 24rpx;
	background: linear-gradient(90deg, rgba(139, 0, 0, 0.1) 0%, transparent 100%);
	border-left: 6rpx solid #8B0000;
	border-radius: 0 8rpx 8rpx 0;

	.title-bar {
		display: none;
	}
}

.shensha-intro {
	line-height: 48rpx;
	color: #4A3728;
	padding: 16rpx 24rpx;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	border: 2rpx solid #DAA520;
}

.shensha-tag {
	color: #8B0000;
	font-weight: bold;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

.shensha-list {
	width: 100%;
}

/* 国风神煞卡片 */
.shensha-item {
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	padding: 24rpx;
	border: 2rpx solid #DAA520;
	box-shadow: 0 4rpx 12rpx rgba(139, 105, 20, 0.2);
}

.shensha-header {
	display: flex;
	align-items: center;
	margin-bottom: 16rpx;
}

.shensha-badge {
	padding: 8rpx 24rpx;
	border-radius: 30rpx;
	color: #fff;
	font-size: 28rpx;
	font-weight: bold;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
	margin-right: 16rpx;
}

.badge-good {
	background: linear-gradient(135deg, #D0000F 0%, #FF4444 100%);
}

.badge-caution {
	background: linear-gradient(135deg, #FF9800 0%, #FFC107 100%);
}

.badge-normal {
	background: linear-gradient(135deg, #666 0%, #999 100%);
}

.shensha-type {
	color: #8B4513;
}

.shensha-desc {
	line-height: 48rpx;
	color: #4A3728;
	font-family: 'QianTuXianMo', 'Microsoft YaHei', sans-serif; /* 千图纤墨体 */
}

.no-shensha {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 60rpx 0;
	background: linear-gradient(180deg, #F5E6C8 0%, #EDD9B5 100%);
	border-radius: 12rpx;
	border: 2rpx solid #DAA520;

	.empty-icon {
		width: 120rpx;
		height: 120rpx;
		margin-bottom: 20rpx;
		opacity: 0.5;
	}
}

/* 祥云装饰样式 */
.xiuwen-decoration {
	width: 100%;
	height: 120rpx;
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
</style>
