
// image_morph.js
// WebGL 粒子系统 - 仅用于五色环的聚合和旋转动画
// 马匹动画已移回 DOM/CSS 实现

// ========== Shader Programs ==========
// Vertex Shader: 支持聚合动画、旋转和每粒子颜色
const vertexShaderSrc = `
attribute vec2 a_pos_start;     // 随机起始位置 (用于聚合动画)
attribute vec2 a_pos_target;    // 目标位置 (图像采样点)
attribute vec3 a_color;         // 粒子颜色 (RGB, 0-1 normalized)

uniform float u_pointSize;
uniform float u_morphProgress;  // 0.0 = 完全散开, 1.0 = 完全聚合
uniform float u_rotation;       // 旋转角度 (弧度), 正值=逆时针, 负值=顺时针
uniform vec2 u_offset;          // 位置偏移 (像素)
uniform vec2 u_scale;           // 缩放因子
uniform vec2 u_canvas_size;     // Canvas 尺寸

varying vec3 v_color;           // 传递给片段着色器的颜色

void main() {
    // 1. 聚合动画: 从起始位置插值到目标位置
    vec2 pos = mix(a_pos_start, a_pos_target, u_morphProgress);

    // 2. 旋转 (绕自身中心)
    if (u_rotation != 0.0) {
        float co = cos(u_rotation);
        float si = sin(u_rotation);
        pos = vec2(
            pos.x * co - pos.y * si,
            pos.x * si + pos.y * co
        );
    }

    // 3. 缩放
    pos = pos * u_scale;

    // 4. 平移
    vec2 finalPos = pos + u_offset;

    // 转换到裁剪空间 (-1 to 1)
    vec2 clipPos = finalPos / (u_canvas_size * 0.5);
    
    gl_Position = vec4(clipPos, 0.0, 1.0);
    gl_PointSize = u_pointSize;
    
    // 传递颜色给片段着色器
    v_color = a_color;
}
`;

// Fragment Shader: 柔和边缘的彩色粒子
const fragmentShaderSrc = `
precision mediump float;
varying vec3 v_color;
uniform float u_alpha;

void main() {
    vec2 coord = gl_PointCoord - vec2(0.5);
    float dist = length(coord);
    if (dist > 0.5) discard;
    
    // 边缘柔化, 降低整体透明度防止叠加过亮
    float alpha = (0.5 - dist) * 1.5 * u_alpha;
    alpha = pow(alpha, 1.4);
    alpha = min(alpha, 0.8); // 限制最大透明度

    gl_FragColor = vec4(v_color, alpha);
}
`;

// ========== Helper Functions ==========
function createShader(gl, type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        console.error('Shader compile error:', gl.getShaderInfoLog(shader));
        gl.deleteShader(shader);
        return null;
    }
    return shader;
}

function createProgram(gl, vertexSrc, fragmentSrc) {
    const vertexShader = createShader(gl, gl.VERTEX_SHADER, vertexSrc);
    const fragmentShader = createShader(gl, gl.FRAGMENT_SHADER, fragmentSrc);
    if (!vertexShader || !fragmentShader) return null;

    const program = gl.createProgram();
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        console.error('Program link error:', gl.getProgramInfoLog(program));
        return null;
    }
    return program;
}

function loadImage(src) {
    return new Promise((resolve) => {
        // #ifdef H5
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = () => resolve(img);
        img.onerror = (e) => {
            console.error("Image load failed:", src, e);
            resolve(null);
        };
        img.src = src;
        // #endif

        // #ifndef H5
        resolve(null);
        // #endif
    });
}

/**
 * 从图像中提取粒子数据 (位置 + 颜色)
 * @returns {{ positions: Float32Array, colors: Float32Array, count: number }}
 */
