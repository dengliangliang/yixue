/** @type {import('tailwindcss').Config} */
module.exports = {
  // uni-app使用rpx单位，需要自定义配置
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
    './src/**/*.nvue'
  ],
  theme: {
    extend: {
      colors: {
        // 保留现有主题色
        primary: '#E2C289',
        'primary-dark': '#D4B278',
        'primary-light': '#F0D49A',
      },
      // 适配uni-app的rpx单位
      spacing: {
        '1': '2rpx',
        '2': '4rpx',
        '3': '6rpx',
        '4': '8rpx',
        '5': '10rpx',
        '6': '12rpx',
        '8': '16rpx',
        '10': '20rpx',
        '12': '24rpx',
        '16': '32rpx',
        '20': '40rpx',
        '24': '48rpx',
        '32': '64rpx',
        '40': '80rpx',
        '48': '96rpx',
        '56': '112rpx',
        '64': '128rpx',
      },
      fontSize: {
        'xs': '24rpx',
        'sm': '28rpx',
        'base': '32rpx',
        'lg': '36rpx',
        'xl': '40rpx',
        '2xl': '48rpx',
        '3xl': '56rpx',
        '4xl': '64rpx',
      },
      borderRadius: {
        'none': '0',
        'sm': '4rpx',
        'DEFAULT': '8rpx',
        'md': '12rpx',
        'lg': '16rpx',
        'xl': '24rpx',
        '2xl': '32rpx',
        '3xl': '48rpx',
        'full': '9999rpx',
      }
    },
  },
  plugins: [],
  // uni-app需要的特殊配置
  corePlugins: {
    preflight: false, // 禁用基础样式重置，避免与uni-app冲突
  }
}
