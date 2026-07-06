<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlatformController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-settings');
        $platforms = Platform::orderBy('name')->get();

        return view('platforms.index', compact('platforms'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-settings');
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:platforms,name'],
        ]);
        Platform::create($data);

        return back()->with('success', 'تمت إضافة المنصة.');
    }

    public function update(Request $request, Platform $platform)
    {
        Gate::authorize('manage-settings');
        $data = $request->validate([
            'name'      => ['required', 'string', "unique:platforms,name,{$platform->id}"],
            'is_active' => ['boolean'],
        ]);
        $platform->update($data);

        return back()->with('success', 'تم تحديث المنصة.');
    }

    public function destroy(Platform $platform)
    {
        Gate::authorize('manage-settings');
        $platform->delete();

        return back()->with('success', 'تم حذف المنصة.');
    }
}
