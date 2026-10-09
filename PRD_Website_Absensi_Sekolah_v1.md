# Product Requirements Document (PRD) — Website Absensi Sekolah

**Fokus Fase 1: Sistem Absensi oleh Guru (Hadir, Izin, Sakit, Alfa)**
Versi 1.0 — 8 Oktober 2026 | Status: Draft untuk Review

---

## 1. Overview & Latar Belakang

Banyak sekolah masih mencatat kehadiran siswa secara manual — di buku absen kertas atau direkap ulang ke Excel/Sheets oleh guru. Cara ini menimbulkan tiga masalah utama:

- **Lambat:** rekap kehadiran per bulan/semester memakan waktu berjam-jam dan rawan salah hitung.
- **Tidak terdokumentasi rapi:** buku absen bisa hilang/rusak, dan riwayat per siswa sulit dilacak.
- **Tidak ada data terpusat:** kepala sekolah/wali kelas sulit memantau tingkat kehadiran (terutama siswa yang sering alfa) secara real-time.

Website absensi ini menggantikan proses manual tersebut. Pada fase 1, sistem hanya berfokus pada pencatatan kehadiran oleh guru melalui browser (web responsive, bisa dibuka dari HP maupun laptop), dengan status kehadiran: Hadir, Izin, Sakit, dan Alfa.

### 1.1 Ringkasan Produk

| Aspek | Keputusan |
|---|---|
| Konteks pengguna | Sekolah (siswa), single instansi |
| Pengguna aktif fase 1 | Guru (mencatat absensi) + Super Admin (mengelola akun guru) |
| Siswa | Tidak login — hanya tercatat sebagai data (nama + NIS) |
| Metode pencatatan | Manual oleh guru per sesi pertemuan |
| Platform | Web responsive (browser HP/laptop), tanpa aplikasi native |
| Status kehadiran | Hadir, Izin, Sakit, Alfa |
| Import data siswa | File Excel (.xlsx): kolom Nama + NIS |
| Struktur kelas | Satu kelas berisi banyak mata pelajaran; guru memilih mapel saat membuat sesi |
| Batasan akses | Guru hanya melihat & mengelola kelas miliknya sendiri |

---

## 2. Tujuan & Metrik Keberhasilan

### 2.1 Tujuan

1. Mendigitalkan pencatatan kehadiran sehingga setiap sesi tercatat rapi, terdokumentasi, dan bisa diaudit.
2. Memangkas waktu rekap kehadiran (per siswa, per kelas, per periode) dari hitungan jam menjadi hitungan detik.
3. Memberi visibilitas dini terhadap siswa bermasalah (sering alfa/izin) melalui rekap persentase kehadiran.
4. Menjamin integritas data: setiap perubahan absensi tercatat siapa yang mengubah dan kapan.

### 2.2 Metrik Keberhasilan (diukur 1 bulan setelah go-live)

| Metrik | Target | Cara Ukur |
|---|---|---|
| Waktu rekap 1 kelas / 1 bulan | ≤ 1 menit | Uji waktu ekspor laporan |
| Akurasi data vs absen kertas (uji paralel 2 minggu) | 100% cocok | Sampling perbandingan |
| Adopsi guru | ≥ 90% sesi tercatat di sistem | Rasio sesi sistem vs jadwal |
| Koreksi setelah finalisasi | < 5% sesi | Log audit |
| Uptime | ≥ 99% | Monitoring hosting |

---

## 3. Ruang Lingkup

### 3.1 In Scope (Fase 1 — Wajib)

