<?php

namespace App\Livewire\Admin\Inversiones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inversion;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // =========================
    // Filtros (misma idea que tu plantilla)
    // =========================
    public string $search = '';
    public string $fTipo = '';
    public string $fEstado = '';

    // Para refrescar desde modals/listeners
    protected $listeners = [
        'inversionUpdated' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFEstado(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->dispatch('openCreateInversion');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'fTipo', 'fEstado']);
        $this->resetPage();
    }

    public function render()
    {
        $q = Inversion::query()->where('empresa_id', auth()->user()->empresa_id);

        if ($this->search !== '') {
            $s = trim($this->search);
            $q->where(function ($w) use ($s) {
                $w->where('codigo', 'like', "%{$s}%")->orWhere('nombre_completo', 'like', "%{$s}%");
            });
        }

        if ($this->fTipo !== '') {
            $q->where('tipo', $this->fTipo);
        }

        if ($this->fEstado !== '') {
            $q->where('estado', $this->fEstado);
        }

        return view('livewire.admin.inversiones.index', [
            'inversiones' => $q->orderByDesc('id')->paginate(10),
        ]);
    }
}
