#!/usr/bin/env bash
#
# ============================================================
#  ABSENSI SEKOLAH — Script Deploy Otomatis VPS (Ubuntu 22.04/24.04)
#  Stack   : Laravel 12 + Nginx + PHP-FPM + PostgreSQL + Node/Vite
#  Domain  : absensi.geezplay.site
#  Pola    : mengikuti /var/www/sidesa/deploy.sh
# ============================================================
#
#  Cara pakai:
#    sudo ./deploy.sh              # instalasi penuh + deploy
#    sudo ./deploy.sh --update     # update kode & deploy ulang
#
set -euo pipefail

# ====================== KONFIGURASI ======================
DOMAIN="${DOMAIN:-absensi.geezplay.site}"
APP_DIR="${APP_DIR:-/var/www/absensi}"
APP_USER="${APP_USER:-www-data}"
REPO_URL="${REPO_URL:-https://github.com/geezplay/absensi.git}"

DB_NAME="${DB_NAME:-absensi_prod}"
DB_USER="${DB_USER:-absensi}"
DB_PASS="${DB_PASS:-}"                       # kosong -> dibuat otomatis

ADMIN_EMAIL="${ADMIN_EMAIL:-admin@${DOMAIN}}"
ADMIN_PASS="${ADMIN_PASS:-}"                 # kosong -> dibuat otomatis

PHP_VER="${PHP_VER:-8.3}"
NODE_MAJOR="${NODE_MAJOR:-22}"

MODE="full"
if [[ "${1:-}" == "--update" ]]; then MODE="update"; fi

# ====================== WARNA LOG ======================
C_G="\033[1;32m"; C_Y="\033[1;33m"; C_R="\033[1;31m"; C_B="\033[1;36m"; C_N="\033[0m"
log()  { echo -e "${C_G}➜${C_N} $*"; }
warn() { echo -e "${C_Y}⚠${C_N} $*"; }
err()  { echo -e "${C_R}✖${C_N} $*" >&2; }
step() { echo -e "\n${C_B}==== $* ====${C_N}"; }

require_root() {
  if [[ "$(id -u)" -ne 0 ]]; then err "Jalankan sebagai root: sudo ./deploy.sh"; exit 1; fi
}

gen_secret() { openssl rand -base64 24 | tr -d '/+=' | cut -c1-20; }

# ====================== INSTALL STACK (bila belum ada) ======================
install_stack() {
  local need=0
  command -v nginx >/dev/null 2>&1 || need=1
  command -v "php${PHP_VER}" >/dev/null 2>&1 || need=1
  command -v psql >/dev/null 2>&1 || need=1
  command -v composer >/dev/null 2>&1 || need=1
  command -v node >/dev/null 2>&1 || need=1
  command -v certbot >/dev/null 2>&1 || need=1

  if [[ "$need" -eq 0 ]]; then
    log "Stack sudah terpasang (nginx, php${PHP_VER}, postgres, node, composer, certbot)."
    return
  fi

  step "Update sistem & install paket yang kurang"
  export DEBIAN_FRONTEND=noninteractive
  apt-get update -y
  apt-get install -y curl git unzip ca-certificates gnupg lsb-release \
    nginx postgresql postgresql-contrib certbot python3-certbot-nginx \
    "php${PHP_VER}-fpm" "php${PHP_VER}-cli" "php${PHP_VER}-mbstring" "php${PHP_VER}-xml" \
    "php${PHP_VER}-curl" "php${PHP_VER}-zip" "php${PHP_VER}-bcmath" "php${PHP_VER}-gd" \
    "php${PHP_VER}-pgsql"

  if ! command -v composer >/dev/null 2>&1; then
    log "Install Composer"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  fi

  if ! command -v node >/dev/null 2>&1; then
    log "Install Node.js ${NODE_MAJOR}"
    curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash -
    apt-get install -y nodejs
  fi
}

# ====================== REPO ======================
ensure_repo() {
  if [[ ! -d "${APP_DIR}/.git" ]]; then
    step "Clone repositori"
    mkdir -p "$(dirname "${APP_DIR}")"
    git clone "${REPO_URL}" "${APP_DIR}"
  fi
}

