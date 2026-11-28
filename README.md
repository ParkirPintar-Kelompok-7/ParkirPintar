# ParkirPintar

Layanan manajemen parkir gedung perkantoran dengan karcis digital: area parkir dan kapasitas per jenis kendaraan, check-in, check-out dengan perhitungan tarif otomatis, serta laporan okupansi dan pendapatan.

Tugas Besar 1 Komputasi Awan 2026-2027 (CI/CD Pipeline Jenkins), **Kelompok 7, studi kasus K07 ParkirPintar**.

## Tim

| Nama | Peran |
|---|---|
| Firza  | Tech Lead / Backend |
| Fadhil | DevOps Engineer |
| Jingga | Container Engineer |
| Rafi   | QA Engineer & Tech Writer |

## Stack

- Laravel (PHP 8.3), REST API (JSON)
- MySQL
- Test: PHPUnit lewat `php artisan test`
- Container: Docker + Docker Compose
- CI/CD: Jenkins + GitHub Webhook (Ngrok)

## Fitur

- CRUD area parkir dan kapasitas per jenis kendaraan (motor/mobil)
- Check-in kendaraan (plat nomor, jenis, area)
- Check-out dengan perhitungan tarif
- Laporan okupansi per area dan pendapatan per hari
- Endpoint `GET /health` (status aplikasi dan koneksi database)

## Aturan bisnis

**Tarif**

| Jenis | Jam pertama | Jam berikutnya | Maksimal per hari |
|---|---|---|---|
| Motor | Rp2.000 | Rp1.000 per jam | Rp20.000 |
| Mobil | Rp5.000 | Rp3.000 per jam | Rp50.000 |

- Durasi dibulatkan ke atas per jam (61 menit dihitung 2 jam).
- Durasi 15 menit atau kurang: gratis (Rp0). Lewat dari itu, ditagih dari total durasi.
- Batas harian berlaku per blok 24 jam. Parkir 25 jam = 1 blok penuh + tarif 1 jam untuk sisanya.

**Check-in**
- Ditolak (409) jika plat yang sama masih berstatus ACTIVE.
- Ditolak (409) jika area penuh untuk jenis kendaraan tersebut. Kapasitas dihitung per area dan per jenis kendaraan.
- Plat dinormalisasi (huruf besar, spasi tunggal): `b  1234 xyz` menjadi `B 1234 XYZ`.

**Check-out**
- Mengubah record ACTIVE menjadi DONE, mengisi `check_out_at` dan `fee`.
- Ditolak (404) jika tidak ada record ACTIVE untuk plat tersebut.

**Area parkir**
- Nama area unik.
- Kapasitas tidak boleh diturunkan di bawah jumlah kendaraan yang sedang parkir (422).
- Area yang sudah punya riwayat parkir tidak bisa dihapus (409). Set kapasitas ke 0 sebagai gantinya.

## Endpoint API

Semua request API wajib memakai header `Accept: application/json`, kalau tidak Laravel membalas dengan redirect, bukan JSON.

| Method | URL | Keterangan |
|---|---|---|
| GET | `/health` | Status aplikasi. 200 jika database terhubung, 503 jika tidak |
| GET | `/api/areas` | Daftar area |
| POST | `/api/areas` | Tambah area |
| GET | `/api/areas/{id}` | Detail area |
| PUT | `/api/areas/{id}` | Ubah area |
| DELETE | `/api/areas/{id}` | Hapus area |
| POST | `/api/check-in` | Check-in kendaraan |
| POST | `/api/check-out` | Check-out kendaraan |
| GET | `/api/reports/occupancy` | Laporan okupansi per area |
| GET | `/api/reports/revenue?date=YYYY-MM-DD` | Laporan pendapatan per hari (default: hari ini) |

Contoh body:

```json
// POST /api/areas  (juga untuk PUT)
{ "name": "Gedung A", "capacity_motor": 50, "capacity_mobil": 20 }

// POST /api/check-in
{ "plate_number": "B 1234 XYZ", "vehicle_type": "motor", "parking_area_id": 1 }

// POST /api/check-out
{ "plate_number": "B 1234 XYZ" }
```

## Menjalankan di lokal

### Prasyarat

- PHP 8.3 dengan ekstensi: `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`
- Composer
- Git
- MySQL (misalnya dari XAMPP) dengan database kosong bernama `parkirpintar`

