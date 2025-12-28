### 海报生成组件
#### 北京潇和信息科技有限公司开发


### changelog

#### 1.0.2
##### 支持设置全局字体
##### 支持单独设置字体
##### 方形图片支持背景色
##### 文本，支持数组方式

#### 1.0.1 
##### 支持图片，文字
##### 支持定义元素位置
##### 图片支持方形图片，圆形图片
##### 文字支持换行，超行末尾省略号结尾

### 使用方法

```js
	import HzPosterMaker from '@/components/hz-poster-maker/hz-poster-maker.vue'
	import { NodeType, ImageSharp, PositionType } from '@/components/hz-poster-maker/constant.js'
	export default {
		components: {
			HzPosterMaker
		},
		data() {
			return {
				posterWidth: 750,
				posterHeight: 1100,
				posterInfo: null
			}
		},
		onShow() {
			this.initPosterInfo();
		},
		methods: {
			initPosterInfo() {
				this.posterInfo = {
					background: '#FFFFFF',	
					fontFamily: 'SERIF, Arial, sans-serif',
					nodes: []
				}
			}
		}
	}
		
```
```html
<hz-poster-maker
	v-if="posterInfo"
	:width="posterWidth"
	:height="posterHeight"
	:posterInfo="posterInfo"
></hz-poster-maker>
```

```json
this.posterInfo = {
	background: '#FFFFFF',	
	fontFamily: 'SERIF, Arial, sans-serif',
	nodes: [
		{
			key: 'backgroundImage',
			type: NodeType.Image,
			url: 'https://i.imgs.ovh/2023/10/09/Ld2It.png',
			sharp: ImageSharp.Square,
			width: this.posterWidth,
			height: this.posterHeight,
			position: PositionType.Absolute,
			top: 0,
			left: 0
		},
		{
			key: 'titleLabel',
			type: NodeType.Text,
			text: '双11狂欢节',
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 80,
			fontFamily: 'SERIF',
			position: PositionType.Absolute,
			left: 80,
			top: 120,
			isBold: true
		},
		{
			key: 'enTitleLabel',
			type: NodeType.Text,
			text: 'Double Eleven Carnival',
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 44,
			fontFamily: 'monospace',
			position: PositionType.Relative,
			relativeKey: 'titleLabel',
			left: 60,
			top: 70,
			isBold: true
		},
		{
			key: 'subTitle',
			type: NodeType.Text,
			text: '超值新品限时秒杀',
			color: '#7B0307',
			breakWord: true,
			fontSize: 32,
			position: PositionType.Absolute,
			left: 253,
			top: 455,
			isBold: true
		},
		{
			key: 'liTitle1',
			type: NodeType.Text,
			text: '优惠一',
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 32,
			position: PositionType.Absolute,
			left: 163,
			top: 555,
			isBold: true
		},
		{
			key: 'liDetail1',
			type: NodeType.Text,
			text: [
				'全场满200减29',
				'满500减89',
			],
			textGap: 6,// 每行分割
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 30,
			position: PositionType.Relative,
			relativeKey: 'liTitle1',
			left: 160,
			top: -30,
		},
		{
			key: 'liTitle2',
			type: NodeType.Text,
			text: '优惠二',
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 32,
			position: PositionType.Relative,
			relativeKey: 'liTitle1',
			left: 0,
			top: 116,
			isBold: true
		},
		{
			key: 'liDetail2',
			type: NodeType.Text,
			text: [
				'全场满200减29',
				'满500减89',
			],
			textGap: 6,// 每行分割
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 30,
			position: PositionType.Relative,
			relativeKey: 'liTitle2',
			left: 160,
			top: -30,
		},
		{
			key: 'liTitle3',
			type: NodeType.Text,
			text: '优惠三',
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 32,
			position: PositionType.Relative,
			relativeKey: 'liTitle2',
			left: 0,
			top: 116,
			isBold: true
		},
		{
			key: 'liDetail3',
			type: NodeType.Text,
			text: [
				'全场满200减29',
				'满500减89测试多行文本，省略号支持',
			],
			textGap: 6,// 每行分割
			color: '#DDD1A6',
			breakWord: true,
			fontSize: 30,
			position: PositionType.Relative,
			relativeKey: 'liTitle3',
			left: 160,
			top: -30,
			moreLine: true, // 多行
			textWidth: 300, // 文字宽度
			lineNum: 1, // 两行，超过省略号
		},
		{
			key: 'contactLabel',
			type: NodeType.Text,
			text: [
				'电话：010-8888888',
				'地址：北京市朝阳区美丽花园'
			],
			textGap: 10,
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Absolute,
			relativeKey: 'jobLabel',
			left: 60,
			top: 960
		},
		{
			key: 'qr',
			type: NodeType.Image,
			url: 'https://i.imgs.ovh/2023/10/09/LEFzO.png',
			sharp: ImageSharp.Square,
			background: '#FFFFFF',
			width: 130,
			height: 130,
			position: PositionType.Absolute,
			top: 940,
			left: 560
		}
	]
};
```