function extractParticlesFromImage(image, targetWidth, targetHeight, step = 2) {
    if (!image) return { positions: new Float32Array(0), colors: new Float32Array(0), count: 0 };

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const w = image.width;
    const h = image.height;

    // 保持宽高比缩放到目标尺寸
    const scale = Math.min(targetWidth / w, targetHeight / h);
    const drawW = w * scale;
    const drawH = h * scale;

    canvas.width = targetWidth;
    canvas.height = targetHeight;
    // 居中绘制
    const dx = (targetWidth - drawW) / 2;
    const dy = (targetHeight - drawH) / 2;
    ctx.drawImage(image, dx, dy, drawW, drawH);

    const imgData = ctx.getImageData(0, 0, targetWidth, targetHeight);
    const data = imgData.data;
    const positions = [];
    const colors = [];

    // 扫描像素
    for (let y = 0; y < targetHeight; y += step) {
        for (let x = 0; x < targetWidth; x += step) {
            const index = (y * targetWidth + x) * 4;
            const r = data[index];
            const g = data[index + 1];
            const b = data[index + 2];
            const alpha = data[index + 3];

            // 可见性阈值
            if (alpha > 30) {
                // 转换为中心坐标系 (0,0 在中心), 翻转 Y 轴
                const px = x - targetWidth / 2;
                const py = -(y - targetHeight / 2);

                // 添加微小抖动增加有机感
                positions.push(px + (Math.random() - 0.5) * step * 0.3);
                positions.push(py + (Math.random() - 0.5) * step * 0.3);

                // 颜色归一化到 0-1
                colors.push(r / 255);
                colors.push(g / 255);
                colors.push(b / 255);
            }
        }
    }

    console.log(`[extractParticlesFromImage] Extracted ${positions.length / 2} particles`);
    return {
        positions: new Float32Array(positions),
        colors: new Float32Array(colors),
        count: positions.length / 2
    };
}

/**
 * 生成随机的起始位置 (屏幕边缘或更远)
 */
function generateScatterPositions(count, canvasWidth, canvasHeight) {
    const startPositions = new Float32Array(count * 2);
    const maxRadius = Math.max(canvasWidth, canvasHeight) * 0.7;

    for (let i = 0; i < count; i++) {
        // 随机角度
        const angle = Math.random() * Math.PI * 2;
        // 随机半径 (从边缘向外)
        const radius = maxRadius * (0.6 + Math.random() * 0.6);

        startPositions[i * 2] = Math.cos(angle) * radius;
        startPositions[i * 2 + 1] = Math.sin(angle) * radius;
    }

    return startPositions;
}


// ========== Main Entry Point ==========
/**
 * 初始化五色环粒子动画
 * @param {HTMLCanvasElement} canvas 
 * @param {Object} config - { imageUrl, scale, position, rotationSpeed, morphDuration, particleDensity }
 * @param {Object} globalOptions - { particleSize }
 */
