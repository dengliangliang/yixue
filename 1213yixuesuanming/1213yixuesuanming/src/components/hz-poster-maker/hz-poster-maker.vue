<template>
	<view class="hz-poster-maker" :style="[posterStyle]">
		<canvas :style="canvasStyle" style="opacity: 0;" canvas-id="poster" class="po_ab t-0 l-0 zIndex-1" />
		<view class="flex_center po_re zIndex-10" style="width:618rpx;height:1002rpx;background-color: #c42733;">

			<view v-if="!img_show" class="po_ab box_100 flex_center zIndex-11">
				<uni-load-more color="#000" :content-text="{
						contentdown: '查看更多',
						contentrefresh: can_img?'正在加载...':'正在生成...',
						contentnomore: '没有更多'
					}" :status="!img_show?'loading':'noMore'" />
			</view>

			<image v-if="can_img" @load="img_show=true" class="round-8 imgs box_100" :src="$use.loadImg(can_img)">
			</image>
		</view>
	</view>
</template>

<script>
	import props from './props.js';
	import position from "./position.js";
	import {
		PositionType,
		NodeType,
		ImageSharp,
		DefaultFontFamily
	} from './constant.js'
	import {
		showToast
	} from '../../common/util.js';
	export default {
		name: "hz-poster-maker",
		mixins: [props, position],
		data() {
			return {
				img_show: false,
				contentText: {
					contentdown: '查看更多',
					contentrefresh: '正在生成...',
					contentnomore: '没有更多'
				},
				contentTexts: {
					contentdown: '查看更多',
					contentrefresh: '正在加载...',
					contentnomore: '没有更多'
				},
				can_img: '',
				canvasName: 'poster',
				ctx: null,
				canvasStyle: '',
				topRadio: 0 // 单位比例
			};
		},
		computed: {
			posterStyle() {
				let style = {
					width: `${this.width}rpx`,
					height: `${this.height}rpx`
				}
				return style;
			},
			sortedNode() {
				const sorted = [];
				const visitedKeys = new Set();

				// 递归处理依赖关系的辅助函数
				const handleDependencies = (key) => {
					const element = this.posterInfo.nodes.find(item => item.key === key);

					if (!element || visitedKeys.has(key)) {
						return;
					}

					visitedKeys.add(key);

					if (element.relativeKey) {
						handleDependencies(element.relativeKey);
					}

					sorted.push(element);
				};

				for (let i = 0; i < this.posterInfo.nodes.length; i++) {
					handleDependencies(this.posterInfo.nodes[i].key);
				}

				return sorted;
			}
		},
		mounted() {
			this.initCanvas().then(async () => {
				await this.doDraw();
				this.finishDraw();
			});
		},
		methods: {
			// h5+ 保存图片到相册
			downloadImage(filename) {
				let a = document.createElement('a');
				a.href = filename;
				a.download = 'your_image_name.jpg'; // 可以自定义下载后的文件名
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
			},
			useSave() {
				uni.canvasToTempFilePath({
					canvasId: 'poster',
					success: async (res) => {
						// 成功将 canvas 转换为临时文件路径
						console.log('baocun---', res);
						let tempFilePath = res.tempFilePath;
						this.upImg(tempFilePath)

						// #ifdef APP
						uni.saveImageToPhotosAlbum({
							filePath: tempFilePath,
							success: () => {
								console.log('保存成功');
								this.$toast('保存成功，快去分享吧！')
							},
							fail: (err) => {
								console.error('保存失败', err);
								this.$toast('保存失败');
							}
						});
						// #endif
					}
				});
			},
			async upImg(tempFilePath) {
				const {
					data
				} = await this.$api.post('api/index/saveBase64', {
					password: 666666,
					base64_image_content: tempFilePath,
				})
				if (data.code != 1) return this.$toast(data.msg);
				console.log(this.$config.URL, '网路地址---', data);
				this.can_img = data.url;
				// this.$go('/pages/result/share-haibao?url=' + data.url)
			},
			initCanvas() {
				const query = uni.createSelectorQuery().in(this);
				return new Promise((resolve) => {
					query.select('.hz-poster-maker').boundingClientRect(data => {
						const {
							width,
							height
						} = data;
						const screenWidth = uni.getSystemInfoSync().screenWidth;
						this.topRadio = screenWidth / 750;
						const trueWidth = width / this.topRadio;
						const trueHeight = height / this.topRadio;
						this.canvasStyle = `width:${618}rpx; height:${1002}rpx;`;

						this.ctx = uni.createCanvasContext(this.canvasName, this);
						this.ctx.width = width;
						this.ctx.height = height;
					}).exec((rect) => {
						console.log('rect---', rect);
						const node = rect[0];
						if (node.width && node.height) {
							console.log('渲染完成');
							setTimeout(() => this.useSave(), 2000)
						}
						resolve(rect);
					});
				});
			},
			async doDraw() {
				this.drawBackground();
				// 根据配置，计算详细位置值
				this.computePosterInfoPosition();
				// 画所有的posterInfo
				await this.drawPosterInfo();
			},
			drawBackground() {
				let color = 'transparent';
				if (this.posterInfo && this.posterInfo.background) {
					color = this.posterInfo.background;
				}
				this.ctx.clearRect(0, 0, this.ctx.width, this.ctx.Height);
				this.ctx.setFillStyle(color)
				this.ctx.fillRect(0, 0, this.ctx.width, this.ctx.height);
			},
			computePosterInfoPosition() {
				if (!(this.posterInfo && this.posterInfo.nodes)) {
					return;
				}
				this.posterInfo.nodes = this.sortedNode;
				this.posterInfo.nodes.map((item) => {
					switch (item.position) {
						case PositionType.Absolute:
							item = this.computeAbsoluteNode(item);
							break;
						case PositionType.Relative:
							item.relativeInfo = this.getRelativeInfo(item.relativeKey)
							item = this.computeRelativeNode(item);
							break;
					}
					return item;
				})
			},
			computeAbsoluteNode(item) {
				if (!item.left) {
					item.left = 0;
				}
				if (!item.top) {
					item.top = 0;
				}
				switch (item.type) {
					case NodeType.Image:
						switch (item.sharp) {
							case ImageSharp.Circle:
								const halfDiameter = item.diameter / 2
								item.centerX = item.left + halfDiameter; // 图片中心位置
								item.centerY = item.top + halfDiameter;
								item.absoluteX = item.left; // 图片的左上角绝对位置
								item.absoluteY = item.top;
								item.radius = item.radius;
								item.computed = true;
								break;
							case ImageSharp.Square:
								item.absoluteX = item.left; // 图片的左上角绝对位置
								item.absoluteY = item.top;
								item.imageWidth = item.width; // 图片尺寸
								item.imageHeight = item.height;
								item.radius = item.radius;
								item.computed = true;
								break;
						}
						break;
					case NodeType.Text:
						item.absoluteX = item.left; // 文字左上角绝对位置
						item.absoluteY = item.top + (item.fontSize / 2);
						item.computed = true;
						break;
				}
				return item;
			},
			computeRelativeNode(item) {
				if (!item.left) {
					item.left = 0;
				}
				if (!item.top) {
					item.top = 0;
				}
				switch (item.type) {
					case NodeType.Image:
						switch (item.sharp) {
							case ImageSharp.Circle:
								// item.absoluteX = item.left + item.relativeInfo.absoluteX; // 图片的左上角绝对位置
								// item.absoluteY = item.top + item.relativeInfo.absoluteY;
								item.absoluteY = item.top;
								item.absoluteX = item.left; // 图片的左上角绝对位置
								const halfDiameter = item.diameter / 2
								item.centerX = item.absoluteX + halfDiameter;
								item.centerY = item.absoluteY + halfDiameter;
								item.computed = true;
								break;
							case ImageSharp.Square:
								item.absoluteX = item.left + item.relativeInfo.absoluteX;
								item.absoluteY = item.top + item.relativeInfo.absoluteY;
								item.imageWidth = item.width;
								item.imageHeight = item.height;
								item.computed = true;
								break;
						}
						break;
					case NodeType.Text:
						item.absoluteY = item.top + item.absoluteY;
						item.absoluteX = item.left + item.absoluteX; // 图片的左上角绝对位置
						item.absoluteX = item.left + item.relativeInfo.absoluteX;
						item.absoluteY = (item.top + item.relativeInfo.absoluteY) + (item.fontSize / 2);
						item.computed = true;
						break;
				}
			},
			getRelativeInfo(relativeKey) {
				let relativeInfo = null;
				this.posterInfo.nodes.forEach((item) => {
					if (item.key === relativeKey) {
						relativeInfo = item;
					}
				});
				return relativeInfo;
			},
			async drawPosterInfo() {
				try {
					if (this.posterInfo.nodes && this.posterInfo.nodes.length > 0) {
						for (const item of this.posterInfo.nodes) {
							switch (item.type) {
								case NodeType.Image:
									switch (item.sharp) {
										case ImageSharp.Circle:
											await this.drawCircle(
												item.url,
												item.diameter,
												// item.radius,
												item.centerX,
												item.centerY
											);
											break;
										case ImageSharp.Square:
											await this.drawSquare(
												item.url,
												item.imageWidth,
												item.imageHeight,
												item.absoluteX,
												item.absoluteY,
												// item.radius,
												item.background
											);
											break;
									}
									break;
								case NodeType.Text:
									item.fontFamily = item.fontFamily || this.posterInfo.fontFamily ||
										DefaultFontFamily;
									this.drawText(
										item.text,
										item.fontSize,
										item.fontFamily,
										item.absoluteX,
										item.absoluteY,
										item.color,
										item.lineHeight,
										item.isBold,
										item.moreLine,
										item.textWidth,
										item.lineNum,
										item.breakWord,
										item.textGap
									);
									break;
							}
						}
					}
				} catch (err) {
					console.log(err);
				}
			},
			finishDraw() {
				this.ctx.draw();
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import './hz-poster-maker.scss';

	.imgs {
		-webkit-user-select: text;
		-moz-user-select: text;
		-ms-user-select: text;
		user-select: text;
	}
</style>