- Autentikasi: login Super Admin & Guru (guru tidak bisa daftar sendiri; akun dibuat oleh Super Admin).
- Manajemen akun guru oleh Super Admin (buat, nonaktifkan, reset password).
- CRUD Kelas oleh guru: nama kelas, tingkat, tahun ajaran, daftar mata pelajaran di dalam kelas.
- Import siswa dari Excel (.xlsx): kolom Nama + NIS, dengan validasi duplikat dan format.
- Manajemen siswa per kelas: tambah/edit/hapus manual, hapus massal.
- Sesi/pertemuan: buat sesi per mapel (tanggal, jam, topik/materi), status Buka/Tutup (finalisasi).
- Input absensi per siswa per sesi: Hadir / Izin / Sakit / Alfa, simpan draft & finalisasi (lock).
- Koreksi absensi sebelum finalisasi; setelah finalisasi hanya bisa dibuka ulang (re-open) dengan catatan + audit log.
- Rekap & dashboard: ringkasan per kelas, per siswa, persentase kehadiran, daftar siswa sering alfa.
- Laporan & ekspor Excel (.xlsx) dan PDF per kelas/periode.
- Audit log: semua perubahan absensi tercatat (siapa, kapan, sebelum/sesudah).

### 3.2 Out of Scope (Ditahan Fase 2+)

- Portal/akun siswa & orang tua (fase 1 siswa tidak login).
- Pengajuan izin mandiri oleh siswa/orang tua.
- QR code, barcode, biometrik, geofence/GPS, foto selfie.
- Notifikasi WhatsApp/SMS/email otomatis.
- Modul nilai, pembayaran/SPP, jadwal otomatis, penggajian.
- Aplikasi mobile native (Android/iOS) — fase 1 cukup web responsive.
- Multi-sekolah / multi-tenant.

---

## 4. Role & Hak Akses

| Kemampuan | Super Admin | Guru |
|---|---|---|
| Login | Ya | Ya |
| Buat/nonaktifkan akun guru, reset password | Ya | Tidak |
| Buat & kelola kelas MILIKNYA sendiri | Tidak | Ya |
| Melihat kelas milik guru lain | Ya (semua, read-only + rekap) | Tidak |
| Import & kelola siswa di kelasnya | Tidak | Ya |
| Buat sesi & isi absensi | Tidak | Ya |
| Melihat rekap & ekspor laporan | Ya (semua kelas) | Ya (kelas sendiri) |
| Membuka ulang (re-open) sesi yang terkunci | Ya | Ya (dengan alasan, tercatat di audit) |

> **Catatan penting:** guru hanya per mata pelajaran/kelas yang menjadi miliknya. Guru A tidak bisa melihat, mengubah, atau mengekspor data kelas milik Guru B. Super Admin bersifat pengelola akun dan pemantau, bukan pengisi absensi.

---

## 5. Alur Bisnis Utama

### 5.1 Langkah 1 — Guru Login ke Sistem

1. Guru membuka URL website absensi di browser HP/laptop.
2. Guru memasukkan username/email + password yang dibuatkan Super Admin.
3. Sistem memvalidasi kredensial; jika salah 5x berturut-turut, akun dikunci sementara 15 menit.
4. Login berhasil → guru diarahkan ke Dashboard berisi daftar kelas miliknya + ringkasan (jumlah kelas, sesi bulan ini, siswa dengan alfa tertinggi).

### 5.2 Langkah 2 — Guru Membuat Kelas & Info Kelas Lainnya

1. Guru klik "Tambah Kelas", mengisi: Nama Kelas (mis. "XII IPA 1"), Tingkat, Tahun Ajaran, Wali Kelas (opsional), dan Daftar Mata Pelajaran yang dia ampu di kelas itu.
2. Sistem menyimpan kelas dengan status "Aktif"; kelas lama (tahun ajaran lalu) bisa diarsipkan agar tidak tercampur.
3. Guru dapat menambah/menghapus mapel di kelas kapan saja sebelum ada sesi final yang memakai mapel tersebut.

### 5.3 Langkah 3 — Guru Mengimport Nama Siswa & NIS

1. Di halaman Detail Kelas, guru klik "Import Siswa" dan mengunggah file Excel (.xlsx).
2. Format wajib: Kolom A = NIS, Kolom B = Nama Lengkap. Baris pertama adalah header. Template .xlsx disediakan via tombol "Unduh Template".
3. Sistem memvalidasi: (a) NIS wajib unik dalam satu kelas — duplikat ditolak per baris dengan pesan jelas (nomor baris + alasan); (b) nama tidak boleh kosong; (c) NIS hanya angka/huruf tanpa spasi berlebih.
4. Hasil import ditampilkan sebagai pratinjau: baris valid (hijau) vs baris gagal (merah + alasan). Guru konfirmasi → hanya baris valid yang disimpan.
5. Selain import, guru bisa tambah/edit/hapus siswa manual satu per satu.