### Langkah

```bash
git clone https://github.com/ParkirPintar-Kelompok-7/ParkirPintar
cd ParkirPintar
composer install
cp .env.example .env        # di CMD/PowerShell: copy .env.example .env
php artisan key:generate
```

Buat database `parkirpintar` (lewat phpMyAdmin atau `CREATE DATABASE parkirpintar;`), cek isi `.env` sesuai MySQL Anda, lalu:

```bash
php artisan migrate --seed
php artisan serve
```

Buka `http://127.0.0.1:8000/health`. Hasil yang benar:

```json
{"status":"ok","database":"up"}
```

## Menjalankan test

```bash
php artisan test
```

Test memakai SQLite in-memory (diatur di `phpunit.xml`), jadi tidak menyentuh database MySQL lokal dan tidak butuh MySQL saat dijalankan di container atau Jenkins. Semua test harus hijau sebelum membuka Pull Request.

## Variabel lingkungan

`.env` **tidak boleh di-commit**. Yang di-commit hanya `.env.example` (tanpa password asli).

| Variabel | Contoh | Keterangan |
|---|---|---|
| `APP_NAME` | `ParkirPintar` | Nama aplikasi |
| `APP_ENV` | `local` | `production` saat deploy |
| `APP_KEY` | (hasil `php artisan key:generate`) | Wajib terisi |
| `APP_DEBUG` | `true` | Harus `false` saat deploy |
| `APP_URL` | `http://localhost:8000` | URL aplikasi |
| `APP_TIMEZONE` | `Asia/Jakarta` | Opsional, default sudah Asia/Jakarta |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` | `127.0.0.1` | Di Docker Compose: nama service database |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `parkirpintar` | |
| `DB_USERNAME` | `root` | |
| `DB_PASSWORD` | (kosong di lokal) | Di server/Jenkins: simpan sebagai secret |

## Alur kerja Git (wajib dibaca semua anggota)

Aturan utama:

1. **Dilarang push langsung ke `main`.** Semua perubahan lewat branch, lalu Pull Request (PR), lalu merge.
2. **Jangan pernah commit** `.env`, token, atau password. Cek dengan `git status` sebelum commit.
3. **Setiap anggota harus punya commit sendiri** di repo ini (dinilai dari riwayat GitHub).

### Pertama kali (sekali saja per laptop)

Atur identitas supaya commit tercatat atas nama Anda:

```bash
git config --global user.name "Nama Anda"
git config --global user.email "email-yang-dipakai-di-github"
```

Clone repo (Anda harus sudah diundang sebagai collaborator):

```bash
git clone <url-repo-kelompok>
cd <nama-folder-repo>
```

Kalau diminta login, pakai login lewat browser atau Personal Access Token. Password akun GitHub tidak lagi diterima untuk Git.

### Setiap kali mulai bekerja

```bash
git checkout main
git pull                                  # ambil perubahan terbaru
git checkout -b <nama-branch>             # contoh: docker/dockerfile, devops/jenkinsfile, qa/readme
```

Penamaan branch: `<bidang>/<hal-singkat>`, contoh `docker/compose`, `devops/jenkinsfile`, `feature/laporan`.

### Setelah selesai mengerjakan sesuatu

```bash
git status                                # pastikan tidak ada .env atau file rahasia
git add .
git commit -m "Pesan singkat tentang apa yang diubah"
git push -u origin <nama-branch>
```

Lalu di GitHub: klik **Compare & pull request**, isi judul, **Create pull request**. Minta satu anggota lain melihatnya, lalu **Merge pull request**.

### Setelah PR ter-merge

```bash
git checkout main
git pull
git branch -d <nama-branch>
```

### Kalau `main` sudah berubah saat Anda masih mengerjakan branch

```bash
git checkout main
git pull
git checkout <nama-branch>
git merge main
```

Kalau muncul conflict, buka file yang ditandai di VS Code, pilih versi yang benar, simpan, lalu `git add .` dan `git commit`.

## Catatan untuk Container Engineer

- Port aplikasi: **8000**. Cara paling sederhana: `php artisan serve --host=0.0.0.0 --port=8000`.
- Base image yang disarankan: `php:8.3-cli`. Tambahkan ekstensi `pdo_mysql` (`docker-php-ext-install pdo_mysql`). `pdo_sqlite` sudah ada di image resmi.
- Composer: salin dari image `composer`. Stage test pakai `composer install` (dengan dev dependency), stage final pakai `composer install --no-dev --optimize-autoloader`.
- **Stage test:** jalankan `php artisan test`. Tidak butuh MySQL. Tapi `APP_KEY` harus ada: lakukan `cp .env.example .env && php artisan key:generate` di dalam stage, atau kirim `APP_KEY` sebagai environment variable. Tanpa itu, test akan gagal dengan `MissingAppKeyException`.
- **Migrasi:** jalankan `php artisan migrate --force` setelah database healthy dan sebelum smoke test. Untuk data contoh: `php artisan db:seed --force`. Perhatikan: `/health` hanya mengecek koneksi database, bukan keberadaan tabel.
- Variabel yang wajib dikirim ke container aplikasi: `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, dan semua `DB_*`. `DB_HOST` adalah nama service database di `compose.yaml`.
- Tambahkan `.dockerignore` untuk `vendor`, `.git`, `.env`, dan `node_modules`.