# ====================== DATABASE ======================
setup_database() {
  step "Konfigurasi PostgreSQL"

  # Reuse password dari .env bila ada agar idempoten
  if [[ -f "${APP_DIR}/.env" ]]; then
    local existing
    existing="$(grep -E '^DB_PASSWORD=' "${APP_DIR}/.env" | head -1 | cut -d= -f2- | tr -d '"')"
    [[ -n "${existing}" ]] && DB_PASS="${existing}"
  fi
  if [[ -z "${DB_PASS}" ]]; then
    DB_PASS="$(gen_secret)"
    warn "DB_PASS dibuat otomatis: ${DB_PASS}"
  fi

  if ! sudo -u postgres psql -tc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1; then
    sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';"
  else
    sudo -u postgres psql -c "ALTER USER ${DB_USER} WITH ENCRYPTED PASSWORD '${DB_PASS}';" >/dev/null
  fi

  if ! sudo -u postgres psql -tc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1; then
    sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME};"
  fi

  sudo -u postgres psql -c "ALTER DATABASE ${DB_NAME} OWNER TO ${DB_USER};" >/dev/null
  log "Database ${DB_NAME} & user ${DB_USER} siap."
}

# ====================== ENV ======================
set_env() {
  local key="$1" val="$2"
  if grep -q "^${key}=" "${APP_DIR}/.env"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "${APP_DIR}/.env"
  else
    echo "${key}=${val}" >> "${APP_DIR}/.env"
  fi
}

write_env() {
  step "Konfigurasi .env produksi"
  cd "${APP_DIR}"

  if [[ ! -f .env ]]; then
    if [[ -f .env.production.example ]]; then cp .env.production.example .env;
    else touch .env; fi
  fi

  if [[ -z "${ADMIN_PASS}" ]]; then ADMIN_PASS="$(gen_secret)"; fi

  set_env APP_NAME "Absensi"
  set_env APP_ENV production
  set_env APP_DEBUG false
  set_env APP_URL "https://${DOMAIN}"
  set_env APP_LOCALE id
  set_env APP_FALLBACK_LOCALE id
  set_env LOG_LEVEL warning

  set_env DB_CONNECTION pgsql
  set_env DB_HOST 127.0.0.1
  set_env DB_PORT 5432
  set_env DB_DATABASE "${DB_NAME}"
  set_env DB_USERNAME "${DB_USER}"
  set_env DB_PASSWORD "${DB_PASS}"

  set_env SESSION_DRIVER database
  set_env SESSION_LIFETIME 720
  set_env SESSION_DOMAIN ".geezplay.site"
  set_env SESSION_SECURE_COOKIE true
  set_env SESSION_HTTP_ONLY true
  set_env SESSION_SAME_SITE lax

  set_env CACHE_STORE database
  set_env QUEUE_CONNECTION database

  set_env SEED_ADMIN_EMAIL "${ADMIN_EMAIL}"
  set_env SEED_ADMIN_PASSWORD "${ADMIN_PASS}"
  log ".env disetel untuk produksi (${DOMAIN})."
}

# ====================== DEPLOY APP ======================
deploy_app() {
  step "Deploy aplikasi Laravel"
  cd "${APP_DIR}"

  # Pastikan www-data bisa menulis (vendor/node_modules/.env) sebelum build
  chown -R "${APP_USER}:${APP_USER}" "${APP_DIR}"

  sudo -u "${APP_USER}" composer install --no-dev --optimize-autoloader --no-interaction
  sudo -u "${APP_USER}" npm ci || sudo -u "${APP_USER}" npm install
  sudo -u "${APP_USER}" npm run build

  if ! grep -q '^APP_KEY=base64' .env; then
    sudo -u "${APP_USER}" php artisan key:generate --force
  fi

  sudo -u "${APP_USER}" php artisan storage:link >/dev/null 2>&1 || true

  if [[ "${MODE}" == "full" ]]; then
    sudo -u "${APP_USER}" php artisan migrate --seed --force
  else
    sudo -u "${APP_USER}" php artisan migrate --force
  fi

  sudo -u "${APP_USER}" php artisan optimize:clear
  sudo -u "${APP_USER}" php artisan config:cache
  sudo -u "${APP_USER}" php artisan route:cache
  sudo -u "${APP_USER}" php artisan view:cache

  chown -R "${APP_USER}:${APP_USER}" "${APP_DIR}"
  chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
  log "Aplikasi siap."
}

