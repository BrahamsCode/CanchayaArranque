# TASKS — CanchaYa

Checklist de las 7 fases del documento de arranque.

## Fase 1 — Estructura + Docker
- [x] Estructura de carpetas `backend/` y `frontend/`
- [x] Laravel 11 instalado (solo API) con Sanctum
- [x] React 18 + Vite + React Router instalados
- [x] `docker-compose.yml` con `db`, `backend` y `frontend`
- [x] Proxy de Vite `/api` → `http://backend:8000`
- [x] CORS limitado al origen del frontend
- [x] Zona horaria `America/Lima` en Laravel y en Postgres
- [x] `.env.example` (raíz y backend)
- [x] README con instrucciones para levantar

## Fase 2 — Base de datos
- [x] Migración `canchas`
- [x] Migración `reservas` (UNIQUE cancha_id+fecha+hora_inicio, CHECK estado, índice)
- [x] Migración `configuracion` (fila única id=1)
- [x] Modelos `Cancha`, `Reserva`, `Configuracion`, `User` (con `HasApiTokens`)
- [x] `DatabaseSeeder`: admin, cancha, configuración y reservas de ejemplo

## Fase 3 — API pública
- [x] `GET /api/configuracion-publica`
- [x] `GET /api/disponibilidad?cancha_id=&fecha=`
- [x] Generación de slots desde apertura/cierre cruzada con reservas
- [x] La respuesta pública nunca incluye nombre ni teléfono

## Fase 4 — Auth admin + API admin
- [x] `POST /api/admin/login` con `throttle:5,1`
- [x] `POST /api/admin/logout`
- [x] `GET /api/admin/reservas`
- [x] `POST /api/admin/reservas` (409 si la hora ya está tomada)
- [x] `PATCH /api/admin/reservas/{id}`
- [x] `DELETE /api/admin/reservas/{id}`
- [x] `GET|PUT /api/admin/configuracion`
- [x] Validaciones: hora en punto, dentro del horario, no en el pasado
- [x] Escrituras dentro de `DB::transaction()`

## Fase 5 — Frontend público
- [x] `PaginaPublica` con `SelectorFecha` y `GrillaHoras`
- [x] Colores por estado + leyenda siempre visible
- [x] Horas pasadas deshabilitadas
- [x] Panel inferior con botón "Reservar por WhatsApp"
- [x] `utils/whatsapp.js` con `armarLinkWhatsapp`
- [x] Botón "Actualizar" y refresco automático cada 60 s

## Fase 6 — Frontend admin
- [x] `Login` + token en `localStorage`
- [x] Rutas protegidas `/admin` y `/admin/configuracion`
- [x] `PanelAdmin` con grilla detallada
- [x] `ModalReserva` (separar, ocupar, cambiar, liberar)
- [x] Pantalla de `Configuracion`
- [x] 401 de la API → redirige a login

## Fase 7 — Pulido
- [x] Mobile first (una columna en móvil, botones grandes)
- [x] Mensajes de error visibles en cliente y admin
- [x] Tests de feature del backend cubriendo los criterios de aceptación
- [x] Revisión de los criterios de aceptación de la sección 8
