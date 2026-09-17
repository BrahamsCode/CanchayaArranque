#!/usr/bin/env sh
# Arranque del contenedor: dependencias, .env, migraciones y servidor.
set -e

cd /var/www/html

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  echo "==> Instalando dependencias de Composer..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
  echo "==> Creando .env desde .env.example..."
  cp .env.example .env
fi

# APP_KEY vacío rompe el cifrado de sesiones/tokens, así que la generamos si falta.
if ! grep -q "^APP_KEY=base64:" .env; then
  php artisan key:generate --force
fi

echo "==> Migrando y sembrando la base..."
php artisan migrate --force --seed

php artisan serve --host=0.0.0.0 --port=8000
