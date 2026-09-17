# CanchaYa

Sistema de reservas **super sencillo** para una cancha deportiva.
El cliente ve la disponibilidad y reserva por WhatsApp; el admin confirma y marca las horas.

- **Cliente** (público, sin login): elige fecha, ve las horas del día en colores y toca una hora libre para abrir WhatsApp con el mensaje ya escrito.
- **Admin** (con login): la misma grilla pero con los datos del cliente, y puede separar, ocupar, editar o liberar cada hora.

Los bloques son fijos de **1 hora**. Una fila en `reservas` es una hora tomada; si no hay fila, la hora está libre.

| Color | Estado | Significado |
|---|---|---|
| 🟢 | `libre` | se puede reservar |
| 🟡 | `separado` | alguien la pidió, pendiente de pago/confirmación |
| 🔴 | `ocupado` | confirmado |

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | Laravel 11 (solo API) + Laravel Sanctum (tokens) |
| Frontend | React 18 + Vite + React Router + `fetch` nativo |
| Estilos | CSS plano con variables. Sin frameworks UI |
| BD | PostgreSQL 16 |
| Entorno | Docker Compose (probado en OrbStack) |
| Zona horaria | `America/Lima` en Laravel y en la BD |

---

## Levantar con Docker

Requisitos: Docker (o OrbStack) con `docker compose`.

```bash
cp .env.example .env        # opcional: solo si quieres cambiar puertos o credenciales
docker compose up --build
```

Eso arranca tres servicios:

| Servicio | Qué hace | URL |
|---|---|---|
| `db` | PostgreSQL 16 con volumen persistente | `localhost:5432` |
| `backend` | Instala dependencias, migra, siembra y sirve el API | http://localhost:8000 |
| `frontend` | `npm install` + `vite dev` | http://localhost:5173 |

El contenedor del backend se encarga solo de `composer install`, del `.env`, del `APP_KEY` y de
`php artisan migrate --seed`, así que la primera vez tarda un poco más.

Cuando terminen de arrancar:

- Cliente → http://localhost:5173
- Admin → http://localhost:5173/admin/login
- API → http://localhost:8000/api/configuracion-publica

**Credenciales sembradas** (cambiar en producción):

```
admin@canchaya.test / password
```

Para apagar todo y borrar la base:

```bash
docker compose down -v
```

---

## Levantar sin Docker (desarrollo local)

Requisitos: PHP 8.2+ con `pdo_pgsql`, Composer, Node 20+, y un PostgreSQL 16 accesible.

```bash
# 1. Base de datos
createdb canchaya

# 2. Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
# ajusta DB_HOST=127.0.0.1 en .env
php artisan migrate --seed
php artisan serve --port=8000

# 3. Frontend (en otra terminal)
cd frontend
npm install
npm run dev
```

El `vite.config.js` hace proxy de `/api` hacia `http://localhost:8000` (o hacia
`VITE_API_PROXY_TARGET` si está definida), así que el navegador siempre habla con un solo origen.

---

## Estructura

```
canchaya/
├── TASKS.md                  # checklist de las 7 fases
├── docker-compose.yml
├── .env.example              # puertos y credenciales que consume Compose
├── backend/                  # Laravel 11, solo API
│   ├── app/Http/Controllers/Public/   # disponibilidad y configuración pública
│   ├── app/Http/Controllers/Admin/    # auth, reservas, configuración
│   ├── app/Http/Requests/             # validaciones
│   ├── app/Models/
│   ├── database/migrations/
│   ├── database/seeders/
│   ├── tests/Feature/                 # criterios de aceptación del API
│   └── routes/api.php
└── frontend/                 # React + Vite
    └── src/
        ├── api/              # un archivo por recurso + wrapper de fetch
        ├── components/
        ├── hooks/
        ├── pages/
        ├── utils/            # horas y link de WhatsApp
        └── styles/
```

---

## Pruebas

```bash
cd backend
php artisan test        # cubre los criterios de aceptación del API
```

```bash
cd frontend
npm run build           # compila
npm run lint            # oxlint
```

---

## Fuera de alcance

Pagos en línea, registro de clientes, notificaciones automáticas, reportes, multi-sede,
bloques de 30 minutos y reservas recurrentes. La tabla `canchas` existe para no rehacer el
modelo si más adelante piden una segunda cancha: la UI pública muestra el selector solo
cuando hay más de una activa.
