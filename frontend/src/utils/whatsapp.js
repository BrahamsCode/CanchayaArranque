import { formatearFecha, formatearHora } from './horas.js'

// Arma el link de WhatsApp con el mensaje ya escrito para el cliente.
export function armarLinkWhatsapp({ numero, cancha, fecha, horaInicio, horaFin }) {
  const mensaje =
    `Hola, quiero reservar la cancha *${cancha}* el *${formatearFecha(fecha)}*` +
    ` de *${formatearHora(horaInicio)}* a *${formatearHora(horaFin)}*.`
  // wa.me solo acepta dígitos (sin +, espacios ni guiones).
  const numeroLimpio = String(numero ?? '').replace(/\D/g, '')
  return `https://wa.me/${numeroLimpio}?text=${encodeURIComponent(mensaje)}`
}
