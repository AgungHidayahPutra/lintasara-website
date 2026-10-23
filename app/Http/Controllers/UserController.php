<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Daftar pengguna.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $status = (string) $request->query('status', '');

        if (! in_array($role, ['admin', 'penulis'], true)) {
            $role = '';
        }

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        $users = User::query()
            ->withCount('articles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,

            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'writers' => User::where('role', 'penulis')->count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],

            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],

            'currentUserId' => $request->user()->id,
        ]);
    }

    /**
     * Form tambah pengguna.
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/Create');
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                Rule::in(['admin', 'penulis']),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Form edit pengguna.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],

            'articlesCount' => $user->articles()->count(),

            'isCurrentUser' => $user->id === Auth::id(),
        ]);
    }

    /**
     * Perbarui pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                Rule::in(['admin', 'penulis']),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $user, $validated) {
            $target = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $newRole = $validated['role'];
            $newActive = (bool) $validated['is_active'];

            /*
             * Admin tidak boleh menurunkan role sendiri
             * atau menonaktifkan akun sendiri.
             */
            if ($target->id === $request->user()->id) {
                if ($newRole !== 'admin' || ! $newActive) {
                    return back()->with(
                        'error',
                        'Anda tidak dapat menurunkan role atau menonaktifkan akun sendiri.'
                    );
                }
            }

            /*
             * Pastikan administrator aktif terakhir
             * tidak kehilangan akses.
             */
            if (
                $target->role === 'admin' &&
                $target->is_active &&
                ($newRole !== 'admin' || ! $newActive)
            ) {
                $activeAdminCount = User::query()
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->count();

                if ($activeAdminCount <= 1) {
                    return back()->with(
                        'error',
                        'Administrator aktif terakhir tidak dapat dinonaktifkan atau diubah rolenya.'
                    );
                }
            }

            /*
             * Jika email berubah, verifikasi email
             * harus dilakukan kembali.
             */
            if ($target->email !== $validated['email']) {
                $target->email_verified_at = null;
            }

            $target->name = $validated['name'];
            $target->email = $validated['email'];
            $target->role = $newRole;
            $target->is_active = $newActive;

            /*
             * Password kosong berarti tidak diubah.
             * Model User sudah menggunakan cast 'hashed'.
             */
            if (! empty($validated['password'])) {
                $target->password = $validated['password'];
            }

            $target->save();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Pengguna berhasil diperbarui.');
        });
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        return DB::transaction(function () use ($request, $user) {
            $target = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            /*
             * Akun sendiri tidak boleh dihapus.
             */
            if ($target->id === $request->user()->id) {
                return back()->with(
                    'error',
                    'Anda tidak dapat menghapus akun sendiri.'
                );
            }

            /*
             * Pengguna yang memiliki artikel
             * tidak boleh dihapus.
             */
            if ($target->articles()->exists()) {
                return back()->with(
                    'error',
                    'Pengguna tidak dapat dihapus karena masih memiliki artikel. Nonaktifkan akun sebagai alternatif.'
                );
            }

            /*
             * Administrator aktif terakhir
             * tidak boleh dihapus.
             */
            if ($target->role === 'admin' && $target->is_active) {
                $activeAdminCount = User::query()
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->count();

                if ($activeAdminCount <= 1) {
                    return back()->with(
                        'error',
                        'Administrator aktif terakhir tidak dapat dihapus.'
                    );
                }
            }

            $target->delete();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        });
    }
}
