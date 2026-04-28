<?php

namespace App\Livewire\Admin\Herramientas\Traits;

use App\Models\Herramienta;

trait WithDetail
{
    public function openDetail(int $id): void
    {
        $h = Herramienta::with([
            'empresa',
            'series' => fn ($q) => $q->orderBy('serie'),
            'bajas' => function ($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            },
            'prestamos' => function ($q) {
                $q->with(['entidad', 'proyecto'])->orderBy('fecha_prestamo', 'desc')->limit(6);
            },
        ])->findOrFail($id);

        if ((int) $h->empresa_id !== (int) $this->userEmpresaId()) {
            abort(403);
        }

        $total = max(1, $h->stock_total);
        $this->detail = [
            'id' => $h->id,
            'codigo' => $h->codigo,
            'nombre' => $h->nombre,
            'empresa' => $h->empresa?->nombre,
            'marca' => $h->marca,
            'modelo' => $h->modelo,
            'descripcion' => $h->descripcion,
            'estado_fisico' => $h->estado_fisico,
            'estado_fisico_label' => $h->estado_fisico_label,
            'estado_fisico_badge' => $h->estado_fisico_badge,
            'unidad' => $h->unidad,
            'stock_total' => $h->stock_total,
            'stock_disponible' => $h->stock_disponible,
            'stock_prestado' => $h->stock_prestado,
            'pct_disponible' => round(($h->stock_disponible / $total) * 100),
            'pct_prestado' => round(($h->stock_prestado / $total) * 100),
            'precio_unitario' => number_format((float) $h->precio_unitario, 2, ',', '.'),
            'precio_total' => number_format((float) $h->precio_total, 2, ',', '.'),
            'tipo' => $h->tipo,
            'active' => $h->active,
            'imagen' => $h->imagen,
            'series' => in_array($h->tipo, ['activo', 'equipo']) ? $h->series->map(fn ($s) => [
                'serie'  => $s->serie,
                'estado' => $s->estado,
            ])->toArray() : [],
            'created_at' => $h->created_at?->format('d/m/Y H:i'),
            'updated_at' => $h->updated_at?->format('d/m/Y H:i'),
            'bajas' => $h->bajas->map(function ($baja) {
                return [
                    'id' => $baja->id,
                    'cantidad' => $baja->cantidad,
                    'observaciones' => $baja->observaciones,
                    'fecha' => $baja->created_at->format('d/m/Y H:i'),
                    'usuario' => $baja->user ? $baja->user->name : 'Sistema',
                    'evidencia' => $baja->imagen,
                ];
            })->toArray(),
            'prestamos_recientes' => $h->prestamos->map(function ($p) {
                return [
                    'nro'      => $p->nro_prestamo,
                    'entidad'  => $p->entidad?->nombre ?? '—',
                    'proyecto' => $p->proyecto?->nombre ?? '—',
                    'fecha'    => $p->fecha_prestamo?->format('d/m/Y'),
                    'cantidad' => $p->cantidad_prestada,
                    'estado'   => $p->estado,
                ];
            })->toArray(),
        ];

        $this->detailModal = true;
    }

    public function closeDetail(): void
    {
        $this->detailModal = false;
        $this->detail = [];
    }

    public function openEditFromDetail(int $id): void
    {
        $this->detailModal = false;
        $this->detail = [];
        $this->openEdit($id);
    }
}
