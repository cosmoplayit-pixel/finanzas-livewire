<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Auditoria extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEvent = '';
    public string $filterModel = '';

    protected $queryString = [
        'search'      => ['except' => ''],
        'filterEvent' => ['except' => ''],
        'filterModel' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEvent(): void
    {
        $this->resetPage();
    }

    public function updatingFilterModel(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Activity::with('causer')
            ->latest()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('causer', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->filterEvent, fn ($q) => $q->where('event', $this->filterEvent))
            ->when($this->filterModel, fn ($q) => $q->where('subject_type', 'like', '%' . $this->filterModel . '%'));

        $logs   = $query->paginate(25);
        $events = Activity::select('event')->distinct()->pluck('event')->filter()->values();

        return view('livewire.admin.auditoria', compact('logs', 'events'));
    }
}
