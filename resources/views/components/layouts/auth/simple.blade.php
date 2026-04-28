<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <script>
        // Force light mode on first visit if no preference is set
        if (!localStorage.getItem('flux.appearance')) {
            localStorage.setItem('flux.appearance', 'light');
        }

        // Apply dark mode immediately if preferred to prevent flash
        if (localStorage.getItem('flux.appearance') === 'dark' || (localStorage.getItem('flux.appearance') === 'system' &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-sm flex-col gap-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Finanzas') }}</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    {{-- ===================== FONDO NODOS (LOGIN) ===================== --}}
    <div class="pointer-events-none fixed inset-0 -z-10" x-data="nodeBackground()">
        <canvas id="app-nodes-bg" x-ref="canvas" class="absolute inset-0 w-full h-full"></canvas>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.04)_0%,transparent_60%)] dark:bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05)_0%,transparent_55%)]">
        </div>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(0,0,0,0.10)_100%)] dark:bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(0,0,0,0.55)_100%)]">
        </div>
    </div>

    @fluxScripts

    {{-- Script del Fondo --}}
    <script>
        document.addEventListener('alpine:init', () => {
            if (window.nodeBackgroundInitialized) return;
            window.nodeBackgroundInitialized = true;

            Alpine.data('nodeBackground', () => ({
                nodes: [],
                mouse: {
                    x: 0,
                    y: 0,
                    tx: 0,
                    ty: 0
                },
                ctx: null,
                mqMobile: window.matchMedia('(max-width: 767px)'),

                init() {
                    this.$nextTick(() => {
                        const canvas = this.$refs.canvas;
                        if (!canvas) return;
                        this.ctx = canvas.getContext('2d', {
                            alpha: true
                        });
                        this.resize();
                        this.initNodes();
                        this.draw();
                    });
                    window.addEventListener('resize', () => {
                        this.resize();
                        this.initNodes();
                    }, {
                        passive: true
                    });
                    if (!this.mqMobile.matches) {
                        window.addEventListener('mousemove', (e) => {
                            const w = window.innerWidth || 1;
                            const h = window.innerHeight || 1;
                            this.mouse.tx = (e.clientX / w - 0.5) * 2;
                            this.mouse.ty = (e.clientY / h - 0.5) * 2;
                        }, {
                            passive: true
                        });
                    }
                },
                resize() {
                    const canvas = this.$refs.canvas;
                    if (!canvas || !this.ctx) return;
                    const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
                    canvas.width = Math.floor(window.innerWidth * dpr);
                    canvas.height = Math.floor(window.innerHeight * dpr);
                    canvas.style.width = window.innerWidth + 'px';
                    canvas.style.height = window.innerHeight + 'px';
                    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                },
                initNodes() {
                    if (!this.$refs.canvas) return;
                    this.nodes = [];
                    const w = window.innerWidth;
                    const h = window.innerHeight;
                    const count = this.mqMobile.matches ? 40 : 120;
                    for (let i = 0; i < count; i++) {
                        this.nodes.push({
                            x: Math.random() * w,
                            y: Math.random() * h,
                            z: 0.15 + Math.random() * 0.85,
                            vx: (Math.random() - 0.5) * 0.3,
                            vy: (Math.random() - 0.5) * 0.3,
                            r: 1 + Math.random() * 1.5,
                        });
                    }
                },
                draw() {
                    const w = window.innerWidth;
                    const h = window.innerHeight;
                    if (!this.ctx) return;
                    const isDark = document.documentElement.classList.contains('dark');
                    this.mouse.x += (this.mouse.tx - this.mouse.x) * 0.05;
                    this.mouse.y += (this.mouse.ty - this.mouse.y) * 0.05;
                    this.ctx.clearRect(0, 0, w, h);
                    const color = isDark ? '255,255,255,' : '0,0,0,';
                    const maxDist = this.mqMobile.matches ? 120 : 200;
                    for (let i = 0; i < this.nodes.length; i++) {
                        const a = this.nodes[i];
                        for (let j = i + 1; j < this.nodes.length; j++) {
                            const b = this.nodes[j];
                            const d = Math.hypot(a.x - b.x, a.y - b.y);
                            if (d < maxDist) {
                                const alpha = (1 - d / maxDist) * 0.15;
                                this.ctx.beginPath();
                                this.ctx.moveTo(a.x + this.mouse.x * (10 * (1 - a.z)), a.y + this.mouse
                                    .y * (10 * (1 - a.z)));
                                this.ctx.lineTo(b.x + this.mouse.x * (10 * (1 - b.z)), b.y + this.mouse
                                    .y * (10 * (1 - b.z)));
                                this.ctx.strokeStyle = `rgba(${color}${alpha})`;
                                this.ctx.stroke();
                            }
                        }
                    }
                    for (const p of this.nodes) {
                        p.x += p.vx * (1.1 - p.z);
                        p.y += p.vy * (1.1 - p.z);
                        if (p.x < -20) p.x = w + 20;
                        if (p.x > w + 20) p.x = -20;
                        if (p.y < -20) p.y = h + 20;
                        if (p.y > h + 20) p.y = -20;
                        const px = p.x + this.mouse.x * (14 * (1 - p.z));
                        const py = p.y + this.mouse.y * (14 * (1 - p.z));
                        this.ctx.beginPath();
                        this.ctx.arc(px, py, p.r, 0, Math.PI * 2);
                        this.ctx.fillStyle = `rgba(${color}${0.2 + (1-p.z)*0.2})`;
                        this.ctx.fill();
                    }
                    requestAnimationFrame(() => this.draw());
                }
            }));
        });
    </script>
</body>

</html>
