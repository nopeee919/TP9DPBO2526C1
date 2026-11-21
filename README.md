# MVP Kandidat & Lowongan

Aplikasi CRUD PHP (PDO + MySQL) dengan arsitektur **Model–View–Presenter (MVP)** edisi **Strict Passive View**. Terdapat dua modul:

- **Kandidat** (`index.php`) — tabel `kandidat`
- **Lowongan** (`lowongan.php`) — tabel `lowongan` (modul baru bertema rekrutmen/HR)

---

## 1. Desain Program

### Arsitektur

```
 index.php / lowongan.php  (merakit objek Model, View, Presenter)
            │
            ▼
      ┌───────────┐   KontrakModel    ┌─────────┐
      │ PRESENTER │ ────────────────► │  MODEL  │ ──► MySQL (prepared statement)
      │           │ ◄──────────────── │         │
      └─────┬─────┘   entity          └─────────┘
            │ KontrakView (string HTML, angka, array)
            ▼
      ┌───────────┐
      │ VIEW      │ ──► template/*.html (ganti token {{...}})
      └───────────┘
```

| Lapisan | Tanggung jawab |
|---------|----------------|
| **Model** | Menjalankan query ke database dan memetakan hasilnya menjadi objek entity (`Kandidat`, `Lowongan`). |
| **View** | Pasif. Hanya memuat template dan mengganti token dengan data siap pakai dari Presenter. |
| **Presenter** | Penghubung. Mengambil data dari Model, menghitung total, melakukan perulangan untuk merakit baris tabel HTML, lalu menyerahkannya ke View. Juga menjalankan aksi tambah/ubah/hapus lalu redirect. |

### Kontrak (Interface)

Semua komunikasi antar-lapisan lewat interface, sehingga Presenter tidak bergantung pada kelas konkret.

| Modul | Model | View | Presenter |
|-------|-------|------|-----------|
| Kandidat | `KontrakModel` | `KontrakView` | `KontrakPresenter` |
| Lowongan | `KontrakModelLowongan` | `KontrakViewLowongan` | `KontrakPresenterLowongan` |

### Prinsip Strict Passive View

- `views/` tidak melakukan `require` ke Model/Entity.
- Tidak ada `foreach` dan `count()` di `views/`. Semuanya dikerjakan Presenter.
- View hanya menerima tipe sederhana: string HTML baris tabel, angka total, dan array data form.

### Struktur Direktori

```
MVP_Kandidat/
├── index.php                 Entry point Kandidat
├── lowongan.php              Entry point Lowongan
├── mvp_db.sql
├── README.md
├── models/       DB.php, Kandidat.php, KontrakModel.php, TabelKandidat.php,
│                 Lowongan.php, KontrakModelLowongan.php, TabelLowongan.php
├── presenters/   KontrakPresenter.php, PresenterKandidat.php,
│                 KontrakPresenterLowongan.php, PresenterLowongan.php
├── views/        KontrakView.php, ViewKandidat.php,
│                 KontrakViewLowongan.php, ViewLowongan.php
└── template/     skin.html, form.html, skin_lowongan.html, form_lowongan.html
```

### Database

| Tabel | Kolom |
|-------|-------|
| `kandidat` | `id`, `nama`, `posisi`, `domisili`, `skorTes`, `pengalamanTahun` |
| `lowongan` | `id`, `judul`, `departemen`, `lokasi`, `kuota`, `status` (`Dibuka`/`Ditutup`) |

---

## 2. Alur Program

### Alur umum

1. Browser membuka `index.php` (Kandidat) atau `lowongan.php` (Lowongan).
2. Entry point membuat **Model** (koneksi PDO), **View**, lalu **Presenter** dengan menyuntikkan Model dan View.
3. Request **GET** menampilkan layar sesuai `?screen=`, request **POST** menjalankan aksi sesuai `action`.

| URL | Layar |
|-----|-------|
| `index.php` / `lowongan.php` | Daftar data |
| `...?screen=add` | Form tambah |
| `...?screen=edit&id=N` | Form ubah (data terisi) |

### Read (menampilkan daftar)

1. Presenter memanggil `getAllKandidat()` / `getAllLowongan()` pada Model.
2. Model menjalankan `SELECT` dan mengubah tiap baris menjadi objek entity.
3. Presenter menghitung total, melakukan `foreach` untuk merakit baris `<tr>`, lalu memanggil `renderDaftar($rowsHtml, $total)`.
4. View mengisi `{{TABLE_ROWS}}` dan `{{TOTAL_DATA}}` pada template, lalu halaman ditampilkan.

### Create (tambah)

1. Klik **+ Tambah** → `?screen=add` → form kosong ditampilkan (`action = tambah`).
2. Klik **Simpan** → POST `action=tambah`.
3. Entry point membersihkan input lalu memanggil `tambahKandidat(...)` / `tambahLowongan(...)`.
4. Presenter memanggil Model (`INSERT` dengan prepared statement), lalu redirect ke daftar.

### Update (ubah)

1. Klik **Edit** → `?screen=edit&id=N`.
2. Presenter mengambil data lewat `getKandidatById(N)` / `getLowonganById(N)` (jika tidak ada, redirect ke daftar), lalu menampilkan form yang sudah terisi.
3. Klik **Simpan** → POST `action=ubah` + `id`.
4. Entry point memanggil `ubahKandidat(...)` / `ubahLowongan(...)` → Model menjalankan `UPDATE ... WHERE id` → redirect ke daftar.

### Delete (hapus)

1. Klik **Hapus** pada baris tabel → dialog konfirmasi → POST `action=hapus` + `id`.
2. Entry point memanggil `hapusKandidat(id)` / `hapusLowongan(id)`.
3. Model menjalankan `DELETE ... WHERE id` → redirect ke daftar.