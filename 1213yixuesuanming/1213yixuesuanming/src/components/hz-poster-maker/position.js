// import image from 'uview-plus/components/u-image/image.js'  // ❌ uview-plus 已移除
// import line from 'uview-plus/components/u-line/line.js';    // ❌ uview-plus 已移除
import { PositionType } from './constant.js' 
export default {
	methods: {
		/**
		 * 文本绘制

		 * @param {Object} text 文案
		 * @param {Object} fontSize 字体大小
		 * @param {Object} positionX 左上角 X坐标
		 * @param {Object} positionY 左上角 Y坐标
		 * @param {Object} color 颜色
		 * @param {Object} lineHeight 行高
		 * @param {Object} isBold 是否加粗
		 * @param {Object} moreLine 是否多行
		 * @param {Object} lineWidth 行宽度
		 * @param {Object} lineNum 行数
		 * @param {Object} textGap text为数组时，行间隔
		 */
		drawText(
			text, 
			fontSize, 
			fontFamily,
			positionX, 
			positionY, 
			color, 
			lineHeight,
			isBold = false, 
			moreLine = false, 
			lineWidth = 0,
			lineNum = 1,
			breakWord = true, // 是否断词 不断词，则英文字母太长不换行，默认断词
			textGap = 0
		){
			if(!lineHeight) {
				lineHeight = this.convertLeng(fontSize + 2);
			} else {
				if(lineHeight <= fontSize) {
					lineHeight += 2;
				}
				lineHeight = this.convertLeng(lineHeight);
			}
			fontSize = this.convertLeng(fontSize);
			positionX = this.convertLeng(positionX);
			positionY = this.convertLeng(positionY);
			lineWidth = this.convertLeng(lineWidth);
			
			this.ctx.restore()
			this.ctx.setFontSize(fontSize)
			this.ctx.setFillStyle(color)
			this.ctx.setTextAlign('left')
			this.ctx.setTextBaseline('middle')
			if(isBold) {
				this.ctx.font = `bold ${Math.floor(fontSize)}px ${fontFamily}`
			} else {
				this.ctx.font = `${Math.floor(fontSize)}px ${fontFamily}`
			}
			if(moreLine && lineWidth > 0) {
				console.log(Array.isArray(text))
				let textLines = [];
				if(Array.isArray(text)) {
					text.forEach((t, i)=> {
						let partTextLines = this.omitText(t, lineWidth, lineNum, fontSize, breakWord);
						textLines = [...textLines, ...partTextLines]
					})
				} else {
					textLines = this.omitText(text, lineWidth, lineNum, fontSize, breakWord);
				}
				
				for (let i = 0; i < textLines.length; i++) {
					const currentLine = textLines[i]
					const lineY = (i * fontSize) + positionY
					const eachLineHeight = i * (lineHeight - fontSize);
					this.ctx.fillText(currentLine, positionX, lineY + eachLineHeight)
				}
			} else {
				if(Array.isArray(text)) {
					text.forEach((t, i)=> {
						let pY = (i * lineHeight) + positionY + this.convertLeng(textGap*i);
						this.ctx.fillText(t, positionX, pY)
					})
				} else {
					this.ctx.fillText(text, positionX, positionY)
				}
			}
		},
		/**
		 * 换行逻辑计算

		 * @param {Object} text 字符串
		 * @param {Object} lineWidth 行宽度
		 * @param {Object} lineNum
		 * @param {Object} breakWord // 是否断词 不断词，则英文字母太长不换行，默认断词
		 */
		omitText(text, lineWidth, lineNum, fontSize, breakWord = true) {
			// 处理换行与省略号
			let lines = []
			let line = ''
			let words = text.split(/([^\x00-\xff]|\b\w+\b)/);
			if(breakWord) {
				words = text.split('');
			}
			for (let i = 0; i < words.length; i++) {
				const testLine = line + words[i]
				const metrics = this.ctx.measureText(testLine)
				const testWidth = metrics.width
				if (testWidth > lineWidth && i > 0) {
					lines.push(line.trim())
					line = words[i]
				} else {
					line = testLine
				}
			}
			lines.push(line.trim())
			lines = lines.slice(0, lineNum+1);
			// 处理超过多行时添加省略号
			if (lines.length > lineNum) {
				const lastLine = lines[lines.length - 2] // lineNum行
				const dotLength = (this.ctx.measureText('...')).width
				const dotNum = Math.ceil(dotLength / fontSize);
				let truncatedLine = lastLine.slice(0, -(dotNum)) + '...' // 截断行
				lines[lines.length - 2] = truncatedLine
			}
			lines = lines.slice(0, lineNum);
			return lines;
		},
		/**
		 * 绘制圆形图片

		 * @param {Object} imgUrl 网络地址
		 * @param {Object} imageDiameter 图片直径
		 * @param {Object} centerX 图片中心X坐标
		 * @param {Object} centerY 图片中心Y坐标
		 */
		drawCircle(imgUrl, imageDiameter, centerX, centerY){
			imageDiameter = this.convertLeng(imageDiameter);
			centerX = this.convertLeng(centerX);
			centerY = this.convertLeng(centerY)
			let that = this;
			let score = {}
			return new Promise((resolve, reject) => {
				uni.downloadFile({
					url: imgUrl,
					success: (res) => {
						// 获取临时文件路径
						const tempFilePath = res.tempFilePath
						// 加载图片信息
						uni.getImageInfo({
							src: tempFilePath,
							success: (image) => {
								// 确定短边的长度，并按比例缩放图片
								const ratio = Math.min(imageDiameter / image.width, imageDiameter / image.height)
								const scaledWidth = image.width * ratio
								const scaledHeight = image.height * ratio
						
								// 计算图片剧中时的位置偏移量
								const offsetX = (imageDiameter - scaledWidth) / 2
								const offsetY = (imageDiameter - scaledHeight) / 2
						
								// 在指定位置绘制图片，裁剪为圆形
								this.ctx.save()
								this.ctx.beginPath()
								this.ctx.setFillStyle('transparent')
								this.ctx.arc(centerX, centerY, imageDiameter / 2, 0, 2 * Math.PI)
								this.ctx.closePath()
								this.ctx.clip()
								this.ctx.drawImage(image.path, centerX - imageDiameter / 2 + offsetX, centerY - imageDiameter / 2 + offsetY, scaledWidth, scaledHeight)
								resolve();
							},
							fail: (error) => {
								reject();
							}
						})
					},
					fail: (error) => {
						reject();
					}
				});
			})
		},
		/**
		 * 绘制方形图片

		 * @param {Object} imgUrl 网络图片地址
		 * @param {Object} rangeWidth 图片占满宽度
		 * @param {Object} rangeHeight 图片占满高度
		 * @param {Object} rangeX 图片位于canvas 左上角X坐标
		 * @param {Object} rangeY 图片位于canvas 左上角Y坐标
		 * @param {Object} background 图片背景颜色
		 */
		drawSquare(imgUrl, rangeWidth, rangeHeight, rangeX, rangeY, background='transparent'){
			rangeWidth = this.convertLeng(rangeWidth);
			rangeHeight = this.convertLeng(rangeHeight);
			rangeX = this.convertLeng(rangeX);
			rangeY = this.convertLeng(rangeY);
			let that = this;
			let score = {}
			return new Promise((resolve, reject) => {
				uni.downloadFile({
					url: imgUrl,
					success: (res) => {
						// 获取临时文件路径
						const tempFilePath = res.tempFilePath
						// 加载图片信息
						uni.getImageInfo({
							src: tempFilePath,
							success: (image) => {
								const imageWidth = image.width;
								const imageHeight = image.height;
								const imageRatio = imageWidth / imageHeight;
								const targetRatio = rangeWidth / rangeHeight;
								
								let sourceX = 0;
								let sourceY = 0;
								let sourceWidth = imageWidth;
								let sourceHeight = imageHeight;
								
								if (imageRatio !== targetRatio) {
								  if (imageRatio > targetRatio) {
								    // 图像宽度过大，需要切除左右部分
								    const scaledImageWidth = imageHeight * targetRatio;
								    sourceX = (imageWidth - scaledImageWidth) / 2;
								    sourceWidth = scaledImageWidth;
								  } else {
								    // 图像高度过大，需要切除上下部分
								    const scaledImageHeight = imageWidth / targetRatio;
								    sourceY = (imageHeight - scaledImageHeight) / 2;
								    sourceHeight = scaledImageHeight;
								  }
								}
								
								const scaleX = rangeWidth / sourceWidth;
								const scaleY = rangeHeight / sourceHeight;
								const scale = Math.min(scaleX, scaleY);
								const scaledWidth = sourceWidth * scale;
								const scaledHeight = sourceHeight * scale;
								const offsetX = rangeX + (rangeWidth - scaledWidth) / 2;
								const offsetY = rangeY + (rangeHeight - scaledHeight) / 2;
								// 绘制颜色块
								if(background){
									that.ctx.fillStyle = background;
									that.ctx.fillRect(offsetX, offsetY, scaledWidth, scaledHeight);
								}
								// 在指定位置绘制图片
								that.ctx.drawImage(image.path, sourceX, sourceY, sourceWidth, sourceHeight, offsetX, offsetY, scaledWidth, scaledHeight);
								resolve();
							},
							fail: (error) => {
								reject();
							}
						})
					},
					fail: (error) => {
						reject();
					}
				});
			})
		},
		// 尺寸转化
		convertLeng(pxLen) {
			return pxLen * this.topRadio
		},
    }
}