## Catatan untuk DevOps Engineer

- Smoke test: `curl -fsS http://localhost:8000/health` (gagal jika status bukan 200). Boleh ditambah `curl -fsS http://localhost:8000/api/reports/occupancy -H "Accept: application/json"`.
- Perintah test: `php artisan test`. Untuk laporan JUnit (tantangan K07): `vendor/bin/phpunit --log-junit storage/logs/junit.xml`, lalu publikasikan dengan langkah `junit` di blok `post { always { ... } }`.
- Jumlah test saat ini lebih dari 60 dan berjalan dalam hitungan detik.
- `Jenkinsfile` ada di root repository. Credential (Docker Hub, GitHub, secret file `.env`) disimpan di Jenkins Credentials, bukan di kode.

## Troubleshooting

| Masalah | Penyebab dan solusi |
|---|---|
| `could not find driver` | Ekstensi `pdo_mysql` atau `pdo_sqlite` belum aktif. Hapus tanda `;` di depan `extension=...` di `php.ini`, lalu buka terminal baru |
| `composer install` timeout | Masalah jaringan atau DNS. Coba hotspot, ganti DNS ke `8.8.8.8`, atau matikan IPv6 di adapter WiFi |
| `No application encryption key has been generated` | Jalankan `php artisan key:generate` |
| `Your local changes would be overwritten by merge` | Commit dulu perubahan Anda, atau simpan sementara dengan `git stash` |
| `git push` ditolak | Jalankan `git pull` lalu push ulang |
| Port 8000 dipakai | `php artisan serve --port=8001` |
| Respons API berupa halaman HTML / redirect | Tambahkan header `Accept: application/json` |


# ParkirPintar

ParkirPintar adalah aplikasi manajemen parkir gedung perkantoran dengan karcis digital. Aplikasi mendukung pengelolaan area dan kapasitas parkir, proses check-in dan check-out kendaraan, perhitungan tarif otomatis, serta laporan okupansi dan pendapatan.

Proyek ini dikembangkan untuk **Tugas Besar 1 Komputasi Awan Tahun Akademik 2026/2027**, **Kelompok 7**, studi kasus **K07 ParkirPintar**, dengan implementasi CI/CD menggunakan Jenkins.

## Tim Pengembang

| Anggota | Peran |
|---|---|
| Firza | Tech Lead / Backend Engineer |
| Fadhil | DevOps Engineer |
| Jingga | Container Engineer |
| Rafi | QA Engineer & Tech Writer |

## Teknologi

- **Backend:** Laravel dan PHP 8.3
- **API:** REST API dengan respons JSON
- **Database aplikasi:** MySQL
- **Database testing:** SQLite in-memory
- **Automated testing:** PHPUnit
- **Container:** Docker dan Docker Compose
- **CI/CD:** Jenkins yang terhubung ke GitHub melalui webhook dan tunnel ngrok
- **Container registry:** Docker Hub, repository `fdhil17/parkirpintar`
- **Diagram:** UML Activity Diagram yang dibuat dengan draw.io

## Fitur Aplikasi

