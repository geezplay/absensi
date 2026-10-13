# Deploy Absensi Sekolah

Deploy produksi ke VPS Ubuntu (22.04/24.04) dengan Laravel + Nginx + PHP-FPM + PostgreSQL + Node/Vite.

- **Domain:** `absensi.geezplay.site`
- **App dir:** `/var/www/absensi`
- **Owner:** `www-data:www-data`
- **Database:** `absensi_prod` (user `absensi`)
- **PHP:** 8.3 (`php8.3-fpm`)

## Prasyarat

1. DNS A record `absensi.geezplay.site` → IP VPS.
2. Akses SSH (idealnya user `deploy` dengan sudo, bukan root).
3. Stack terpasang (script akan memasang otomatis bila kurang):
   `nginx`, `php8.3-fpm` + ekstensi (`pgsql, mbstring, xml, curl, zip, bcmath, gd`),
   `postgresql`, `node` 22, `composer`, `certbot`.

## Deploy pertama kali

```bash
sudo git clone https://github.com/geezplay/absensi.git /var/www/absensi
cd /var/www/absensi
sudo ./deploy.sh          # konfirmasi "y"
```

Script mengerjakan: buat DB+user → tulis `.env` produksi → `composer install` →
`npm ci && npm run build` → `migrate --seed` → vhost Nginx → SSL certbot →
cache config/route/view → reload php-fpm.

Di akhir, script mencetak **URL, kredensial DB, dan akun super admin** yang
di-generate otomatis. Ganti password admin saat login pertama (dipaksa sistem).

## Update berikutnya

```bash
cd /var/www/absensi
sudo ./deploy.sh --update
```

Menjalankan `git reset --hard origin/main` + build + migrate + cache + reload,
tanpa menyentuh database/akun.

## Variabel lingkungan script (opsional)

| Variabel | Default |
|---|---|
| `DOMAIN` | `absensi.geezplay.site` |
| `APP_DIR` | `/var/www/absensi` |
| `APP_USER` | `www-data` |
| `PHP_VER` | `8.3` |
| `DB_NAME` / `DB_USER` | `absensi_prod` / `absensi` |
| `DB_PASS` / `ADMIN_PASS` | di-generate otomatis |
| `ADMIN_EMAIL` | `admin@<DOMAIN>` |

Contoh override:

```bash
sudo DB_PASS='rahasia' ADMIN_PASS='rahasia' ./deploy.sh
```

## Operasional

```bash
# Log aplikasi
tail -f /var/www/absensi/storage/logs/laravel.log

# Cek service
sudo systemctl status php8.3-fpm nginx postgresql

# Backup database harian (crontab root/deploy)
0 2 * * * pg_dump -U absensi absensi_prod | gzip > /home/deploy/backup/absensi_$(date +\%F).sql.gz
```

## Catatan

- `deploy/nginx.conf` adalah contoh manual; vhost sebenarnya dibuat oleh script
  di `/etc/nginx/sites-available/absensi`.
- Script **tidak** menghapus `sites-enabled/default` dan tidak menyentuh vhost
  `geezplay`, `sidesa`, `api.*`.
- Mode `full` menjalankan `migrate --seed` (admin awal). Mode `--update` hanya
  `migrate` (tanpa seed).
