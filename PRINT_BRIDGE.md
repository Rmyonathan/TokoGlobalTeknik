## Print Bridge Design (Client-Side Printing Service)

### 1. Problem Statement

Laravel (running on server or shared hosting) tidak bisa langsung mengirim print job ke printer lokal user, karena:

- Browser membatasi akses langsung ke device (printer, port, filesystem).
- Server tidak tahu daftar printer yang terpasang di setiap PC kasir / admin.

Untuk kebutuhan POS, invoice, surat jalan, label, dll, dibutuhkan mekanisme **print langsung** ke printer lokal (thermal, dot-matrix, A4) dengan minimal interaksi user (tanpa dialog print browser jika memungkinkan).

### 2. High-Level Solution

Buat sebuah **Print Bridge** (service kecil) yang berjalan di masing‑masing PC user:

- Laravel/web app memanggil **HTTP API lokal** di `http://localhost:PORT`.
- Print Bridge menerima payload (data yang mau dicetak), memilih printer yang sesuai, dan mengirim print job ke OS.
- Bridge bisa menangani beberapa format:
  - Raw ESC/POS / text untuk thermal.
  - PDF / HTML / image yang di‑render lalu dikirim ke printer A4/dot‑matrix.

Tidak ada perubahan di server soal koneksi ke printer; semua handling dilakukan di PC user melalui bridge ini.

### 3. Komponen Utama

- **Laravel Web App (Server)**
  - Menyediakan halaman UI dan endpoint untuk generate dokumen (HTML/PDF/raw string).
  - Mengirim data ke Print Bridge melalui JavaScript (AJAX/fetch) ke `http://localhost:{bridge_port}`.

- **Print Bridge (Client Service)**
  - Aplikasi kecil (bisa Node.js / Go / .NET / Python) yang berjalan di tray / background.
  - Menjalankan HTTP server lokal hanya di `localhost`.
  - Tahu daftar printer lokal dan konfigurasi default (per jenis dokumen).
  - Menerima request, memproses data, lalu memanggil API printing OS.

- **Printers**
  - Thermal printers (ESC/POS, 58mm / 80mm).
  - Dot-matrix / Inkjet / Laser (A4, continuous form).

### 4. Alur Kerja (Sequence)

#### 4.1 Setup Awal

1. User mengunduh dan meng‑install **Print Bridge** di PC kasir/admin.
2. Bridge berjalan otomatis (service / startup) dan listen di port lokal, misal `127.0.0.1:32145`.
3. Bridge membaca/menyimpan konfigurasi:
   - Default printer thermal (nama printer OS).
   - Default printer A4.
   - Mapping tipe dokumen → printer (misal: faktur → A4, struk → thermal).

#### 4.2 Proses Print dari Laravel

1. User klik tombol **Print** di halaman Laravel (contoh: faktur / surat jalan).
2. Laravel (server) men‑render dokumen:
   - Opsi A: Generate **data siap print** (raw ESC/POS / plain text / PDF base64) dan embed di JS.
   - Opsi B: Endpoint API server yang bisa di‑fetch oleh Bridge jika diperlukan.
3. JavaScript di browser memanggil:
   - `POST http://localhost:32145/print`  
     dengan JSON body berisi:
     - `document_type` (e.g. `"invoice"`, `"surat_jalan"`, `"label"`)
     - `format` (`"raw" | "pdf" | "html" | "text"`)
     - `content` (raw text / base64 PDF / HTML string / URL)
     - optional: `printer_name`, `copies`, `options` (margin, orientation, dll).
4. Print Bridge menerima request, validasi, pilih printer sesuai mapping / override, lalu:
   - Jika `format=raw`: kirim langsung sebagai raw bytes ke printer target (ESC/POS).
   - Jika `format=pdf` / `html`: render (menggunakan viewer/headless engine) lalu kirim print job ke OS.
5. Bridge merespon ke browser dengan status JSON:
   - `{ success: true, job_id: "...", message: "Printed to EPSON-TM-T20" }`  
     atau
   - `{ success: false, error: "Printer not found" }`.
6. Frontend menampilkan notifikasi sukses/gagal ke user.

### 5. API Design (Print Bridge)

> Catatan: Ini desain rencana; implementasi bisa disesuaikan saat coding.

#### 5.1 Endpoint: `POST /print`

- **Request JSON body:**

```jsonc
{
  "document_type": "invoice",           // optional, untuk mapping printer default
  "format": "raw",                      // "raw" | "pdf" | "html" | "text"
  "content": "base64 or plain text",    // tergantung format
  "printer_name": "EPSON TM-T20",       // optional override
  "copies": 1,
  "options": {
    "paper_size": "80mm",               // atau "A4", custom size
    "orientation": "portrait",
    "cut_paper": true,                  // untuk thermal
    "open_drawer": false                // untuk POS drawer
  }
}
```

- **Response:**

