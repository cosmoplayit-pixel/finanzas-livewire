export const documentScanner = (model) => ({
    phase: 'idle', // idle | camera | editor | preview
    editorTab: 'persp', // persp | crop | adjust
    isProcessing: false,
    processingMsg: 'Procesando…',
    localPdfUrl: '',
    isUploading: false,
    removing: false,
    hovered: false,

    uploadFile(f) {
        if (!f) return;
        this.removing = true;
        if (this.localPdfUrl) URL.revokeObjectURL(this.localPdfUrl);
        this.localPdfUrl = '';
        this.isUploading = true;
        this.$wire.set(model, null).then(() => {
            this.removing = false;
            if (f.type === 'application/pdf') {
                this.localPdfUrl = URL.createObjectURL(f);
            }
            this.$wire.upload(model, f, () => {
                this.isUploading = false;
            }, () => {
                this.isUploading = false;
            });
        });
    },

    handlePaste(e) {
        if (this.isUploading || this.removing) return;
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/') || item.type === 'application/pdf') {
                const f = item.getAsFile();
                if (f) {
                    e.preventDefault();
                    this.uploadFile(f);
                    break;
                }
            }
        }
    },

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

        if (file.type === 'application/pdf') {
            if (this.localPdfUrl) URL.revokeObjectURL(this.localPdfUrl);
            this.localPdfUrl = URL.createObjectURL(file);
        } else {
            this.localPdfUrl = '';
        }

        const fr = new FileReader();
        fr.onload = ev => {
            if (file.type === 'application/pdf') return;
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
        const ctx = small.getContext('2d', {
            willReadFrequently: true
        });
        const data = ctx.getImageData(0, 0, sw, sh).data;

        const lum = (x, y) => {
            const i = (Math.round(y) * sw + Math.round(x)) * 4;
            return 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        };

        // Color de fondo: promedio de 4 esquinas
        const bgL = (lum(0, 0) + lum(sw - 1, 0) + lum(0, sh - 1) + lum(sw - 1, sh -
            1)) / 4;
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

        const cx0 = Math.floor(sw * 0.15),
            cx1 = Math.ceil(sw * 0.85);
        const cy0 = Math.floor(sh * 0.15),
            cy1 = Math.ceil(sh * 0.85);
        const minHW = (cx1 - cx0) * 0.25;
        const minVH = (cy1 - cy0) * 0.25;

        let top = 0,
            bottom = sh - 1,
            left = 0,
            right = sw - 1;

        for (let y = 0; y < sh; y++)
            if (scanH(y, cx0, cx1) >= minHW) {
                top = Math.max(0, y - 1);
                break;
            }
        for (let y = sh - 1; y >= 0; y--)
            if (scanH(y, cx0, cx1) >= minHW) {
                bottom = Math.min(sh - 1, y + 1);
                break;
            }
        for (let x = 0; x < sw; x++)
            if (scanV(x, cy0, cy1) >= minVH) {
                left = Math.max(0, x - 1);
                break;
            }
        for (let x = sw - 1; x >= 0; x--)
            if (scanV(x, cy0, cy1) >= minVH) {
                right = Math.min(sw - 1, x + 1);
                break;
            }

        const lx = left / sw * 100,
            rx = right / sw * 100;
        const ty = top / sh * 100,
            by = bottom / sh * 100;

        // Solo aplicar si detectó algo más pequeño que el 95% de la imagen
        if (lx > 1 || rx < 99 || ty > 1 || by < 99) {
            this.handles = [{
                    x: lx,
                    y: ty
                },
                {
                    x: rx,
                    y: ty
                },
                {
                    x: rx,
                    y: by
                },
                {
                    x: lx,
                    y: by
                },
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
        if (this.localPdfUrl) URL.revokeObjectURL(this.localPdfUrl);
        this.localPdfUrl = '';
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
});
