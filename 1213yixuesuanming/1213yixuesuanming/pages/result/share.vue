<template>
	<view class="max_page" :style="'min-height:'+windowHeight+'px'">
		<view class="bom_box po_fi b-0 l-0 flex_column jc_b">
			<view class="w_100">
				<view class="c_f text-align-center mb-28">
					中信保诚更多精彩活动，请关注下方二维码
				</view>
				<view class="w_100 flex_auto px-50">
					<view class="flex_column fz_24 c_f">
						<image src="/static/erweima.png" class="erweima_img" mode="aspectFill"></image>
						- 视频号 -
					</view>
					<view class="flex_column fz_24 c_f">
						<image src="/static/douyin.png" class="erweima_img" mode="aspectFill"></image>
						- 抖音号 -
					</view>
				</view>
			</view>
			<view class="w_100">
				<view class="w_100 px-15 flex_between pb-30">
					<view @click="$go('/pages/login/setInfo')" class="bom_btn">
						重新测算
					</view>
					<!-- ;$refs.popups.open('bottom') -->
					<view @click="()=>{$refs.popup.open('center')}" class="bom_btns flex_center">
						分享
					</view>
				</view>
				<view class="bom_h"> </view>
			</view>
		</view>
		<l-qrcode style="opacity: 0;" ref="qrcodeRef" :value="qr_code" />
		<uni-popup ref="popup">
			<view class="w_100 flex_center round-8">
				<hz-poster-maker ref="myCanvas" v-if="posterInfo" :width="618" :height="1002"
					:posterInfo="posterInfo"></hz-poster-maker>
			</view>
		</uni-popup>
		<uni-popup borderRadius="20px 20px 0 0" :is-mask-click="false" mask-background-color="rgba(0,0,0,0)"
			ref="popups" background-color="#fff">
			<!-- <view class="share-pro-dialog round-10">
				<view class="fz_32 pt-24 pl-32 fz_500">
					分享到
				</view>
				<view class="w_100 flex a_c pt-20 pl-48 pb-32 border-b-1">
					<button open-type="share" class="flex_column" @tap="useShare(1)">
						<image src="/static/share-wx.png" class="shaicon" mode="aspectFill"></image>
						<view class="fz_24 mt-16 text-align-center">微信好友</view>
					</button>
					<view class="flex_column ml-50 pl-24" @tap="useShare(2)">
						<image src="/static/pyq.png" class="shaicon" mode="aspectFill"></image>
						<view class="fz_24 mt-16">朋友圈</view>
					</view>
				</view>
				<view @click="()=>{$refs.popups.close();$refs.popup.close()}" class="w_100 py-24 flex_column c_9">
					取消
					<view class="bom_h mt-30"> </view>
				</view>
			</view> -->
			<view class="flex_center py-30">
				<view @click="useSaveImg()" class="bom_btn_download fz_32 po_re">
					保存本地相册后可转发
				</view>
				<view class="bom_h mt-50 pt-50"> </view>
			</view>
		</uni-popup>

		<uni-popup :is-mask-click="false" mask-background-color="rgba(0,0,0,0)" ref="popups_wx" background-color="#fff">
			<view style="height: 100vh;width: 100vw;background-color: rgba(25, 25, 25,0.4);">
				<image src="/static/down.png" class="box_100" mode=""></image>
			</view>
		</uni-popup>
	</view>
