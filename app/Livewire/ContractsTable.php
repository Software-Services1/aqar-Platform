<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Employee;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ContractsTable extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $state = '';      // active | expiring | pending | expired | cancelled

    #[Url]
    public string $type = '';       // exclusive | brokerage | marketing

    #[Url]
    public string $employee = '';   // فلترة حسب المسؤول (للمدير)

    #[Url]
    public string $sort = 'end_date';
    #[Url]
    public string $dir = 'asc';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'state', 'type', 'employee'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir  = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'state', 'type', 'employee']);
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $canManageContracts = $user->isManager() || $user->can('manage-contracts');

        $query = Contract::query()->with(['employee', 'representative', 'licenses']);

        // الموظف العادي يرى العقود المعتمدة فقط؛ صاحب صلاحية إدارة العقود يرى الجميع بكل الحالات
        if (! $canManageContracts) {
            $query->approved();
        } elseif ($this->employee !== '') {
            $query->where('employee_id', $this->employee);
        }

        $query->search($this->search)->ofType($this->type ?: null);

        $activeCount = \App\Models\Platform::active()->count();

        match ($this->state) {
            'active'      => $query->active(),
            'expiring'    => $query->active()->expiringSoon(),
            'pending'     => $query->pending(),
            'finished'    => $query->finished(),
            'expired'     => $query->expired(),
            'cancelled'   => $query->cancelled(),
            'no_license'  => $query->withoutLicense(),
            'unpublished' => $query->notFullyPublished($activeCount),
            default       => null,
        };

        $allowed = ['end_date', 'start_date', 'project_name', 'contract_number', 'approval_status'];
        $sort = in_array($this->sort, $allowed) ? $this->sort : 'end_date';
        $query->orderBy($sort, $this->dir === 'desc' ? 'desc' : 'asc');

        return view('livewire.contracts-table', [
            'contracts' => $query->paginate(12),
            'employees' => $canManageContracts ? Employee::orderBy('name')->get() : collect(),
            'types'     => Contract::TYPES,
        ]);
    }
}
