@props(['model', 'label' => 'Comprobante', 'file' => null, 'accept' => '.jpg,.jpeg,.png,.pdf'])

<script>
    if (!window.documentScannerRegistered) {
        window.documentScannerRegistered = true;

        const registerScanner = () => {
            if (window.Alpine && !Alpine.data('documentScanner')) {
                Alpine.data('documentScanner', (model) => ({

                    phase: 'idle', // idle | camera | editor | preview
                    editorTab: 'persp', // persp | crop | adjust
                    isProcessing: false,
                    processingMsg: 'Procesando…',

                    // Cámara
                    stream: null,
                    rotation: 0,
                    brightness: 100,
                    contrast: 100,
                    isShuttering: false,
                    canZoom: false,
                    useDigitalZoom: false,
                    zoomValue: 1,
                    minZoom: 1,
                    maxZoom: 4,
                    stepZoom: 0.1,
                    zoomPosX: 50,
                    zoomPosY: 50,

                    // Imágenes
                    srcCanvas: null,
                    srcDataURL: '',
                    warpedCanvas: null,
                    warpedDataURL: '',

                    // Handles perspectiva [TL, TR, BR, BL] en % de la imagen mostrada
                    handles: [{
                        x: 0,
                        y: 0
                    }, {
                        x: 100,
                        y: 0
                    }, {
                        x: 100,
                        y: 100
                    }, {
                        x: 0,
                        y: 100
                    }],
                    _warpDirty: true,

                    // Recorte en % de la imagen warpeada
                    crop: {
                        x: 0,
                        y: 0,
                        w: 100,
                        h: 100
                    },
                    _dt: null,
                    _ds: null,

                    // Ajustes
                    adjBrightness: 100,
                    adjContrast: 100,

                    // Final
                    finalURL: '',
                    finalBlob: null,

                    // Ecuaciones y utilidades (definidas dentro del componente o accesibles)
                    _gaussSolve8(A, b) {
                        const n = 8;
                        const M = A.map((r, i) => [...r, b[i]]);
                        for (let c = 0; c < n; c++) {
                            let p = c;
                            for (let r = c + 1; r < n; r++)
                                if (Math.abs(M[r][c]) > Math.abs(M[p][c])) p = r;
                            [M[c], M[p]] = [M[p], M[c]];
                            for (let r = 0; r < n; r++) {
                                if (r === c || !M[c][c]) continue;
                                const f = M[r][c] / M[c][c];
                                for (let k = c; k <= n; k++) M[r][k] -= f * M[c][k];
                            }
                        }
                        return M.map((r, i) => r[n] / r[i]);
                    },

                    _computeH(src4, dst4) {
                        const A = [],
                            b = [];
                        for (let i = 0; i < 4; i++) {
                            const [sx, sy, dx, dy] = [src4[i].x, src4[i].y, dst4[i].x, dst4[i].y];
                            A.push([sx, sy, 1, 0, 0, 0, -dx * sx, -dx * sy]);
                            A.push([0, 0, 0, sx, sy, 1, -dy * sx, -dy * sy]);
                            b.push(dx, dy);
                        }
                        const h = this._gaussSolve8(A, b);
                        return [...h, 1];
                    },

                    _warpImage(srcCanvas, corners) {
                        const [TL, TR, BR, BL] = corners;
                        let W = Math.round(Math.max(Math.hypot(TR.x - TL.x, TR.y - TL.y), Math.hypot(BR
                            .x - BL.x, BR.y - BL.y)));
                        let H = Math.round(Math.max(Math.hypot(BL.x - TL.x, BL.y - TL.y), Math.hypot(BR
                            .x - TR.x, BR.y - TR.y)));
                        if (W < 1 || H < 1) return null;
                        const MAX = 1800;
                        if (W > MAX || H > MAX) {
                            const s = Math.min(MAX / W, MAX / H);
                            W = Math.round(W * s);
                            H = Math.round(H * s);
                        }
                        const dstPts = [{
                            x: 0,
                            y: 0
                        }, {
                            x: W,
                            y: 0
                        }, {
                            x: W,
                            y: H
                        }, {
                            x: 0,
                            y: H
                        }];
                        const Hinv = this._computeH(dstPts, [TL, TR, BR, BL]);
                        const [h0, h1, h2, h3, h4, h5, h6, h7] = Hinv;
                        const out = document.createElement('canvas');
                        out.width = W;
                        out.height = H;
                        const octx = out.getContext('2d', {
                            willReadFrequently: true
                        });
                        const sw = srcCanvas.width,
                            sh = srcCanvas.height;
                        const sctx = srcCanvas.getContext('2d', {
                            willReadFrequently: true
                        });
                        const sd = sctx.getImageData(0, 0, sw, sh).data;
                        const outImg = octx.createImageData(W, H);
                        const dd = outImg.data;
                        const sw4 = sw * 4;
                        let di = 0;
                        for (let oy = 0; oy < H; oy++) {
                            for (let ox = 0; ox < W; ox++) {
                                const ww = h6 * ox + h7 * oy + 1;
                                const sx = (h0 * ox + h1 * oy + h2) / ww;
                                const sy = (h3 * ox + h4 * oy + h5) / ww;
                                const x0 = sx | 0,
                                    y0 = sy | 0;
                                if (x0 >= 0 && y0 >= 0 && x0 < sw - 1 && y0 < sh - 1) {
                                    const fx = sx - x0,
                                        fy = sy - y0;
                                    const w00 = (1 - fx) * (1 - fy),
                                        w10 = fx * (1 - fy),
                                        w01 = (1 - fx) * fy,
                                        w11 = fx * fy;
                                    const i00 = y0 * sw4 + x0 * 4;
                                    const i10 = i00 + 4,
                                        i01 = i00 + sw4,
                                        i11 = i01 + 4;
                                    dd[di] = w00 * sd[i00] + w10 * sd[i10] + w01 * sd[i01] + w11 * sd[
                                        i11];
                                    dd[di + 1] = w00 * sd[i00 + 1] + w10 * sd[i10 + 1] + w01 * sd[i01 +
                                        1] + w11 * sd[i11 + 1];
                                    dd[di + 2] = w00 * sd[i00 + 2] + w10 * sd[i10 + 2] + w01 * sd[i01 +
                                        2] + w11 * sd[i11 + 2];
                                    dd[di + 3] = 255;
                                } else if (x0 >= 0 && y0 >= 0 && x0 < sw && y0 < sh) {
                                    const i = y0 * sw4 + x0 * 4;
                                    dd[di] = sd[i];
                                    dd[di + 1] = sd[i + 1];
                                    dd[di + 2] = sd[i + 2];
                                    dd[di + 3] = 255;
                                }
                                di += 4;
                            }
                        }
                        octx.putImageData(outImg, 0, 0);
                        return out;
                    },

                    _resizeCanvas(src, maxPx) {
                        if (src.width <= maxPx && src.height <= maxPx) return src;
                        const s = Math.min(maxPx / src.width, maxPx / src.height);
                        const c = document.createElement('canvas');
                        c.width = Math.round(src.width * s);
                        c.height = Math.round(src.height * s);
                        c.getContext('2d', {
                            willReadFrequently: true
                        }).drawImage(src, 0, 0, c.width, c.height);
                        return c;
                    },

                    /* ── CÁMARA ─────────────────────────────────── */
                    startCamera() {
                        this.$refs.cameraInput.click();
                    },

                    applyOpticalZoom() {
                        if (!this.stream || this.useDigitalZoom) return;
                        this.stream.getVideoTracks()[0]?.applyConstraints({
                            advanced: [{
                                zoom: this.zoomValue
                            }]
                        }).catch(() => {});
                    },

                    handleWheel(e) {
                        if (!this.canZoom) return;
                        const st = this.useDigitalZoom ? 0.15 : (this.maxZoom - this.minZoom) / 25;
                        this.zoomValue = parseFloat(Math.min(this.maxZoom, Math.max(this.minZoom, this
                            .zoomValue + (e.deltaY < 0 ? st : -st))).toFixed(2));
                        if (this.zoomValue <= this.minZoom) {
                            this.zoomPosX = 50;
                            this.zoomPosY = 50;
                        }
                        this.applyOpticalZoom();
                    },

                    _isPanning: false,
                    _panStart: null,
                    startPan(e) {
                        if (!this.useDigitalZoom || this.zoomValue <= 1) return;
                        this._isPanning = true;
                        const cx = e.touches ? e.touches[0].clientX : e.clientX;
                        const cy = e.touches ? e.touches[0].clientY : e.clientY;
                        this._panStart = {
                            cx,
                            cy,
                            x: this.zoomPosX,
                            y: this.zoomPosY
                        };
                        const move = ev => {
                            if (!this._isPanning) return;
                            const ex = ev.touches ? ev.touches[0].clientX : ev.clientX;
                            const ey = ev.touches ? ev.touches[0].clientY : ev.clientY;
                            const r = this.$refs.video.parentElement.getBoundingClientRect();
                            const dx = ((this._panStart.cx - ex) / r.width) * (100 / this
                                .zoomValue);
                            const dy = ((this._panStart.cy - ey) / r.height) * (100 / this
                                .zoomValue);
                            this.zoomPosX = Math.max(0, Math.min(100, this._panStart.x + dx));
                            this.zoomPosY = Math.max(0, Math.min(100, this._panStart.y + dy));
                        };
                        const up = () => {
                            this._isPanning = false;
                            window.removeEventListener('mousemove', move);
                            window.removeEventListener('mouseup', up);
                            window.removeEventListener('touchmove', move);
                            window.removeEventListener('touchend', up);
                        };
                        window.addEventListener('mousemove', move);
                        window.addEventListener('mouseup', up);
                        window.addEventListener('touchmove', move, {
                            passive: false
                        });
                        window.addEventListener('touchend', up);
                    },

                    rotateLeft() {
                        this.zoomValue = this.minZoom;
                        this.zoomPosX = 50;
                        this.zoomPosY = 50;
                        this.rotation = (this.rotation - 90 + 360) % 360;
                    },
                    rotateRight() {
                        this.zoomValue = this.minZoom;
                        this.zoomPosX = 50;
                        this.zoomPosY = 50;
                        this.rotation = (this.rotation + 90) % 360;
                    },

                    getRotationScale() {
                        if (this.rotation % 180 === 0) return 1;
                        const v = this.$refs.video;
                        if (!v?.videoWidth) return 0.55;
                        return Math.min(
                            (v.parentElement?.clientHeight ?? window.innerHeight * 0.7) / v
                            .videoWidth,
                            (v.parentElement?.clientWidth ?? window.innerWidth) / v.videoHeight, 1
                        );
                    },

                    stopCamera() {
                        this.stream?.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    },

                    capture() {
                        this.isShuttering = true;
                        setTimeout(() => this.isShuttering = false, 200);
                        const v = this.$refs.video,
                            vw = v.videoWidth,
                            vh = v.videoHeight;
                        if (!vw || !vh) return;
                        const rot = this.rotation % 180 !== 0,
                            cw = rot ? vh : vw,
                            ch = rot ? vw : vh;
                        const raw = document.createElement('canvas');
                        raw.width = cw;
                        raw.height = ch;
                        const ctx = raw.getContext('2d', {
                            willReadFrequently: true
                        });
                        ctx.save();
                        ctx.filter = `brightness(${this.brightness}%) contrast(${this.contrast}%)`;
                        ctx.translate(cw / 2, ch / 2);
                        ctx.rotate(this.rotation * Math.PI / 180);
                        if (this.useDigitalZoom && this.zoomValue > 1) {
                            const z = this.zoomValue,
                                sw = vw / z,
                                sh = vh / z;
                            const sx = Math.max(0, Math.min(vw - sw, vw * (this.zoomPosX / 100) - sw /
                                2));
                            const sy = Math.max(0, Math.min(vh - sh, vh * (this.zoomPosY / 100) - sh /
                                2));
                            ctx.drawImage(v, sx, sy, sw, sh, -vw / 2, -vh / 2, vw, vh);
                        } else {
                            ctx.drawImage(v, 0, 0, vw, vh, -vw / 2, -vh / 2, vw, vh);
                        }
                        ctx.restore();
                        this.srcCanvas = this._resizeCanvas(raw, 2400);
                        this.srcDataURL = this.srcCanvas.toDataURL('image/jpeg', 0.9);
                        this._initEditor();
                        setTimeout(() => {
                            this.stopCamera();
                            this.phase = 'editor';
                        }, 120);
                    },

                    handleFileCapture(e) {
                        const file = e.target.files[0];
                        if (!file) return;
                        const fr = new FileReader();
                        fr.onload = ev => {
                            const img = new Image();
                            img.onload = () => {
                                const c = document.createElement('canvas');
                                c.width = img.width;
                                c.height = img.height;
                                c.getContext('2d', {
                                    willReadFrequently: true
                                }).drawImage(img, 0, 0);
                                this.srcCanvas = this._resizeCanvas(c, 2400);
                                this.srcDataURL = this.srcCanvas.toDataURL('image/jpeg', 0.9);
                                this._initEditor();
                                this._autoDetectEdges();
                                this.phase = 'editor';
                            };
                            img.src = ev.target.result;
                        };
                        fr.readAsDataURL(file);
                    },

                    _initEditor() {
                        this.handles = [{
                            x: 0,
                            y: 0
                        }, {
                            x: 100,
                            y: 0
                        }, {
                            x: 100,
                            y: 100
                        }, {
                            x: 0,
                            y: 100
                        }];
                        this.crop = {
                            x: 0,
                            y: 0,
                            w: 100,
                            h: 100
                        };
                        this.adjBrightness = 100;
                        this.adjContrast = 100;
                        this.warpedCanvas = null;
                        this.warpedDataURL = '';
                        this._warpDirty = true;
                        this.editorTab = 'persp';
                    },

                    _autoDetectEdges() {
                        const src = this.srcCanvas;
                        if (!src) return;

                        // Downsample a ~200px para velocidad
                        const scale = Math.min(1, 200 / Math.max(src.width, src.height));
                        const sw = Math.round(src.width * scale);
                        const sh = Math.round(src.height * scale);
                        const small = document.createElement('canvas');
                        small.width = sw;
                        small.height = sh;
                        small.getContext('2d').drawImage(src, 0, 0, sw, sh);
                        const ctx = small.getContext('2d', { willReadFrequently: true });
                        const data = ctx.getImageData(0, 0, sw, sh).data;

                        const lum = (x, y) => {
                            const i = (Math.round(y) * sw + Math.round(x)) * 4;
                            return 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                        };

                        // Color de fondo: promedio de 4 esquinas
                        const bgL = (lum(0, 0) + lum(sw - 1, 0) + lum(0, sh - 1) + lum(sw - 1, sh - 1)) / 4;
                        const threshold = 28;
                        const minHits = ratio => v => v > ratio;

                        const scanH = (y, x0, x1) => {
                            let hits = 0;
                            for (let x = x0; x < x1; x++)
                                if (Math.abs(lum(x, y) - bgL) > threshold) hits++;
                            return hits;
                        };
                        const scanV = (x, y0, y1) => {
                            let hits = 0;
                            for (let y = y0; y < y1; y++)
                                if (Math.abs(lum(x, y) - bgL) > threshold) hits++;
                            return hits;
                        };

                        const cx0 = Math.floor(sw * 0.15), cx1 = Math.ceil(sw * 0.85);
                        const cy0 = Math.floor(sh * 0.15), cy1 = Math.ceil(sh * 0.85);
                        const minHW = (cx1 - cx0) * 0.25;
                        const minVH = (cy1 - cy0) * 0.25;

                        let top = 0, bottom = sh - 1, left = 0, right = sw - 1;

                        for (let y = 0; y < sh; y++)
                            if (scanH(y, cx0, cx1) >= minHW) { top = Math.max(0, y - 1); break; }
                        for (let y = sh - 1; y >= 0; y--)
                            if (scanH(y, cx0, cx1) >= minHW) { bottom = Math.min(sh - 1, y + 1); break; }
                        for (let x = 0; x < sw; x++)
                            if (scanV(x, cy0, cy1) >= minVH) { left = Math.max(0, x - 1); break; }
                        for (let x = sw - 1; x >= 0; x--)
                            if (scanV(x, cy0, cy1) >= minVH) { right = Math.min(sw - 1, x + 1); break; }

                        const lx = left / sw * 100, rx = right / sw * 100;
                        const ty = top / sh * 100, by = bottom / sh * 100;

                        // Solo aplicar si detectó algo más pequeño que el 95% de la imagen
                        if (lx > 1 || rx < 99 || ty > 1 || by < 99) {
                            this.handles = [
                                { x: lx, y: ty },
                                { x: rx, y: ty },
                                { x: rx, y: by },
                                { x: lx, y: by },
                            ];
                            this._warpDirty = true;
                        }
                    },

                    switchTab(tab) {
                        if (tab === this.editorTab) return;
                        if (tab !== 'persp') {
                            if (this._warpDirty || !this.warpedCanvas) {
                                this._computeWarp(() => {
                                    this.editorTab = tab;
                                });
                            } else {
                                this.editorTab = tab;
                            }
                        } else {
                            this._warpDirty = true;
                            this.editorTab = tab;
                        }
                    },

                    _computeWarp(callback) {
                        this.isProcessing = true;
                        this.processingMsg = 'Aplicando corrección de perspectiva…';
                        requestAnimationFrame(() => setTimeout(() => {
                            try {
                                const corners = this.handles.map(h => ({
                                    x: h.x / 100 * this.srcCanvas.width,
                                    y: h.y / 100 * this.srcCanvas.height
                                }));
                                this.warpedCanvas = this._warpImage(this.srcCanvas,
                                    corners);
                                if (!this.warpedCanvas) throw new Error(
                                    'Perspectiva inválida');
                                this.warpedDataURL = this.warpedCanvas.toDataURL(
                                    'image/jpeg', 0.92);
                                this._warpDirty = false;
                                this.isProcessing = false;
                                if (callback) callback();
                            } catch (err) {
                                this.isProcessing = false;
                                alert('Error: ' + err.message);
                            }
                        }, 30));
                    },

                    startDragHandle(e, i) {
                        e.preventDefault();
                        this._warpDirty = true;
                        const move = ev => {
                            ev.preventDefault();
                            const el = this.$refs.perspImg;
                            if (!el) return;
                            const r = el.getBoundingClientRect();
                            const cx = ev.touches ? ev.touches[0].clientX : ev.clientX;
                            const cy = ev.touches ? ev.touches[0].clientY : ev.clientY;
                            const x = Math.max(0, Math.min(100, ((cx - r.left) / r.width) * 100));
                            const y = Math.max(0, Math.min(100, ((cy - r.top) / r.height) * 100));
                            this.handles = this.handles.map((h, j) => j === i ? {
                                x,
                                y
                            } : h);
                        };
                        const up = () => {
                            window.removeEventListener('mousemove', move);
                            window.removeEventListener('mouseup', up);
                            window.removeEventListener('touchmove', move);
                            window.removeEventListener('touchend', up);
                        };
                        window.addEventListener('mousemove', move);
                        window.addEventListener('mouseup', up);
                        window.addEventListener('touchmove', move, {
                            passive: false
                        });
                        window.addEventListener('touchend', up);
                    },

                    get polyPoints() {
                        return this.handles.map(h => `${h.x},${h.y}`).join(' ');
                    },

                    startDragCrop(e, type) {
                        e.preventDefault();
                        e.stopPropagation();
                        const el = this.$refs.warpedImg;
                        if (!el) return;
                        const r = el.getBoundingClientRect();
                        const cx = e.touches ? e.touches[0].clientX : e.clientX;
                        const cy = e.touches ? e.touches[0].clientY : e.clientY;
                        this._dt = type;
                        this._ds = {
                            cx,
                            cy,
                            crop: {
                                ...this.crop
                            },
                            rw: r.width,
                            rh: r.height
                        };
                        const move = ev => {
                            ev.preventDefault();
                            if (!this._dt) return;
                            const ex = ev.touches ? ev.touches[0].clientX : ev.clientX;
                            const ey = ev.touches ? ev.touches[0].clientY : ev.clientY;
                            const dx = ((ex - this._ds.cx) / this._ds.rw) * 100;
                            const dy = ((ey - this._ds.cy) / this._ds.rh) * 100;
                            let {
                                x,
                                y,
                                w,
                                h
                            } = this._ds.crop;
                            const MIN = 5;
                            switch (this._dt) {
                                case 'move':
                                    x = Math.max(0, Math.min(100 - w, x + dx));
                                    y = Math.max(0, Math.min(100 - h, y + dy));
                                    break;
                                case 'tl': {
                                    const nx = Math.max(0, Math.min(x + w - MIN, x + dx)),
                                        ny = Math.max(0, Math.min(y + h - MIN, y + dy));
                                    w += x - nx;
                                    h += y - ny;
                                    x = nx;
                                    y = ny;
                                    break;
                                }
                                case 'tr': {
                                    const ny = Math.max(0, Math.min(y + h - MIN, y + dy));
                                    w = Math.max(MIN, Math.min(100 - x, w + dx));
                                    h += y - ny;
                                    y = ny;
                                    break;
                                }
                                case 'bl': {
                                    const nx = Math.max(0, Math.min(x + w - MIN, x + dx));
                                    w += x - nx;
                                    h = Math.max(MIN, Math.min(100 - y, h + dy));
                                    x = nx;
                                    break;
                                }
                                case 'br':
                                    w = Math.max(MIN, Math.min(100 - x, w + dx));
                                    h = Math.max(MIN, Math.min(100 - y, h + dy));
                                    break;
                                case 't': {
                                    const ny = Math.max(0, Math.min(y + h - MIN, y + dy));
                                    h += y - ny;
                                    y = ny;
                                    break;
                                }
                                case 'b':
                                    h = Math.max(MIN, Math.min(100 - y, h + dy));
                                    break;
                                case 'l': {
                                    const nx = Math.max(0, Math.min(x + w - MIN, x + dx));
                                    w += x - nx;
                                    x = nx;
                                    break;
                                }
                                case 'r':
                                    w = Math.max(MIN, Math.min(100 - x, w + dx));
                                    break;
                            }
                            this.crop = {
                                x,
                                y,
                                w,
                                h
                            };
                        };
                        const up = () => {
                            this._dt = null;
                            this._ds = null;
                            window.removeEventListener('mousemove', move);
                            window.removeEventListener('mouseup', up);
                            window.removeEventListener('touchmove', move);
                            window.removeEventListener('touchend', up);
                        };
                        window.addEventListener('mousemove', move);
                        window.addEventListener('mouseup', up);
                        window.addEventListener('touchmove', move, {
                            passive: false
                        });
                        window.addEventListener('touchend', up);
                    },

                    confirmAll() {
                        this.isProcessing = true;
                        this.processingMsg = 'Generando imagen final…';
                        const doExport = (warped) => {
                            try {
                                let src = warped;
                                const cx = Math.round(this.crop.x / 100 * src.width),
                                    cy = Math.round(this.crop.y / 100 * src.height);
                                const cw = Math.round(this.crop.w / 100 * src.width),
                                    ch = Math.round(this.crop.h / 100 * src.height);
                                const out = document.createElement('canvas');
                                out.width = Math.max(1, cw);
                                out.height = Math.max(1, ch);
                                const oc = out.getContext('2d', {
                                    willReadFrequently: true
                                });
                                oc.filter =
                                    `brightness(${this.adjBrightness}%) contrast(${this.adjContrast}%)`;
                                oc.drawImage(src, cx, cy, cw, ch, 0, 0, cw, ch);
                                out.toBlob(blob => {
                                    if (this.finalURL) URL.revokeObjectURL(this.finalURL);
                                    this.finalBlob = blob;
                                    this.finalURL = URL.createObjectURL(blob);
                                    this.isProcessing = false;
                                    this.phase = 'preview';
                                }, 'image/jpeg', 0.92);
                            } catch (err) {
                                this.isProcessing = false;
                                alert('Error: ' + err.message);
                            }
                        };
                        requestAnimationFrame(() => setTimeout(() => {
                            if (this.warpedCanvas && !this._warpDirty) {
                                doExport(this.warpedCanvas);
                            } else {
                                try {
                                    const corners = this.handles.map(h => ({
                                        x: h.x / 100 * this.srcCanvas.width,
                                        y: h.y / 100 * this.srcCanvas.height
                                    }));
                                    const warped = this._warpImage(this.srcCanvas, corners);
                                    this.warpedCanvas = warped;
                                    this.warpedDataURL = warped.toDataURL('image/jpeg',
                                        0.92);
                                    this._warpDirty = false;
                                    doExport(warped);
                                } catch (err) {
                                    this.isProcessing = false;
                                    alert('Error: ' + err.message);
                                }
                            }
                        }, 30));
                    },

                    uploadFinal() {
                        if (!this.finalBlob) return;
                        const file = new File([this.finalBlob], 'documento_escaneado.jpg', {
                            type: 'image/jpeg'
                        });
                        this.$wire.upload(model, file, () => this.closeAll(), () => alert(
                            'Error al subir.'));
                    },

                    closeAll() {
                        this.stopCamera();
                        if (this.finalURL) URL.revokeObjectURL(this.finalURL);
                        this.phase = 'idle';
                        this.srcCanvas = null;
                        this.srcDataURL = '';
                        this.warpedCanvas = null;
                        this.warpedDataURL = '';
                        this.finalURL = '';
                        this.finalBlob = null;
                        this.handles = [{
                            x: 0,
                            y: 0
                        }, {
                            x: 100,
                            y: 0
                        }, {
                            x: 100,
                            y: 100
                        }, {
                            x: 0,
                            y: 100
                        }];
                        this.crop = {
                            x: 0,
                            y: 0,
                            w: 100,
                            h: 100
                        };
                        this.adjBrightness = 100;
                        this.adjContrast = 100;
                        this.rotation = 0;
                        this.brightness = 100;
                        this.contrast = 100;
                        this.zoomValue = 1;
                        this.zoomPosX = 50;
                        this.zoomPosY = 50;
                        this._warpDirty = true;
                    }

                }));
            }
        };

        if (window.Alpine) {
            registerScanner();
        } else {
            document.addEventListener('alpine:init', registerScanner);
        }
        document.addEventListener('livewire:navigated', registerScanner);
    }
