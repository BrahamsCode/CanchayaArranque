# CanchaYa — Frontend

React 18 + Vite. Consume el API de Laravel que vive en `../backend`.

## Correr

```bash
npm install
npm run dev        # http://localhost:5173
```

Las llamadas van siempre a rutas relativas `/api/...`. `vite.config.js` las manda al backend:
a `http://localhost:8000` por defecto, o a `VITE_API_PROXY_TARGET` si está definida (es lo que
usa `docker-compose.yml` para apuntar al servicio `backend`).

Necesitas el backend levantado; mira el README de la raíz.

## Scripts

| Comando | Qué hace |
|---|---|
| `npm run dev` | servidor de desarrollo |
| `npm run build` | build de producción en `dist/` |
| `npm run preview` | sirve el build |
| `npm run lint` | oxlint |

## Estructura

```
src/
├── api/            # un archivo por recurso; cliente.js es el único que llama a fetch
├── components/     # SelectorFecha, SelectorCancha, GrillaHoras, CeldaHora, Leyenda,
│                   # MensajeError y ModalReserva (solo admin)
├── hooks/          # useAuth (token) y useDisponibilidad (grilla + recarga)
├── pages/          # PaginaPublica, Login, PanelAdmin, Configuracion
├── utils/          # horas.js (fechas y slots pasados) y whatsapp.js (link wa.me)
└── styles/         # variables.css, base.css, componentes.css
```

## Detalles que conviene saber

- **Las fechas viajan como texto `yyyy-mm-dd`** y se parten a mano. Nunca se usa
  `new Date('2026-09-20')`: eso se interpreta en UTC y en Lima (UTC−5) devuelve el día anterior.
- **"Ya pasó"** se calcula contra el reloj del navegador y solo cuando la fecha elegida es hoy.
  Es una ayuda visual: quien decide de verdad es el backend, que rechaza con 422 cualquier
  reserva en una fecha u hora pasada según `America/Lima`.
- **Una hora pasada con reserva conserva su color** en el panel admin, porque el admin todavía
  puede liberarla o cambiarle el estado. Solo las horas pasadas y libres se ven grises.
- **El token vive en `localStorage`.** Cualquier 401 del API lo borra y devuelve al login.