### 5.4 Langkah 4 — Guru Membuat Sesi & Melakukan Absensi

1. Saat jam masuk kelas, guru membuka Detail Kelas → klik "Buat Sesi", memilih: Mata Pelajaran (dari daftar mapel kelas itu), Tanggal (default hari ini), Jam mulai–selesai, Topik/Materi (opsional).
2. Sistem membuat sesi berstatus "Buka (Draft)" dan menampilkan daftar seluruh siswa kelas dengan status default Hadir.
3. Guru menandai siswa yang Izin / Sakit / Alfa (radio button per siswa). Ada tombol "Tandai semua Hadir" dan filter "belum ditandai".
4. Guru klik "Simpan Draft" (bisa lanjut nanti) atau "Finalisasi" (sesi dikunci/terkunci).
5. Sesi yang sudah difinalisasi tidak bisa diubah; jika ada kesalahan, guru klik "Buka Ulang" → wajib isi alasan → sesi terbuka kembali dan seluruh kejadian tercatat di audit log.
6. Rekap otomatis terhitung: per siswa (% hadir/izin/sakit/alfa), per kelas, per mapel, per rentang tanggal; bisa diekspor ke Excel/PDF.

### 5.5 Diagram Alir Ringkas

```
Login Guru → Dashboard (daftar kelas) → [Buat Kelas + isi mapel]
→ Detail Kelas → [Import .xlsx (NIS + Nama)] → Daftar Siswa
→ [Buat Sesi (pilih mapel + tanggal)] → Form Absensi (Hadir/Izin/Sakit/Alfa)
→ Simpan Draft / Finalisasi (Lock) → Rekap & Ekspor Laporan
```

---

## 6. Functional Requirements

Prioritas memakai MoSCoW: **M** = Must (wajib MVP), **S** = Should (penting, boleh susulan v1.1), **C** = Could (nice-to-have), **W** = Won't (fase ini tidak).

### 6.1 FR-AUTH — Autentikasi & Akun

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-AUTH-01 | Login dengan email/username + password untuk Super Admin & Guru. | M |
| FR-AUTH-02 | Super Admin membuat akun guru (nama, email/username, password awal); guru tidak bisa registrasi sendiri. | M |
| FR-AUTH-03 | Guru wajib ganti password saat login pertama. | M |
| FR-AUTH-04 | Lupa password: reset via link/token oleh Super Admin (tanpa email otomatis di fase 1). | M |
| FR-AUTH-05 | Kunci akun sementara setelah 5x gagal login (15 menit) + captcha. | S |
| FR-AUTH-06 | Sesi login kedaluwarsa setelah 12 jam tidak aktif; tombol Logout di semua halaman. | M |

### 6.2 FR-KELAS — Manajemen Kelas & Mata Pelajaran

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-KLS-01 | Guru membuat kelas: nama kelas, tingkat, tahun ajaran, keterangan (opsional). | M |
| FR-KLS-02 | Satu kelas bisa punya banyak mapel; guru menambah/menghapus mapel per kelas. | M |
| FR-KLS-03 | Guru hanya melihat & mengelola kelas miliknya; kelas guru lain tidak tampil. | M |
| FR-KLS-04 | Arsipkan kelas (tahun ajaran lama disembunyikan dari dashboard, data tetap ada). | S |
| FR-KLS-05 | Hapus kelas hanya jika belum punya sesi final; jika sudah ada, wajib arsip (soft delete). | M |

### 6.3 FR-SISWA — Data Siswa & Import Excel

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-SW-01 | Unduh template .xlsx (kolom NIS, Nama). | M |
| FR-SW-02 | Import .xlsx: validasi NIS unik per kelas, nama wajib isi; pratinjau baris valid/gagal + alasan per baris. | M |
| FR-SW-03 | Batas import 500 baris per file; file lebih besar ditolak dengan pesan jelas. | S |
| FR-SW-04 | Tambah/edit/hapus siswa manual; NIS tidak boleh diubah jika siswa sudah punya absensi final (atau wajib konfirmasi + audit). | M |
| FR-SW-05 | Cari & urutkan siswa (berdasar nama/NIS). | S |

