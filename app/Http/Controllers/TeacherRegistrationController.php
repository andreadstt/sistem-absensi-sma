<?php

namespace App\Http\Controllers;

use App\Models\TeacherRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Password;

class TeacherRegistrationController extends Controller
{
    /**
     * Show the registration form
     */
    public function create()
    {
        return Inertia::render('Auth/TeacherRegister');
    }

    /**
     * Handle registration submission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:teacher_registrations,email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::min(8)],
            'nip' => 'nullable|string|max:255|unique:teachers,nip',
            'phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ], [
            'email.unique' => 'Email sudah terdaftar atau sedang dalam proses verifikasi.',
            'nip.unique' => 'NIP sudah terdaftar di sistem.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        TeacherRegistration::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'nip' => $request->nip,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.register.create')->with(
            'success',
            'Pendaftaran berhasil. Akun Anda akan diverifikasi oleh administrator. Setelah disetujui, Anda bisa login dengan password yang telah Anda buat.'
        );
    }
}