</script>

<div x-data="documentScanner('{{ $model }}')" @keydown.escape.window="closeAll()" class="relative">

    {{-- ── Input área ─────────────────────────────────────── --}}
    <label class="block text-sm mb-1">{{ $label }} <span class="text-red-500">*</span></label>
    <div
        class="group h-11 flex items-center justify-between w-full rounded-lg border border-dashed border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-4 py-2 hover:bg-gray-50 dark:hover:bg-neutral-800 transition">
        <label class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer">
            <div
                class="w-7 h-7 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-600 dark:text-neutral-200" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-800 dark:text-neutral-100">Adjuntar archivo</div>
                <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">
                    @if ($file)
                        {{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'Archivo seleccionado' }}
                    @else
                        JPG, PNG o PDF (máx. 5 MB)
                    @endif
                </div>
            </div>
            <input type="file" wire:model.live="{{ $model }}" accept="{{ $accept }}" class="hidden" />
        </label>
        <button type="button" @click="startCamera()" title="Escanear con cámara"
            class="md:hidden shrink-0 p-1.5 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors cursor-pointer border border-transparent hover:border-indigo-200">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                <circle cx="12" cy="13" r="3" />
            </svg>
        </button>
        <input type="file" x-ref="cameraInput" @change="handleFileCapture($event)" capture="environment"
            accept="image/*" class="hidden" />
    </div>
    @error($model)
        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
    @enderror
    @if ($file)
        @php
            $isPdf = method_exists($file, 'getClientOriginalExtension')
                && strtolower($file->getClientOriginalExtension()) === 'pdf';
            $previewUrl = (!$isPdf && method_exists($file, 'temporaryUrl')) ? $file->temporaryUrl() : null;
        @endphp
        <div x-data="{ removing: false }">
        <div class="mt-1 text-xs flex items-center justify-end gap-3">
            @if ($previewUrl && !$isPdf)
                <div x-data="{ open: false }">
                    <button type="button" @click="open = true"
                        class="cursor-pointer inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-600 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Ver imagen
                    </button>
                    <template x-if="open">
                        <div x-data="{
                                zoom: 1,
                                setOrigin(e) {
                                    const r = e.target.getBoundingClientRect();
                                    e.target.style.transformOrigin = ((e.clientX-r.left)/r.width*100)+'% '+((e.clientY-r.top)/r.height*100)+'%';
                                },
                                wheelZoom(e) { this.zoom = Math.max(1,Math.min(5,this.zoom+(e.deltaY<0?.2:-.2))); this.setOrigin(e); }
                            }"
                            @keydown.escape.window="open = false"
                            class="fixed inset-0 z-[10002] flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="open = false"></div>
                            <button type="button" @click="open = false"
                                class="absolute top-4 right-4 z-[10003] p-3 text-white/40 hover:text-white">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div class="relative z-[10002] flex items-center justify-center overflow-hidden">
                                <img src="{{ $previewUrl }}" alt="Vista previa"
                                    class="max-w-full max-h-[90vh] rounded shadow-2xl transition-transform duration-200 ease-out cursor-zoom-in"
                                    :style="'transform:scale('+zoom+')'"
                                    @mousemove="setOrigin($event)" @wheel.prevent="wheelZoom($event)" draggable="false" />
                            </div>
                            <div x-show="zoom > 1"
                                class="absolute bottom-8 left-1/2 -translate-x-1/2 z-[10003] px-4 py-2 rounded-full bg-white/10 text-white/90 text-xs font-semibold backdrop-blur-xl border border-white/20 pointer-events-none">
                                Zoom: <span x-text="zoom.toFixed(1)+'x'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            @elseif ($previewUrl && $isPdf)
                <a href="{{ $previewUrl }}" target="_blank"
                    class="cursor-pointer inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-600 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    Ver PDF
                </a>
            @endif
            <button type="button"
                @click="removing = true"
                wire:click="$set('{{ $model }}', null)"
                class="cursor-pointer text-red-500 hover:text-red-600 font-medium">
                Quitar archivo
            </button>
        </div>
        <div wire:loading wire:target="{{ $model }}"
            x-show="!removing"
            class="text-xs text-indigo-500 font-bold mt-1 animate-pulse">
            Subiendo…
        </div>
        </div>{{-- x-data removing --}}
    @endif
    @if (!$file)
        <div wire:loading wire:target="{{ $model }}" class="text-xs text-indigo-500 font-bold mt-1 animate-pulse">
            Subiendo…</div>
    @endif

    {{-- ── Overlay de procesamiento ────────────────────────── --}}
    <template x-if="isProcessing">
        <div class="fixed inset-0 z-[10001] bg-black/80 backdrop-blur-sm flex items-center justify-center">
            <div
                class="flex flex-col items-center gap-4 bg-neutral-900 rounded-2xl px-10 py-8 border border-white/10 shadow-2xl">
                <div class="w-10 h-10 border-2 border-white/10 border-t-indigo-400 rounded-full animate-spin"></div>
                <span class="text-white text-sm font-medium" x-text="processingMsg"></span>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: CÁMARA
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'camera'">
        <div class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center p-3 backdrop-blur-md">
            <div
                class="relative w-full max-w-6xl h-[90vh] bg-neutral-950 rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col">
                <div class="relative flex-1 bg-black overflow-hidden flex items-center justify-center"
                    :class="(useDigitalZoom && zoomValue > 1) ? 'cursor-grab active:cursor-grabbing' : 'cursor-crosshair'"
                    @wheel.prevent="handleWheel($event)" @mousedown="startPan($event)"
                    @touchstart.prevent="startPan($event)">
                    <video x-ref="video" autoplay playsinline muted
                        class="w-full h-full object-contain transition-transform duration-75"
                        :style="'transform-origin: ' + zoomPosX + '% ' + zoomPosY + '%; transform:rotate(' + rotation +
                            'deg) scale(' + (getRotationScale() * (useDigitalZoom ? zoomValue : 1)) +
                            '); filter:brightness(' + brightness + '%) contrast(' + contrast + '%)'">
                    </video>
                    <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 opacity-20">
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-r border-b border-white/50"></div>
                        <div class="border-b border-white/50"></div>
                        <div class="border-r border-white/50"></div>
                        <div class="border-r border-white/50"></div>
                        <div></div>
                    </div>
                    <div x-show="isShuttering" class="absolute inset-0 bg-white z-50 pointer-events-none"></div>
                    <div
                        class="absolute top-4 left-4 bg-black/60 backdrop-blur px-3 py-1.5 rounded-lg border border-white/10 text-[10px] text-white/80 font-bold flex gap-3">
                        <span x-text="parseFloat(zoomValue).toFixed(1)+'×'"></span>
                        <span class="border-l border-white/20 pl-3" x-text="rotation+'°'"></span>
                    </div>
                    <template x-if="canZoom">
                        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 bg-black/60 backdrop-blur px-6 py-3 rounded-2xl border border-white/10"
                            style="width:min(70%,400px)" @click.stop>
                            <input type="range" x-model="zoomValue" :min="minZoom" :max="maxZoom"
                                :step="stepZoom" @input="applyOpticalZoom()"
                                class="w-full h-1 accent-indigo-500 bg-white/20 rounded appearance-none cursor-pointer">
                        </div>
                    </template>
                </div>
                <div class="px-6 py-5 flex items-center justify-between bg-neutral-950 border-t border-white/5">
                    <button type="button" @click="closeAll()"
                        class="text-neutral-400 hover:text-white text-sm font-medium transition">Cancelar</button>
                    <button type="button" @click="capture()"
                        class="w-16 h-16 rounded-full border-4 border-white/80 flex items-center justify-center bg-white/5 hover:bg-white/15 hover:scale-105 transition-transform active:scale-95">
                        <div class="w-11 h-11 rounded-full bg-white shadow-lg"></div>
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="rotateLeft()"
                            class="p-3 rounded-xl bg-white/5 hover:bg-white/15 text-white border border-white/5 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M2.5 2v6h6M2.66 15.57a10 10 0 1 0 .57-8.38" />
                            </svg>
                        </button>
                        <button type="button" @click="rotateRight()"
                            class="p-3 rounded-xl bg-white/5 hover:bg-white/15 text-white border border-white/5 transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: EDITOR UNIFICADO — sidebar izquierdo
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'editor'">
        <div class="fixed inset-0 z-[9999] bg-neutral-950 flex flex-col">

            {{-- ── Header ─────────────────────────────── --}}
            <div class="flex items-center justify-between px-4 py-2.5 bg-black/50 border-b border-white/5 shrink-0">
                <button type="button" @click="closeAll()"
                    class="flex items-center gap-1 text-neutral-400 hover:text-white text-sm transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Cancelar
                </button>
                <span class="text-white/60 text-sm font-medium">Editar documento</span>
                <button type="button" @click="confirmAll()"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition active:scale-95">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Confirmar
                </button>
            </div>

            {{-- ── Cuerpo: sidebar + imagen ────────────── --}}
            {{-- Desktop: flex-row | Mobile: flex-col (imagen arriba, controles abajo) --}}
            <div class="flex flex-1 overflow-hidden min-h-0 flex-col md:flex-row">

                {{-- SIDEBAR IZQUIERDO (solo desktop) --}}
                <div class="hidden md:flex w-56 shrink-0 bg-black/40 border-r border-white/5 flex-col overflow-y-auto">

                    {{-- Botones de pestaña --}}
                    <div class="p-3 flex flex-col gap-1">
                        <button type="button" @click="switchTab('persp')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'persp' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 3L3 9l7 3 3 9 8-18z" />
                            </svg>
                            Perspectiva
                        </button>
                        <button type="button" @click="switchTab('crop')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'crop' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="6 2 6 6 2 6" />
                                <polyline points="18 2 18 6 22 6" />
                                <polyline points="6 22 6 18 2 18" />
                                <polyline points="18 22 18 18 22 18" />
                            </svg>
                            Recortar
                        </button>
                        <button type="button" @click="switchTab('adjust')"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-left transition w-full"
                            :class="editorTab === 'adjust' ? 'bg-white/10 text-white' :
                                'text-white/40 hover:text-white hover:bg-white/5'">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14" />
                                <line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" />
                                <line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                            Ajustes
                        </button>
                    </div>

                    {{-- Separador --}}
                    <div class="border-t border-white/5 mx-3"></div>

                    {{-- Perspectiva --}}
                    <div x-show="editorTab === 'persp'" class="p-4 flex flex-col gap-4">
                        <p class="text-white/35 text-xs leading-relaxed">Arrastrá las 4 esquinas (puntos de colores) al
                            borde exacto del documento.</p>
                        <button type="button"
                            @click="handles=[{x:0,y:0},{x:100,y:0},{x:100,y:100},{x:0,y:100}]; _warpDirty=true"
                            class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium">
                            Resetear esquinas
                        </button>
                    </div>

                    {{-- Recortar --}}
                    <div x-show="editorTab === 'crop'" class="p-4 flex flex-col gap-4">
                        <p class="text-white/35 text-xs leading-relaxed">Arrastrá los bordes y esquinas del recuadro
                            blanco para ajustar el recorte.</p>
                        <button type="button" @click="crop={x:0,y:0,w:100,h:100}"
                            class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium">
                            Resetear recorte
                        </button>
                    </div>

                    {{-- Ajustes --}}
                    <div x-show="editorTab === 'adjust'" class="p-4 flex flex-col gap-5">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-[10px] text-white/50 uppercase font-black tracking-widest">Brillo</span>
                                <span class="text-[10px] text-indigo-400 font-mono" x-text="adjBrightness+'%'"></span>
                            </div>
                            <input type="range" x-model="adjBrightness" min="50" max="200"
                                class="w-full h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-[10px] text-white/50 uppercase font-black tracking-widest">Contraste</span>
                                <span class="text-[10px] text-indigo-400 font-mono" x-text="adjContrast+'%'"></span>
                            </div>
                            <input type="range" x-model="adjContrast" min="50" max="300"
                                class="w-full h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                        </div>
                        <div class="border-t border-white/5 pt-3">
                            <button type="button" @click="adjBrightness=100;adjContrast=100"
                                class="w-full text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 py-2 rounded-xl transition font-medium uppercase tracking-wider">
                                Reset ajustes
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ÁREA DE IMAGEN --}}
                <div class="flex-1 bg-[#0d0d0d] flex items-center justify-center overflow-hidden p-4 min-h-0">

                    {{-- Perspectiva: imagen original con handles --}}
                    <div x-show="editorTab === 'persp'" class="relative"
                        style="display:inline-block;max-width:100%;max-height:100%">
                        <img x-ref="perspImg" :src="srcDataURL" class="block select-none rounded-lg shadow-2xl"
                            style="max-width:100%;max-height:calc(100vh - 56px);width:auto;height:auto;object-fit:contain"
                            draggable="false" />

                        <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100"
                            preserveAspectRatio="none">
                            <polygon :points="polyPoints" fill="rgba(99,102,241,0.08)"
                                stroke="rgba(99,102,241,0.85)" stroke-width="0.6" />
                            <line :x1="handles[0].x" :y1="handles[0].y" :x2="handles[2].x"
                                :y2="handles[2].y" stroke="rgba(255,255,255,0.12)" stroke-width="0.3" />
                            <line :x1="handles[1].x" :y1="handles[1].y" :x2="handles[3].x"
                                :y2="handles[3].y" stroke="rgba(255,255,255,0.12)" stroke-width="0.3" />
                        </svg>

                        <template x-for="(h, i) in handles" :key="i">
                            <div class="absolute touch-none z-10 cursor-grab active:cursor-grabbing"
                                :style="'left:' + h.x + '%;top:' + h.y + '%;transform:translate(-50%,-50%)'"
                                @mousedown.prevent="startDragHandle($event, i)"
                                @touchstart.prevent="startDragHandle($event, i)">
                                <div class="w-8 h-8 rounded-full border-[3px] border-white shadow-xl flex items-center justify-center"
                                    :class="(i === 0 || i === 2) ? 'bg-indigo-500' : 'bg-violet-500'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white opacity-90"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Recortar / Ajustes: imagen warpeada --}}
                    <div x-show="editorTab !== 'persp'" class="relative"
                        style="display:inline-block;max-width:100%;max-height:100%">
                        <img x-ref="warpedImg" :src="warpedDataURL" class="block select-none rounded-lg shadow-2xl"
                            :style="'max-width:100%;max-height:calc(100vh - 56px);width:auto;height:auto;filter:brightness(' +
                            adjBrightness + '%) contrast(' + adjContrast + '%)'"
                            draggable="false" />

                        {{-- Overlay de recorte --}}
                        <div x-show="editorTab === 'crop'" class="absolute inset-0">
                            <div class="absolute inset-0 pointer-events-none">
                                <svg class="w-full h-full">
                                    <defs>
                                        <mask id="cmask_{{ md5($model) }}">
                                            <rect width="100%" height="100%" fill="white" />
                                            <rect :x="crop.x + '%'" :y="crop.y + '%'"
                                                :width="crop.w + '%'" :height="crop.h + '%'" fill="black" />
                                        </mask>
                                    </defs>
                                    <rect width="100%" height="100%" fill="rgba(0,0,0,0.58)"
                                        mask="url(#cmask_{{ md5($model) }})" />
                                    <rect :x="crop.x + '%'" :y="crop.y + '%'" :width="crop.w + '%'"
                                        :height="crop.h + '%'" fill="none" stroke="white" stroke-width="1.5" />
                                    <line :x1="(crop.x + crop.w / 3) + '%'" :y1="crop.y + '%'"
                                        :x2="(crop.x + crop.w / 3) + '%'" :y2="(crop.y + crop.h) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="(crop.x + crop.w * 2 / 3) + '%'" :y1="crop.y + '%'"
                                        :x2="(crop.x + crop.w * 2 / 3) + '%'" :y2="(crop.y + crop.h) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="crop.x + '%'" :y1="(crop.y + crop.h / 3) + '%'"
                                        :x2="(crop.x + crop.w) + '%'" :y2="(crop.y + crop.h / 3) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                    <line :x1="crop.x + '%'" :y1="(crop.y + crop.h * 2 / 3) + '%'"
                                        :x2="(crop.x + crop.w) + '%'" :y2="(crop.y + crop.h * 2 / 3) + '%'"
                                        stroke="white" stroke-width="0.4" opacity="0.22" />
                                </svg>
                            </div>
                            <div class="absolute cursor-move"
                                :style="'left:' + crop.x + '%;top:' + crop.y + '%;width:' + crop.w + '%;height:' + crop.h + '%'"
                                @mousedown.self="startDragCrop($event,'move')"
                                @touchstart.self.prevent="startDragCrop($event,'move')">
                                <div class="absolute -top-2.5 -left-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nwse-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'tl')"
                                    @touchstart.stop.prevent="startDragCrop($event,'tl')"></div>
                                <div class="absolute -top-2.5 -right-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nesw-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'tr')"
                                    @touchstart.stop.prevent="startDragCrop($event,'tr')"></div>
                                <div class="absolute -bottom-2.5 -left-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nesw-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'bl')"
                                    @touchstart.stop.prevent="startDragCrop($event,'bl')"></div>
                                <div class="absolute -bottom-2.5 -right-2.5 w-5 h-5 bg-white rounded-sm shadow cursor-nwse-resize z-10"
                                    @mousedown.stop="startDragCrop($event,'br')"
                                    @touchstart.stop.prevent="startDragCrop($event,'br')"></div>
                                <div class="absolute cursor-ns-resize z-10 flex justify-center"
                                    style="top:-13px;left:25%;width:50%;height:13px"
                                    @mousedown.stop="startDragCrop($event,'t')"
                                    @touchstart.stop.prevent="startDragCrop($event,'t')">
                                    <div class="w-8 h-1.5 bg-white rounded-full mt-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ns-resize z-10 flex justify-center items-end"
                                    style="bottom:-13px;left:25%;width:50%;height:13px"
                                    @mousedown.stop="startDragCrop($event,'b')"
                                    @touchstart.stop.prevent="startDragCrop($event,'b')">
                                    <div class="w-8 h-1.5 bg-white rounded-full mb-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ew-resize z-10 flex items-center"
                                    style="left:-13px;top:25%;width:13px;height:50%"
                                    @mousedown.stop="startDragCrop($event,'l')"
                                    @touchstart.stop.prevent="startDragCrop($event,'l')">
                                    <div class="w-1.5 h-8 bg-white rounded-full ml-1.5 shadow"></div>
                                </div>
                                <div class="absolute cursor-ew-resize z-10 flex items-center justify-end"
                                    style="right:-13px;top:25%;width:13px;height:50%"
                                    @mousedown.stop="startDragCrop($event,'r')"
                                    @touchstart.stop.prevent="startDragCrop($event,'r')">
                                    <div class="w-1.5 h-8 bg-white rounded-full mr-1.5 shadow"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- BARRA INFERIOR (solo mobile) --}}
                <div class="md:hidden shrink-0 bg-black/70 border-t border-white/10">

                    {{-- Tabs horizontales --}}
                    <div class="flex border-b border-white/5">
                        <button type="button" @click="switchTab('persp')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'persp' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 3L3 9l7 3 3 9 8-18z" />
                            </svg>
                            Perspectiva
                        </button>
                        <button type="button" @click="switchTab('crop')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'crop' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 2 6 6 2 6" />
                                <polyline points="18 2 18 6 22 6" />
                                <polyline points="6 22 6 18 2 18" />
                                <polyline points="18 22 18 18 22 18" />
                            </svg>
                            Recortar
                        </button>
                        <button type="button" @click="switchTab('adjust')"
                            class="flex-1 flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium transition"
                            :class="editorTab === 'adjust' ? 'text-white border-b-2 border-indigo-500' : 'text-white/40'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14" /><line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" /><line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" /><line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" /><line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                            Ajustes
                        </button>
                    </div>

                    {{-- Contenido compacto según tab --}}
                    <div class="px-4 py-3">

                        {{-- Perspectiva --}}
                        <div x-show="editorTab === 'persp'" class="flex items-center justify-between gap-3">
                            <p class="text-white/40 text-xs">Arrastrá las 4 esquinas al borde del documento.</p>
                            <button type="button"
                                @click="handles=[{x:0,y:0},{x:100,y:0},{x:100,y:100},{x:0,y:100}]; _warpDirty=true"
                                class="shrink-0 text-xs text-white/50 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                Resetear
                            </button>
                        </div>

                        {{-- Recortar --}}
                        <div x-show="editorTab === 'crop'" class="flex items-center justify-between gap-3">
                            <p class="text-white/40 text-xs">Arrastrá los bordes para ajustar el recorte.</p>
                            <button type="button" @click="crop={x:0,y:0,w:100,h:100}"
                                class="shrink-0 text-xs text-white/50 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                Resetear
                            </button>
                        </div>

                        {{-- Ajustes --}}
                        <div x-show="editorTab === 'adjust'" class="space-y-2.5">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] text-white/50 uppercase font-black tracking-widest w-16 shrink-0">Brillo</span>
                                <input type="range" x-model="adjBrightness" min="50" max="200"
                                    class="flex-1 h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                                <span class="text-[10px] text-indigo-400 font-mono w-9 text-right shrink-0" x-text="adjBrightness+'%'"></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] text-white/50 uppercase font-black tracking-widest w-16 shrink-0">Contraste</span>
                                <input type="range" x-model="adjContrast" min="50" max="300"
                                    class="flex-1 h-1.5 accent-indigo-500 bg-white/10 rounded-full appearance-none cursor-pointer">
                                <span class="text-[10px] text-indigo-400 font-mono w-9 text-right shrink-0" x-text="adjContrast+'%'"></span>
                            </div>
                            <div class="flex justify-end pt-0.5">
                                <button type="button" @click="adjBrightness=100;adjContrast=100"
                                    class="text-xs text-white/40 hover:text-white border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition font-medium">
                                    Reset
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </template>

    {{-- ════════════════════════════════════════════════
         FASE: VISTA PREVIA FINAL
    ════════════════════════════════════════════════ --}}
    <template x-if="phase === 'preview'">
        <div class="fixed inset-0 z-[9999] bg-black/95 flex flex-col backdrop-blur-2xl">
            <div class="relative w-full h-full bg-neutral-950 flex flex-col"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="px-5 py-3 border-b border-white/5 text-center shrink-0">
                    <h3 class="text-white font-semibold">Vista previa final</h3>
                    <p class="text-white/40 text-xs mt-0.5">Verificá que el documento se vea correctamente antes de
                        subir</p>
                </div>
                <div class="flex-1 bg-[#0d0d0d] flex items-center justify-center p-4 overflow-hidden min-h-0">
                    <img :src="finalURL" class="max-h-full max-w-full object-contain rounded shadow-2xl" />
                </div>
                <div class="px-6 py-5 flex items-center justify-between bg-neutral-950 border-t border-white/5">
                    <button type="button" @click="phase='editor'"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-neutral-400 hover:text-white border border-white/10 hover:border-white/20 text-sm transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Editar
                    </button>
                    <button type="button" @click="uploadFinal()"
                        class="flex items-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 transition shadow-lg active:scale-95 text-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Subir Documento
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
