<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->loadCount(['contracts', 'licenses']);

        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', "unique:employees,email,{$user->id}"],
            'phone'    => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        if (! empty($data['password'])) {
            $user->password = $data['password']; // يُجزّأ عبر الـ cast
        }
        $user->save();

        return redirect()->route('profile.show')->with('success', 'تم تحديث بياناتك.');
    }
}
