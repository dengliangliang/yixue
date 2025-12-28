# 字体文件放置指南

## 📥 需要下载的字体文件

### 1. 霞鹜文楷（正文字体）
**下载地址：** https://github.com/lxgw/LxgwWenKai/releases

**步骤：**
1. 点击页面上的最新版本（Latest）
2. 在 Assets 区域找到 `LXGWWenKai-Regular.ttf`
3. 点击下载
4. 下载完成后，将文件重命名为：`XiaWuWenKai-Regular.ttf`
5. 将重命名后的文件复制到当前目录（ttf文件夹）

---

### 2. 思源黑体 Bold（按钮字体 - 推荐）
**下载地址：** https://github.com/adobe-fonts/source-han-sans/releases

**步骤：**
1. 点击最新版本（Latest Release）
2. 向下滚动找到 Assets 区域
3. 下载 `SourceHanSansCN.zip`（简体中文版，约 50MB）
4. 解压后找到 `SourceHanSansCN-Bold.otf` 文件
5. 将文件复制到当前目录（ttf文件夹）

**快速下载链接（备用）：**
- 百度网盘：https://pan.baidu.com/s/1xf4E56rA2QKBGwjI5Uziig 提取码：u9br
- 夸克网盘：https://pan.quark.cn/s/01b915592f79

---

### 方案2：阿里巴巴普惠体 Bold（备选）
**下载地址：** https://www.alibabafonts.com/#/font

**步骤：**
1. 打开阿里巴巴字体官网
2. 点击"立即下载"
3. 解压后找到 `AlibabaPuHuiTi-3-Bold.ttf` 或 `AlibabaPuHuiTi-2-Bold.ttf`
4. 重命名为：`SourceHanSansCN-Bold.otf`（保持代码兼容）
5. 将文件复制到当前目录

**快速下载（夸克网盘）：** https://pan.quark.cn/s/dacc23531ef3

---

## 📁 最终文件列表

完成后，本目录应该包含：
```
ttf/
├── font1.ttf (已有)
├── font2.ttf (已有)
├── XiaWuWenKai-Regular.ttf (新增 - 正文字体)
└── SourceHanSansCN-Bold.otf (新增 - 按钮字体)
```

---

## ✅ 验证

将文件放入后，运行项目测试：
```bash
cd D:\workspace\1213易学\1213yixuesuanming\1213yixuesuanming
npm run dev:h5
```

打开浏览器查看字体是否生效。

---

## 🎨 字体效果

- **正文**：将显示优雅的楷体（霞鹜文楷）
- **按钮**：将显示粗壮有力的黑体（站酷高端黑）

---

## ⚡ 快速下载链接

**霞鹜文楷直接下载：**
https://github.com/lxgw/LxgwWenKai/releases/download/v1.330/LXGWWenKai-Regular.ttf

**站酷字体官网：**
https://www.zcool.com.cn/special/zcoolfonts/

---

## 📞 需要帮助？

如果下载有问题，可以：
1. 使用百度网盘或其他网盘搜索字体名称
2. 搜索"霞鹜文楷下载"或"站酷高端黑下载"
3. 确保文件名完全一致

---

**重要提醒：**
- 文件名必须完全匹配（区分大小写）
- 必须是 .ttf 格式
- 放在本目录下（与本README.md同级）
