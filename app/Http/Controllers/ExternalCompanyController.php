<?php

namespace App\Http\Controllers;

use App\Models\ExternalCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExternalCompanyController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-external-companies');
        $companies = ExternalCompany::withCount('contracts')->orderBy('name')->get();

        return view('external-companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-external-companies');
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
        ]);
        ExternalCompany::create($data + ['is_active' => true]);

        return back()->with('success', 'تمت إضافة الشركة الخارجية.');
    }

    public function update(Request $request, ExternalCompany $externalCompany)
    {
        Gate::authorize('manage-external-companies');
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'is_active'      => ['boolean'],
        ]);
        $externalCompany->update($data);

        return back()->with('success', 'تم تحديث بيانات الشركة.');
    }

    public function destroy(ExternalCompany $externalCompany)
    {
        Gate::authorize('manage-external-companies');
        $externalCompany->delete();

        return back()->with('success', 'تم حذف الشركة.');
    }
}