### 6.4 FR-SESI — Sesi Pertemuan

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-SS-01 | Buat sesi: pilih mapel (dari mapel kelas itu), tanggal (default hari ini), jam mulai–selesai, topik (opsional). | M |
| FR-SS-02 | Satu kelas boleh punya >1 sesi di tanggal yang sama selama mapelnya berbeda; sesi mapel sama di tanggal+jam sama dilarang (dicegah duplikat). | M |
| FR-SS-03 | Status sesi: Buka (Draft) → Final (Terkunci). Daftar sesi per kelas dengan filter tanggal/mapel/status. | M |
| FR-SS-04 | Hapus sesi berstatus Draft; sesi Final tidak bisa dihapus (hanya re-open koreksi). | M |

### 6.5 FR-ABS — Input Absensi

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-ABS-01 | Form absensi menampilkan semua siswa kelas; status default Hadir. | M |
| FR-ABS-02 | Guru menandai per siswa: Hadir / Izin / Sakit / Alfa (satu pilihan, wajib terisi semua). | M |
| FR-ABS-03 | Tombol "Tandai semua Hadir" + penghitung "X dari N sudah ditandai". | M |
| FR-ABS-04 | Simpan Draft kapan saja; Finalisasi mengunci sesi (validasi: semua siswa sudah bertanda). | M |
| FR-ABS-05 | Buka Ulang sesi Final wajib isi alasan; peristiwa tercatat di audit log. | M |
| FR-ABS-06 | Siswa yang ditambahkan ke kelas SETELAH sesi difinalisasi tidak mengubah sesi lama (snapshot daftar saat sesi dibuat). | S |

### 6.6 FR-REKAP — Rekap & Dashboard

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-RKP-01 | Dashboard guru: jumlah kelas, sesi bulan berjalan, 5 siswa alfa terbanyak. | M |
| FR-RKP-02 | Rekap per siswa: jumlah + % Hadir/Izin/Sakit/Alfa dalam rentang tanggal & mapel. | M |
| FR-RKP-03 | Rekap per kelas & per sesi (tabel + ringkasan persentase). | M |
| FR-RKP-04 | Filter periode (minggu/bulan/semester/custom) dan filter mapel. | M |
| FR-RKP-05 | Ambang peringatan: siswa dengan alfa ≥ 3 dalam 30 hari ditandai merah. | S |

### 6.7 FR-LAP — Laporan & Ekspor

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-LAP-01 | Ekspor rekap ke Excel (.xlsx): satu sheet ringkasan + satu sheet detail per sesi. | M |
| FR-LAP-02 | Ekspor ke PDF siap cetak (kop sekolah, tanda tangan wali kelas & guru). | M |
| FR-LAP-03 | Nama file otomatis: Absensi_[NamaKelas]_[Periode].xlsx/pdf. | C |

### 6.8 FR-ADM — Super Admin

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-ADM-01 | Kelola akun guru: buat, nonaktifkan/aktifkan, reset password, lihat daftar guru + jumlah kelasnya. | M |
| FR-ADM-02 | Lihat rekap semua kelas (read-only) + ekspor. | M |
| FR-ADM-03 | Lihat audit log global (filter guru/kelas/tanggal). | S |

### 6.9 FR-AUD — Audit Log

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-AUD-01 | Setiap finalisasi, buka-ulang, dan perubahan absensi mencatat: siapa, kapan, sesi apa, nilai sebelum→sesudah, alasan. | M |
| FR-AUD-02 | Riwayat perubahan bisa dilihat per sesi oleh pemilik kelas. | M |

---

## 7. Aturan Bisnis Detail

