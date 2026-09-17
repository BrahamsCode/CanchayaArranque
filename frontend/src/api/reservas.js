import { pedir } from './cliente.js'

// Misma grilla que disponibilidad, pero con los datos del cliente.
export function obtenerReservasAdmin(canchaId, fecha) {
  return pedir(`/admin/reservas?cancha_id=${encodeURIComponent(canchaId)}&fecha=${encodeURIComponent(fecha)}`)
}

export function crearReserva(datos) {
  return pedir('/admin/reservas', { metodo: 'POST', cuerpo: datos })
}

export function actualizarReserva(id, datos) {
  return pedir(`/admin/reservas/${id}`, { metodo: 'PATCH', cuerpo: datos })
}

export function eliminarReserva(id) {
  return pedir(`/admin/reservas/${id}`, { metodo: 'DELETE' })
}
