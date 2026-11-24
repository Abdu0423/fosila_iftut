<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'avatar.required' => 'Выберите фото',
            'avatar.image' => 'Файл должен быть изображением',
            'avatar.mimes' => 'Допустимы JPG, PNG, WEBP и GIF',
            'avatar.max' => 'Фото не должно быть больше 2 МБ',
        ]);

        $user = User::findOrFail(auth()->id());
        $this->deleteAvatarFile($user);

        $file = $request->file('avatar');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        $user->avatar = $file->storeAs('avatars', $user->id.'_'.time().'.'.$extension, 'public');
        $user->save();

        return redirect($this->profilePath($request))
            ->with('success', 'Фото обновлено');
    }

    public function deleteAvatar(Request $request)
    {
        $user = User::findOrFail(auth()->id());
        $this->deleteAvatarFile($user);
        $user->avatar = null;
        $user->save();

        return redirect($this->profilePath($request))
            ->with('success', 'Фото удалено');
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
            'avatar' => $user->avatarUrl(),
        ];
    }

    protected function deleteAvatarFile(User $user): void
    {
        if (! $user->avatar || str_contains($user->avatar, '..') || ! str_starts_with($user->avatar, 'avatars/')) {
            return;
        }

        Storage::disk('public')->delete($user->avatar);
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

        foreach (['/password', '/avatar'] as $suffix) {
            if (str_ends_with($path, $suffix)) {
                $path = substr($path, 0, -strlen($suffix));
            }
        }

        return $path !== '' ? $path : '/student/profile';
    }
}
