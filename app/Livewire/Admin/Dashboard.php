<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $isAdmin = false;

    // Contexto empresa
    public ?int $empresaId = null;
    public string $empresaNombre = '';

    // KPIs
    public int $usuariosTotal = 0;
    public int $usuariosActivos = 0;
    public int $usuariosInactivos = 0;

    public int $empresasTotal = 0; // solo admin
    public int $entidadesTotal = 0;
    public int $proyectosTotal = 0;

    // ✅ Monto total de proyectos (global o por empresa)
    public float $proyectosMontoTotal = 0;

    // Listas rápidas
    public $ultimosUsuarios = [];
    public $ultimosProyectos = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->isAdmin = $user?->hasRole('Administrador') ?? false;

        // Contexto empresa (para no admin)
        $this->empresaId = $user?->empresa_id ? (int) $user->empresa_id : null;
        $this->empresaNombre = $user?->empresa?->nombre ?? '';

        $this->loadKpis();
        $this->loadRecents();
    }

    protected function loadKpis(): void
    {
        // -----------------------
        // Usuarios
        // -----------------------
        $usersQuery = User::query();

        if (!$this->isAdmin) {
            $usersQuery->where('empresa_id', $this->empresaId);
        }

        $this->usuariosTotal = (int) (clone $usersQuery)->count();
        $this->usuariosActivos = (int) (clone $usersQuery)->where('active', true)->count();
        $this->usuariosInactivos = (int) (clone $usersQuery)->where('active', false)->count();

        // -----------------------
        // Empresas (solo admin)
        // -----------------------
        $this->empresasTotal = $this->isAdmin ? (int) Empresa::query()->count() : 0;

        // -----------------------
        // Entidades / Proyectos
        // -----------------------
        $entidadesQuery = Entidad::query();
        $proyectosQuery = Proyecto::query();

        if (!$this->isAdmin) {
            // Ajusta aquí si tu relación es distinta
            $entidadesQuery->where('empresa_id', $this->empresaId);
            $proyectosQuery->where('empresa_id', $this->empresaId);
        }

        $this->entidadesTotal = (int) $entidadesQuery->count();
        $this->proyectosTotal = (int) $proyectosQuery->count();

        // ✅ SUMA TOTAL MONTO PROYECTOS (MISMA QUERY FILTRADA)
        $this->proyectosMontoTotal = (float) $proyectosQuery->sum('monto');
    }

    protected function loadRecents(): void
    {
        // -----------------------
        // Recientes
        // -----------------------
        $usersQuery = User::query()->latest('id');
        $proyectosQuery = Proyecto::query()->latest('id');

        if (!$this->isAdmin) {
            $usersQuery->where('empresa_id', $this->empresaId);

            // Ajusta aquí si tu relación es distinta
            $proyectosQuery->where('empresa_id', $this->empresaId);
        }

        $this->ultimosUsuarios = $usersQuery
            ->select(['id', 'name', 'email', 'active', 'created_at'])
            ->limit(6)
            ->get();

        $this->ultimosProyectos = $proyectosQuery
            ->select(['id', 'nombre', 'codigo', 'monto', 'created_at'])
            ->limit(6)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->title('Panel de Control');
    }
}