export function initImageMorph(canvas, config, globalOptions = {}) {
    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    if (!gl) {
        console.error("WebGL not supported");
        return null;
    }

    // 启用透明度混合
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);

    // 编译着色器程序
    const program = createProgram(gl, vertexShaderSrc, fragmentShaderSrc);
    if (!program) return null;

    // 获取 Attribute/Uniform 位置
    const locs = {
        a_pos_start: gl.getAttribLocation(program, 'a_pos_start'),
        a_pos_target: gl.getAttribLocation(program, 'a_pos_target'),
        a_color: gl.getAttribLocation(program, 'a_color'),
        u_canvas_size: gl.getUniformLocation(program, 'u_canvas_size'),
        u_pointSize: gl.getUniformLocation(program, 'u_pointSize'),
        u_morphProgress: gl.getUniformLocation(program, 'u_morphProgress'),
        u_rotation: gl.getUniformLocation(program, 'u_rotation'),
        u_offset: gl.getUniformLocation(program, 'u_offset'),
        u_scale: gl.getUniformLocation(program, 'u_scale'),
        u_alpha: gl.getUniformLocation(program, 'u_alpha')
    };

    // 调试: 检查 a_color 属性位置
    console.log('[initImageMorph] a_color location:', locs.a_color);

    // 状态
    let isInitialized = false;
    let buffers = {
        posStart: null,
        posTarget: null,
        colors: null,
        count: 0
    };
    let morphProgress = 0;
    let isMorphing = true;
    let rotation = 0;

    // 配置
    const morphDuration = config.morphDuration || 2.5;
    const rotationSpeed = config.rotationSpeed || 0.4;
    const offsetX = config.position?.x || 0;
    const offsetY = config.position?.y || 0;
    const scaleX = config.scale?.x || 1.0;
    const scaleY = config.scale?.y || 1.0;
    const particleSize = globalOptions.particleSize || 2.5;

    // 异步加载图片
    loadImage(config.imageUrl).then(img => {
        if (!img) {
            console.error('[initImageMorph] Failed to load image:', config.imageUrl);
            return;
        }

        // 提取粒子
        const step = config.particleDensity || 2;
        const { positions, colors, count } = extractParticlesFromImage(img, 500, 500, step);

        if (count === 0) {
            console.warn('[initImageMorph] No particles extracted from image');
            return;
        }

        buffers.count = count;

        // 生成起始位置
        const startPositions = generateScatterPositions(count, canvas.width, canvas.height);

        // 创建 WebGL 缓冲区
        // 起始位置
        buffers.posStart = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.posStart);
        gl.bufferData(gl.ARRAY_BUFFER, startPositions, gl.STATIC_DRAW);

        // 目标位置
        buffers.posTarget = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.posTarget);
        gl.bufferData(gl.ARRAY_BUFFER, positions, gl.STATIC_DRAW);

        // 颜色
        buffers.colors = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.colors);
        gl.bufferData(gl.ARRAY_BUFFER, colors, gl.STATIC_DRAW);

        isInitialized = true;
        console.log('[initImageMorph] Initialized with', count, 'particles');
    });

    let animationId;
    let lastTime = Date.now();

    function render() {
        if (!isInitialized || buffers.count === 0) {
            animationId = requestAnimationFrame(render);
            return;
        }

        const now = Date.now();
        const dt = (now - lastTime) / 1000;
        lastTime = now;

        // 更新动画状态
        if (isMorphing) {
            morphProgress += dt / morphDuration;
            if (morphProgress >= 1.0) {
                morphProgress = 1.0;
                isMorphing = false;
            }
        } else {
            // 聚合完成后开始旋转 (顺时针 = 负角速度)
            rotation -= dt * rotationSpeed;
        }

        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.clearColor(0.0, 0.0, 0.0, 0.0);
        gl.clear(gl.COLOR_BUFFER_BIT);

        gl.useProgram(program);

        // 设置 Uniforms
        gl.uniform2f(locs.u_canvas_size, canvas.width, canvas.height);
        gl.uniform1f(locs.u_pointSize, particleSize);
        gl.uniform1f(locs.u_morphProgress, morphProgress);
        gl.uniform1f(locs.u_rotation, rotation);
        gl.uniform2f(locs.u_offset, offsetX, offsetY);
        gl.uniform2f(locs.u_scale, scaleX, scaleY);
        gl.uniform1f(locs.u_alpha, 0.6); // 降低透明度防止过亮

        // 绑定起始位置
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.posStart);
        gl.enableVertexAttribArray(locs.a_pos_start);
        gl.vertexAttribPointer(locs.a_pos_start, 2, gl.FLOAT, false, 0, 0);

        // 绑定目标位置
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.posTarget);
        gl.enableVertexAttribArray(locs.a_pos_target);
        gl.vertexAttribPointer(locs.a_pos_target, 2, gl.FLOAT, false, 0, 0);

        // 绑定颜色
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.colors);
        gl.enableVertexAttribArray(locs.a_color);
        gl.vertexAttribPointer(locs.a_color, 3, gl.FLOAT, false, 0, 0);

        // 绘制
        gl.drawArrays(gl.POINTS, 0, buffers.count);

        animationId = requestAnimationFrame(render);
    }

    render();

    return {
        destroy: () => {
            if (animationId) cancelAnimationFrame(animationId);
        }
    };
}
