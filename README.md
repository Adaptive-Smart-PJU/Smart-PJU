# Smart-PJU — Adaptive Smart Street Lighting Dashboard Monitoring

Sistem monitoring dan manajemen Penerangan Jalan Umum Pintar (Smart PJU) berbasis web menggunakan framework **Laravel**. Sistem ini dirancang untuk memantau kondisi operasional lampu PJU secara real-time, mendeteksi anomali/gangguan, mengontrol intensitas pencahayaan secara adaptif guna efisiensi energi, serta mengelola tiket tindak lanjut teknisi.

---

## 📋 Daftar Kebutuhan Fungsional (Functional Requirements)

Berikut adalah daftar kebutuhan fungsional sistem yang menjadi acuan utama pengembangan:

| ID | Kebutuhan Fungsional | Deskripsi |
| :--- | :--- | :--- |
| **KF-01** | **Autentikasi pengguna** | Sistem menyediakan login dan logout bagi pengguna yang terdaftar serta membatasi akses berdasarkan perannya (Admin, Operator, Teknisi). |
| **KF-02** | **Pengelolaan data lampu** | Pengelola dapat menambahkan, melihat, mengubah, dan menonaktifkan data lampu yang mencakup kode lampu, lokasi (koordinat GIS), serta identitas perangkat pengendali (Controller ID / Node ID). |
| **KF-03** | **Pembacaan dan pengiriman data sensor** | Perangkat membaca kondisi cahaya (lux), deteksi aktivitas (PIR/radar), serta parameter kelistrikan (tegangan, arus, daya, kWh), lalu mengirimkannya via komunikasi radio estafet (mesh) ke Gateway hingga masuk ke sistem. |
| **KF-04** | **Pengendalian berdasarkan cahaya** | Perangkat menyalakan lampu ketika gelap dan mematikannya ketika terang dengan ambang batas (threshold hysteresis) untuk mencegah nyala-mati berulang. |
| **KF-05** | **Pengaturan intensitas adaptif** | Perangkat meredupkan lampu (misal 30%) ketika tidak ada aktivitas, dan meningkatkan intensitas (100%) ketika aktivitas terdeteksi. |
| **KF-06** | **Pengaturan parameter operasi** | Pengelola dapat mengubah periode hemat energi, setelan redup/terang, dan ambang cahaya dari dashboard web secara sentral atau per grup/zona. |
| **KF-07** | **Monitoring kondisi lampu** | Sistem menampilkan lokasi pada peta digital interaktif, mode operasi, setelan intensitas, pembacaan sensor realtime, dan waktu pembaruan terakhir. |
| **KF-08** | **Deteksi indikasi gangguan** | Sistem mendeteksi gangguan ketika lampu diperintahkan ON tetapi pembacaan arus berada di bawah ambang normal (misal 0A menandakan lampu putus/rusak). |
| **KF-09** | **Deteksi perangkat offline** | Sistem menandai perangkat sebagai offline (Lost Communication) apabila tidak ada data telemetri yang diterima dalam batas waktu tertentu (*heartbeat timeout*). |
| **KF-10** | **Notifikasi gangguan** | Sistem mengirimkan notifikasi instan berisi kode lampu, lokasi, waktu kejadian, dan jenis gangguan kepada grup teknisi terkait. |
| **KF-11** | **Riwayat operasional** | Sistem menyimpan riwayat pembacaan sensor (telemetri log) dan perubahan konfigurasi berdasarkan rentang waktu untuk kebutuhan audit dan analisis. |
| **KF-12** | **Pencatatan tindak lanjut** | Teknisi dapat mencatat pemeriksaan lapangan, tindakan perbaikan, pergantian komponen, dan status penanganan tiket gangguan (*Open / In Progress / Resolved*). |
| **KF-13** | **Rekapitulasi konsumsi energi** | Sistem menampilkan estimasi penghematan energi (kWh & estimasi biaya) dari hasil penerapan mode adaptif dibandingkan mode konvensional (selalu terang). |

---

## 🔄 Sequence Diagram Kebutuhan Fungsional (KF-01 s/d KF-13)

Berikut adalah visualisasi alur interaksi sistem (sequence diagram) berbasis **Mermaid** untuk setiap kebutuhan fungsional. Diagram ini otomatis dirender secara grafis di GitHub, GitLab, VS Code, dan Antigravity IDE.

