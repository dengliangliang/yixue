export default {
    props: {
		// 海报宽度 最多 750
		width: {
			type: Number,
			required: true
		},
		// 海报高度
		height: {
			type: Number,
			require: true
		},
		// 海报内容配置数组
		posterInfo: {
			type: Object,
			require: true,
			default: () => ({
				background: '#FFFFFF'
			})
		}
    }
}
