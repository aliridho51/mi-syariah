# 🕌 Sistem Informasi Pembayaran Syariah Madrasah Ibtidaiyah (MI)

Aplikasi web pengelolaan pembayaran pendidikan berbasis prinsip syariah (**SPP, Uang Gedung, Seragam, Buku, Kegiatan, dan Infaq Sukarela**) dengan mekanisme **Virtual Account (VA) BSI & QRIS Otomatis ala SIAKAD Universitas** serta integrasi **Midtrans Snap Payment Gateway**.

---

## ✨ Fitur Utama

### 1. Multi-Role Access Control (3 Role)
| Role | Hak Akses Utama |
| :--- | :--- |
| **Admin / Bendahara** | Mengelola Master Data (Tahun Ajaran & Jenis Pembayaran ber-akad syariah), Data Wali, Data Santri (termasuk keringanan Yatim/Dhuafa), menerbitkan tagihan (per siswa / per kelas / seluruh siswa), verifikasi transaksi, serta mencetak laporan. |
| **Kepala Madrasah** | Monitoring real-time pemasukan dana pokok vs dana amanah (infaq), rekapitulasi per akad & per kelas, memantau daftar tunggakan, serta mengunduh laporan Excel (CSV) / PDF. |
| **Wali Santri** | Melihat tagihan anak, melakukan pembayaran non-tunai melalui **Nomor Virtual Account (VA) BSI / QRIS** dengan opsi menambahkan **Infaq Sukarela**, serta mengunduh **Kwitansi Digital Resmi** yang terbit otomatis saat lunas. |

### 2. Prinsip Keuangan Syariah
- **Pencatatan Akad Jelas:** Setiap jenis pembayaran memiliki akad eksplisit (*Ijarah* untuk SPP/jasa pendidikan, *Wakaf* untuk sarana/uang gedung, *Murabahah/Jual Beli* untuk seragam & buku, serta *Hibah* untuk kegiatan/infaq).
- **Infaq 100% Sukarela:** Nominal infaq tidak ditentukan sepihak oleh admin, melainkan diisi sendiri secara sukarela oleh Wali Santri pada saat melakukan pembayaran.
- **Keringanan / Beasiswa Yatim & Dhuafa:** Santri dengan kategori *Yatim/Piatu*, *Dhuafa*, atau *Beasiswa Prestasi* mendapatkan potongan persentase SPP otomatis saat tagihan diterbitkan.
- **Tanpa Denda Riba:** Tidak menerapkan denda keterlambatan berbunga.

### 3. Pembayaran Non-Tunai Otomatis (Hybrid SIAKAD VA & Midtrans)
- **Generator Nomor Virtual Account (VA) BSI Otomatis** (`900733` + `NIS` + `ID Tagihan`) untuk setiap tagihan.
- **Verifikasi & Kwitansi Otomatis:** Begitu pembayaran dilakukan, status otomatis berubah menjadi **LUNAS**, sistem menerbitkan **Nomor Referensi Bank**, dan **Kwitansi Digital** langsung dapat dicetak tanpa perlu upload struk manual.
- **Dukungan Midtrans Snap & Webhook SHA-512:** Cukup isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di `.env` untuk mengaktifkan popup resmi Midtrans (BSI VA, BRI/BNI/Mandiri VA, QRIS).

---

## 🚀 Cara Instalasi & Menjalankan Proyek

```bash
# 1. Salin konfigurasi environment
cp .env.example .env

# 2. Sesuaikan nama database MySQL di .env (default: db_mi_syariah)
# Lalu jalankan migrasi dan seeder data contoh:
php artisan migrate --seed

# 3. Jalankan server lokal
php artisan serve
```

---

## 🔐 Akun Demo (Tersedia Tombol Login 1-Klik di Halaman `/login`)

| Role | Email | Password |
| :--- | :--- | :--- |
| **Bendahara / Admin** | `admin@mi.sch.id` | `password123` |
| **Kepala Madrasah** | `kepala@mi.sch.id` | `password123` |
| **Wali Santri** | `wali@mi.sch.id` | `password123` |
