<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        $users = $query->orderBy('name', 'asc')->paginate(15);
        $roles = Role::orderBy('name')->get();

        $permissionLabels = [
            'master.manage' => 'Mengelola Data Master (Kategori, Produk, Supplier, dll)',
            'purchasing.manage' => 'Mengelola Pembelian (Pesanan, Penerimaan, Retur)',
            'receiving.manage' => 'Penerimaan Barang',
            'stock.manage' => 'Mengelola Stok (Stok, Mutasi, Opname)',
            'stock.approve' => 'Menyetujui Opname Stok',
            'pos.access' => 'Mengakses POS / Kasir',
            'sales.view' => 'Melihat Riwayat Penjualan',
            'promotions.manage' => 'Mengelola Promosi',
            'members.view' => 'Melihat Data Member',
            'members.create' => 'Mendaftarkan Member Baru',
            'expenses.manage' => 'Mengelola Pengeluaran',
            'expenses.approve' => 'Menyetujui Pengeluaran',
            'reports.view' => 'Melihat Laporan',
            'reports.export' => 'Mengekspor Laporan (CSV / Print)',
            'settings.manage' => 'Mengelola Pengaturan (Profil Perusahaan, User)',
            'suppliers.manage' => 'Mengelola Supplier',
        ];

        return view('settings.users.index', compact('users', 'roles', 'permissionLabels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'image' => 'nullable|file|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        $user = User::create($data);

        app(ActivityLogger::class)->log('created', $user, "Membuat user {$user->name}");

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'image' => 'nullable|file|max:2048',
        ]);

        $data = $request->only('name', 'email', 'username', 'role_id', 'phone', 'address');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        $old = $user->toArray();
        $user->update($data);
        app(ActivityLogger::class)->log('updated', $user, "Mengupdate user {$user->name}", $old, $user->toArray());

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        app(ActivityLogger::class)->log('deleted', $user, "Menghapus user {$user->name}");
        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
