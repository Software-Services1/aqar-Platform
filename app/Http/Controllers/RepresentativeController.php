<?php

namespace App\Http\Controllers;

use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RepresentativeController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-representatives');
        $representatives = Representative::withCount('contracts')->orderBy('name')->get();

        return view('representatives.index', compact('representatives'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-representatives');
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        Representative::create($data + ['is_active' => true]);

        return back()->with('success', 'تمت إضافة المندوب.');
    }

    public function update(Request $request, Representative $representative)
    {
        Gate::authorize('manage-representatives');
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);
        $representative->update($data);

        return back()->with('success', 'تم تحديث بيانات المندوب.');
    }

    public function destroy(Representative $representative)
    {
        Gate::authorize('manage-representatives');
        $representative->delete();

        return back()->with('success', 'تم حذف المندوب.');
    }
}
