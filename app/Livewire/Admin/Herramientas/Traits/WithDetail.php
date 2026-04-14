<?php

namespace App\Livewire\Admin\Herramientas\Traits;

use App\Models\Herramienta;

trait WithDetail
{
        // Ver detalle
	    // =========================
	    public function openDetail(int $id): void
	    {
	        $h = Herramienta::with(['empresa', 'bajas' => function ($q) {
	            $q->with('user')->orderBy('created_at', 'desc');
	        }])->findOrFail($id);
	
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
	            'active' => $h->active,
	            'imagen' => $h->imagen,
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
