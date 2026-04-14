<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Préstamo {{ $nro_prestamo }}</title>
    <style>
        @page {
            size: letter;
            margin: 100px 40px 80px 40px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Footer fijo en cada página */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 9px;
            color: #9ca3af;
        }

        footer .page-number:after {
            content: "Página " counter(page);
        }

        .header-top {
            position: fixed;
            top: -85px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }

        .header-top h1 {
            margin: 0;
            font-size: 20px;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-top .status {
            position: absolute;
            right: 0;
            top: 0;
            padding: 4px 10px;
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            width: 50%;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .info-val {
            font-size: 11px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table th,
        .table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .table th {
            background: #f9fafb;
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
        }

        .table tr:nth-child(even) {
            background: #fdfdfd;
        }

        .sect-title {
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 20px;
            color: #111827;
            text-transform: uppercase;
            display: block;
            width: 100%;
        }

        .photo-grid {
            width: 100%;
            margin-top: 10px;
        }

        .photo-item {
            display: inline-block;
            width: 48%;
            margin: 0.5%;
            box-sizing: border-box;
            text-align: center;
            vertical-align: top;
            margin-bottom: 15px;
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }

        .photo-caption {
            font-size: 8px;
            color: #6b7280;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }
    </style>
</head>

<body>

    <header class="header-top">
        <h1>Constancia de Préstamo</h1>
        <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">{{ $first->empresa->nombre ?? '' }} &bull;
            Documento de Control Interno</div>
        <div class="status">{{ $first->estado }}</div>
    </header>

    <footer>
        <div style="margin-bottom: 4px;">
            <strong>{{ $nro_prestamo }}</strong> &bull; Generado el {{ now()->format('d/m/Y H:i') }} &bull;
            <span class="page-number"></span>
        </div>
        <div>Control de Activos y Finanzas - Empresa Andina SRL</div>
    </footer>

    <main>
        <table class="info-grid">
            <tr>
                <td>
                    <span class="info-label">Nro. de Préstamo</span>
                    <p class="info-val" style="color: #4f46e5;">{{ $nro_prestamo }}</p>
                    <div style="margin-top:10px;">
                        <span class="info-label">Fecha de Salida</span>
                        <p class="info-val">{{ \Carbon\Carbon::parse($first->fecha_prestamo)->format('d/m/Y') }}</p>
                    </div>
                    <div style="margin-top:10px;">
                        <span class="info-label">Responsable / Agente de Servicio</span>
                        <p class="info-val" style="color: #4f46e5;">
                            {{ $first->agente?->nombre ?? ($first->receptor_manual ?: 'No especificado') }}</p>
                    </div>
                </td>
                <td>
                    <span class="info-label">Entidad / Proyecto</span>
                    <p class="info-val">{{ $first->entidad->nombre ?? 'N/A' }}</p>
                    <div style="margin-top:10px;">
                        <span class="info-label">Ubicación / Obra</span>
                        <p class="info-val">{{ $first->proyecto->nombre ?? 'N/A' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <div class="sect-title">Detalle de Equipos</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%">Herramienta</th>
                    <th class="text-center" style="width: 15%">Código</th>
                    <th class="text-center" style="width: 15%">Cant. Orig.</th>
                    <th class="text-center" style="width: 10%">Devuelto</th>
                    <th class="text-right" style="width: 10%">Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prestamos as $item)
                    <tr>
                        <td><strong>{{ $item->herramienta->nombre }}</strong></td>
                        <td class="text-center font-mono" style="font-size: 10px;">
                            {{ $item->herramienta->codigo ?? '-' }}</td>
                        <td class="text-center">{{ $item->cantidad_prestada }}</td>
                        <td class="text-center">{{ $item->cantidad_devuelta }}</td>
                        <td class="text-right font-bold" style="color:#e11d48;">{{ $item->cantidad_pendiente }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $evidenciasSalida = $first->fotos_salida ?? [];
            $evidenciasDevueltas = [];
            foreach ($prestamos as $item) {
                foreach ($item->devoluciones as $dev) {
                    if (!empty($dev->fotos_entrada)) {
                        foreach ($dev->fotos_entrada as $fe) {
                            $evidenciasDevueltas[] = [
                                'fecha' => $dev->fecha_devolucion,
                                'foto' => $fe,
                            ];
                        }
                    }
                }
            }
        @endphp

        @if (count($evidenciasSalida) > 0)
            <div style="page-break-before: always;">
                <div class="sect-title">Evidencia Fotográfica de Salida</div>
                <div class="photo-grid">
                    @foreach ($evidenciasSalida as $foto)
                        @php
                            $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                            $file = public_path('storage/' . $foto);
                        @endphp
                        @if ($ext !== 'pdf' && file_exists($file))
                            <div class="photo-item">
                                <img src="{{ $file }}">
                                <div class="photo-caption">Estado inicial - Salida</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if (count($evidenciasDevueltas) > 0)
            <div style="page-break-before: always;">
                <div class="sect-title">Evidencia Fotográfica de Retorno</div>
                <div class="photo-grid">
                    @foreach ($evidenciasDevueltas as $ev)
                        @php
                            $ext = strtolower(pathinfo($ev['foto'], PATHINFO_EXTENSION));
                            $file = public_path('storage/' . $ev['foto']);
                        @endphp
                        @if ($ext !== 'pdf' && file_exists($file))
                            <div class="photo-item">
                                <img src="{{ $file }}">
                                <div class="photo-caption">Retorno
                                    ({{ \Carbon\Carbon::parse($ev['fecha'])->format('d/m/Y') }})</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if (!empty($bajas) && $bajas->isNotEmpty())
            <div style="margin-top: 30px;">
                <div class="sect-title" style="color: #b91c1c; border-color: #fca5a5;">
                    ⚠ Equipos Dados de Baja en este Préstamo
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 35%">Herramienta</th>
                            <th class="text-center" style="width: 12%">Código</th>
                            <th class="text-center" style="width: 10%">Cantidad</th>
                            <th style="width: 30%">Motivo / Observaciones</th>
                            <th class="text-center" style="width: 13%">Registrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bajas as $baja)
                            <tr style="background: #fff5f5;">
                                <td>
                                    <strong style="color: #b91c1c;">
                                        {{ $baja->herramienta->nombre ?? 'N/A' }}
                                    </strong>
                                </td>
                                <td class="text-center" style="font-size: 10px; font-family: monospace;">
                                    {{ $baja->herramienta->codigo ?? '-' }}
                                </td>
                                <td class="text-center" style="font-weight: bold; color: #b91c1c;">
                                    {{ $baja->cantidad }}
                                </td>
                                <td style="font-size: 10px; color: #374151;">
                                    {{ $baja->observaciones ?? '-' }}
                                </td>
                                <td class="text-center" style="font-size: 9px; color: #6b7280;">
                                    {{ $baja->created_at->format('d/m/Y') }}<br>
                                    <span style="color: #9ca3af;">{{ $baja->user->name ?? 'Sistema' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p style="font-size: 9px; color: #9ca3af; margin-top: 6px; font-style: italic;">
                    * Los equipos dados de baja han sido descontados permanentemente del inventario.
                </p>
            </div>
        @endif
    </main>

</body>

</html>