#### -------------------------
```json
// 1.0.1支持
this.posterInfo = {
	background: '#FFFFFF',	
	nodes: [
		{
			key: 'avatar',
			type: NodeType.Image,
			url: 'https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5icWfDiajjiaQZIhffR7WQaYQ26rCgLC39EIBDdV0RMhtFrAcQgGfZNRJsZL2vXXvSpooMYicPLXLib6Q/132',
			sharp: ImageSharp.Circle,
			diameter: 80, // 直径
			position: PositionType.Relative,
			relativeKey: 'goodImage',
			left: 20,
			top: 20
		},
		{
			key: 'name',
			type: NodeType.Text,
			text: '我是潇和',
			color: '#333333',
			fontSize: 14,
			position: PositionType.Relative,
			relativeKey: 'avatar',
			left: 20,
			top: 0,
			moreLine: true, // 多行
			textWidth: 100, // 文字宽度
			lineNum: 2, // 两行，超过省略号
		},
		{
			key: 'name',
			type: NodeType.Text,
			text: '这是123dasasdfasfasdfasdfdfdasd一个测试文字长度的字段，可以看看今天的天气',
			color: '#333333',
			breakWord: true,
			fontSize: 30,
			isBold: true,
			position: PositionType.Absolute,
			left: 0,
			top: 0,
			moreLine: true, // 多行
			textWidth: 100, // 文字宽度
			lineNum: 3, // 两行，超过省略号
		},
		{
			key: 'name2',
			type: NodeType.Text,
			text: '这是123dasasdfasfasdfasdfdfdasd一个测试文字长度的字段，可以看看今天的天气',
			color: '#333333',
			breakWord: true,
			fontSize: 30,
			position: PositionType.Absolute,
			left: 100,
			top: 0,
			moreLine: true, // 多行
			textWidth: 100, // 文字宽度
			lineNum: 3, // 两行，超过省略号
		},
		{
			key: 'goodImage',
			type: NodeType.Image,
			url: 'http://img.ssss.cn/attach/2023/09/24f1a202309251400306354.jpg',
			sharp: ImageSharp.Square,
			width: this.posterWidth,
			height: 400,
			position: PositionType.Absolute,
			top: 0,
			left: 0
		},
		{
			key: 'goodImage2',
			type: NodeType.Image,
			url: 'http://img.sssss.cn/attach/2023/09/bb489202309281040428000.jpg',
			sharp: ImageSharp.Square,
			width: 250,
			height: 300,
			position: PositionType.Relative,
			relativeKey: 'goodImage',
			top: 400, // 如果是相对位置，top 0 意思是图片顶部一样
			left: 250 
		},
		{
			key: 'avatar',
			type: NodeType.Image,
			url: 'https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5icWfDiajjiaQZIhffR7WQaYQ26rCgLC39EIBDdV0RMhtFrAcQgGfZNRJsZL2vXXvSpooMYicPLXLib6Q/132',
			sharp: ImageSharp.Circle,
			diameter: 80, // 直径
			position: PositionType.Absolute,
			left: 20,
			top: 20
		},
		{
			key: 'avatar2',
			type: NodeType.Image,
			url: 'https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5icWfDiajjiaQZIhffR7WQaYQ26rCgLC39EIBDdV0RMhtFrAcQgGfZNRJsZL2vXXvSpooMYicPLXLib6Q/132',
			sharp: ImageSharp.Circle,
			diameter: 80, // 直径
			position: PositionType.Relative,
			relativeKey: 'goodImage',
			left: 20,
			top: 420
		},
	]
};

// example 1
this.posterInfo = {
	background: '#FFFFFF',	
	nodes: [
		{
			key: 'backgroundImage',
			type: NodeType.Image,
			url: 'https://bpic.51yuansu.com/backgd/cover/00/15/83/5b872e829de14.jpg?x-oss-process=image/resize,w_780/sharpen,100',
			sharp: ImageSharp.Square,
			width: this.posterWidth,
			height: this.posterHeight,
			position: PositionType.Absolute,
			top: 0,
			left: 0
		},
		{
			key: 'fromLabel',
			type: NodeType.Text,
			text: 'From:',
			color: '#333333',
			breakWord: true,
			fontSize: 35,
			position: PositionType.Absolute,
			left: 30,
			top: 80,
			isBold: true
		},
		{
			key: 'avatar',
			type: NodeType.Image,
			url: 'https://thirdwx.qlogo.cn/mmopen/vi_32/Q3auHgzwzM5icWfDiajjiaQZIhffR7WQaYQ26rCgLC39EIBDdV0RMhtFrAcQgGfZNRJsZL2vXXvSpooMYicPLXLib6Q/132',
			sharp: ImageSharp.Circle,
			diameter: 60, // 直径
			position: PositionType.Relative,
			relativeKey: 'fromLabel',
			left: 110,
			top: -29
		},
		{
			key: 'nickname',
			type: NodeType.Text,
			text: '小红花',
			color: '#333333',
			breakWord: true,
			fontSize: 28,
			position: PositionType.Relative,
			relativeKey: 'avatar',
			left: 66,
			top: 18,
		},
		{
			key: 'goodLuck',
			type: NodeType.Text,
			text: '祝周老师教师节快乐！',
			color: '#333333',
			breakWord: true,
			fontSize: 50,
			isBold: true,
			position: PositionType.Relative,
			relativeKey: 'fromLabel',
			left: 120,
			top: 360,
		},
		{
			key: 'toLabel',
			type: NodeType.Text,
			text: 'To:',
			color: '#333333',
			breakWord: true,
			fontSize: 35,
			position: PositionType.Relative,
			relativeKey: 'fromLabel',
			left: 330,
			top: 720,
			isBold: true
		},
		{
			key: 'avatar2',
			type: NodeType.Image,
			url: 'https://a.520gexing.com/uploads/allimg/2019111010/g10czso3ly5.jpeg',
			sharp: ImageSharp.Circle,
			diameter: 60, // 直径
			position: PositionType.Relative,
			relativeKey: 'toLabel',
			left: 66,
			top: -30
		},
		{
			key: 'nickname2',
			type: NodeType.Text,
			text: '周老师',
			color: '#333333',
			breakWord: true,
			fontSize: 28,
			position: PositionType.Relative,
			relativeKey: 'avatar2',
			left: 66,
			top: 18,
		},
	]
};

// example 2 招聘海报
this.posterInfo = {
	background: '#FFFFFF',	
	nodes: [
		{
			key: 'backgroundImage',
			type: NodeType.Image,
			url: 'https://img.88icon.com/download/jpg/202111/cb32662e6fbafdf28f04e00f1a57c17c_800_1053.jpg!con0',
			sharp: ImageSharp.Square,
			width: this.posterWidth,
			height: this.posterHeight,
			position: PositionType.Absolute,
			top: 0,
			left: 0
		},
		{
			key: 'titleLabel',
			type: NodeType.Text,
			text: '招募信息',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 50,
			position: PositionType.Absolute,
			left: 70,
			top: 280,
			isBold: true
		},
		{
			key: 'jobLabel',
			type: NodeType.Text,
			text: 'AI算法工程师',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 36,
			position: PositionType.Relative,
			relativeKey: 'titleLabel',
			left: 20,
			top: 50,
			isBold: true
		},
		{
			key: 'addressLabel',
			type: NodeType.Text,
			text: '工作地点：',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'jobLabel',
			left: 0,
			top: 36,
			isBold: true
		},
		{
			key: 'address',
			type: NodeType.Text,
			text: '北京市海淀区中关村鼎好大厦400000-22-33',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'addressLabel',
			left: 0,
			top: 30,
			moreLine: true, // 多行
			textWidth: 300, // 文字宽度
			lineNum: 2, // 两行，超过省略号
		},
		{
			key: 'jobDetailLabel',
			type: NodeType.Text,
			text: '职位要求：',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'address',
			left: 0,
			top: 64,
			isBold: true
		},
		{
			key: 'jobDetail1',
			type: NodeType.Text,
			text: '1，最低学历：研究生',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'jobDetailLabel',
			left: 0,
			top: 32,
		},
		{
			key: 'jobDetail2',
			type: NodeType.Text,
			text: '2，专业要求：数学，计算机相关专业',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'jobDetail1',
			left: 0,
			top: 20,
			moreLine: true, // 多行
			textWidth: 300, // 文字宽度
			lineNum: 2, // 两行，超过省略号
		},
		{
			key: 'attactLabel',
			type: NodeType.Text,
			text: '联系电话：',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'jobDetail2',
			left: 0,
			top: 64,
			isBold: true
		},
		{
			key: 'phone',
			type: NodeType.Text,
			text: '18600019451',
			color: '#FFFFFF',
			breakWord: true,
			fontSize: 24,
			position: PositionType.Relative,
			relativeKey: 'attactLabel',
			left: 0,
			top: 32,
		},
		{
			key: 'avatar',
			type: NodeType.Image,
			url: 'https://i.imgs.ovh/2023/10/03/VUZWv.png',
			sharp: ImageSharp.Circle,
			diameter: 120, // 直径
			position: PositionType.Relative,
			relativeKey: 'phone',
			left: 0,
			top: 30
		}
	]
};
```