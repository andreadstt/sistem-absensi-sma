<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the teacher's profile page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        $avatarUrl = $teacher->avatar ? Storage::url($teacher->avatar) : null;

        return Inertia::render('Guru/Profile', [
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'phone' => $teacher->phone,
                'avatar' => $avatarUrl,
            ],
            'user' => [
                'email' => $user->email,
            ],
            'mustChangePassword' => $user->must_change_password,
        ]);
    }

    /**
     * Update teacher's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'Teacher profile not found.');
        }

        // Validate image
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB in KB
        ], [
            'avatar.required' => 'Silakan pilih file gambar.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus: JPEG, PNG, JPG, atau GIF.',
            'avatar.max' => 'Ukuran gambar tidak boleh lebih dari 5MB.',
        ]);

        // Delete old avatar if exists
        if ($teacher->avatar && Storage::exists($teacher->avatar)) {
            Storage::delete($teacher->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars/teachers', 'public');

        // Update teacher record
        $teacher->update(['avatar' => $path]);

        return back()->with('success', 'Avatar berhasil diperbarui.');
    }

    /**
     * Update teacher's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        // Validate profile data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:20|unique:teachers,nip,' . $teacher->id,
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama guru wajib diisi.',
            'name.max' => 'Nama guru tidak boleh lebih dari 255 karakter.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.max' => 'NIP tidak boleh lebih dari 20 karakter.',
            'nip.unique' => 'NIP ini sudah terdaftar di sistem.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
        ]);

        // Update teacher record
        $teacher->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
