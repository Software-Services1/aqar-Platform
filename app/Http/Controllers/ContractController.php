<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Representative;
use App\Services\NotificationService;
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

        $draft = $request->boolean('save_as_draft');
        $data = $this->validateContract($request, null, $draft);
        $data['created_by'] = auth()->id();
        $data['is_draft']   = $draft;

        // المسودة قد تكون بلا عنوان → عنوان مؤقت للعرض في القوائم
        if (empty($data['project_name'])) {
            $data['project_name'] = 'مسودة بدون عنوان';
        }

        $request->validate(['assigned' => ['array'], 'assigned.*' => ['exists:employees,id']]);
        $contract = Contract::create($data);
        $contract->assignedEmployees()->sync($request->input('assigned', []));

        // إشعار الموظفين المصرّح لهم فقط عند اعتماد العقد (وليس مسودة/بانتظار الموافقة)
        if (! $draft && $contract->approval_status === 'approved') {
            app(NotificationService::class)->contractCreated($contract);
        }

        $msg = $draft
            ? 'تم حفظ العقد كمسودة (بيانات ناقصة).'
            : 'تم حفظ العقد.';

        return redirect()->route('contracts.show', $contract)->with('success', $msg);
    }

    public function show(Contract $contract)
    {
        $this->authorizeView($contract);

        $contract->load(['representative', 'creator', 'licenses.employee', 'parent', 'subContracts', 'externalCompany', 'assignedEmployees']);

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

        $wasApproved = $contract->approval_status === 'approved';

        $draft = $request->boolean('save_as_draft');
        $data = $this->validateContract($request, $contract, $draft);
        $data['is_draft'] = $draft;
        if (empty($data['project_name'])) {
            $data['project_name'] = 'مسودة بدون عنوان';
        }

        $request->validate(['assigned' => ['array'], 'assigned.*' => ['exists:employees,id']]);
        $contract->update($data);
        $contract->assignedEmployees()->sync($request->input('assigned', []));

        // إشعار عند الانتقال إلى «تمت الموافقة» فقط
        if (! $draft && $contract->approval_status === 'approved' && ! $wasApproved) {
            app(NotificationService::class)->contractCreated($contract);
        }

        return redirect()->route('contracts.show', $contract)
            ->with('success', $draft ? 'تم حفظ العقد كمسودة.' : 'تم تحديث العقد.');
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
        Gate::authorize('manage-contracts');

        // نسخة غير محفوظة من بيانات العقد الأصل لملء النموذج (عدا رقم العقد)
        $prefill = new Contract($contract->only([
            'project_name', 'developer_name', 'developer_phone', 'neighborhood',
            'contract_type', 'transaction_type', 'responsible_name', 'responsible_phone', 'representative_id',
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
        Gate::authorize('manage-contracts');

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

    private function validateContract(Request $request, ?Contract $contract = null, bool $draft = false): array
    {
        $unique = 'unique:contracts,contract_number' . ($contract ? ",{$contract->id}" : '');

        // في المسودة: كل الحقول اختيارية عدا اسم المشروع (للتعريف)
        $req = fn (array $rules) => $draft ? array_merge(['nullable'], array_slice($rules, 1)) : $rules;

        return $request->validate([
            'contract_number'   => ['nullable', 'string', 'max:60', $unique],
            'project_name'      => $req(['required', 'string', 'max:255']),
            'developer_name'    => $req(['required', 'string', 'max:255']),
            'developer_phone'   => ['nullable', 'string', 'max:30'],
            'neighborhood'      => ['nullable', 'string', 'max:120'],
            'contract_type'     => ['required', 'in:' . implode(',', array_keys(Contract::TYPES))],
            'transaction_type'  => ['required', 'in:' . implode(',', array_keys(Contract::TRANSACTION_TYPES))],
            'responsible_name'  => ['nullable', 'string', 'max:255'],
            'responsible_phone' => ['nullable', 'string', 'max:30'],
            'representative_id' => ['nullable', 'exists:representatives,id'],
            'start_date'        => $req(['required', 'date']),
            'end_date'          => $draft ? ['nullable', 'date'] : ['required', 'date', 'after_or_equal:start_date'],
            'approval_status'   => ['required', 'in:' . implode(',', array_keys(Contract::STATUSES))],
            'notes'             => ['nullable', 'string'],
        ], [
            'contract_number.unique' => 'رقم العقد مستخدم مسبقاً.',
        ]);
    }

    private function formData(): array
    {
        return [
            'allEmployees'     => Employee::where('is_active', true)->orderBy('name')->get(),
            'representatives'  => Representative::active()->orderBy('name')->get(),
            'types'            => Contract::TYPES,
            'transactionTypes' => Contract::TRANSACTION_TYPES,
            'statuses'         => Contract::STATUSES,
        ];
    }

    private function authorizeView(Contract $contract): void
    {
        $user = auth()->user();

        // المدير/صاحب صلاحية إدارة العقود يرى الكل؛ غيرهم وفق قاعدة رؤية الموظف
        // (العقود بانتظار الموافقة/الملغاة/المنتهية دون موافقة لا تظهر للموظف)
        abort_unless(
            $user->isManager()
            || $user->can('manage-contracts')
            || Contract::whereKey($contract->id)->visibleToEmployee($user->id)->exists(),
            403
        );
    }
}