```jsonc
// sukses
{
  "success": true,
  "job_id": "local-uuid-or-os-job-id",
  "printer": "EPSON TM-T20",
  "message": "Printed successfully"
}

// gagal
{
  "success": false,
  "error": "Printer 'EPSON TM-T20' not found"
}
```

#### 5.2 Endpoint: `GET /printers`

- Mengembalikan daftar printer yang tersedia di OS:

```jsonc
{
  "success": true,
  "printers": [
    { "name": "EPSON TM-T20", "default": true, "type": "thermal" },
    { "name": "HP LaserJet 1020", "default": false, "type": "a4" }
  ]
}
```

Dipakai oleh halaman pengaturan di Laravel untuk membantu user memilih printer mana untuk jenis dokumen tertentu.

#### 5.3 Endpoint: `GET /config` & `POST /config`

- `GET /config`: baca konfigurasi printer (mapping document_type → printer).
- `POST /config`: simpan konfigurasi baru (local JSON file / registry).

### 6. Format Data yang Disarankan

- **Raw ESC/POS (`format = "raw"`):**
  - Laravel generate string ESC/POS (atau via library) lalu kirim sebagai base64 (lebih aman dalam JSON).
- **PDF (`format = "pdf"`):**
  - Laravel sudah punya DomPDF; bisa `->output()` dan encode ke base64.
  - Bridge decode dan kirim ke printer A4.
- **HTML (`format = "html"`):**
  - Alternatif: kirim HTML dan Bridge pakai headless browser/engine untuk print.
- **Text (`format = "text"`):**
  - Untuk dokumen sederhana (log, test page, dsb).

### 7. Integrasi di Laravel

- **Frontend (Blade + JS):**
  - Tombol Print akan memanggil fungsi JS seperti `window.printBridge.print(data)`.
  - JS:
    - Build payload sesuai format.
    - `fetch('http://localhost:32145/print', {...})` dengan timeout & error handling (bridge tidak jalan, port blocked, dll).
    - Tampilkan hasil ke user.

- **Backend (Controller / Service):**
  - Fungsi helper untuk generate payload siap kirim (ESC/POS / PDF base64).
  - Optional: endpoint `GET /print/{id}/raw` yang bisa diakses Bridge jika ingin model “bridge pull data” bukan “browser push data”.

### 8. Security & Network Considerations

- Bridge hanya listen di `localhost` (127.0.0.1), **bukan** di jaringan, untuk menghindari abuse dari komputer lain.
- Bisa tambahkan:
  - Simple token/API key lokal yang disimpan di browser localStorage & config Bridge.
  - CORS hanya izinkan origin tertentu (domain aplikasi).
- Tidak ada data sensitif (kartu kredit, dsb) yang disimpan lama di Bridge; hanya payload dokumen print.

### 9. Error Handling & UX

- Kasus yang perlu ditangani:
  - Bridge tidak berjalan → `fetch` gagal (`ERR_CONNECTION_REFUSED`):
    - Tampilkan pesan: *“Print Bridge tidak berjalan. Jalankan aplikasi Print Bridge di komputer ini lalu coba lagi.”*
  - Printer tidak ditemukan / offline.
  - Format data tidak valid (base64 rusak, ESC/POS salah).
- Laravel bisa menampilkan status terakhir print di UI (warna hijau/merah).

### 10. Roadmap Implementasi

1. **Prototype Bridge**
   - Pilih bahasa (Node.js / Go) dan buat HTTP server `localhost:32145`.
   - Implementasikan endpoint `/printers` dan `/print` untuk format `text/raw` sederhana.
2. **Integrasi Laravel (Frontend)**
   - Tambah JS helper untuk memanggil Bridge dari halaman Invoice / Surat Jalan.
   - Test dengan printer lokal.
3. **Dukungan Format Lain**
   - Tambah dukungan `pdf` (decode base64 → print).
   - Tambah opsi khusus thermal (cut paper, open drawer).
4. **Halaman Setting Printer di Laravel**
   - UI untuk mapping: `document_type → printer_name` per user/PC.
   - Simpan mapping di sisi Bridge (`/config`) atau di Laravel (per user, lalu dikirim ke Bridge).
5. **Hardening & Packaging**
   - Tambah auth token lokal (opsional).
   - Buat installer untuk Windows (dan nanti Linux kalau perlu).

### 11. Catatan Implementasi

- Fokus awal: **Windows** (karena mayoritas POS pakai Windows + printer thermal USB).
- Pastikan Bridge bisa:
  - Jalan tanpa butuh hak admin tinggi.
  - Auto start saat login (Startup folder / Task Scheduler / service ringan).
- Logging penting untuk debugging (log file lokal di PC user). 

> **Next step**: setelah design ini disetujui, baru kita buat skeleton code untuk Bridge (misalnya Node.js) dan JS helper di Laravel, lalu integrasi satu per satu.


