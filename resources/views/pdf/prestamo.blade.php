<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Préstamo {{ $nro_prestamo }}</title>
    <style>
        /* ── Base ────────────────────────────────────────────────── */
        @page {
            size: letter;
            margin: 115px 40px 70px 40px;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Fixed Header ─────────────────────────────────────────── */
        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 95px;
        }

        .hdr-main {
            background: #4a5568;
            width: 100%;
            border-collapse: collapse;
            height: 58px;
        }

        .hdr-main td {
            padding: 0 16px;
            vertical-align: middle;
        }

        .hdr-title {
            font-size: 15px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hdr-sub {
            font-size: 7.5px;
            color: #cbd5e0;
            margin-top: 2px;
        }

        .hdr-nro {
            font-size: 22px;
            font-weight: bold;
            color: #bee3f8;
            text-align: right;
        }

        .hdr-strip {
            background: #edf2f7;
            border-bottom: 2px solid #e2e8f0;
            width: 100%;
            border-collapse: collapse;
            height: 28px;
        }

        .hdr-strip td {
            padding: 0 16px;
            vertical-align: middle;
            font-size: 8px;
            color: #718096;
        }

        /* ── Fixed Footer ─────────────────────────────────────────── */
        footer {
            position: fixed;
            bottom: -52px;
            left: 0;
            right: 0;
            height: 44px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .ftr {
            width: 100%;
            border-collapse: collapse;
            height: 44px;
        }

        .ftr td {
            padding: 0 16px;
            vertical-align: middle;
            font-size: 8px;
            color: #a0aec0;
        }

        .page-number::after {
            content: "Pág. " counter(page) " de " counter(pages);
        }

        /* ── Badges ───────────────────────────────────────────────── */
        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-activo {
            background: #bee3f8;
            color: #2b6cb0;
        }

        .badge-finalizado {
            background: #c6f6d5;
            color: #276749;
        }

        .badge-vencido {
            background: #fed7d7;
            color: #9b2c2c;
        }

        /* ── Info Cards ───────────────────────────────────────────── */
        .cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-bottom: 14px;
        }

        .card-td {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            vertical-align: top;
            width: 33%;
        }

        .clabel {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #a0aec0;
            font-weight: bold;
        }

        .cval {
            font-size: 11px;
            font-weight: bold;
            color: #2d3748;
            margin-top: 2px;
        }

        .cval-sm {
            font-size: 10px;
            font-weight: bold;
            color: #4a5568;
            margin-top: 2px;
        }

        /* ── Summary Strip ────────────────────────────────────────── */
        .summary {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            background: #f7fafc;
            margin-bottom: 16px;
        }

        .summary td {
            text-align: center;
            padding: 8px 6px;
            border-right: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .summary td:last-child {
            border-right: none;
        }

        .sval {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            line-height: 1;
        }

        .slbl {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ── Section Title ────────────────────────────────────────── */
        .sect-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #5a7fa8;
            padding: 6px 0 6px 10px;
            border-left: 3px solid #7fb3d3;
            margin-bottom: 8px;
            margin-top: 18px;
        }

        .sect-title-red {
            border-left-color: #fc8181;
            color: #c53030;
        }

        .sect-title-green {
            border-left-color: #68d391;
            color: #276749;
        }

        /* ── Main Table ───────────────────────────────────────────── */
        .tbl {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .tbl thead tr {
            background: #4a5568;
        }

        .tbl thead th {
            padding: 7px 10px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #e2e8f0;
            text-align: left;
        }

        .tbl thead th.tc {
            text-align: center;
        }

        .tbl thead th.tr {
            text-align: right;
        }

        .tbl tbody tr {
            border-bottom: 1px solid #edf2f7;
        }

        .tbl tbody tr.even {
            background: #f7fafc;
        }

        .tbl tbody td {
            padding: 7px 10px;
            font-size: 10px;
            color: #4a5568;
            vertical-align: middle;
        }

        .tbl tbody td.tc {
            text-align: center;
        }

        .tbl tbody td.tr {
            text-align: right;
        }

        .tbl tfoot td {
            padding: 8px 10px;
            background: #edf2f7;
            font-size: 10px;
            font-weight: bold;
            color: #2d3748;
            border-top: 2px solid #e2e8f0;
        }

        .tbl tfoot td.tc {
            text-align: center;
        }

        /* ── Qty Badges ───────────────────────────────────────────── */
        .qty {
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }

        .qty-n {
            background: #f1f5f9;
            color: #334155;
        }

        .qty-g {
            background: #dcfce7;
            color: #15803d;
        }

        .qty-r {
            background: #fee2e2;
            color: #dc2626;
        }

        .qty-a {
            background: #fef3c7;
            color: #92400e;
        }

        /* ── Progress bar ─────────────────────────────────────────── */
        .prog-wrap {
            background: #e2e8f0;
            border-radius: 3px;
            height: 4px;
            width: 70px;
        }

        .prog-fill {
            height: 4px;
            border-radius: 3px;
        }

        /* Progress as table cell */
        .prog-tbl {
            border-collapse: collapse;
            min-width: 90px;
        }

        .prog-tbl td {
            padding: 0;
            vertical-align: middle;
        }

        /* ── Bajas table ──────────────────────────────────────────── */
        .tbl-baja thead tr {
            background: #9b2c2c;
        }

        /* ── Recepciones ──────────────────────────────────────────── */
        .recep-row {
            padding: 7px 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            margin-bottom: 5px;
        }

        .recep-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .recep-tbl td {
            padding: 1px 3px;
            vertical-align: middle;
        }

        /* ── Photo Pages ──────────────────────────────────────────── */
        .photo-sect {
            page-break-before: always;
            padding-top: 4px;
        }

        .photo-tbl {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .photo-tbl td {
            vertical-align: top;
            width: 50%;
        }

        .photo-frame {
            border: 1px solid #e2e8f0;
        }

        .photo-frame img {
            width: 100%;
            height: 185px;
            object-fit: cover;
            display: block;
        }

        .photo-cap {
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 4px 7px;
            font-size: 7.5px;
            color: #718096;
            text-align: center;
        }

        .photo-cap-red {
            background: #fff5f5;
            border-top-color: #feb2b2;
            color: #c53030;
        }

        /* ── Disclaimer ───────────────────────────────────────────── */
        .disclaimer {
            font-size: 7.5px;
            color: #a0aec0;
            font-style: italic;
            margin-top: 6px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 5px;
        }

        .mono {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
        }

        /* ── Firma ────────────────────────────────────────────────── */
        .sig-container {
            width: 100%;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-box {
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }

        .sig-frame {
            background: #ffffff;
            border: 1px solid #edf2f7;
            padding: 5px;
            text-align: center;
        }

        .sig-img {
            height: 60px;
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }

        .sig-label {
            font-size: 7px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            margin-top: 4px;
            border-top: 1px solid #f1f5f9;
            padding-top: 3px;
        }
    </style>
</head>

<body>

    @php
        $totalPrestadas = $prestamos->sum('cantidad_prestada');
        $totalDevueltas = $prestamos->sum('cantidad_devuelta');
        $totalPendientes = $totalPrestadas - $totalDevueltas;
        $progresoGlobal = $totalPrestadas > 0 ? round(($totalDevueltas / $totalPrestadas) * 100) : 0;
        $isVencido = $prestamos->contains(
            fn($i) => $i->estado !== 'finalizado' && $i->fecha_vencimiento && $i->fecha_vencimiento->isPast(),
        );
        $estadoGlobal = $totalPendientes == 0 ? 'finalizado' : ($isVencido ? 'vencido' : 'activo');

        $evidenciasSalida = $first->fotos_salida ?? [];
        $evidenciasDevueltas = [];
        foreach ($prestamos as $item) {
            foreach ($item->devoluciones as $dev) {
                if (!empty($dev->fotos_entrada)) {
                    foreach ($dev->fotos_entrada as $fe) {
                        $evidenciasDevueltas[] = ['fecha' => $dev->fecha_devolucion, 'foto' => $fe];
                    }
                }
            }
        }

        $evidenciasBajas = [];
        if (!empty($bajas) && $bajas->isNotEmpty()) {
            foreach ($bajas as $baja) {
                if (!empty($baja->imagen)) {
                    $evidenciasBajas[] = [
                        'fecha' => $baja->created_at,
                        'foto' => $baja->imagen,
                        'nombre' => $baja->herramienta->nombre ?? 'Herramienta Eliminada',
                    ];
                }
            }
        }

        $badgeClass =
            $estadoGlobal === 'finalizado'
                ? 'badge-finalizado'
                : ($estadoGlobal === 'vencido'
                    ? 'badge-vencido'
                    : 'badge-activo');
        $estadoLabel = strtoupper($estadoGlobal);
    @endphp

    {{-- ═══════════════════ HEADER FIJO ══════════════════════════════════ --}}
    <header>
        <table class="hdr-main">
            <tr>
                <td>
                    <div class="hdr-title">Constancia de Préstamo</div>
                    <div class="hdr-sub">{{ $first->empresa->nombre ?? 'Control de Activos' }} &bull; Documento de
                        Control Interno</div>
                </td>
                <td style="text-align:right; padding-right:20px;">
                    <div class="hdr-nro">{{ $nro_prestamo }}</div>
                </td>
            </tr>
        </table>
        <table class="hdr-strip">
            <tr>
                <td>Generado el {{ now()->format('d/m/Y \a\s H:i') }} &bull; {{ $first->empresa->nombre ?? '' }}</td>
                <td style="text-align:right;"><span class="badge {{ $badgeClass }}">{{ $estadoLabel }}</span></td>
            </tr>
        </table>
    </header>

    {{-- ═══════════════════ FOOTER FIJO ══════════════════════════════════ --}}
    <footer>
        <table class="ftr">
            <tr>
                <td>Control de Activos &bull; {{ $nro_prestamo }}</td>
                <td style="text-align:right;"><span class="page-number"></span></td>
            </tr>
        </table>
    </footer>

    {{-- ═══════════════════ MAIN ══════════════════════════════════════════ --}}
    <main>

        {{-- 1. Tarjetas de Info ──────────────────────────────────────────── --}}
        <table class="cards">
            <tr>
                <td class="card-td">
                    <div class="clabel">Cliente / Entidad</div>
                    <div class="cval">{{ $first->entidad->nombre ?? 'N/A' }}</div>
                    <div style="margin-top:8px;">
                        <div class="clabel">Ubicación / Obra</div>
                        <div class="cval-sm">{{ $first->proyecto->nombre ?? 'N/A' }}</div>
                    </div>
                </td>
                <td class="card-td">
                    <div class="clabel">Responsable / Agente</div>
                    <div class="cval">{{ $first->agente?->nombre ?? ($first->receptor_manual ?: 'No especificado') }}
                    </div>
                    <div style="margin-top:8px;">
                        <div class="clabel">Fecha de Salida</div>
                        <div class="cval-sm">{{ \Carbon\Carbon::parse($first->fecha_prestamo)->format('d/m/Y') }}</div>
                    </div>
                </td>
                <td class="card-td">
                    <div class="clabel">Retorno Estimado</div>
                    <div class="cval" style="{{ $isVencido ? 'color:#c53030;' : '' }}">
                        {{ $first->fecha_vencimiento ? $first->fecha_vencimiento->format('d/m/Y') : 'Abierto / Sin Límite' }}
                    </div>
                    <div style="margin-top:8px;">
                        <div class="clabel">Estado</div>
                        <span class="badge {{ $badgeClass }}">{{ $estadoLabel }}</span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 2. Resumen de Métricas ───────────────────────────────────────── --}}
        <table class="summary">
            <tr>
                <td>
                    <div class="sval" style="color:#5a7fa8;">{{ $totalPrestadas }}</div>
                    <div class="slbl">Ítems Prestados</div>
                </td>
                <td>
                    <div class="sval" style="color:#276749;">{{ $totalDevueltas }}</div>
                    <div class="slbl">Devueltos</div>
                </td>
                <td>
                    <div class="sval" style="{{ $totalPendientes > 0 ? 'color:#b7791f;' : 'color:#a0aec0;' }}">
                        {{ $totalPendientes }}</div>
                    <div class="slbl">Pendientes</div>
                </td>
                <td>
                    <div class="sval"
                        style="{{ $bajas && $bajas->isNotEmpty() ? 'color:#c53030;' : 'color:#a0aec0;' }}">
                        {{ $bajas ? $bajas->count() : 0 }}</div>
                    <div class="slbl">Bajas / Pérdidas</div>
                </td>
                <td>
                    <div class="sval"
                        style="{{ $estadoGlobal === 'finalizado' ? 'color:#276749;' : ($isVencido ? 'color:#c53030;' : 'color:#5a7fa8;') }}">
                        {{ $progresoGlobal }}%</div>
                    <div class="slbl">Completado</div>
                </td>
            </tr>
        </table>

        {{-- 3. Detalle de Equipos ────────────────────────────────────────── --}}
        <div class="sect-title">Detalle de Equipos Prestados</div>

        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:44%;">Herramienta</th>
                    <th class="tc" style="width:16%;">Código</th>
                    <th class="tc" style="width:12%;">Prestado</th>
                    <th class="tc" style="width:12%;">Devuelto</th>
                    <th class="tc" style="width:16%;">Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prestamos as $idx => $item)
                    @php
                        $pendiente = $item->cantidad_prestada - $item->cantidad_devuelta;
                        $pct =
                            $item->cantidad_prestada > 0
                                ? round(($item->cantidad_devuelta / $item->cantidad_prestada) * 100)
                                : 0;
                        $fillColor = $pendiente == 0 ? '#68d391' : ($isVencido ? '#fc8181' : '#7fb3d3');
                    @endphp
                    <tr class="{{ $idx % 2 == 1 ? 'even' : '' }}">
                        <td>
                            <strong>{{ $item->herramienta->nombre ?? 'N/A' }}</strong>
                            <br>
                            <table class="prog-tbl" style="margin-top:4px;">
                                <tr>
                                    <td>
                                        <div class="prog-wrap">
                                            <div class="prog-fill"
                                                style="width:{{ $pct }}%; background:{{ $fillColor }};">
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding-left:4px; font-size:8px; color:#94a3b8;">{{ $pct }}%
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="tc mono" style="color:#6366f1;">{{ $item->herramienta->codigo ?? '—' }}</td>
                        <td class="tc"><span class="qty qty-n">{{ $item->cantidad_prestada }}</span></td>
                        <td class="tc"><span
                                class="qty {{ $item->cantidad_devuelta > 0 ? 'qty-g' : 'qty-n' }}">{{ $item->cantidad_devuelta }}</span>
                        </td>
                        <td class="tc">
                            @if ($pendiente > 0)
                                <span class="qty {{ $isVencido ? 'qty-r' : 'qty-a' }}">{{ $pendiente }}</span>
                            @else
                                <span style="color:#22c55e; font-size:14px; font-weight:bold;">&#10003;</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-size:8.5px; color:#64748b; font-style:italic;">Totales acumulados del
                        préstamo</td>
                    <td class="tc">{{ $totalPrestadas }}</td>
                    <td class="tc" style="color:#15803d;">{{ $totalDevueltas }}</td>
                    <td class="tc" style="{{ $totalPendientes > 0 ? 'color:#92400e;' : 'color:#15803d;' }}">
                        {{ $totalPendientes }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- 4. Bajas / Pérdidas ─────────────────────────────────────────── --}}
        @if (!empty($bajas) && $bajas->isNotEmpty())
            <div class="sect-title sect-title-red">Equipos Dados de Baja / Pérdidas</div>

            <table class="tbl tbl-baja">
                <thead>
                    <tr>
                        <th style="width:36%;">Herramienta</th>
                        <th class="tc" style="width:13%;">Código</th>
                        <th class="tc" style="width:8%;">Cant.</th>
                        <th style="width:30%;">Motivo / Observaciones</th>
                        <th class="tc" style="width:13%;">Fecha / Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bajas as $idx => $baja)
                        <tr style="background:#fff5f5;" class="{{ $idx % 2 == 1 ? '' : '' }}">
                            <td><strong
                                    style="color:#b91c1c;">{{ $baja->herramienta->nombre ?? 'Herramienta Eliminada' }}</strong>
                            </td>
                            <td class="tc mono" style="color:#b91c1c;">{{ $baja->herramienta->codigo ?? '—' }}</td>
                            <td class="tc"><span class="qty qty-r">{{ $baja->cantidad }}</span></td>
                            <td style="font-size:9px; color:#374151;">{{ $baja->observaciones ?? '—' }}</td>
                            <td class="tc" style="font-size:8px; color:#6b7280;">
                                {{ $baja->created_at->format('d/m/Y') }}<br>
                                <span style="color:#9ca3af;">{{ $baja->user->name ?? 'Sistema' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="disclaimer">* Los equipos dados de baja han sido descontados permanentemente del inventario.
                Este registro es de carácter legal y contable.</div>
        @endif

        {{-- 5. Historial de Recepciones ─────────────────────────────────── --}}
        @php
            $devSessions = [];
            $tmp = null;
            $sidx = 1;
            $allDevs = collect();
            foreach ($prestamos as $item) {
                foreach ($item->devoluciones as $dev) {
                    $allDevs->push($dev);
                }
            }
            $allDevs = $allDevs->sortBy('id');
            foreach ($allDevs as $dev) {
                if (!$tmp || !empty($dev->fotos_entrada)) {
                    if ($tmp) {
                        $devSessions[] = $tmp;
                    }
                    $tmp = [
                        'nro' => $sidx++,
                        'fecha' => $dev->fecha_devolucion,
                        'obs' => $dev->observaciones,
                        'firma' => $dev->firma_entrada,
                        'items' => collect([]),
                    ];
                }
                $tmp['items']->push($dev);
            }
            if ($tmp) {
                $devSessions[] = $tmp;
            }
            $devSessions = array_reverse($devSessions);
        @endphp

        @if (!empty($devSessions))
            <div class="sect-title sect-title-green">Historial de Recepciones ({{ count($devSessions) }} sesion(es))
            </div>
            @foreach ($devSessions as $ses)
                <table
                    style="width:100%; border-collapse:collapse; margin-bottom:5px; background:#f0fdf4; border:1px solid #bbf7d0;">
                    <tr>
                        <td style="padding:6px 10px; font-size:9px; font-weight:bold; color:#166534;">
                            Retorno {{ $ses['nro'] }} &bull;
                            {{ \Carbon\Carbon::parse($ses['fecha'])->format('d/m/Y') }}
                        </td>
                        @if ($ses['obs'])
                            <td
                                style="padding:6px 10px; font-size:9px; color:#6b7280; font-style:italic; text-align:right;">
                                "{{ $ses['obs'] }}"</td>
                        @endif
                    </tr>
                </table>
            @endforeach
        @endif

        {{-- ══ SECCIÓN UNIFICADA DE FIRMAS ══════════════════════════════════ --}}
        @php
            $firmasSalida = $first->firma_salida ?? null;
            $firmasRecep = collect($devSessions ?? [])
                ->filter(fn($s) => !empty($s['firma']))
                ->values();
            $hayFirmas = $firmasSalida || $firmasRecep->isNotEmpty();
        @endphp

        @if ($hayFirmas)
            <div class="sect-title" style="margin-top: 20px; border-left-color: #6366f1; color: #4338ca;">
                Firmas Digitales de Conformidad
            </div>

            <table style="width:100%; border-collapse:separate; border-spacing: 6px;">
                <tr>
                    {{-- Firma de Salida --}}
                    @if ($firmasSalida)
                        <td style="width: 50%; vertical-align: top;">
                            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                                <div
                                    style="background: #eef2ff; padding: 5px 10px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #4338ca;">
                                    &#9998; Firma de Salida
                                </div>
                                <div style="background: #ffffff; padding: 8px; text-align: center;">
                                    <img src="{{ $firmasSalida }}"
                                        style="height: 70px; max-width: 100%; display: block; margin: 0 auto;"
                                        alt="Firma Salida">
                                </div>
                                <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 4px 10px;">
                                    <div style="font-size: 7px; color: #64748b;">Responsable:
                                        <strong>{{ $first->agente?->nombre ?? ($first->receptor_manual ?: 'N/A') }}</strong>
                                    </div>
                                    <div style="font-size: 7px; color: #94a3b8;">Fecha salida:
                                        {{ \Carbon\Carbon::parse($first->fecha_prestamo)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </td>
                    @endif

                    {{-- Firmas de Recepción (la más reciente) --}}
                    @if ($firmasRecep->isNotEmpty())
                        <td style="width: 50%; vertical-align: top;">
                            @foreach ($firmasRecep as $fr)
                                <div
                                    style="border: 1px solid #bbf7d0; border-radius: 4px; overflow: hidden; {{ !$loop->first ? 'margin-top: 6px;' : '' }}">
                                    <div
                                        style="background: #dcfce7; padding: 5px 10px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #166534;">
                                        &#10003; Firma Recepción {{ $fr['nro'] }}
                                    </div>
                                    <div style="background: #ffffff; padding: 8px; text-align: center;">
                                        <img src="{{ $fr['firma'] }}"
                                            style="height: 70px; max-width: 100%; display: block; margin: 0 auto;"
                                            alt="Firma Recepción">
                                    </div>
                                    <div
                                        style="background: #f0fdf4; border-top: 1px solid #bbf7d0; padding: 4px 10px;">
                                        <div style="font-size: 7px; color: #166534;">Fecha:
                                            <strong>{{ \Carbon\Carbon::parse($fr['fecha'])->format('d/m/Y') }}</strong>
                                        </div>
                                        @if (!empty($fr['obs']))
                                            <div style="font-size: 7px; color: #6b7280; font-style: italic;">
                                                "{{ $fr['obs'] }}"</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </td>
                    @endif

                    {{-- Columna vacía si solo hay firma de salida y no hubo recepciones --}}
                    @if ($firmasSalida && $firmasRecep->isEmpty())
                        <td style="width: 50%; vertical-align: bottom; padding-left: 10px;">
                            <div
                                style="border: 1px dashed #e2e8f0; border-radius: 4px; padding: 18px 10px; text-align: center;">
                                <div style="font-size: 8px; color: #cbd5e1; font-style: italic;">Pendiente firma de
                                    recepción</div>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>

            <div
                style="font-size: 7px; color: #a0aec0; font-style: italic; margin-top: 6px; padding-top: 4px; border-top: 1px dashed #e2e8f0;">
                * Firmas digitales capturadas al momento de la transacci&oacute;n. V&aacute;lidas para control interno
                de activos.
            </div>
        @endif

        {{-- ══════════════ PÁGINAS DE FOTOS ══════════════════════════════ --}}

        @if (count($evidenciasSalida) > 0)
            <div class="photo-sect">
                <div class="sect-title">Evidencia Fotográfica — Estado de Salida</div>
                <table class="photo-tbl">
                    @foreach (array_chunk($evidenciasSalida, 2) as $pair)
                        <tr>
                            @foreach ($pair as $foto)
                                @php
                                    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                                    $file = public_path('storage/' . $foto);
                                @endphp
                                <td>
                                    @if ($ext !== 'pdf' && file_exists($file))
                                        <div class="photo-frame">
                                            <img src="{{ $file }}" alt="Salida">
                                            <div class="photo-cap">Estado inicial — Salida</div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            @if (count($pair) === 1)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (count($evidenciasDevueltas) > 0)
            <div class="photo-sect">
                <div class="sect-title sect-title-green">Evidencia Fotográfica — Retorno de Equipos</div>
                <table class="photo-tbl">
                    @foreach (array_chunk($evidenciasDevueltas, 2) as $pair)
                        <tr>
                            @foreach ($pair as $ev)
                                @php
                                    $ext = strtolower(pathinfo($ev['foto'], PATHINFO_EXTENSION));
                                    $file = public_path('storage/' . $ev['foto']);
                                @endphp
                                <td>
                                    @if ($ext !== 'pdf' && file_exists($file))
                                        <div class="photo-frame">
                                            <img src="{{ $file }}" alt="Retorno">
                                            <div class="photo-cap">Retorno —
                                                {{ \Carbon\Carbon::parse($ev['fecha'])->format('d/m/Y') }}</div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            @if (count($pair) === 1)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if (count($evidenciasBajas) > 0)
            <div class="photo-sect">
                <div class="sect-title sect-title-red">Evidencia Fotográfica — Equipos Dados de Baja</div>
                <table class="photo-tbl">
                    @foreach (array_chunk($evidenciasBajas, 2) as $pair)
                        <tr>
                            @foreach ($pair as $ev)
                                @php
                                    $ext = strtolower(pathinfo($ev['foto'], PATHINFO_EXTENSION));
                                    $file = public_path('storage/' . $ev['foto']);
                                @endphp
                                <td>
                                    @if ($ext !== 'pdf' && file_exists($file))
                                        <div class="photo-frame" style="border-color:#fca5a5;">
                                            <img src="{{ $file }}" alt="Baja">
                                            <div class="photo-cap photo-cap-red">
                                                Baja: {{ $ev['nombre'] }}<br>
                                                {{ \Carbon\Carbon::parse($ev['fecha'])->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            @if (count($pair) === 1)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

    </main>
</body>

</html>
