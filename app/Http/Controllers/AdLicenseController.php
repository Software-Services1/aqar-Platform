<?php

namespace App\Http\Controllers;

use App\Models\AdLicense;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdLicenseController extends Controller
{
    public function index(Request $request)
    {
        $from       = $request->input('from');
        $to         = $request->input('to');
        $employeeId = $request->input('employee_id');

        $query = AdLicense::with(['contract', 'employee'])
            ->betweenIssueDates($from, $to)
            ->latest();

        if (! $this->canManageAll()) {
            // الموظف يرى تراخيصه فقط
            $query->where('employee_id', auth()->id());
        } elseif ($employeeId) {
            // المدير/صاحب الصلاحية: فلترة باسم الموظف
            $query->where('employee_id', $employeeId);
        }

        return view('licenses.index', [
            'licenses'   => $query->paginate(15)->withQueryString(),
            'from'       => $from,
            'to'         => $to,
            'employeeId' => $employeeId,
            'employees'  => $this->canManageAll() ? Employee::orderBy('name')->get() : collect(),
        ]);
    }

    public function create(Contract $contract)
    {
        abort_if($contract->approval_status === 'cancelled', 403, 'لا يمكن إنشاء ترخيص لعقد ملغي.');

        if (! $this->canManageAll()) {
            $existing = $contract->licenses()->where('employee_id', auth()->id())->first();
            if ($existing) {
                return redirect()->route('licenses.edit', $existing)
                    ->with('success', 'لديك ترخيص لهذا العقد بالفعل — يمكنك تعديله.');
            }
        }

        return view('licenses.create', [
            'contract'  => $contract,
            'platforms' => Platform::active()->orderBy('name')->get(),
            'employees' => $this->canManageAll() ? Employee::orderBy('name')->get() : collect(),
            'statuses'  => AdLicense::STATUSES,
        ]);
    }

    public function store(Request $request, Contract $contract)
    {
        abort_if($contract->approval_status === 'cancelled', 403);

        $data = $this->validateLicense($request);
        $data['employee_id'] = $this->resolveEmployeeId($request);
        $data['platforms']   = $this->buildPlatforms($request);

        $duplicate = $contract->licenses()
            ->where('employee_id', $data['employee_id'])
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'employee_id' => 'يوجد ترخيص لهذا الموظف على هذا العقد بالفعل.',
            ]);
        }

        $contract->licenses()->create($data);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'تم إنشاء الترخيص الإعلاني.');
    }

    public function edit(AdLicense $license)
    {
        $this->authorizeOwn($license);

        return view('licenses.edit', [
            'license'   => $license->load('contract'),
            'platforms' => Platform::active()->orderBy('name')->get(),
            'statuses'  => AdLicense::STATUSES,
        ]);
    }

    public function update(Request $request, AdLicense $license)
    {
        $this->authorizeOwn($license);

        $data = $this->validateLicense($request, $license);
        $data['platforms'] = $this->buildPlatforms($request);

        $license->update($data);

        return redirect()
            ->route('contracts.show', $license->contract_id)
            ->with('success', 'تم تحديث الترخيص.');
    }

    public function destroy(AdLicense $license)
    {
        $this->authorizeOwn($license);
        $contractId = $license->contract_id;
        $license->delete();

        return redirect()
            ->route('contracts.show', $contractId)
            ->with('success', 'تم حذف الترخيص.');
    }

    /* ----------------------- مساعدات ----------------------- */

    private function validateLicense(Request $request, ?AdLicense $license = null): array
    {
        $unique = 'unique:ad_licenses,license_number' . ($license ? ",{$license->id}" : '');

        // قواعد أساسية
        $rules = [
            'license_number' => ['required', 'string', 'max:60', $unique],
            'issue_date'     => ['required', 'date'],
            'expiry_date'    => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status'         => ['required', 'in:' . implode(',', array_keys(AdLicense::STATUSES))],
            'platforms'      => ['nullable', 'array'],
            'platforms.*'    => ['string'],
            'notes'          => ['nullable', 'string'],
        ];

        // رابط الإعلان إجباري لكل منصة مختارة
        foreach ((array) $request->input('platforms', []) as $name) {
            $rules["links.$name"] = ['required', 'url'];
        }

        return $request->validate($rules, [
            'license_number.unique' => 'رقم الترخيص مستخدم مسبقاً.',
            'links.*.required'      => 'رابط الإعلان مطلوب لكل منصة محدّدة.',
            'links.*.url'           => 'رابط الإعلان غير صالح.',
        ]);
    }

    /** بناء بنية المنصات [{ name, url }] من المختار + الروابط */
    private function buildPlatforms(Request $request): array
    {
        $platforms = [];
        foreach ((array) $request->input('platforms', []) as $name) {
            $platforms[] = [
                'name' => $name,
                'url'  => $request->input("links.$name"),
            ];
        }
        return $platforms;
    }

    private function resolveEmployeeId(Request $request): int
    {
        if ($this->canManageAll() && $request->filled('employee_id')) {
            $request->validate(['employee_id' => ['exists:employees,id']]);
            return (int) $request->input('employee_id');
        }
        return (int) auth()->id();
    }

    private function canManageAll(): bool
    {
        return auth()->user()->isManager() || auth()->user()->can('manage-licenses');
    }

    private function authorizeOwn(AdLicense $license): void
    {
        abort_unless(
            $this->canManageAll() || $license->employee_id === auth()->id(),
            403
        );
    }
}
