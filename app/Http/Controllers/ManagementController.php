<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function index()
    {
        return view('management.index', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => ['admin', 'manager', 'storekeeper', 'accountant', 'worker'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,manager,storekeeper,accountant,worker'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function toggleActive(User $user, Request $request): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'لا يمكن تعطيل المستخدم الحالي.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active ? 'تم تفعيل المستخدم.' : 'تم تعطيل المستخدم.');
    }
}