1. **BR-01 Status wajib tunggal:** setiap siswa dalam satu sesi tepat memiliki satu status (Hadir/Izin/Sakit/Alfa). Tidak ada status kosong saat finalisasi.
2. **BR-02 Default Hadir:** form dibuka dengan semua siswa bertanda Hadir untuk mempercepat pengisian; guru hanya mengubah yang tidak hadir.
3. **BR-03 Alfa = tanpa keterangan:** status Alfa hanya diisi guru saat siswa tidak hadir tanpa kabar. Sistem tidak mengubah status otomatis.
4. **BR-04 Lock finalisasi:** sesi Final tidak bisa diubah kecuali lewat Buka Ulang + alasan; alasan minimal 10 karakter.
5. **BR-05 Duplikat sesi:** kombinasi (kelas, mapel, tanggal, jam mulai) harus unik; sistem menolak duplikat dengan pesan jelas.
6. **BR-06 Snapshot peserta:** daftar siswa pada sesi mengacu pada komposisi kelas saat sesi dibuat; siswa baru tidak retroaktif mengubah sesi lama.
7. **BR-07 NIS unik per kelas:** NIS boleh sama antar kelas berbeda, tapi tidak boleh ganda dalam satu kelas.
8. **BR-08 Ambang peringatan:** alfa ≥ 3 dalam 30 hari berjalan → nama siswa ditandai merah di rekap (bisa dikonfigurasi admin).
9. **BR-09 Arsip vs hapus:** kelas/siswa dengan riwayat sesi Final tidak boleh dihapus fisik — hanya arsip/nonaktif (soft delete) agar laporan historis tetap valid.

---

## 8. Data Model (Konseptual)

| Entitas | Atribut Kunci | Relasi |
|---|---|---|
| users | id, nama, email/username, password_hash, role (super_admin/guru), aktif | 1 guru → N kelas |
| kelas | id, owner_guru_id, nama, tingkat, tahun_ajaran, status (aktif/arsip) | 1 kelas → N mapel, N siswa, N sesi |
| mapel | id, kelas_id, nama_mapel | 1 mapel → N sesi |
| siswa | id, kelas_id, nis, nama, aktif | 1 siswa → N baris absensi |
| sesi | id, kelas_id, mapel_id, tanggal, jam_mulai, jam_selesai, topik, status (draft/final), created_by | 1 sesi → N absensi (satu per siswa) |
| absensi | id, sesi_id, siswa_id, status (H/S/I/A), updated_by, updated_at | Unik per (sesi_id, siswa_id) |
| audit_log | id, waktu, actor_id, aksi, sesi_id, detail_sebelum, detail_sesudah, alasan | — |

**Constraint penting:** `UNIQUE(sesi_id, siswa_id)` di tabel absensi; `UNIQUE(kelas_id, nis)` di tabel siswa; `UNIQUE(kelas_id, mapel_id, tanggal, jam_mulai)` di tabel sesi.

---

## 9. API Inti (Usulan)

REST JSON dengan autentikasi token (mis. JWT). Endpoint utama fase 1:

| Method & Path | Fungsi |
|---|---|
| `POST /api/auth/login` | Login guru / super admin |
| `POST /api/auth/change-password` | Ganti password (wajib saat login pertama) |
| `GET/POST /api/kelas` | Daftar kelas milik guru / buat kelas |
| `GET/PUT/DELETE /api/kelas/:id` | Detail, ubah, arsip kelas |
| `GET/POST /api/kelas/:id/mapel` | Daftar & tambah mapel |
| `GET /api/kelas/:id/siswa/template` | Unduh template .xlsx |
| `POST /api/kelas/:id/siswa/import` | Upload .xlsx + pratinjau validasi |
| `GET/POST /api/kelas/:id/siswa` | Daftar & tambah manual |
| `GET/POST /api/kelas/:id/sesi` | Daftar & buat sesi |
| `GET /api/sesi/:id` | Detail sesi + daftar absensi |
| `PUT /api/sesi/:id/absensi` | Simpan draft absensi (bulk per siswa) |
| `POST /api/sesi/:id/finalisasi` | Kunci sesi |
| `POST /api/sesi/:id/reopen` | Buka ulang + alasan |
| `GET /api/kelas/:id/rekap?dari&sampai&mapel` | Rekap + persentase |
| `GET /api/kelas/:id/laporan.xlsx` / `.pdf` | Ekspor laporan |
| `GET/POST /api/admin/guru` | Kelola akun guru (super admin) |

