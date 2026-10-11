<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['role', 'group']);

        return spa('Profile/Index', [
            'profile' => $this->profileData($user),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'current_password.required' => __('auth.current_password_required'),
            'password.required' => __('auth.password_required'),
            'password.confirmed' => __('auth.password_confirmed'),
            'password.min' => __('auth.password_min', ['min' => 4]),
        ]);

        $userId = auth()->id();
        $user = User::findOrFail($userId);

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('auth.current_password_incorrect'),
            ]);
        }

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password_must_differ'),
            ]);
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'password' => Hash::make($request->password),
                'must_change_password' => 0,
                'updated_at' => now(),
            ]);

        return redirect($this->profilePath($request))
            ->with('success', __('auth.password_changed'));
    }

    protected function profileData(User $user): array
    {
        return [
            'full_name' => $user->full_name,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role?->name,
            'group' => $user->group?->name,
        ];
    }

    /**
     * Вернуть на ту же страницу профиля, с которой отправили форму.
     */
    protected function profilePath(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');

        if (str_starts_with($path, '/api/')) {
            $path = substr($path, 4) ?: '/';
        } elseif ($path === '/api') {
            $path = '/';
        }

        if (str_ends_with($path, '/password')) {
            $path = substr($path, 0, -strlen('/password'));
        }

        return $path !== '' ? $path : '/student/profile';
    }
}
