import { pedir } from './cliente.js'

export function obtenerDisponibilidad(canchaId, fecha) {
  return pedir(`/disponibilidad?cancha_id=${encodeURIComponent(canchaId)}&fecha=${encodeURIComponent(fecha)}`)
}