- CRUD area parkir dan pengaturan kapasitas motor maupun mobil.
- Check-in kendaraan berdasarkan plat nomor, jenis kendaraan, dan area parkir.
- Check-out kendaraan beserta perhitungan biaya parkir otomatis.
- Laporan okupansi area parkir dan pendapatan harian.
- Endpoint kesehatan aplikasi `GET /health` untuk memeriksa status aplikasi dan koneksi database.

## Aturan Bisnis

### Tarif Parkir

| Kendaraan | Jam pertama | Jam berikutnya | Batas tarif per 24 jam |
|---|---:|---:|---:|
| Motor | Rp2.000 | Rp1.000/jam | Rp20.000 |
| Mobil | Rp5.000 | Rp3.000/jam | Rp50.000 |

- Durasi **15 menit atau kurang** tidak dikenakan biaya.
- Jika durasi melewati 15 menit, tarif dihitung berdasarkan total durasi parkir.
- Perhitungan durasi dibulatkan ke atas per jam, misalnya 61 menit dihitung dua jam.
- Batas tarif harian diterapkan per blok 24 jam.

### Check-in dan Check-out

- Sistem menolak check-in dengan status HTTP **409** jika plat nomor yang sama masih memiliki parkir aktif, termasuk jika mencoba masuk ke area berbeda.
- Sistem menolak check-in dengan HTTP **409** jika kapasitas area untuk jenis kendaraan tersebut sudah penuh.
- Plat nomor dinormalisasi menjadi huruf kapital dan spasi tunggal.
- Check-out mengubah status parkir dari `ACTIVE` menjadi `DONE`, serta mencatat waktu keluar dan biaya.
- Check-out dengan plat tanpa parkir aktif menghasilkan HTTP **404**.

### Pengelolaan Area

- Nama area parkir harus unik.
- Kapasitas area tidak dapat dikurangi hingga lebih kecil daripada kendaraan yang sedang parkir (HTTP **422**).
- Area yang memiliki riwayat parkir tidak dapat dihapus (HTTP **409**); kapasitas dapat diubah menjadi nol jika diperlukan.

## Endpoint API

Untuk endpoint API, sertakan header `Accept: application/json`.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/health` | Pemeriksaan aplikasi dan database |
| GET | `/api/areas` | Melihat daftar area parkir |
| POST | `/api/areas` | Menambah area parkir |
| GET | `/api/areas/{id}` | Melihat detail area |
| PUT | `/api/areas/{id}` | Memperbarui area |
| DELETE | `/api/areas/{id}` | Menghapus area jika diizinkan |
| POST | `/api/check-in` | Mencatat kendaraan masuk |
| POST | `/api/check-out` | Mencatat kendaraan keluar dan menghitung biaya |
| GET | `/api/reports/occupancy` | Melihat laporan okupansi |
| GET | `/api/reports/revenue?date=YYYY-MM-DD` | Melihat pendapatan berdasarkan tanggal |

Contoh request JSON:

```json
{
  "plate_number": "B 1234 XYZ",
  "vehicle_type": "motor",
  "parking_area_id": 1
}
```

Contoh request tersebut digunakan untuk `POST /api/check-in`.

## Menjalankan Aplikasi Secara Lokal

### Prasyarat

- PHP 8.3 beserta ekstensi `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, dan `zip`.
- Composer dan Git.
- MySQL dengan database kosong bernama `parkirpintar`.

### Instalasi

```bash
git clone https://github.com/ParkirPintar-Kelompok-7/ParkirPintar.git
cd ParkirPintar
composer install
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell, penyalinan file lingkungan dapat menggunakan:

```powershell
Copy-Item .env.example .env
```

Sesuaikan pengaturan database pada `.env`, buat database `parkirpintar`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan serve
```

Aplikasi dijalankan pada `http://127.0.0.1:8000`.

Endpoint `http://127.0.0.1:8000/health` seharusnya mengembalikan:

```json
{"status":"ok","database":"up"}
```

## Quality Assurance dan Automated Testing

Pengujian otomatis digunakan untuk memeriksa fungsi utama aplikasi serta mencegah perubahan kode yang melanggar aturan bisnis. Unit test dan feature test dijalankan menggunakan **PHPUnit**.

Lingkungan pengujian menggunakan **SQLite in-memory** sesuai konfigurasi `phpunit.xml`, sehingga pengujian tidak mengubah database MySQL aplikasi.

