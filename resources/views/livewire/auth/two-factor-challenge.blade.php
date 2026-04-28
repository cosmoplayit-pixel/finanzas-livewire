<x-layouts.auth>
    <style>
        /* Quitar scroll (layout + página) */
        html,
        body {
            height: 100%;
            overflow: hidden !important;
        }
    </style>

    <div class="relative h-screen w-screen overflow-hidden bg-white dark:bg-zinc-950">

        {{-- ===================== FONDO NODOS FULL (DESKTOP + MOBILE) ===================== --}}
        <div class="fixed inset-0 z-0 bg-white dark:bg-zinc-950">
            <canvas id="nodes-bg" class="absolute inset-0 w-full h-full"></canvas>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.03)_0%,transparent_55%)]">
            </div>
        </div>

        {{-- ===================== CONTENIDO CENTRADO ===================== --}}
        <div class="fixed inset-0 z-10 flex items-center justify-center px-4" x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;
                    this.code = '';
                    this.recovery_code = '';
                    $dispatch('clear-2fa-auth-code');
                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code');
                    });
                },
            }">
            <div class="w-full max-w-md">

                {{-- Brand / Title --}}
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white" x-text="showRecoveryInput ? 'Código de Recuperación' : 'Código de Autenticación'">
                        Código de Autenticación
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-zinc-400" x-text="showRecoveryInput ? 'Confirma el acceso usando uno de tus códigos de emergencia.' : 'Ingresa el código proporcionado por tu aplicación autenticadora.'">
                        Ingresa el código proporcionado por tu aplicación autenticadora.
                    </p>
                </div>

                {{-- Status --}}
                <x-auth-session-status class="mt-5 text-center" :status="session('status')" />

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-lg shadow-black/10 dark:shadow-black/40">
                    <div class="p-6">
                        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-5 text-center">
                            @csrf

                            <div x-show="!showRecoveryInput">
                                <div class="flex items-center justify-center my-5">
                                    <flux:otp
                                        x-model="code"
                                        length="6"
                                        name="code"
                                        label="Código OTP"
                                        label:sr-only
                                        class="mx-auto"
                                     />
                                </div>
                                @error('code')
                                    <p class="text-xs text-red-500 text-center mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="showRecoveryInput">
                                <div class="my-5">
                                    <flux:input
                                        type="text"
                                        name="recovery_code"
                                        x-ref="recovery_code"
                                        x-bind:required="showRecoveryInput"
                                        autocomplete="one-time-code"
                                        x-model="recovery_code"
                                        placeholder="Ej: 12345-67890"
                                    />
                                </div>

                                @error('recovery_code')
                                    <flux:text color="red" class="text-xs mt-2">
                                        {{ $message }}
                                    </flux:text>
                                @enderror
                            </div>

                            <flux:button variant="primary" type="submit" class="w-full">
                                {{ __('Continuar') }}
                            </flux:button>

                            <div class="mt-5 space-x-0.5 text-sm leading-5 text-center">
                                <span class="text-gray-500 dark:text-zinc-400">{{ __('o puedes') }}</span>
                                <div class="inline font-medium underline cursor-pointer text-indigo-600 dark:text-indigo-400">
                                    <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('usar un código de recuperación') }}</span>
                                    <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('usar el código de aplicación') }}</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Micro footer --}}
                <p class="mt-6 text-center text-xs text-gray-400">
                    {{ __('© ') }}{{ date('Y') }} — {{ __('Gestión Financiera') }}
                </p>

            </div>
        </div>

        {{-- ===================== NODOS 3D CANVAS (AUTO AJUSTA EN MOBILE) ===================== --}}
        <script>
            (function() {
                const canvas = document.getElementById('nodes-bg');
                if (!canvas) return;

                const ctx = canvas.getContext('2d', {
                    alpha: true
                });

                const prefersReduced = window.matchMedia &&
                    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                const mqMobile = window.matchMedia('(max-width: 767px)');

                const mouse = {
                    x: 0,
                    y: 0,
                    tx: 0,
                    ty: 0
                };

                function onMove(e) {
                    const w = window.innerWidth || 1;
                    const h = window.innerHeight || 1;
                    mouse.tx = (e.clientX / w - 0.5) * 2;
                    mouse.ty = (e.clientY / h - 0.5) * 2;
                }

                if (!mqMobile.matches) {
                    window.addEventListener('mousemove', onMove, {
                        passive: true
                    });
                }

                function resize() {
                    const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
                    canvas.width = Math.floor(window.innerWidth * dpr);
                    canvas.height = Math.floor(window.innerHeight * dpr);
                    canvas.style.width = window.innerWidth + 'px';
                    canvas.style.height = window.innerHeight + 'px';
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                }
                resize();
                window.addEventListener('resize', resize, {
                    passive: true
                });

                const nodes = [];
                const rand = (min, max) => min + Math.random() * (max - min);

                function init() {
                    nodes.length = 0;
                    const w = window.innerWidth;
                    const h = window.innerHeight;
                    const area = w * h;

                    const divisor = mqMobile.matches ? 22000 : 14000;
                    const minN = mqMobile.matches ? 55 : 120;
                    const maxN = mqMobile.matches ? 120 : 240;

                    const count = Math.max(minN, Math.min(maxN, Math.floor(area / divisor)));

                    for (let i = 0; i < count; i++) {
                        nodes.push({
                            x: rand(0, w),
                            y: rand(0, h),
                            z: rand(0.15, 1.0),
                            vx: rand(-0.16, 0.16),
                            vy: rand(-0.16, 0.16),
                            r: rand(1.0, 2.0),
                        });
                    }
                }
                init();

                let resizeTimer = null;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => init(), 120);
                }, {
                    passive: true
                });

                mqMobile.addEventListener?.('change', () => {
                    window.removeEventListener('mousemove', onMove);
                    if (!mqMobile.matches) window.addEventListener('mousemove', onMove, {
                        passive: true
                    });
                    init();
                });

                function draw() {
                    const w = window.innerWidth;
                    const h = window.innerHeight;

                    const ease = mqMobile.matches ? 0.02 : 0.06;
                    mouse.x += (mouse.tx - mouse.x) * ease;
                    mouse.y += (mouse.ty - mouse.y) * ease;

                    ctx.clearRect(0, 0, w, h);

                    const isDark = document.documentElement.classList.contains('dark');
                    const color = isDark ? '255,255,255' : '0,0,0';
                    const line = `rgba(${color},`;
                    const glow = `rgba(${color},`;
                    const core = `rgba(${color},`;

                    for (const p of nodes) {
                        if (!prefersReduced) {
                            const speed = mqMobile.matches ? 0.55 : 1.00;
                            p.x += p.vx * speed * (1.10 - p.z);
                            p.y += p.vy * speed * (1.10 - p.z);
                        }
                        if (p.x < -25) p.x = w + 25;
                        if (p.x > w + 25) p.x = -25;
                        if (p.y < -25) p.y = h + 25;
                        if (p.y > h + 25) p.y = -25;
                    }

                    const maxDist = mqMobile.matches ?
                        Math.min(150, Math.max(110, Math.floor(Math.min(w, h) / 6))) :
                        Math.min(240, Math.max(170, Math.floor(Math.min(w, h) / 4)));

                    for (let i = 0; i < nodes.length; i++) {
                        const a = nodes[i];
                        for (let j = i + 1; j < nodes.length; j++) {
                            const b = nodes[j];
                            const dx = a.x - b.x;
                            const dy = a.y - b.y;
                            const d = Math.hypot(dx, dy);
                            if (d > maxDist) continue;

                            const depth = (2 - (a.z + b.z)) / 2;

                            const baseAlpha = mqMobile.matches ? 0.14 : 0.22;
                            const alpha = Math.max(0, (1 - d / maxDist)) * baseAlpha * (0.6 + depth);

                            const parallax = mqMobile.matches ? 6 : 12;
                            const ax = a.x + mouse.x * (parallax * (1 - a.z));
                            const ay = a.y + mouse.y * (parallax * (1 - a.z));
                            const bx = b.x + mouse.x * (parallax * (1 - b.z));
                            const by = b.y + mouse.y * (parallax * (1 - b.z));

                            ctx.beginPath();
                            ctx.moveTo(ax, ay);
                            ctx.lineTo(bx, by);

                            ctx.strokeStyle = line + alpha + ')';
                            ctx.lineWidth = 1;
                            ctx.stroke();
                        }
                    }

                    for (const p of nodes) {
                        const depth = (1 - p.z);

                        const parallax = mqMobile.matches ? 9 : 16;
                        const px = p.x + mouse.x * (parallax * (1 - p.z));
                        const py = p.y + mouse.y * (parallax * (1 - p.z));
                        const rr = p.r + depth * 1.4;

                        ctx.beginPath();
                        ctx.arc(px, py, rr * 3.0, 0, Math.PI * 2);
                        const glowA = mqMobile.matches ? (0.02 + depth * 0.03) : (0.03 + depth * 0.05);
                        ctx.fillStyle = glow + glowA + ')';
                        ctx.fill();

                        ctx.beginPath();
                        ctx.arc(px, py, rr, 0, Math.PI * 2);
                        const coreA = mqMobile.matches ? (0.22 + depth * 0.14) : (0.30 + depth * 0.18);
                        ctx.fillStyle = core + coreA + ')';
                        ctx.fill();
                    }

                    requestAnimationFrame(draw);
                }

                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) resize();
                });

                draw();
            })();
        </script>

        {{-- ===================== OCULTAR HEADER/NAV DEL LAYOUT ===================== --}}
        <style>
            header,
            nav {
                display: none !important;
            }

            .min-h-screen>.pt-6,
            .min-h-screen>.sm\:pt-0 {
                display: none !important;
            }
        </style>

    </div>
</x-layouts.auth>
