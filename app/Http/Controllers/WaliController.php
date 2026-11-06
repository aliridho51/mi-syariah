<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class WaliController extends Controller
{
    // index: Admin (kelola) & Kepala Madrasah (lihat saja) - dibatasi middleware role
    public function index(Request $request)
    {
        $walis = User::where('role', 'wali_murid')->withCount('students')->latest()->paginate(10);
        return view('walis.index', compact('walis'));
    }

    // store, update, destroy dibatasi middleware role:admin

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'wali_murid',
        ]);

        return redirect()->back()->with('success', 'Wali murid baru berhasil didaftarkan.');
    }

    public function update(Request $request, $id)
    {
        // Hanya akun ber-role wali_murid yang boleh diubah dari menu ini
        $user = User::where('role', 'wali_murid')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Data wali murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Hanya akun ber-role wali_murid yang boleh dihapus (admin & kepala aman)
        $user = User::where('role', 'wali_murid')->findOrFail($id);

        // Lindungi data: wali yang masih punya anak/tagihan tidak boleh dihapus
        if ($user->students()->exists() || $user->payments()->exists()) {
            return redirect()->back()->with('error', 'Wali ' . $user->name . ' tidak dapat dihapus karena masih memiliki data murid atau riwayat tagihan. Pindahkan/hapus data murid terlebih dahulu.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Akun wali murid berhasil dihapus.');
    }
}
