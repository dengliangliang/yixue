/**
 * image_morph.js - 五色环粒子聚合动画
 * 
 * 功能:
 * 1. 从图片提取像素颜色和位置
 * 2. 粒子从随机位置聚合到目标位置
 * 3. 聚合后持续顺时针旋转
 * 
 * 修复要点:
 * - 每个粒子使用从图片采样的真实颜色
 * - 确保 a_color attribute 正确绑定
 * - 添加调试日志验证数据
 */

// ========== Vertex Shader ==========
const VERTEX_SHADER = `
precision mediump float;

// 属性 (每个粒子独立)
attribute vec2 a_startPos;   // 随机起始位置
attribute vec2 a_targetPos;  // 目标位置 (从图片采样)
attribute vec3 a_color;      // 颜色 (RGB, 0-1)

// Uniforms (全局)
uniform float u_progress;    // 聚合进度 0->1
uniform float u_rotation;    // 旋转角度 (弧度)
uniform vec2 u_canvasSize;   // Canvas 尺寸
uniform float u_pointSize;   // 粒子大小
uniform vec2 u_scale;        // 缩放

// 传递给片段着色器
varying vec3 v_color;

void main() {
    // 1. 聚合插值
    vec2 pos = mix(a_startPos, a_targetPos, u_progress);
    
    // 2. 旋转 (顺时针 = 负角度)
    float c = cos(u_rotation);
    float s = sin(u_rotation);
    pos = vec2(pos.x * c - pos.y * s, pos.x * s + pos.y * c);
    
    // 3. 缩放
    pos *= u_scale;
    
    // 4. 转换到裁剪空间 (-1 to 1)
    vec2 clipPos = pos / (u_canvasSize * 0.5);
    
    gl_Position = vec4(clipPos, 0.0, 1.0);
    gl_PointSize = u_pointSize;
    
    // 传递颜色
    v_color = a_color;
}
`;

// ========== Fragment Shader ==========
const FRAGMENT_SHADER = `
precision mediump float;

varying vec3 v_color;

void main() {
    // 圆形粒子
    vec2 coord = gl_PointCoord - vec2(0.5);
    float dist = length(coord);
    if (dist > 0.5) discard;
    
    // 边缘柔化 (更锐利的边缘)
    float alpha = 1.0 - smoothstep(0.4, 0.5, dist);
    
    // 增强颜色饱和度 (让颜色更鲜明)
    vec3 enhancedColor = v_color * 1.15; // 轻微提亮
    enhancedColor = clamp(enhancedColor, 0.0, 1.0);
    
    // 提高透明度到 0.95
    gl_FragColor = vec4(enhancedColor, alpha * 0.95);
}
`;

// ========== 工具函数 ==========

function createShader(gl, type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);

    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        console.error('[Shader] 编译失败:', gl.getShaderInfoLog(shader));
        gl.deleteShader(shader);
        return null;
    }
    return shader;
}

function createProgram(gl, vsSource, fsSource) {
    const vs = createShader(gl, gl.VERTEX_SHADER, vsSource);
    const fs = createShader(gl, gl.FRAGMENT_SHADER, fsSource);
    if (!vs || !fs) return null;

    const program = gl.createProgram();
    gl.attachShader(program, vs);
    gl.attachShader(program, fs);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        console.error('[Program] 链接失败:', gl.getProgramInfoLog(program));
        return null;
    }
    return program;
}

function loadImage(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = (e) => {
            console.error('[loadImage] 加载失败:', url);
            reject(e);
        };
        img.src = url;
    });
}

/**
 * 从图片提取粒子数据
 * @returns {{ startPos: Float32Array, targetPos: Float32Array, colors: Float32Array, count: number }}
 */
