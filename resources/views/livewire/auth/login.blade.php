{{-- resources/views/auth/login.blade.php --}}
<x-layouts.auth>
    <style>
        /* Quitar scroll (layout + página) */
        html,
        body {
            height: 100%;
            overflow: hidden !important;
        }
    </style>

    <div class="relative h-screen w-screen overflow-hidden bg-white">

        {{-- ===================== FONDO NODOS FULL (DESKTOP + MOBILE) ===================== --}}
        <div class="fixed inset-0 z-0 bg-white">
            {{-- Canvas SIEMPRE visible (mobile incluido) --}}
            <canvas id="nodes-bg" class="absolute inset-0 w-full h-full"></canvas>

            {{-- Overlay MUY sutil --}}
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.03)_0%,transparent_55%)]">
            </div>
        </div>

        {{-- ===================== CONTENIDO CENTRADO (SIN SCROLL) ===================== --}}
        <div class="fixed inset-0 z-10 flex items-center justify-center px-4">
            <div class="w-full max-w-md">

                {{-- Brand / Title --}}
                <div class="text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1v22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900">
                        {{ __('Accede a tu cuenta') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Control financiero seguro y trazable. Inicia sesión para continuar.') }}
                    </p>
                </div>

                {{-- Status --}}
                <x-auth-session-status class="mt-5 text-center" :status="session('status')" />

                {{-- Card --}}
                <div class="mt-5 rounded-2xl border border-gray-200 bg-white shadow-lg shadow-black/10">
                    <div class="p-6">
                        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                            @csrf

                            {{-- Email --}}
                            <flux:input name="email" :label="__('Correo electrónico')" :value="old('email')"
                                type="email" required autofocus autocomplete="email"
                                placeholder="correo@ejemplo.com" />

                            {{-- Password --}}
                            <flux:input name="password" :label="__('Contraseña')" type="password" required
                                autocomplete="current-password" :placeholder="__('Ingresa tu contraseña')" viewable />

                            <div class="flex items-center justify-between gap-3">
                                <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />
                            </div>

                            {{-- Submit --}}
                            <flux:button variant="primary" type="submit" class="w-full cursor-pointer justify-center"
                                data-test="login-button">
                                {{ __('Ingresar') }}
                            </flux:button>
                        </form>
                    </div>

                    {{-- Footer note --}}
                    <div class="border-t border-gray-200 px-6 py-4 text-center">
                        <p class="text-xs text-gray-500 leading-relaxed">
                            {{ __('Este sistema prioriza seguridad, control de accesos y trazabilidad. Si no tienes acceso, contacta a un administrador.') }}
                        </p>
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

                // Detect mobile/tablet
                const mqMobile = window.matchMedia('(max-width: 767px)');

                // Mouse parallax (en mobile lo reducimos)
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

                // Solo escuchar mouse si no es mobile
                if (!mqMobile.matches) {
                    window.addEventListener('mousemove', onMove, {
                        passive: true
                    });
                }

                // DPI aware - FULL viewport
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

                // Nodes
                const nodes = [];
                const rand = (min, max) => min + Math.random() * (max - min);

                function init() {
                    nodes.length = 0;

                    const w = window.innerWidth;
                    const h = window.innerHeight;
                    const area = w * h;

                    // ✅ En mobile: menos densidad (para que NO se vea "full" y no lag)
                    // ✅ En desktop: más densidad
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

                // Si cambia entre mobile/desktop (rotación, resize), re-ajusta
                mqMobile.addEventListener?.('change', () => {
                    // activar/desactivar mouse listener
                    window.removeEventListener('mousemove', onMove);
                    if (!mqMobile.matches) window.addEventListener('mousemove', onMove, {
                        passive: true
                    });
                    init();
                });

                function draw() {
                    const w = window.innerWidth;
                    const h = window.innerHeight;

                    // smooth mouse (en mobile, casi fijo / muy suave)
                    const ease = mqMobile.matches ? 0.02 : 0.06;
                    mouse.x += (mouse.tx - mouse.x) * ease;
                    mouse.y += (mouse.ty - mouse.y) * ease;

                    ctx.clearRect(0, 0, w, h);

                    const line = 'rgba(0,0,0,';
                    const glow = 'rgba(0,0,0,';
                    const core = 'rgba(0,0,0,';

                    // update
                    for (const p of nodes) {
                        if (!prefersReduced) {
                            const speed = mqMobile.matches ? 0.55 : 1.00; // mobile más lento
                            p.x += p.vx * speed * (1.10 - p.z);
                            p.y += p.vy * speed * (1.10 - p.z);
                        }
                        if (p.x < -25) p.x = w + 25;
                        if (p.x > w + 25) p.x = -25;
                        if (p.y < -25) p.y = h + 25;
                        if (p.y > h + 25) p.y = -25;
                    }

                    // ✅ En mobile: menos conexiones (menor maxDist)
                    const maxDist = mqMobile.matches ?
                        Math.min(150, Math.max(110, Math.floor(Math.min(w, h) / 6))) :
                        Math.min(240, Math.max(170, Math.floor(Math.min(w, h) / 4)));

                    // lines
                    for (let i = 0; i < nodes.length; i++) {
                        const a = nodes[i];
                        for (let j = i + 1; j < nodes.length; j++) {
                            const b = nodes[j];
                            const dx = a.x - b.x;
                            const dy = a.y - b.y;
                            const d = Math.hypot(dx, dy);
                            if (d > maxDist) continue;

                            const depth = (2 - (a.z + b.z)) / 2;

                            // ✅ En mobile: alpha más bajo para que no “llene”
                            const baseAlpha = mqMobile.matches ? 0.14 : 0.22;
                            const alpha = Math.max(0, (1 - d / maxDist)) * baseAlpha * (0.6 + depth);

                            const parallax = mqMobile.matches ? 6 : 12; // mobile más suave
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

                    // nodes
                    for (const p of nodes) {
                        const depth = (1 - p.z);

                        const parallax = mqMobile.matches ? 9 : 16;
                        const px = p.x + mouse.x * (parallax * (1 - p.z));
                        const py = p.y + mouse.y * (parallax * (1 - p.z));
                        const rr = p.r + depth * 1.4;

                        // glow
                        ctx.beginPath();
                        ctx.arc(px, py, rr * 3.0, 0, Math.PI * 2);
                        const glowA = mqMobile.matches ? (0.02 + depth * 0.03) : (0.03 + depth * 0.05);
                        ctx.fillStyle = glow + glowA + ')';
                        ctx.fill();

                        // core
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

        {{-- ===================== OCULTAR HEADER/NAV DEL LAYOUT (si aplica) ===================== --}}
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
