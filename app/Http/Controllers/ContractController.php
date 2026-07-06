<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContractController extends Controller
{
    public function index()
    {
        return view('contracts.index');
    }

    public function create()
    {
        Gate::authorize('manage-contracts');

        return view('contracts.create', $this->formData());
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-contracts');

        $data = $this->validateContract($request);
        $data['created_by'] = auth()->id();

        $contract = Contract::create($data);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', "تم إنشاء العقد رقم {$contract->contract_number} وإشعار الموظفين.");
    }

    public function show(Contract $contract)
    {
        $this->authorizeView($contract);

        $contract->load(['employee', 'representative', 'creator', 'licenses.employee', 'parent', 'subContracts', 'externalCompany']);

        // ترخيص الموظف الحالي لهذا العقد (إن وُجد)
        $myLicense = $contract->licenses->firstWhere('employee_id', auth()->id());

        return view('contracts.show', compact('contract', 'myLicense'));
    }

    public function edit(Contract $contract)
    {
        Gate::authorize('manage-contracts');

        return view('contracts.edit', array_merge(['contract' => $contract], $this->formData()));
    }

    public function update(Request $request, Contract $contract)
    {
        Gate::authorize('manage-contracts');

        $contract->update($this->validateContract($request, $contract));

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'تم تحديث العقد.');
    }

    public function destroy(Contract $contract)
    {
        Gate::authorize('manage-contracts');
        $contract->delete();

        return redirect()->route('contracts.index')->with('success', 'تم حذف العقد.');
    }

    /* ----------------------- العقود الفرعية ----------------------- */

    /** نموذج إنشاء عقد فرعي لشركة أخرى — يأخذ بيانات العقد الأصل */
    public function createSub(Contract $contract)
    {
        Gate::authorize('create-subcontract');

        // نسخة غير محفوظة من بيانات العقد الأصل لملء النموذج (عدا رقم العقد)
        $prefill = new Contract($contract->only([
            'project_name', 'developer_name', 'developer_phone', 'neighborhood',
            'contract_type', 'transaction_type', 'employee_id', 'representative_id',
            'start_date', 'end_date', 'notes',
        ]));
        $prefill->approval_status = 'pending';

        return view('contracts.create_sub', array_merge(
            ['parent' => $contract, 'contract' => $prefill, 'externalCompanies' => \App\Models\ExternalCompany::active()->orderBy('name')->get()],
            $this->formData()
        ));
    }

    public function storeSub(Request $request, Contract $contract)
    {
        Gate::authorize('create-subcontract');

        $data = $this->validateContract($request);

        // بيانات الشركة الخارجية (اسم الشركة + المسؤول + الجوال)
        $companyData = $request->validate([
            'ext_company_name'   => ['required', 'string', 'max:255'],
            'ext_contact_person' => ['nullable', 'string', 'max:255'],
            'ext_phone'          => ['nullable', 'string', 'max:30'],
        ], [], [
            'ext_company_name' => 'اسم الشركة',
        ]);

        // إعادة استخدام الشركة إن وُجدت بنفس الاسم، وإلا إنشاؤها
        $company = \App\Models\ExternalCompany::firstOrNew(['name' => $companyData['ext_company_name']]);
        $company->contact_person = $companyData['ext_contact_person'] ?? $company->contact_person;
        $company->phone          = $companyData['ext_phone'] ?? $company->phone;
        $company->is_active      = true;
        $company->save();

        $data['created_by']          = auth()->id();
        $data['parent_id']           = $contract->id;
        $data['external_company_id'] = $company->id;

        $sub = Contract::create($data);

        return redirect()
            ->route('contracts.show', $sub)
            ->with('success', "تم إنشاء العقد الفرعي رقم {$sub->contract_number} للشركة «{$company->name}».");
    }

    /* ----------------------- مساعدات ----------------------- */

    private function validateContract(Request $request, ?Contract $contract = null): array
    {
        $unique = 'unique:contracts,contract_number' . ($contract ? ",{$contract->id}" : '');

        return $request->validate([
            'contract_number'   => ['required', 'string', 'max:60', $unique],
            'project_name'      => ['required', 'string', 'max:255'],
            'developer_name'    => ['required', 'string', 'max:255'],
            'developer_phone'   => ['nullable', 'string', 'max:30'],
            'neighborhood'      => ['nullable', 'string', 'max:120'],
            'contract_type'     => ['required', 'in:' . implode(',', array_keys(Contract::TYPES))],
            'transaction_type'  => ['required', 'in:' . implode(',', array_keys(Contract::TRANSACTION_TYPES))],
            'employee_id'       => ['nullable', 'exists:employees,id'],
            'representative_id' => ['nullable', 'exists:representatives,id'],
            'start_date'        => ['required', 'date'],
            'end_date'          => ['required', 'date', 'after_or_equal:start_date'],
            'approval_status'   => ['required', 'in:' . implode(',', array_keys(Contract::STATUSES))],
            'notes'             => ['nullable', 'string'],
        ], [
            'contract_number.unique' => 'رقم العقد مستخدم مسبقاً.',
        ]);
    }

    private function formData(): array
    {
        return [
            'employees'        => Employee::orderBy('name')->get(),
            'representatives'  => Representative::active()->orderBy('name')->get(),
            'types'            => Contract::TYPES,
            'transactionTypes' => Contract::TRANSACTION_TYPES,
            'statuses'         => Contract::STATUSES,
        ];
    }

    private function authorizeView(Contract $contract): void
    {
        $user = auth()->user();

        // المدير أو صاحب صلاحية إدارة العقود يرى كل العقود بكل حالاتها؛
        // الموظف العادي يرى المعتمدة (لإنشاء ترخيصه) أو ما هو مسؤول عنه
        abort_unless(
            $user->isManager()
            || $user->can('manage-contracts')
            || $contract->approval_status === 'approved'
            || $contract->employee_id === $user->id
            || $contract->created_by === $user->id,
            403
        );
    }
}