**Navigasi Cepat:**
[KF-01](#kf-01-autentikasi-pengguna) • [KF-02](#kf-02-pengelolaan-data-lampu) • [KF-03](#kf-03-pembacaan-dan-pengiriman-data-sensor) • [KF-04](#kf-04-pengendalian-berdasarkan-cahaya) • [KF-05](#kf-05-pengaturan-intensitas-adaptif) • [KF-06](#kf-06-pengaturan-parameter-operasi) • [KF-07](#kf-07-monitoring-kondisi-lampu) • [KF-08](#kf-08-deteksi-indikasi-gangguan) • [KF-09](#kf-09-deteksi-perangkat-offline) • [KF-10](#kf-10-notifikasi-gangguan) • [KF-11](#kf-11-riwayat-operasional) • [KF-12](#kf-12-pencatatan-tindak-lanjut) • [KF-13](#kf-13-rekapitulasi-konsumsi-energi)

---

### KF-01: Autentikasi Pengguna
*Sistem menyediakan login dan logout bagi pengguna terdaftar serta membatasi akses berdasarkan perannya.*

```mermaid
sequenceDiagram
    autonumber
    actor Pengguna as Pengguna (Admin/Teknisi)
    participant UI as Browser / Frontend
    participant Auth as Auth Controller
    participant DB as Database

    Pengguna->>UI: Input Email & Password
    UI->>Auth: POST /login (credentials)
    Auth->>DB: Query User & Verifikasi Hash Password
    alt Kredensial Valid
        DB-->>Auth: Data User & Role
        Auth->>Auth: Generate Session / Token
        Auth-->>UI: 200 OK (Redirect sesuai Role)
        UI-->>Pengguna: Tampilan Dashboard sesuai Hak Akses
    else Kredensial Tidak Valid
        Auth-->>UI: 422 / 401 Unauthorized
        UI-->>Pengguna: Tampilkan Pesan Error
    end
```

---

### KF-02: Pengelolaan Data Lampu
*Pengelola dapat menambahkan, melihat, mengubah, dan menonaktifkan data lampu (kode lampu, lokasi GIS, Controller ID).*

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Pengelola / Admin
    participant UI as Dashboard Web
    participant PoleCtrl as LampPole Controller
    participant DB as Database

    Admin->>UI: Buka Menu Manajemen Lampu
    UI->>PoleCtrl: GET /poles
    PoleCtrl->>DB: Query Data Lampu & Koordinat
    DB-->>PoleCtrl: Daftar Tiang Lampu
    PoleCtrl-->>UI: Render Tabel & Titik Peta

    Admin->>UI: Submit Form Tambah/Ubah (Kode, GIS Lat-Long, Controller ID)
    UI->>PoleCtrl: POST /poles atau PUT /poles/{id}
    PoleCtrl->>PoleCtrl: Validasi Input (FormRequest)
    PoleCtrl->>DB: Simpan / Update Record Lampu
    DB-->>PoleCtrl: Sukses
    PoleCtrl-->>UI: Respon Berhasil (200 OK)
    UI-->>Admin: Notifikasi Data Tersimpan
```

---

### KF-03: Pembacaan dan Pengiriman Data Sensor
*Perangkat membaca kondisi cahaya, aktivitas, dan listrik, lalu mengirim via radio estafet (mesh) ke Gateway hingga ke sistem.*

```mermaid
sequenceDiagram
    autonumber
    participant Sensor as Sensor (Lux, PIR, Volt, Arus)
    participant Node as Controller Node PJU
    participant Mesh as Node Relay (Radio Mesh)
    participant GW as IoT Gateway
    participant API as Laravel Ingestion API
    participant DB as Database

    Sensor->>Node: Baca parameter (Lux, Gerak, Volt, Ampere, Watt)
    Node->>Node: Kemas payload data telemetri
    Node->>Mesh: Transmisi paket via Radio RF / LoRa
    Mesh->>GW: Teruskan paket estafet ke Gateway
    GW->>API: HTTP POST /api/telemetry (JSON Payload + API Key)
    API->>API: Validasi format data & otorisasi Gateway
    API->>DB: Simpan ke tabel TelemetryLogs
    API->>DB: Update status terkini di tabel Poles (last_seen, lux, power)
    API-->>GW: 201 Created (Data Diterima)
```

---

### KF-04: Pengendalian Berdasarkan Cahaya
*Perangkat menyalakan lampu ketika gelap dan mematikan ketika terang dengan ambang batas histeresis.*

```mermaid
sequenceDiagram
    autonumber
    participant Sensor as Sensor Cahaya (LDR / Lux)
    participant Node as Controller Node PJU
    participant Lamp as Driver Lampu LED

    loop Siklus Pembacaan Periodik
        Sensor->>Node: Nilai Intensitas Cahaya (Lux)
        alt Lux < Ambang Gelap (misal < 20 Lux) & Status = OFF
            Node->>Lamp: Nyalakan Lampu (ON)
            Node->>Node: Set status = ON
        else Lux > Ambang Terang + Histeresis (misal > 50 Lux) & Status = ON
            Node->>Lamp: Matikan Lampu (OFF)
            Node->>Node: Set status = OFF
        else Rentang Histeresis (20 - 50 Lux)
            Node->>Node: Pertahankan status lampu saat ini (Anti-flicker)
        end
    end
```

---

### KF-05: Pengaturan Intensitas Adaptif
*Perangkat meredupkan lampu (misal 30%) saat tidak ada aktivitas, dan meningkatkan ke 100% saat aktivitas terdeteksi.*

```mermaid
sequenceDiagram
    autonumber
    participant PIR as Sensor Gerak (PIR / Radar)
    participant Node as Controller Node PJU
    participant PWM as Driver Lampu (Dimmer PWM)

    Note over Node: Mode Malam Aktif (Lampu Beroperasi)
    alt Aktivitas Terdeteksi (Kendaraan / Pejalan Kaki)
        PIR->>Node: Trigger Sinyal Gerak (HIGH)
        Node->>PWM: Naikkan Intensitas ke 100% (Terang Penuh)
        Node->>Node: Reset Countdown Timer Idle (misal 45 detik)
    else Tidak Ada Gerak & Timer Idle Habis
        Node->>PWM: Turunkan Intensitas ke Mode Redup (30%)
        Node->>Node: Masuk Status Hemat Energi (Energy Saving)
    end
```

---

### KF-06: Pengaturan Parameter Operasi
*Pengelola dapat mengubah periode hemat energi, setelan redup/terang, dan ambang cahaya dari dashboard web.*

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Pengelola
    participant UI as Dashboard Web
    participant ConfigCtrl as Config Controller
    participant DB as Database
    participant GW as IoT Gateway
    participant Node as Controller Node PJU

    Admin->>UI: Ubah Parameter (Ambang Lux, % Dimming, Jam Hemat Energi)
    UI->>ConfigCtrl: POST /configs/update
    ConfigCtrl->>DB: Simpan Perubahan Parameter ke Database
    ConfigCtrl->>GW: Kirim Perintah Sync Config via MQTT/HTTP
    GW->>Node: Broadcast Paket Konfigurasi Radio Mesh
    Node->>Node: Simpan Nilai Parameter ke Non-volatile Memory
    Node-->>GW: ACK Konfigurasi Diterima
    GW-->>ConfigCtrl: Konfirmasi Sinkronisasi Sukses
    ConfigCtrl-->>UI: Update Status: Terkonfigurasi
```

---

### KF-07: Monitoring Kondisi Lampu
*Sistem menampilkan lokasi, mode operasi, setelan intensitas, pembacaan sensor, dan waktu pembaruan terakhir.*

```mermaid
sequenceDiagram
    autonumber
    actor User as Operator / Admin
    participant UI as Dashboard Web (Leaflet GIS)
    participant MonCtrl as Monitoring Controller
    participant DB as Database

    User->>UI: Akses Halaman Monitoring Peta
    UI->>MonCtrl: GET /monitoring/data
    MonCtrl->>DB: Query Data Lampu + Telemetri Terkini
    DB-->>MonCtrl: Dataset (Status, Watt, Lux, Arus, Lat/Long, Last Seen)
    MonCtrl-->>UI: JSON Data Telemetri Real-time
    UI->>UI: Render Titik Peta (Warna: Hijau=ON, Kuning=DIM, Merah=FAULT, Abu=OFFLINE)
    UI-->>User: Tampilan Peta Interaktif & Panel Detail Tiang Lampu
```

---

### KF-08: Deteksi Indikasi Gangguan
*Sistem mendeteksi gangguan saat lampu diperintahkan ON tetapi pembacaan arus di bawah ambang normal (misal 0A).*

```mermaid
sequenceDiagram
    autonumber
    participant Node as Controller Node PJU
    participant GW as IoT Gateway
    participant API as Telemetry Ingestion API
    participant FaultService as Fault Detection Service
    participant DB as Database

    Node->>GW: Kirim Telemetri (Status Lampu=ON, Arus=0.00 A)
    GW->>API: POST /api/telemetry
    API->>FaultService: Evaluasi Parameter Listrik
    alt Status Perintah = ON & Pembacaan Arus < Ambang Batas (misal < 0.05 A)
        FaultService->>DB: Catat Insiden Gangguan (Jenis: Lampu Putus/Mati)
        FaultService->>DB: Update Status Lampu = FAULT
        FaultService->>FaultService: Trigger Event: LampFaultDetected
    end
    API-->>GW: 200 OK (Telemetri Tersimpan)
```

---

### KF-09: Deteksi Perangkat Offline
*Sistem menandai perangkat sebagai offline apabila tidak ada data yang diterima dalam batas waktu tertentu.*

```mermaid
sequenceDiagram
    autonumber
    participant Scheduler as Laravel Task Scheduler (Cron)
    participant Command as CheckOfflinePoles Command
    participant DB as Database

    loop Setiap Menit
        Scheduler->>Command: Jalankan `php artisan poles:check-offline`
        Command->>DB: Query Lampu dengan status != OFFLINE
        DB-->>Command: Daftar Lampu yang Sedang Terhubung
        loop Untuk Setiap Tiang Lampu
            alt (Waktu Sekarang - last_seen) > Batas Heartbeat (misal > 10 menit)
                Command->>DB: Update Status Lampu = OFFLINE
                Command->>DB: Catat Log Anomali Koneksi Terputus
            end
        end
    end
```

---

### KF-10: Notifikasi Gangguan
*Sistem mengirimkan notifikasi instan berisi kode lampu, lokasi, waktu, dan jenis gangguan kepada grup teknisi.*

```mermaid
sequenceDiagram
    autonumber
    participant Event as Event: LampFaultDetected
    participant Listener as SendFaultNotification Listener
    participant Channel as Notifier (Telegram / WhatsApp / Email / Push)
    actor Teknisi as Grup Teknisi Lapangan

    Event->>Listener: Tangkap Event Gangguan
    Listener->>Listener: Susun Pesan (Kode Lampu, Nama Jalan, Koordinat, Waktu, Indikasi Kerusakan)
    Listener->>Channel: Kirim Notifikasi via Webhook / Bot API
    Channel-->>Teknisi: Menerima Pesan Peringatan Gangguan di Smartphone
```

---

### KF-11: Riwayat Operasional
*Sistem menyimpan riwayat pembacaan sensor dan perubahan konfigurasi berdasarkan rentang waktu.*

```mermaid
sequenceDiagram
    autonumber
    actor Pengguna as Pengelola / Operator
    participant UI as Dashboard Web
    participant HistoryCtrl as History Controller
    participant DB as Database

    Pengguna->>UI: Tentukan Filter (Rentang Tanggal, ID Lampu / Zona)
    UI->>HistoryCtrl: GET /reports/history?start=...&end=...&pole_id=...
    HistoryCtrl->>DB: Query Time-series TelemetryLogs & ConfigAudit
    DB-->>HistoryCtrl: Dataset Rekapitulasi Riwayat
    HistoryCtrl-->>UI: Kirim Data untuk Tabel & Grafik Tren
    Pengguna->>UI: Klik 'Export CSV / Excel'
    UI->>HistoryCtrl: GET /reports/export
    HistoryCtrl-->>UI: Unduh Berkas Rekapitulasi Riwayat Operasional
```

---

### KF-12: Pencatatan Tindak Lanjut
*Teknisi dapat mencatat pemeriksaan, tindakan perbaikan, dan status penanganan tiket gangguan (Open/Resolved).*

```mermaid
sequenceDiagram
    autonumber
    actor Teknisi as Teknisi Lapangan
    participant UI as Dashboard / Modul Tiket
    participant TicketCtrl as Ticket Controller
    participant DB as Database

    Teknisi->>UI: Buka Daftar Tiket Gangguan
    UI->>TicketCtrl: GET /tickets?status=open
    TicketCtrl->>DB: Query Tiket Gangguan Aktif
    DB-->>TicketCtrl: Data Tiket
    TicketCtrl-->>UI: Tampilkan Daftar Tiket

    Teknisi->>UI: Buka Tiket & Ubah Status ke 'In Progress'
    Teknisi->>UI: Input Hasil Pengecekan, Penggantian Komponen, Catatan, & Set 'Resolved'
    UI->>TicketCtrl: POST /tickets/{id}/resolve
    TicketCtrl->>DB: Simpan Catatan Tindak Lanjut & Ubah Status Tiket = RESOLVED
    TicketCtrl->>DB: Pulihkan Status Lampu = NORMAL
    TicketCtrl-->>UI: Respon Berhasil (200 OK)
    UI-->>Teknisi: Notifikasi Tiket Berhasil Ditutup
```

---

### KF-13: Rekapitulasi Konsumsi Energi
*Sistem menampilkan estimasi penghematan energi dari mode adaptif dibandingkan mode selalu terang.*

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Pengelola
    participant UI as Halaman Analitik Energi
    participant EnergyCtrl as Energy Analytics Controller
    participant Service as EnergyCalculationService
    participant DB as Database

    Admin->>UI: Buka Menu Rekapitulasi Konsumsi Energi
    UI->>EnergyCtrl: GET /analytics/energy-savings?period=monthly
    EnergyCtrl->>Service: Hitung Penghematan (Aktual vs Baseline)
    Service->>DB: Ambil Data Total kWh Terpakai (Mode Adaptif)
    DB-->>Service: Total kWh Aktual
    Service->>Service: Hitung Baseline kWh = (Daya Lampu Penuh x Jam Operasi)
    Service->>Service: Hitung kWh Penghematan = (Baseline - Aktual)
    Service->>Service: Hitung Estimasi Penghematan Biaya Listrik (Rp)
    Service-->>EnergyCtrl: Metrik Penghematan & Data Time-series
    EnergyCtrl-->>UI: Kirim Data Grafik & Ringkasan Persentase Efisiensi
    UI-->>Admin: Visualisasi Grafik Penghematan Energi & Angka Biaya Terhemat
```

---

## 🤖 Aturan Baku untuk AI Coding Agent (AI Agent Guidelines)

Bagi seluruh anggota tim yang menggunakan AI Coding Agent (seperti **Antigravity**, **Claude Code**, **Cursor**, **GitHub Copilot**, atau tools AI lainnya), setiap AI Agent **WAJIB** mematuhi aturan berikut saat membaca, memodifikasi, atau menambahkan kode:

### 1. Fondasi & Standar Kode PHP (PHP 8.3 & Laravel 11+)
- **Laravel Way:** Selalu gunakan perintah resmi `php artisan make:*` untuk membuat komponen baru (Controller, Model, Migration, Seeder, Request, Event, Job, Resource, dll.).
- **Strict Typing:** Selalu gunakan `declare(strict_types=1);` jika relevan, gunakan type hint eksplisit pada parameter fungsi dan return type:
  ```php
  public function updateThreshold(Pole $pole, float $luxThreshold): bool
  ```
- **Constructor Property Promotion:** Gunakan fitur PHP 8:
  ```php
  public function __construct(
      public readonly TelemetryService $telemetryService,
  ) {}
  ```
- **Kontrol Alur:** Selalu gunakan kurung kurawal `{}` untuk semua blok kontrol (`if`, `foreach`, `while`), hindari single-line tanpa kurung kurawal.
- **Validasi:** Selalu gunakan Form Request (`php artisan make:request`) untuk validasi input form atau API payload, jangan menuliskan validasi panjang di dalam Controller.
- **RESTful API:** Gunakan Eloquent API Resource (`php artisan make:resource`) untuk memformat respon JSON endpoint IoT atau mobile.

### 2. Struktur Database & Model
- **Dilarang manipulasi DB langsung:** Jangan membuat/mengubah tabel manual di database manager. Selalu buat file **Migration** yang rapi dan idempotent.
- **Factory & Seeder:** Setiap pembuatan Model baru harus disertai **Factory** dan **Seeder** data dummy realistis (termasuk simulasi data koordinat GPS, telemetri sensor, dan riwayat gangguan).
- **Relasi & Query Optimization:** Wajib memperhatikan potensi isu *N+1 query*. Selalu gunakan Eager Loading (`with(['telemetries', 'pole'])`).
- **Data Casting & Enums:** Gunakan PHP Enums atau Eloquent Attribute Casting untuk status penting (contoh: status lampu `ON`, `OFF`, `FAULT`, status tiket `OPEN`, `RESOLVED`).

### 3. Arsitektur Folder & Dependensi
- **Patuhi Struktur Eksisting:** Dilarang membuat folder baru di root direktori tanpa persetujuan tim. Ikuti struktur bawaan Laravel (`app/Models`, `app/Http/Controllers`, `app/Services`, `routes/web.php`, `routes/api.php`).
- **Logika Bisnis:** Tempatkan logika perhitungan rumit (seperti kalkulasi penghematan energi KF-13 atau pemrosesan paket radio KF-03) pada lapisan **Service Class** (`app/Services/`), bukan menumpuk di Controller atau Blade.
- **Tidak Mengubah Dependensi Sembarangan:** Jangan menambahkan library eksternal baru (`composer require` atau `npm i`) tanpa konfirmasi kebutuhan teknisnya.

### 4. Frontend & Antarmuka
- **Desain Modern & Responsif:** Tampilan dashboard harus bersih, elegan, bertema modern (*dark mode friendly* atau palet warna terkurasi khas IoT/smart city), dan mudah diakses di perangkat tablet/smartphone teknisi lapangan.
- **Aset & Build:** Gunakan Vite (`npm run dev` / `npm run build`). Pastikan ID elemen interaktif jelas untuk memudahkan pengujian otomatis.

### 5. Pengujian & Quality Assurance
- **Unit & Feature Test:** Setiap penambahan fitur baru (khususnya logika KF-01 s/d KF-13) wajib disertai Feature Test menggunakan PHPUnit/Pest:
  ```bash
  php artisan test
  ```
- **Linter & Code Formatter:** Sebelum membuat commit atau mengajukan pull request, AI Agent dan pengembang wajib menjalankan Laravel Pint:
  ```bash
  vendor/bin/pint --format agent
  ```
- **Hindari Script Coba-Coba:** Jangan meninggalkan file script sementara atau file tes manual di folder publik.

### 6. Git & Commit Workflow
- Gunakan format **Conventional Commits**:
  - `feat(kf-02): add lamp pole management CRUD`
  - `feat(kf-08): implement current fault detection logic`
  - `fix(kf-09): resolve heartbeat timeout calculation`
  - `test(kf-04): add tests for lux-based threshold switching`
  - `docs: update deployment and architecture documentation`

---

## 🚀 Panduan Memulai (Quick Start)

### Prasyarat Sistem
- **PHP:** Versi `>= 8.3` (dengan ekstensi `pdo_mysql`, `mbstring`, `curl`, `openssl`, `zip`)
- **Database Server:** **MySQL** `>= 8.0` / **MariaDB** `>= 10.4` (dapat dijalankan via Laragon / XAMPP / Docker)
- **Composer:** Versi `>= 2.2`
- **Node.js:** Versi `>= 18.x` dan **NPM**

### Langkah Instalasi

1. **Clone repositori:**
   ```bash
   git clone https://github.com/Adaptive-Smart-PJU/Smart-PJU.git
   cd Smart-PJU
   ```

2. **Salin konfigurasi environment:**
   ```bash
   cp .env.example .env
   ```

3. **Konfigurasi Database MySQL di `.env`:**
   Pastikan service MySQL Anda sudah aktif (Start di Laragon/XAMPP), lalu buat database baru:
   ```sql
   CREATE DATABASE smart_pju;
   ```
   Sesuaikan parameter koneksi di file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smart_pju
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Install dependensi PHP dan Node.js:**
   ```bash
   composer install
   npm install
   ```

5. **Generate App Key:**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi dan Database Seeder:**
   ```bash
   php artisan migrate --seed
   ```

7. **Jalankan Aplikasi:**
   - Terminal 1 (Vite Frontend):
     ```bash
     npm run dev
     ```
   - Terminal 2 (Laravel Server):
     ```bash
     php artisan serve
     ```

8. Buka browser pada alamat: `http://127.0.0.1:8000`

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 11/12 (PHP 8.3)
- **Database:** **MySQL** (kompatibel MariaDB)
- **Frontend:** Blade Templates, Tailwind CSS / Vanilla CSS, Alpine.js / JavaScript
- **Maps / GIS:** Leaflet.js / OpenStreetMap untuk plotting koordinat titik tiang PJU
- **Tooling:** Laravel Pint, PHPUnit, Laravel Boost, Vite

