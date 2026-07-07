<?php

namespace App\Http\Controllers;

use App\Models\AdLicense;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\ExternalCompany;
use App\Models\Platform;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view-reports');

        $reportType  = $request->input('report_type') === 'licenses' ? 'licenses' : 'contracts';
        $activeCount = Platform::active()->count();

        // بيانات مشتركة للقوائم المنسدلة
        $shared = [
            'reportType'       => $reportType,
            'employees'        => Employee::orderBy('name')->get(),
            'representatives'  => Representative::orderBy('name')->get(),
            'externalCompanies' => ExternalCompany::orderBy('name')->get(),
            'responsibles'     => Contract::whereNotNull('responsible_name')->distinct()->orderBy('responsible_name')->pluck('responsible_name'),
            'neighborhoods'    => Contract::whereNotNull('neighborhood')->distinct()->orderBy('neighborhood')->pluck('neighborhood'),
            'types'            => Contract::TYPES,
            'transactionTypes' => Contract::TRANSACTION_TYPES,
            'statuses'         => Contract::STATUSES,
            'licenseStatuses'  => AdLicense::STATUSES,
            'licenseStates'    => [
                'no_license'  => 'بلا ترخيص',
                'unpublished' => 'ترخيص غير منشور بالكامل',
                'published'   => 'منشور بالكامل',
            ],
            'publishStates'    => [
                'none'    => 'لم يُنشر',
                'partial' => 'نشر جزئي',
                'full'    => 'منشور بالكامل',
            ],
        ];

        return $reportType === 'licenses'
            ? view('reports.index', array_merge($shared, $this->licensesReport($request, $activeCount)))
            : view('reports.index', array_merge($shared, $this->contractsReport($request, $activeCount)));
    }

    /* ----------------------- تقرير العقود ----------------------- */

    private function contractsReport(Request $request, int $activeCount): array
    {
        $filters = [
            'from'                => $request->input('from'),
            'to'                  => $request->input('to'),
            'responsible'         => $request->input('responsible'),
            'representative_id'   => $request->input('representative_id'),
            'external_company_id' => $request->input('external_company_id'),
            'neighborhood'        => $request->input('neighborhood'),
            'contract_type'       => $request->input('contract_type'),
            'transaction_type'    => $request->input('transaction_type'),
            'status'              => $request->input('status'),
            'license_state'       => $request->input('license_state'),
        ];

        $query = Contract::query()
            ->with(['representative', 'externalCompany', 'licenses'])
            ->betweenDates($filters['from'], $filters['to'])
            ->ofType($filters['contract_type'])
            ->ofTransaction($filters['transaction_type'])
            ->inNeighborhood($filters['neighborhood']);

        foreach (['representative_id', 'external_company_id'] as $col) {
            if ($filters[$col]) {
                $query->where($col, $filters[$col]);
            }
        }
        if ($filters['responsible']) {
            $query->where('responsible_name', $filters['responsible']);
        }
        if ($filters['status']) {
            $query->where('approval_status', $filters['status']);
        }
        match ($filters['license_state']) {
            'no_license'  => $query->withoutLicense(),
            'unpublished' => $query->notFullyPublished($activeCount),
            'published'   => $query->fullyPublished($activeCount),
            default       => null,
        };

        return [
            'filters'  => $filters,
            'results'  => (clone $query)->latest('start_date')->paginate(20)->withQueryString(),
            'summary'  => [
                'total'     => (clone $query)->count(),
                'approved'  => (clone $query)->where('approval_status', 'approved')->count(),
                'pending'   => (clone $query)->where('approval_status', 'pending')->count(),
                'finished'  => (clone $query)->where('approval_status', 'finished')->count(),
                'expired'   => (clone $query)->where('approval_status', 'expired')->count(),
                'cancelled' => (clone $query)->where('approval_status', 'cancelled')->count(),
            ],
        ];
    }

    /* ----------------------- تقرير التراخيص (لكل مستخدم) ----------------------- */

    private function licensesReport(Request $request, int $activeCount): array
    {
        $filters = [
            'from'          => $request->input('from'),
            'to'            => $request->input('to'),
            'employee_id'   => $request->input('employee_id'),
            'status'        => $request->input('status'),
            'publish_state' => $request->input('publish_state'),
        ];

        $query = AdLicense::query()
            ->with(['contract', 'employee'])
            ->betweenIssueDates($filters['from'], $filters['to']);

        // الموظف/المستخدم الذي أنشأ الترخيص (قد لا يكون مسؤول العقد)
        if ($filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        $threshold = max($activeCount, 1);
        match ($filters['publish_state']) {
            'none'    => $query->where('platform_count', 0),
            'partial' => $query->where('platform_count', '>', 0)->where('platform_count', '<', $threshold),
            'full'    => $query->where('platform_count', '>=', $threshold),
            default   => null,
        };

        $none = (clone $query)->where('platform_count', 0)->count();
        $full = (clone $query)->where('platform_count', '>=', $threshold)->count();
        $total = (clone $query)->count();

        return [
            'filters'         => $filters,
            'licenseResults'  => (clone $query)->latest('issue_date')->paginate(20)->withQueryString(),
            'licenseSummary'  => [
                'total'   => $total,
                'none'    => $none,
                'partial' => max($total - $none - $full, 0),
                'full'    => $full,
            ],
        ];
    }
}
