<?php

namespace Inox\Api\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Inox\Api\Models\ApiLogEntry;

#[Layout('layouts.admin')]
class ApiLog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $methodFilter = '';

    public string $statusFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'methodFilter', 'statusFilter', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $query = ApiLogEntry::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('url', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%");
            });
        }

        if ($this->methodFilter) {
            $query->where('method', $this->methodFilter);
        }

        if ($this->statusFilter) {
            $query->where('status_code', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('api::livewire.api-log', [
            'entries' => $entries,
            'methods' => ApiLogEntry::distinct()->pluck('method')->toArray(),
            'statuses' => ApiLogEntry::distinct()->orderBy('status_code')->pluck('status_code')->toArray(),
        ]);
    }
}
