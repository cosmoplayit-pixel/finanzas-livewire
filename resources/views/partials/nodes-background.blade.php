{{-- ===================== FONDO NODOS ANIMADO ===================== --}}
<div class="fixed inset-0 z-0 bg-white dark:bg-zinc-950">
    <canvas id="nodes-bg" class="absolute inset-0 w-full h-full"></canvas>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(0,0,0,0.03)_0%,transparent_55%)]"></div>
</div>

<script>
    (function() {
        const canvas = document.getElementById('nodes-bg');
        if (!canvas) return;
        const ctx = canvas.getContext('2d', { alpha: true });
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const mqMobile = window.matchMedia('(max-width: 767px)');
        const mouse = { x: 0, y: 0, tx: 0, ty: 0 };

        function onMove(e) {
            mouse.tx = (e.clientX / (window.innerWidth || 1) - 0.5) * 2;
            mouse.ty = (e.clientY / (window.innerHeight || 1) - 0.5) * 2;
        }
        if (!mqMobile.matches) window.addEventListener('mousemove', onMove, { passive: true });

        function resize() {
            const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
            canvas.width  = Math.floor(window.innerWidth * dpr);
            canvas.height = Math.floor(window.innerHeight * dpr);
            canvas.style.width  = window.innerWidth + 'px';
            canvas.style.height = window.innerHeight + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        resize();
        window.addEventListener('resize', resize, { passive: true });

        const nodes = [];
        function init() {
            nodes.length = 0;
            const w = window.innerWidth, h = window.innerHeight;
            const divisor = mqMobile.matches ? 22000 : 14000;
            const minN    = mqMobile.matches ? 55 : 120;
            const maxN    = mqMobile.matches ? 120 : 240;
            const count   = Math.max(minN, Math.min(maxN, Math.floor((w * h) / divisor)));
            for (let i = 0; i < count; i++) {
                nodes.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    z: 0.15 + Math.random() * 0.85,
                    vx: (Math.random() - 0.5) * 0.16,
                    vy: (Math.random() - 0.5) * 0.16,
                    r:  1 + Math.random() * 1.0,
                });
            }
        }
        init();

        let resizeTimer;
        window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(init, 120); }, { passive: true });
        mqMobile.addEventListener?.('change', () => {
            window.removeEventListener('mousemove', onMove);
            if (!mqMobile.matches) window.addEventListener('mousemove', onMove, { passive: true });
            init();
        });

        function draw() {
            const w = window.innerWidth, h = window.innerHeight;
            const ease = mqMobile.matches ? 0.02 : 0.06;
            mouse.x += (mouse.tx - mouse.x) * ease;
            mouse.y += (mouse.ty - mouse.y) * ease;
            ctx.clearRect(0, 0, w, h);

            const isDark = document.documentElement.classList.contains('dark');
            const c = isDark ? '255,255,255' : '0,0,0';
            const maxDist = mqMobile.matches
                ? Math.min(150, Math.max(110, Math.floor(Math.min(w,h) / 6)))
                : Math.min(240, Math.max(170, Math.floor(Math.min(w,h) / 4)));
            const parallax = mqMobile.matches ? 6 : 12;

            for (const p of nodes) {
                if (!prefersReduced) {
                    const speed = mqMobile.matches ? 0.55 : 1.0;
                    p.x += p.vx * speed * (1.1 - p.z);
                    p.y += p.vy * speed * (1.1 - p.z);
                }
                if (p.x < -20) p.x = w + 20; if (p.x > w + 20) p.x = -20;
                if (p.y < -20) p.y = h + 20; if (p.y > h + 20) p.y = -20;
            }

            for (let i = 0; i < nodes.length; i++) {
                const a = nodes[i];
                for (let j = i + 1; j < nodes.length; j++) {
                    const b = nodes[j];
                    const d = Math.hypot(a.x - b.x, a.y - b.y);
                    if (d > maxDist) continue;
                    const alpha = (1 - d / maxDist) * (mqMobile.matches ? 0.14 : 0.20);
                    ctx.beginPath();
                    ctx.moveTo(a.x + mouse.x * parallax * (1 - a.z), a.y + mouse.y * parallax * (1 - a.z));
                    ctx.lineTo(b.x + mouse.x * parallax * (1 - b.z), b.y + mouse.y * parallax * (1 - b.z));
                    ctx.strokeStyle = `rgba(${c},${alpha})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }

            for (const p of nodes) {
                const px = p.x + mouse.x * (parallax + 2) * (1 - p.z);
                const py = p.y + mouse.y * (parallax + 2) * (1 - p.z);
                const depth = 1 - p.z;
                ctx.beginPath(); ctx.arc(px, py, p.r * 3, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${c},${0.03 + depth * 0.04})`; ctx.fill();
                ctx.beginPath(); ctx.arc(px, py, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${c},${0.25 + depth * 0.18})`; ctx.fill();
            }

            requestAnimationFrame(draw);
        }

        document.addEventListener('visibilitychange', () => { if (!document.hidden) resize(); });
        draw();
    })();
</script>