# ====================== NGINX ======================
setup_nginx() {
  step "Konfigurasi Nginx"
  cat > "/etc/nginx/sites-available/absensi" <<EOF
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VER}-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
    client_max_body_size 10M;
}
EOF

  ln -sf /etc/nginx/sites-available/absensi /etc/nginx/sites-enabled/absensi
  nginx -t
  systemctl reload nginx
  log "Nginx dikonfigurasi (hanya vhost absensi; vhost lain tidak disentuh)."
}

# ====================== SSL ======================
setup_ssl() {
  step "Pasang SSL Let's Encrypt"
  if certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos \
       -m "${ADMIN_EMAIL}" --redirect; then
    log "SSL terpasang & HTTP dialihkan ke HTTPS."
  else
    warn "SSL gagal otomatis. Pastikan DNS ${DOMAIN} mengarah ke VPS, lalu jalankan:"
    warn "  sudo certbot --nginx -d ${DOMAIN} --redirect"
  fi
}

# ====================== RESTART ======================
restart_services() {
  step "Reload layanan"
  systemctl reload "php${PHP_VER}-fpm"
  systemctl reload nginx
  log "Selesai."
}

# ====================== RINGKASAN ======================
print_summary() {
  echo -e "\n${C_G}==================================================${C_N}"
  echo -e "${C_G} ABSENSI berhasil dideploy!${C_N}"
  echo -e " Web     : https://${DOMAIN}"
  echo -e " App Dir : ${APP_DIR}  (owner ${APP_USER})"
  echo -e " DB      : ${DB_NAME} (user: ${DB_USER})"
  echo -e " DB PASS : ${DB_PASS}"
  echo -e " Admin   : ${ADMIN_EMAIL}"
  echo -e " Admin PW: ${ADMIN_PASS}   (wajib ganti saat login pertama)"
  echo -e "${C_G}==================================================${C_N}"
  echo -e "Perintah berguna:"
  echo -e "  cd ${APP_DIR} && sudo -u ${APP_USER} php artisan about"
  echo -e "  tail -f ${APP_DIR}/storage/logs/laravel.log"
  echo -e "  sudo systemctl status php${PHP_VER}-fpm"
}

# ====================== ALUR UTAMA ======================
main() {
  require_root

  if [[ "${MODE}" == "update" ]]; then
    step "MODE UPDATE"
    ensure_repo
    if [[ -d "${APP_DIR}/.git" ]]; then
      git -C "${APP_DIR}" fetch origin main
      git -C "${APP_DIR}" reset --hard origin/main
    fi
    install_stack
    # Ambil password dari .env agar tidak berubah
    if [[ -f "${APP_DIR}/.env" ]]; then
      DB_PASS="$(grep -E '^DB_PASSWORD=' "${APP_DIR}/.env" | head -1 | cut -d= -f2- | tr -d '"')"
      ADMIN_PASS="$(grep -E '^SEED_ADMIN_PASSWORD=' "${APP_DIR}/.env" | head -1 | cut -d= -f2- | tr -d '"')"
    fi
    deploy_app
    restart_services
    print_summary
    exit 0
  fi

  warn "Script akan: buat DB, tulis .env, deploy Laravel, set Nginx, dan SSL."
  warn "Domain  : ${DOMAIN}"
  warn "App Dir : ${APP_DIR}"
  read -rp "Lanjutkan instalasi penuh? (y/N): " ok
  [[ "${ok,,}" == "y" ]] || { err "Dibatalkan."; exit 1; }

  install_stack
  ensure_repo
  setup_database
  write_env
  deploy_app
  setup_nginx
  setup_ssl
  restart_services
  print_summary
}

main