### Menjalankan Test

Untuk menjalankan pengujian secara lokal, gunakan perintah:

```bash
php artisan test
```

Pada tahap `Test` Jenkins, pengujian dijalankan di dalam container Docker menggunakan perintah:

```bash
vendor/bin/phpunit --log-junit /tmp/junit.xml
```

Hasil JUnit XML dipindahkan ke workspace Jenkins sebagai `test-results/junit.xml` dan dipublikasikan melalui fitur JUnit sehingga hasilnya dapat dipantau pada **Test Result Trend**.

### Pengujian Tambahan QA Engineer

Pengujian tambahan dilakukan melalui file:

`tests/Feature/QABusinessFlowTest.php`

Pengujian ini mencakup tiga skenario utama.

| No. | Skenario Pengujian | Hasil yang Diharapkan |
|---|---|---|
| 1 | Plat nomor yang masih `ACTIVE` mencoba check-in di area parkir lain | Sistem menolak dengan HTTP 409. Setelah check-out, plat dapat digunakan kembali |
| 2 | Kendaraan motor parkir selama tepat 15 menit | Tidak dikenakan biaya atau Rp0 |
| 3 | Kendaraan motor parkir selama 15 menit 1 detik | Dikenakan biaya parkir Rp2.000 |

Ketiga skenario tersebut ditambahkan melalui **Pull Request #11** dan telah digabungkan ke branch `main`.

Hasil regression testing pada Jenkins Build #20 menunjukkan bahwa seluruh **71 automated test berhasil dengan 185 assertions**.

### Demonstrasi Quality Gate Gagal dan Pulih

Quality Gate digunakan untuk memastikan bahwa source code hanya dapat melanjutkan proses build dan deployment apabila seluruh automated test berhasil dijalankan.

Untuk membuktikan mekanisme tersebut, dilakukan simulasi pengujian gagal dan pemulihan melalui Jenkins.

| Jenkins Build | Metode | Hasil | Keterangan |
|---|---|---|---|
| **#19** | Replay dari Build #17 | **FAILURE** | 72 tests, 186 assertions, 1 failure |
| **#20** | Build Now menggunakan Jenkinsfile asli | **SUCCESS** | 71 tests, 185 assertions, seluruh pipeline berhasil |

#### Jenkins Build #19 — FAILED

Pada Build #19, dilakukan simulasi kegagalan menggunakan fitur **Jenkins Replay** dengan menambahkan file test sementara bernama `QualityGateDemoTest.php`.

File tersebut hanya dibuat di dalam container pengujian dan berisi assertion yang sengaja gagal menggunakan `assertTrue(false)`.

Hasil pengujian menunjukkan:

- Total pengujian: **72 tests**
- Total assertions: **186 assertions**
- Pengujian gagal: **1 failure**
- Status pipeline: **FAILURE**

Kegagalan terjadi pada tahap Test sehingga Jenkins tidak melanjutkan tahap Build Image, Push Image, Deploy, dan Smoke Test.

Hasil pengujian tetap direkam melalui JUnit XML agar dapat dianalisis melalui Jenkins.

Simulasi ini dilakukan tanpa mengubah source code maupun Jenkinsfile asli pada repository GitHub.

#### Jenkins Build #20 — SUCCESS

Setelah demonstrasi kegagalan selesai, pipeline dijalankan kembali melalui **Build Now** menggunakan Jenkinsfile asli dari branch `devops/jenkinsfile`.

Pada Build #20, seluruh pengujian berhasil dijalankan dengan hasil:

- Total pengujian: **71 tests**
- Total assertions: **185 assertions**
- Pengujian gagal: **0**
- Status pipeline: **SUCCESS**

Docker image berhasil dibangun dan dikirim ke Docker Hub. Deployment menggunakan Docker Compose juga berhasil dilakukan.

Hasil Smoke Test menunjukkan respons HTTP **200**, dengan kondisi aplikasi dan database berjalan normal.

Perbandingan Build #19 dan Build #20 membuktikan bahwa Quality Gate dapat mencegah deployment apabila pengujian gagal dan mengizinkan proses deployment setelah pengujian berhasil.

Build #19 merupakan simulasi menggunakan Jenkins Replay, sedangkan Build #20 merupakan eksekusi manual menggunakan Jenkinsfile asli dari GitHub.

