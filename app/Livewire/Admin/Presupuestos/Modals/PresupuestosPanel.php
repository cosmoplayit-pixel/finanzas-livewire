<?php

namespace App\Livewire\Admin\Presupuestos\Modals;

use App\Models\AgenteServicio;
use App\Models\Rendicion;
use App\Queries\AgentePresupuestosResumenQuery;

trait PresupuestosPanel
{
    // UI anti doble click
    public bool $creatingRendicion = false;

    // Paneles inline
    public array $panelsOpen = [];

    public array $panelData = [];

    public array $panelTotalFalta = [];

    public array $panelAgenteMeta = [];

    public array $panelEstado = []; // ALL | OPEN | CLOSED

    protected function normalizeMoneda(?string $moneda): string
    {
        $m = strtoupper(trim((string) $moneda));

        return in_array($m, ['BOB', 'USD'], true) ? $m : 'BOB';
    }

    protected function rowKey(int $agenteId, string $moneda): string
    {
        return $agenteId.'|'.$this->normalizeMoneda($moneda);
    }

    public function togglePanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $key = $this->rowKey($agenteId, $moneda);

        $isOpen = (bool) ($this->panelsOpen[$key] ?? false);
        $this->panelsOpen[$key] = ! $isOpen;

        if ($this->panelsOpen[$key]) {
            $this->loadPanel($agenteId, $moneda);
        }
    }

    public function toggleAllPanels(bool $expand): void
    {
        $filters = [
            'empresaId' => $this->isAdmin() ? null : $this->userEmpresaId(),
            'empresaFilter' => $this->empresaFilter,
            'search' => $this->search,
            'moneda' => $this->moneda,
            'soloPendientes' => $this->soloPendientes,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $query = new AgentePresupuestosResumenQuery;
        $agentesResumen = $query->paginate($filters, $this->perPage);

        foreach ($agentesResumen as $row) {
            $key = $this->rowKey((int) $row->agente_servicio_id, $row->moneda);
            $this->panelsOpen[$key] = $expand;

            if ($expand) {
                $this->loadPanel((int) $row->agente_servicio_id, $row->moneda);
            } else {
                unset($this->panelData[$key], $this->panelTotalFalta[$key], $this->panelAgenteMeta[$key]);
            }
        }
    }

    protected function loadPanel(int $agenteId, string $moneda): void
    {
        $moneda = $this->normalizeMoneda($moneda);
        $rowKey = $this->rowKey($agenteId, $moneda);
        $empresaId = $this->isAdmin() ? null : $this->userEmpresaId();

        $agente = AgenteServicio::query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->find($agenteId);

        if (! $agente) {
            $this->panelData[$rowKey] = [];
            $this->panelTotalFalta[$rowKey] = 0;
            $this->panelAgenteMeta[$rowKey] = ['nombre' => '—', 'ci' => '—'];

            return;
        }

        $this->panelAgenteMeta[$rowKey] = [
            'nombre' => $agente->nombre,
            'ci' => $agente->ci ?? '—',
        ];

        // Ahora cargamos directamente de rendiciones (tabla unificada)
        $rows = Rendicion::query()
            ->with(['banco'])
            ->withCount('movimientos')
            ->where('agente_servicio_id', $agenteId)
            ->where('moneda', $moneda)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('estado', $this->soloPendientes ? 'abierto' : 'cerrado')
            ->orderByDesc('fecha_presupuesto')
            ->orderByDesc('id')
            ->get();

        $this->panelData[$rowKey] = $rows->all();
        $this->panelTotalFalta[$rowKey] = (float) $rows->sum('saldo_por_rendir');
    }

    /**
     * Abre el editor de movimientos de una rendición.
     * Ya no crea rendición separada — la rendición ya existe desde que se crea el presupuesto.
     */
    public function abrirEditorRendicion(int $rendicionId): void
    {
        $this->openRendicionEditor($rendicionId);
    }
}