</template>
<script>
	import {
		share,
		download
	} from "@/common/hooks/app_fn.js";
	import HzPosterMaker from '@/components/hz-poster-maker/hz-poster-maker.vue'
	import {
		NodeType,
		ImageSharp,
		PositionType
	} from '@/components/hz-poster-maker/constant.js'
	export default {
		components: {
			HzPosterMaker
		},
		data() {
			return {
				record_id: '',
				info: {},
				show: false,

				posterWidth: 618,
				posterHeight: 1002,
				posterInfo: null,
				max_height: 0,
				windowHeight: 0,
				qr_code: '',
				qr_image: '/static/erweima.png'
			}
		},
		onReady() {
			uni.getSystemInfo({
				success: res => {
					// console.log(res)
					this.windowHeight = res.windowHeight
				}
			});
		},
		onLoad({
			record_id,
			qrCodeUrl
		}) {
			this.record_id = record_id;
			this.qr_code = uni.getStorageSync('app_parmas').qrCodeUrl || qrCodeUrl;
			this.loadData();
		},
		methods: {
			useSaveImg() {
				this.$refs['myCanvas'].useSave();
				// let en = window.navigator.userAgent.toLowerCase()
				// 匹配en中是否含有MicroMessenger字符串
				// if (en.match(/MicroMessenger/i) == 'micromessenger') {
				// 	this.$refs['popups_wx'].open('center');
				// }
				// if (en.match(/MicroMessenger/i) != 'micromessenger') {
				// 	console.log('myCanvas---', this.$refs['myCanvas']);
				// 	this.$refs['myCanvas'].useSave();
				// }
			},
			initPosterInfo() {
				// 1.0.2支持
				this.posterInfo = {
					// background: '#FFFFFF',
					// fontFamily: 'SERIF, Arial, sans-serif',
					nodes: [{
							key: 'backgroundImage',
							type: NodeType.Image,
							url: '/static/share-pop.png',
							sharp: ImageSharp.Square,
							width: 618,
							height: 1002,
							position: PositionType.Absolute,
							top: 0,
							left: 0
						},
						{
							key: 'contactLabel',
							type: NodeType.Text,
							text: '快来获取您的2025好消息吧~',
							textGap: 10,
							color: '#FFE89C',
							breakWord: true,
							fontSize: 30,
							position: PositionType.Absolute,
							relativeKey: 'jobLabel',
							// left: 32,
							// top: 770
							top: 826,
							left: 20,
						},
						{
							key: 'qr',
							type: NodeType.Image,
							url: this.qr_image,
							sharp: ImageSharp.Square,
							background: '#FFFFFF',
							width: 155,
							height: 155,
							position: PositionType.Absolute,
							top: 812,
							left: 427,
						},
						// {
						// 	key: 'avatar',
						// 	type: NodeType.Image,
						// 	url: '/static/avatar.png',
						// 	sharp: ImageSharp.Circle,
						// 	diameter: 80, // 直径
						// 	position: PositionType.Relative,
						// 	relativeKey: 'goodImage',
						// 	top: 826,
						// 	left: 20,
						// 	width: 98,
						// 	height: 98,
						// 	absoluteX: 0,
						// 	absoluteY: 0,
						// },
						// {
						// 	key: 'contactLabel2',
						// 	type: NodeType.Text,
						// 	text: '用户名称',
						// 	textGap: 10,
						// 	color: '#FFE89C',
						// 	breakWord: true,
						// 	fontSize: 28,
						// 	position: PositionType.Absolute,
						// 	relativeKey: 'jobLabel2',
						// 	left: 130,
						// 	top: 854
						// },
						{
							key: 'contactLabel3',
							type: NodeType.Text,
							text: '长按或扫码识别二维码',
							textGap: 10,
							color: '#333',
							height: 40,
							widht: 232,
							breakWord: true,
							fontSize: 22,
							position: PositionType.Absolute,
							relativeKey: 'jobLabel3',
							left: 22,
							top: 949
						}
					]
				};
			},
			async loadData() {
				const res = await this.$api.post('api/si_zhu/getResult', {
					record_id: this.record_id
				});
				if (res.code != 1) return this.$toast(res.msg);
				this.info = res.data;

				let image;
				this.$refs['qrcodeRef'].canvasToTempFilePath({
					success: (res) => {
						image = res.tempFilePath;
						this.qr_image = image;
						this.initPosterInfo();
					},
					fail(err) {
						console.log('err:::', err)
					}
				})
			},
			/** 
			 * @param {Object} wechatType 1:微信好友;2:朋友圈，不传默认好友分享
			 */
			async useShare(wechatType) {
				// let shareData = {
				// 	"shareType": "1",
				// 	"ur1": "/static/logo.png",
				// 	"image": "缩略图图片链接",
				// 	"shareechatType": wechatType,
				// 	"xType": "sendNonGifcontent",
				// }
				const {
					data
				} = await this.$api.post('api/index/saveBase64', {
					password: 666666,
					base64_image_content: this.qr_image,
				})
				if (data.code != 1) return this.$toast(data.msg);
				let shareData = {
					"shareType": "0",
					"title": "来自2025的好消息",
					"url": this.qr_code,
					"des": "来自2025的好消息",
					image: this.$use.loadImg(data.url),
					"shareWeChatType": wechatType,
					"xType": "sendNonGifcontent"
				}
				console.log('share入参---', shareData);
				share((share_) => {
					console.log('share回调---', share_);
				}, shareData)
			}
		}
	}