### Bukti Hasil Quality Assurance

Bukti yang digunakan dalam dokumentasi dan laporan meliputi:

1. Screenshot Stage View Build #19 yang menunjukkan tahap Test gagal.
2. Screenshot Console Output Build #19 yang menunjukkan kegagalan `QualityGateDemoTest`.
3. Screenshot Stage View Build #20 dengan seluruh tahap berhasil.
4. Screenshot Console Output Build #20 yang menunjukkan `OK (71 tests, 185 assertions)`.
5. Screenshot hasil Smoke Test HTTP 200.
6. Screenshot grafik Test Result Trend pada Jenkins.
7. Screenshot Pull Request #11 yang telah di-merge.

## CI/CD Pipeline Jenkins

Pipeline CI/CD ParkirPintar didefinisikan melalui file `Jenkinsfile` pada branch `devops/jenkinsfile`.

Jenkins dapat dijalankan secara otomatis ketika menerima event push dari GitHub melalui webhook dan tunnel ngrok, atau dijalankan secara manual melalui fitur Build Now.

Pipeline terdiri atas enam tahap utama yang dijalankan secara berurutan.

| Tahap | Proses | Kriteria Berhasil |
|---|---|---|
| 1. Checkout | Mengambil source code dari branch `devops/jenkinsfile` | Source code berhasil diambil |
| 2. Test | Membuat Docker image pengujian, menjalankan PHPUnit, dan menghasilkan laporan JUnit XML | Seluruh automated test berhasil |
| 3. Build Image | Membangun Docker image aplikasi menggunakan Dockerfile | Docker image berhasil dibuat |
| 4. Push Image | Mengirim image ke Docker Hub | Docker image berhasil diunggah |
| 5. Deploy | Menjalankan aplikasi melalui Docker Compose dan melakukan migrasi database | Container aplikasi dan database berjalan |
| 6. Smoke Test | Mengakses endpoint `/health` | Menghasilkan HTTP 200 |

Jika terjadi kegagalan pada tahap Test, Jenkins menghentikan proses sehingga tahapan selanjutnya tidak dijalankan.

Pada bagian `post` action, Jenkins tetap memublikasikan laporan JUnit XML apabila tersedia agar hasil automated testing dapat diperiksa.

### UML Activity Diagram Alur CI/CD

Diagram berikut menggambarkan proses CI/CD ParkirPintar dari trigger GitHub hingga deployment, termasuk mekanisme Quality Gate ketika automated testing gagal.

Diagram dibuat menggunakan **draw.io** dalam bentuk **UML Activity Diagram**.

![UML Activity Diagram CI/CD ParkirPintar](docs/CI_CD_ACTIVITY.png)

File diagram yang dapat diedit:

[CI_CD_ACTIVITY.drawio](docs/CI_CD_ACTIVITY.drawio)

File gambar diagram:

[CI_CD_ACTIVITY.png](docs/CI_CD_ACTIVITY.png)

Struktur penyimpanan diagram:

```text
docs/
├── CI_CD_ACTIVITY.drawio
└── CI_CD_ACTIVITY.png
```

## Konfigurasi dan Keamanan

File `.env`, token, dan password tidak boleh di-commit ke repository GitHub.

Gunakan `.env.example` sebagai contoh konfigurasi yang tidak memuat informasi sensitif.

Kredensial Docker Hub dan konfigurasi deployment disimpan melalui **Jenkins Credentials**.

| Variabel | Keterangan |
|---|---|
| `APP_NAME` | Nama aplikasi, yaitu ParkirPintar |
| `APP_ENV` | `local` ketika pengembangan dan `production` saat deployment |
| `APP_KEY` | Dibuat menggunakan `php artisan key:generate` |
| `APP_DEBUG` | Diatur menjadi `false` saat deployment |
| `APP_URL` | `http://localhost:8000` pada lingkungan lokal |
| `DB_CONNECTION` | MySQL untuk aplikasi dan SQLite untuk testing |
| `DB_HOST` | Alamat MySQL atau nama service Docker Compose |
| `DB_PORT` | Port database, umumnya 3306 |
| `DB_DATABASE` | `parkirpintar` |
| `DB_USERNAME` | Username database |
| `DB_PASSWORD` | Password database yang harus disimpan secara aman |