---

## 10. UI/UX & Deskripsi Halaman

| Halaman | Isi & Perilaku |
|---|---|
| Login | Form email/username + password; pesan error jelas; link "minta reset ke admin". |
| Dashboard Guru | Kartu statistik (jumlah kelas, sesi bulan ini), daftar kelas miliknya, tabel "perhatian": 5 siswa alfa terbanyak. |
| Daftar/Tambah Kelas | Form nama, tingkat, tahun ajaran, daftar mapel (tambah dinamis); tombol Arsip. |
| Detail Kelas | Tab: Siswa \| Sesi \| Rekap. Info kelas di atas; tombol Import, Buat Sesi, Ekspor. |
| Import Siswa | Upload .xlsx → tabel pratinjau valid/gagal → Konfirmasi simpan. |
| Form Sesi + Absensi | Pilih mapel/tanggal/jam/topik; daftar siswa dengan radio H/I/S/A; penghitung progres; Simpan Draft / Finalisasi. |
| Rekap | Filter periode + mapel; tabel per siswa (H/I/S/A + %); baris merah jika alfa ≥ ambang; tombol Excel/PDF. |
| Audit per Sesi | Timeline perubahan: waktu, pelaku, sebelum→sesudah, alasan. |
| Admin: Kelola Guru | Tabel guru + status; buat akun, reset password, nonaktifkan; lihat rekap semua kelas. |

*Prinsip UX: mobile-first (form absensi enak dipakai dari HP di dalam kelas), maksimal 3 klik dari dashboard ke form absensi, semua aksi destruktif memakai dialog konfirmasi, dan setiap tabel besar punya pencarian + pagination.*

---

## 11. Non-Functional Requirements

| Kategori | Kebutuhan |
|---|---|
| Keamanan | Password di-hash (bcrypt/argon2); token sesi kedaluwarsa 12 jam; proteksi CSRF/XSS; rate-limit login; hak akses dicek di server per request (guru tidak bisa intip kelas orang lain via URL). |
| Privasi (UU PDP) | Data siswa (nama + NIS) hanya terlihat oleh guru pemilik kelas & super admin; tidak ada data yang dibagikan ke pihak ketiga; hak hapus data lulusan/pindah. |
| Performa | Form absensi ≤ 2 detik untuk 40 siswa; rekap 1 semester ≤ 5 detik; import 500 baris ≤ 10 detik. |
| Ketersediaan | Target 99% (hosting cloud); backup database harian otomatis; RPO ≤ 24 jam. |
| Kompatibilitas | Chrome/Edge/Safari/Firefox versi 2 tahun terakhir; layar HP ≥ 360px hingga desktop. |
| Kegunaan | Guru baru bisa mencatat sesi pertama tanpa pelatihan (>80% berhasil dalam uji coba 15 menit). |
| Auditabilitas | Audit log tidak bisa dihapus/diubah oleh siapa pun termasuk super admin (append-only). |

---

## 12. Arsitektur & Tech Stack (Usulan)

Usulan awal — keputusan final menunggu tim development:

- **Frontend:** Web responsive — React/Vue atau Blade/Livewire (jika backend Laravel). Mobile-first CSS.
- **Backend:** Laravel (PHP) atau Express/NestJS (Node.js) — REST API JSON + auth token.
- **Database:** PostgreSQL/MySQL dengan constraint unik sesuai §8.
- **File/ekspor:** library Excel (PhpSpreadsheet / SheetJS / exceljs) dan PDF (Dompdf/TCPDF atau Puppeteer).
- **Hosting:** VPS/cloud sesuai budget sekolah; HTTPS wajib; backup harian.
- **Lingkungan:** staging (uji) terpisah dari production.

---

