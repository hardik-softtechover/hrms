<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:120'],
            'email'         => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'designation'   => ['nullable', 'string', 'max:80'],
            'department'    => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'date'],
            'joined_on'     => ['nullable', 'date'],
            'address'       => ['nullable', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'max:2048'],
            'password'      => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if ($file = $request->file('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $file->store('avatars', 'public');
        }

        $user->fill([
            'name'          => $data['name'],
            'email'         => $data['email']         ?? null,
            'phone'         => $data['phone']         ?? null,
            'designation'   => $data['designation']   ?? null,
            'department'    => $data['department']    ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'joined_on'     => $data['joined_on']     ?? null,
            'address'       => $data['address']       ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated.');
    }
}