function extractParticles(image, sampleSize, step, canvasWidth, canvasHeight) {
    const offCanvas = document.createElement('canvas');
    const ctx = offCanvas.getContext('2d', { willReadFrequently: true });

    // 保持宽高比缩放
    const scale = Math.min(sampleSize / image.width, sampleSize / image.height);
    const drawW = Math.floor(image.width * scale);
    const drawH = Math.floor(image.height * scale);

    offCanvas.width = drawW;
    offCanvas.height = drawH;
    ctx.drawImage(image, 0, 0, drawW, drawH);

    const imageData = ctx.getImageData(0, 0, drawW, drawH);
    const pixels = imageData.data;

    const startPos = [];
    const targetPos = [];
    const colors = [];

    const maxRadius = Math.max(canvasWidth, canvasHeight) * 0.8;

    for (let y = 0; y < drawH; y += step) {
        for (let x = 0; x < drawW; x += step) {
            const i = (y * drawW + x) * 4;
            const r = pixels[i];
            const g = pixels[i + 1];
            const b = pixels[i + 2];
            const a = pixels[i + 3];

            // 只取可见像素
            if (a > 50) {
                // 目标位置 (以中心为原点)
                const tx = x - drawW / 2;
                const ty = -(y - drawH / 2); // Y轴翻转

                // 随机起始位置 (屏幕外围)
                const angle = Math.random() * Math.PI * 2;
                const radius = maxRadius * (0.5 + Math.random() * 0.5);
                const sx = Math.cos(angle) * radius;
                const sy = Math.sin(angle) * radius;

                startPos.push(sx, sy);
                targetPos.push(tx, ty);
                colors.push(r / 255, g / 255, b / 255);
            }
        }
    }

    console.log(`[extractParticles] 提取 ${targetPos.length / 2} 个粒子`);
    console.log(`[extractParticles] 颜色样本:`, colors.slice(0, 9)); // 打印前3个粒子的颜色

    return {
        startPos: new Float32Array(startPos),
        targetPos: new Float32Array(targetPos),
        colors: new Float32Array(colors),
        count: targetPos.length / 2
    };
}

// ========== 主入口 ==========
/**
 * 初始化五色环粒子动画
 * @param {HTMLCanvasElement} canvas
 * @param {Object} config - { imageUrl, scale, rotationSpeed, morphDuration, particleStep }
 * @param {Object} options - { particleSize }
 */