</script>
<style lang="scss">
	page {
		background-color: #bf0f06;
	}

	.shaicon {
		width: 108rpx;
		height: 108rpx;
	}

	/* 每个页面公共css 放app.vue页面的 */
	@font-face {
		font-family: 'font_family';
		/* project id 1991769 */
		src: url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.eot');
		src: url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.eot?#iefix') format('embedded-opentype'),
			url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.woff2') format('woff2'),
			url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.woff') format('woff'),
			url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.ttf') format('truetype'),
			url('//at.alicdn.com/t/font_1991769_u8wpg8jfhpq.svg#iconfont') format('svg');
	}

	.font_family {
		font-family: 'font_family' !important;
		font-size: 16px;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		font-style: normal;
		-webkit-text-stroke-width: 0.2px;
	}

	/* 按钮去掉边框 */
	button::after {
		border: none;
	}

	button {
		height: auto; //支付宝需要加
		padding-right: 0;
		padding-left: 0;
		margin-right: 0;
		margin-left: 0;
		font-size: 28rpx;
		line-height: 1;
		color: #1c1c1c;
		background: none;
		border: none; //支付宝需要加
	}

	.button-hover {
		color: #1c1c1c;
		background: none;
	}

	.poster-img {
		width: 40%;
	}

	/* 每个页面公共css */
	.content {
		height: 100%;
		text-align: center;
	}

	.share-btn {
		padding: 30rpx 60rpx;
		color: $uni-text-color-inverse;
		background-color: $uni-btn-color;
	}

	.share-pro {
		z-index: 5;
		display: flex;
		line-height: 1;
		box-sizing: border-box;
		align-items: center;
		justify-content: flex-end;
		flex-direction: column;

		.share-pro-mask {
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
		}

		.share-pro-dialog {
			position: relative;
			position: fixed;
			bottom: 0;
			width: 750rpx;
			height: 300rpx;
			overflow: hidden;
			background-color: #fff;
			border-radius: 24rpx 24rpx 0 0;
			box-sizing: border-box;

			.close-btn {
				position: absolute;
				top: 0rpx;
				right: 29rpx;
				padding: 20rpx 15rpx;
			}

			.share-pro-title {
				padding: 28rpx 41rpx;
				font-size: 28rpx;
				color: #1c1c1c;
				background-color: #f7f7f7;
			}

			.share-pro-body {
				display: flex;
				font-size: 28rpx;
				color: #1c1c1c;
				flex-direction: row;
				justify-content: space-around;

				.share-item {
					display: flex;
					flex-direction: column;
					justify-content: center;
					justify-content: space-around;

					.share-icon {
						margin-top: 39rpx;
						margin-bottom: 16rpx;
						font-size: 70rpx;
						color: #42ae3c;
						text-align: center;
					}

					&:nth-child(2) {
						.share-icon {
							color: #ff5f33;
						}
					}
				}
			}
		}

		/* 显示或关闭内容时动画 */
		.open {
			transform: translateY(0);
			transition: all 0.3s ease-out;
		}

		.close {
			transform: translateY(350rpx);
			transition: all 0.3s ease-out;
		}
	}
</style>
<style scoped lang="scss">
	.max_page {
		width: 100%;
		background-color: #a81a21;
		background-image: url("@/static/share-bj.png");
		background-size: cover;
		background-repeat: no-repeat;
	}

	.bom_btn_download {
		width: 690rpx;
		height: 110rpx;
		line-height: 110rpx;
		text-align: center;
		color: #fff;
		background-image: url("/static/jiesuo-btn.png");
		background-repeat: no-repeat;
		background-size: cover;
	}

	.bom_btn {
		background-image: url("@/static/chongxin.png");
		background-repeat: no-repeat;
		background-size: cover;
		width: 344rpx;
		height: 128rpx;
		line-height: 128rpx;
		text-align: center;
		color: #fff;
		font-size: 32rpx;
	}

	.bom_btns {
		background-image: url("@/static/share-btn.png");
		background-repeat: no-repeat;
		background-size: cover;
		width: 344rpx;
		height: 128rpx;
		line-height: 128rpx;
		text-align: center;
		color: #fff;
		font-size: 32rpx;
		padding-left: 50rpx;
	}

	.erweima_img {
		width: 108rpx;
		height: 108rpx;
		border-radius: 8rpx;
	}

	.bom_box {
		height: 665rpx;
		width: 100%;
	}
</style>