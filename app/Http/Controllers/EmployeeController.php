<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-employees');
        $employees = Employee::with('roles')->withCount('contracts')->orderBy('name')->paginate(15);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        Gate::authorize('manage-employees');

        return view('employees.create', ['roles' => Role::orderBy('name')->get()]);
    }

    /** إضافة موظف جديد → إنشاء حسابه تلقائياً */
    public function store(Request $request)
    {
        Gate::authorize('manage-employees');

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role'  => ['required', 'exists:roles,name'],
        ]);

        $tempPassword = Str::password(10);

        $employee = Employee::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'password'  => $tempPassword, // يُجزّأ تلقائياً
            'is_active' => true,
        ]);

        $employee->assignRole($data['role']);

        return redirect()
            ->route('employees.index')
            ->with('success', "تم إنشاء الموظف وحسابه. كلمة المرور المؤقتة: {$tempPassword}");
    }

    public function edit(Employee $employee)
    {
        Gate::authorize('manage-employees');

        return view('employees.edit', [
            'employee' => $employee,
            'roles'    => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        Gate::authorize('manage-employees');

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', "unique:employees,email,{$employee->id}"],
            'phone'     => ['nullable', 'string', 'max:30'],
            'role'      => ['required', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'password'  => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        $employee->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // تغيير كلمة المرور من قِبل الأدمن (اختياري)
        if (! empty($data['password'])) {
            $employee->password = $data['password']; // يُجزّأ عبر الـ cast
            $employee->save();
        }

        $employee->syncRoles([$data['role']]);

        return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف.');
    }

    public function destroy(Employee $employee)
    {
        Gate::authorize('manage-employees');

        if ($employee->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف.');
    }
}
