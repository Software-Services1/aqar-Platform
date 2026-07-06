<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-reports');

        $filters = [
            'from'              => $request->input('from'),
            'to'                => $request->input('to'),
            'employee_id'       => $request->input('employee_id'),
            'representative_id' => $request->input('representative_id'),
            'neighborhood'      => $request->input('neighborhood'),
            'contract_type'     => $request->input('contract_type'),
            'status'            => $request->input('status'),
            'license_state'     => $request->input('license_state'),
        ];

        $activeCount = \App\Models\Platform::active()->count();

        $query = Contract::query()
            ->with(['employee', 'representative', 'licenses'])
            ->betweenDates($filters['from'], $filters['to'])
            ->ofType($filters['contract_type'])
            ->inNeighborhood($filters['neighborhood']);

        if ($filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if ($filters['representative_id']) {
            $query->where('representative_id', $filters['representative_id']);
        }
        if ($filters['status']) {
            $query->where('approval_status', $filters['status']);
        }
        // فلتر حالة الترخيص/النشر
        match ($filters['license_state']) {
            'no_license'  => $query->withoutLicense(),
            'unpublished' => $query->notFullyPublished($activeCount),
            'published'   => $query->fullyPublished($activeCount),
            default       => null,
        };

        $results = (clone $query)->latest('start_date')->paginate(20)->withQueryString();

        $summary = [
            'total'     => (clone $query)->count(),
            'approved'  => (clone $query)->where('approval_status', 'approved')->count(),
            'pending'   => (clone $query)->where('approval_status', 'pending')->count(),
            'finished'  => (clone $query)->where('approval_status', 'finished')->count(),
            'expired'   => (clone $query)->where('approval_status', 'expired')->count(),
            'cancelled' => (clone $query)->where('approval_status', 'cancelled')->count(),
        ];

        return view('reports.index', [
            'filters'         => $filters,
            'results'         => $results,
            'summary'         => $summary,
            'employees'       => Employee::orderBy('name')->get(),
            'representatives' => Representative::orderBy('name')->get(),
            'neighborhoods'   => Contract::whereNotNull('neighborhood')->distinct()->orderBy('neighborhood')->pluck('neighborhood'),
            'types'           => Contract::TYPES,
            'statuses'        => Contract::STATUSES,
            'licenseStates'   => [
                'no_license'  => 'بلا ترخيص',
                'unpublished' => 'ترخيص غير منشور بالكامل',
                'published'   => 'منشور بالكامل',
            ],
        ]);
    }
}
