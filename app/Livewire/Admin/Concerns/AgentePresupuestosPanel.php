<?php namespace App\Livewire\Admin\Concerns;

use App\Models\AgentePresupuesto;
use App\Models\AgenteServicio;
use App\Models\RendicionService;
use App\Services\RendicionService as RendicionDomainService;

trait AgentePresupuestosPanel
{
    // UI anti doble click (crear rendición)
    public bool $creatingRendicion = false;

    // Paneles inline
    public array $panelsOpen = [];
    public array $panelData = [];
    public array $panelTotalFalta = [];
    public array $panelAgenteMeta = [];
    public array $panelEstado = []; // ALL | OPEN | CLOSED

    // Helpers
    protected function normalizeMoneda(?string $moneda): string
    {
        $m = strtoupper(trim((string) $moneda));
        return in_array($m, ['BOB', 'USD'], true) ? $m : 'BOB';
    }

    // Clave única por fila agente+moneda
    protected function rowKey(int $agenteId, string $moneda): string
    {
        return $agenteId . '|' . $this->normalizeMoneda($moneda);
    }

    // TOGGLE panel inline
    public function togglePanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $key = $this->rowKey($agenteId, $moneda);

        $isOpen = (bool) ($this->panelsOpen[$key] ?? false);
        $this->panelsOpen[$key] = !$isOpen;

        if ($this->panelsOpen[$key]) {
            $this->loadPanel($agenteId, $moneda);
        }
    }

    // Cargar datos del panel inline
    protected function loadPanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $rowKey = $this->rowKey($agenteId, $moneda);

        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $agente = AgenteServicio::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->find($agenteId);

        if (!$agente) {
            $this->panelData[$rowKey] = [];
            $this->panelTotalFalta[$rowKey] = 0;
            $this->panelAgenteMeta[$rowKey] = ['nombre' => '—', 'ci' => '—'];
            return;
        }

        $this->panelAgenteMeta[$rowKey] = [
            'nombre' => $agente->nombre,
            'ci' => $agente->ci ?? '—',
        ];

        $rows = AgentePresupuesto::query()
            ->with(['rendicion', 'banco'])
            ->where('agente_servicio_id', $agenteId)
            ->where('moneda', $moneda)
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where('estado', $this->soloPendientes ? 'abierto' : 'cerrado')
            ->orderByDesc('fecha_presupuesto')
            ->orderByDesc('id')
            ->get();

        $this->panelData[$rowKey] = $rows->all();
        $this->panelTotalFalta[$rowKey] = (float) $rows->sum('saldo_por_rendir');
    }

    // RENDICIÓN: crear + abrir editor
    public function crearRendicion(int $presupuestoId, RendicionDomainService $service): void
    {
        if ($this->creatingRendicion) {
            return;
        }

        $this->creatingRendicion = true;

        try {
            $p0 = AgentePresupuesto::query()
                ->when(!$this->isAdmin(), fn($q) => $q->where('empresa_id', $this->userEmpresaId()))
                ->findOrFail($presupuestoId);

            $rend = $service->crearDesdePresupuesto($p0, auth()->user());

            $this->openRendicionEditor((int) $rend->id);
            session()->flash('success', 'Rendición creada.');
        } finally {
            $this->creatingRendicion = false;
        }
    }
}
