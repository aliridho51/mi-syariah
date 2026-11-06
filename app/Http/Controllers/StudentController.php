<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil data wali murid untuk dropdown pilihan di form Admin
        $walis = $user->role === 'admin'
            ? User::where('role', 'wali_murid')->orderBy('name')->get()
            : collect();

        if ($user->role === 'admin' || $user->role === 'kepala_madrasah') {
            // Admin & Kepala Madrasah bisa melihat semua siswa
            $students = Student::with('wali')->latest()->paginate(10);
        } else {
            // Wali murid hanya bisa melihat siswa miliknya sendiri
            $students = Student::where('user_id', $user->id)->latest()->paginate(10);
        }

        return view('students.index', compact('students', 'walis'));
    }

    // store, update, destroy dibatasi middleware role:admin

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['kategori'] = $data['kategori'] ?? 'Reguler';
        $data['potongan_persen'] = (int) ($data['potongan_persen'] ?? 0);

        Student::create($data);

        return redirect()->back()->with('success', 'Data murid berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $data = $request->validate($this->rules($student->id));
        $data['kategori'] = $data['kategori'] ?? 'Reguler';
        $data['potongan_persen'] = (int) ($data['potongan_persen'] ?? 0);

        $student->update($data);

        // Sinkronkan juga user_id pada tagihan siswa ini jika walinya diubah
        $student->payments()->update(['user_id' => $student->user_id]);

        return redirect()->back()->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // Lindungi riwayat keuangan: murid yang sudah punya tagihan tidak boleh dihapus
        if ($student->payments()->exists()) {
            return redirect()->back()->with('error', 'Murid ' . $student->nama . ' tidak dapat dihapus karena sudah memiliki riwayat tagihan/pembayaran.');
        }

        $student->delete();

        return redirect()->back()->with('success', 'Data murid berhasil dihapus.');
    }

    private function rules(?int $studentId = null): array
    {
        return [
            // Siswa hanya boleh dikaitkan ke akun ber-role wali_murid
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'wali_murid'),
            ],
            'nama' => 'required|string|max:255',
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($studentId)],
            'kelas' => 'required|string|max:20',
            'kategori' => 'nullable|in:Reguler,Yatim/Piatu,Dhuafa,Beasiswa Prestasi',
            'potongan_persen' => 'nullable|integer|min:0|max:100',
        ];
    }
}