Jenkins dan Docker Engine harus berjalan agar pipeline dapat digunakan.

Apabila menggunakan ngrok, alamat webhook GitHub harus diarahkan menuju endpoint `/github-webhook/` pada alamat publik Jenkins yang aktif.

Jenkins tidak boleh diekspos ke internet tanpa autentikasi dan pengamanan yang sesuai.

## Alur Kerja Git Tim

Pengembangan ParkirPintar menggunakan GitHub sebagai tempat penyimpanan source code dan pengelolaan kontribusi anggota.

Aturan kerja yang diterapkan:

1. Setiap anggota bekerja menggunakan branch sesuai tanggung jawabnya.
2. Perubahan tidak langsung di-push ke branch `main`, tetapi melalui Pull Request.
3. Sebelum commit, anggota harus memastikan perubahan tidak mengandung file rahasia seperti `.env` atau token.
4. Pengujian dilakukan untuk memastikan perubahan tidak merusak fungsi yang sudah berjalan.
5. Pull Request diperiksa terlebih dahulu sebelum digabungkan ke branch `main`.

Contoh branch yang digunakan:

- `qa/automated-tests` untuk pengujian QA.
- `docs/qa-pipeline` untuk dokumentasi QA dan CI/CD.
- `devops/jenkinsfile` untuk konfigurasi pipeline Jenkins.

Contoh perintah commit dokumentasi:

```bash
git add README.md docs/
git commit -m "docs(qa): document pipeline and quality gate results"
git push -u origin docs/qa-pipeline
```

## Catatan Deployment

Deployment ParkirPintar dilakukan melalui Jenkins menggunakan Docker dan Docker Compose.

- Docker image aplikasi dibuat menggunakan `Dockerfile`.
- Image dikirim ke Docker Hub dengan repository `fdhil17/parkirpintar`.
- Deployment menggunakan file `compose.deploy.yaml`.
- Database aplikasi menggunakan MySQL 8.4.
- Data MySQL disimpan menggunakan volume Docker agar tetap tersedia ketika container dibuat ulang.
- Migrasi database dijalankan menggunakan `php artisan migrate --force`.
- Smoke Test mengakses endpoint `http://127.0.0.1:8000/health`.

Pada Jenkins Build #20, Docker image berhasil dibuat dengan tag:

```text
fdhil17/parkirpintar:20
fdhil17/parkirpintar:latest
```

Hasil deployment menunjukkan container aplikasi berjalan, database MySQL berstatus healthy, serta endpoint `/health` memberikan respons:

```json
{"status":"ok","database":"up"}
```

## Troubleshooting

| Masalah | Penyebab atau Solusi |
|---|---|
| `could not find driver` | Aktifkan ekstensi PHP `pdo_mysql` atau `pdo_sqlite` |
| `No application encryption key has been generated` | Jalankan `php artisan key:generate` |
| `composer install` gagal | Periksa koneksi internet dan akses Packagist |
| Docker API tidak dapat dihubungi | Jalankan Docker Desktop dan pastikan Docker Engine aktif |
| GitHub Webhook HTTP 404 | Periksa alamat ngrok dan endpoint `/github-webhook/` |
| Port 8000 sudah digunakan | Hentikan proses yang menggunakan port tersebut atau gunakan port lain |
| Respons API bukan JSON | Tambahkan header `Accept: application/json` |
| Automated testing gagal | Periksa Console Output dan laporan JUnit pada Jenkins |

## Hasil Akhir Implementasi

Pada tanggal **28 November 2026**, pengujian Quality Gate berhasil didemonstrasikan melalui Jenkins Build #19 dan Build #20.

Build #19 memperlihatkan bahwa satu automated test yang gagal menyebabkan Jenkins menghentikan proses sebelum build dan deployment.

Build #20 membuktikan bahwa setelah kembali menggunakan konfigurasi normal, seluruh **71 automated test dengan 185 assertions** berhasil dijalankan.

Pipeline kemudian melanjutkan proses Build Image, Push Image, Deploy, dan Smoke Test hingga memperoleh status **SUCCESS** dengan respons HTTP **200**.

Hasil ini menunjukkan bahwa automated testing, Quality Gate, dan proses CI/CD telah terintegrasi pada proyek ParkirPintar.

