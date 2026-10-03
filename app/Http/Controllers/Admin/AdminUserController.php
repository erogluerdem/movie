<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::withCount(['watchHistories', 'contentRequests', 'watchlists']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:user,admin'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'User updated successfully!');
    }

    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        AuditLog::log('toggle_user_role', "Updated role of user {$user->name} ({$user->email}) to {$newRole}", $user);

        return back()->with('success', "User role updated to {$newRole}!");
    }

    public function toggleBan(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Kendi hesabınızı yasaklayamazsınız.');
        }

        if ($user->is_banned) {
            $user->update([
                'is_banned' => false,
                'banned_reason' => null,
            ]);

            AuditLog::log('unban_user', "Unbanned user {$user->name} ({$user->email})", $user);

            return back()->with('success', "Kullanıcı ({$user->name}) yasağı kaldırıldı.");
        }

        $reason = $request->input('reason', 'Yönetici tarafından platform kuralları ihlali nedeniyle erişim engellendi.');
        $user->update([
            'is_banned' => true,
            'banned_reason' => $reason,
        ]);

        AuditLog::log('ban_user', "Banned user {$user->name} ({$user->email}): {$reason}", $user);

        return back()->with('success', "Kullanıcı ({$user->name}) askıya alındı.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account from the admin panel.');
        }

        $user->delete();

        return back()->with('success', 'User account deleted.');
    }
}