export function initImageMorph(canvas, config, options = {}) {
    console.log('[initImageMorph] 开始初始化...');

    const gl = canvas.getContext('webgl', { alpha: true, premultipliedAlpha: false });
    if (!gl) {
        console.error('[initImageMorph] WebGL 不支持');
        return null;
    }

    // 透明度混合
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);

    // 编译 Shader
    const program = createProgram(gl, VERTEX_SHADER, FRAGMENT_SHADER);
    if (!program) return null;

    // 获取 Attribute 位置
    const aStartPos = gl.getAttribLocation(program, 'a_startPos');
    const aTargetPos = gl.getAttribLocation(program, 'a_targetPos');
    const aColor = gl.getAttribLocation(program, 'a_color');

    console.log('[initImageMorph] Attribute 位置:', { aStartPos, aTargetPos, aColor });

    // 检查是否有效
    if (aStartPos === -1 || aTargetPos === -1 || aColor === -1) {
        console.error('[initImageMorph] Attribute 位置无效! 可能被优化掉了。');
    }

    // 获取 Uniform 位置
    const uProgress = gl.getUniformLocation(program, 'u_progress');
    const uRotation = gl.getUniformLocation(program, 'u_rotation');
    const uCanvasSize = gl.getUniformLocation(program, 'u_canvasSize');
    const uPointSize = gl.getUniformLocation(program, 'u_pointSize');
    const uScale = gl.getUniformLocation(program, 'u_scale');

    // 配置参数
    const morphDuration = config.morphDuration || 2.5; // 聚合时长 (秒)
    const baseRotationSpeed = config.rotationSpeed || 0.2; // 初始旋转速度 (弧度/秒)
    const maxRotationSpeed = 1.5; // 最大旋转速度 (加快)
    const accelDuration = 2.0; // 加速到最大速度所需时间 (缩短)
    const scaleX = config.scale?.x || 1.0;
    const scaleY = config.scale?.y || 1.0;
    const particleSize = options.particleSize || 2.5;
    const particleStep = config.particleStep || 3;

    // 状态
    let buffers = { startPos: null, targetPos: null, colors: null };
    let particleCount = 0;
    let isReady = false;
    let progress = 0;
    let rotation = 0;
    let rotationTime = 0; // 记录旋转开始后的时间
    let animationId = null;
    let lastTime = Date.now();

    // 异步加载图片并提取粒子
    loadImage(config.imageUrl).then(img => {
        console.log('[initImageMorph] 图片加载成功:', img.width, 'x', img.height);

        const data = extractParticles(img, 400, particleStep, canvas.width, canvas.height);
        particleCount = data.count;

        if (particleCount === 0) {
            console.warn('[initImageMorph] 没有提取到粒子!');
            return;
        }

        // 创建并填充 Buffer
        buffers.startPos = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.startPos);
        gl.bufferData(gl.ARRAY_BUFFER, data.startPos, gl.STATIC_DRAW);

        buffers.targetPos = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.targetPos);
        gl.bufferData(gl.ARRAY_BUFFER, data.targetPos, gl.STATIC_DRAW);

        buffers.colors = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.colors);
        gl.bufferData(gl.ARRAY_BUFFER, data.colors, gl.STATIC_DRAW);

        console.log('[initImageMorph] Buffer 创建完成, 粒子数:', particleCount);
        isReady = true;

    }).catch(err => {
        console.error('[initImageMorph] 初始化失败:', err);
    });

    // 渲染循环
    function render() {
        if (!isReady) {
            animationId = requestAnimationFrame(render);
            return;
        }

        const now = Date.now();
        const dt = (now - lastTime) / 1000;
        lastTime = now;

        // 更新动画状态
        if (progress < 1.0) {
            progress += dt / morphDuration;
            if (progress > 1.0) progress = 1.0;
        } else {
            // 聚合完成后开始旋转 (顺时针 = 负值，渐进加速)
            rotationTime += dt;
            // 使用 easeOutQuad 缓动函数实现渐进加速
            const t = Math.min(rotationTime / accelDuration, 1.0);
            const currentSpeed = baseRotationSpeed + (maxRotationSpeed - baseRotationSpeed) * (1 - Math.pow(1 - t, 2));
            rotation -= dt * currentSpeed;
        }

        // 清屏
        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.clearColor(0, 0, 0, 0);
        gl.clear(gl.COLOR_BUFFER_BIT);

        gl.useProgram(program);

        // 设置 Uniforms
        gl.uniform1f(uProgress, progress);
        gl.uniform1f(uRotation, rotation);
        gl.uniform2f(uCanvasSize, canvas.width, canvas.height);
        gl.uniform1f(uPointSize, particleSize);
        gl.uniform2f(uScale, scaleX, scaleY);

        // 绑定 startPos
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.startPos);
        gl.enableVertexAttribArray(aStartPos);
        gl.vertexAttribPointer(aStartPos, 2, gl.FLOAT, false, 0, 0);

        // 绑定 targetPos
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.targetPos);
        gl.enableVertexAttribArray(aTargetPos);
        gl.vertexAttribPointer(aTargetPos, 2, gl.FLOAT, false, 0, 0);

        // 绑定 colors (关键!)
        gl.bindBuffer(gl.ARRAY_BUFFER, buffers.colors);
        gl.enableVertexAttribArray(aColor);
        gl.vertexAttribPointer(aColor, 3, gl.FLOAT, false, 0, 0);

        // 绘制
        gl.drawArrays(gl.POINTS, 0, particleCount);

        animationId = requestAnimationFrame(render);
    }

    render();

    return {
        destroy: () => {
            if (animationId) cancelAnimationFrame(animationId);
        }
    };
}