## 13. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Guru salah menandai (tertukar siswa) | Data salah | Default Hadir + pencarian nama; koreksi mudah sebelum final; re-open beralasan setelah final. |
| Guru lupa finalisasi (sesi menggantung draft) | Rekap tidak akurat | Badge "Draft" mencolok + pengingat sesi belum final di dashboard. |
| File import berantakan (kolom tidak sesuai) | Import gagal | Template resmi + pratinjau validasi per baris + pesan error nomor baris. |
| Akun guru disalahgunakan | Kebocoran data | Akun per guru, wajib ganti password awal, lockout brute-force, audit log. |
| HP guru offline di kelas | Tidak bisa absen | Fase 1 butuh koneksi; dokumentasikan; fase 2 pertimbangkan mode offline/PWA. |
| Sengketa nilai kehadiran | Komplain | Audit log append-only sebagai bukti perubahan. |

---

## 14. Roadmap Bertahap

| Fase | Isi | Estimasi |
|---|---|---|
| MVP (dokumen ini) | Auth, kelas+mapel, import .xlsx, sesi, absensi H/I/S/A + lock, rekap, ekspor Excel/PDF, audit log. | 6–8 minggu (1–2 dev) |
| v1.1 | Lockout+captcha login, arsip massal, ambang peringatan configurable, nama file otomatis, grafik tren kehadiran. | +2–3 minggu |
| Fase 2 | Portal siswa/orang tua (lihat rekap), pengajuan izin mandiri + approval guru, notifikasi WA, QR sesi, mode offline/PWA. | Terpisah, PRD baru |

---

## 15. Kriteria Penerimaan (Acceptance Criteria)

1. **AC-01:** Super Admin bisa membuat akun guru; guru login pertama wajib ganti password; guru tidak menemukan halaman registrasi mandiri.
2. **AC-02:** Guru membuat kelas + ≥2 mapel, lalu kelas muncul di dashboard-nya dan TIDAK muncul di dashboard guru lain.
3. **AC-03:** Import file .xlsx 40 siswa (nama+NIS) lolos validasi; file berisi 2 NIS duplikat menampilkan 2 baris error bernomor baris dan hanya 38 tersimpan.
4. **AC-04:** Guru membuat sesi mapel X tanggal hari ini; duplikat (mapel+tanggal+jam sama) ditolak dengan pesan jelas.
5. **AC-05:** Form absensi terbuka dengan semua default Hadir; guru mengubah 3 siswa (Izin/Sakit/Alfa) → Simpan Draft → data bertahan setelah refresh.
6. **AC-06:** Finalisasi sesi mengunci form; mengubah sesi Final tanpa Buka Ulang mustahil.
7. **AC-07:** Buka Ulang tanpa alasan ≥10 karakter ditolak; alasan + perubahan tercatat di audit.
8. **AC-08:** Rekap 1 bulan menampilkan angka & % yang cocok dengan hitungan manual; siswa alfa ≥3/30 hari bertanda merah.
9. **AC-09:** Ekspor Excel berisi sheet ringkasan + detail; PDF memuat kop, tabel rekap, dan kolom tanda tangan.
10. **AC-10:** Semua endpoint menolak akses guru ke kelas milik guru lain (uji IDOR, ekspektasi 403).

---

## 16. Pertanyaan Terbuka

1. Apakah satu guru bisa mengajar mapel yang sama di banyak kelas paralel (mis. Matematika di XII IPA 1–3)? → asumsi PRD: ya, guru membuat tiap kelas terpisah. Konfirmasi.
2. Siapa yang mengisi kop sekolah & nama kepala sekolah di PDF? (input super admin di Pengaturan?) — belum ada FR Pengaturan; perlu ditambah?
3. Apakah NIS mengikuti format khusus sekolah (panjang tetap/awalan angkatan)? Jika ya, sebutkan polanya untuk validasi.
4. Perlu batas "sesi hanya bisa dibuat untuk tanggal ≤ hari ini" (tidak boleh sesi masa depan), atau sesi masa depan diizinkan sebagai rencana?
5. Apakah wali kelas (yang bukan pengampu mapel) perlu akses read-only rekap kelasnya? Saat ini hanya pemilik kelas + super admin.
6. Berapa jumlah maksimal siswa per kelas dan total guru pengguna (untuk sizing server)?

---

*— Akhir PRD v1.0 —*
