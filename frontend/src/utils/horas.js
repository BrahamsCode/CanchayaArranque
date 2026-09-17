// Las fechas viajan como texto 'yyyy-mm-dd'. Se manipulan partiendo el string
// porque new Date('2026-09-20') se interpreta en UTC y en Lima (UTC-5) devuelve el día anterior.

function aIso(fecha) {
  const anio = fecha.getFullYear()
  const mes = String(fecha.getMonth() + 1).padStart(2, '0')
  const dia = String(fecha.getDate()).padStart(2, '0')
  return `${anio}-${mes}-${dia}`
}

export function hoyIso() {
  return aIso(new Date())
}

// 'yyyy-mm-dd' -> 'dd/mm/yyyy'
export function formatearFecha(iso) {
  const [anio, mes, dia] = String(iso).split('-')
  return `${dia}/${mes}/${anio}`
}

// Texto amable para el encabezado, ej. "domingo 20 de septiembre"
export function formatearFechaLegible(iso) {
  const [anio, mes, dia] = String(iso).split('-').map(Number)
  const fecha = new Date(anio, mes - 1, dia)
  return fecha.toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long' })
}

export function sumarDias(iso, dias) {
  const [anio, mes, dia] = String(iso).split('-').map(Number)
  const fecha = new Date(anio, mes - 1, dia) // constructor local: no hay salto de zona horaria
  fecha.setDate(fecha.getDate() + dias)
  return aIso(fecha)
}

export function esHoy(iso) {
  return iso === hoyIso()
}

// El backend puede mandar 'HH:mm' o 'HH:mm:ss'; en pantalla siempre 'HH:mm'.
export function formatearHora(hora) {
  return String(hora).slice(0, 5)
}

// Un slot "ya pasó" si su hora de inicio quedó atrás. Solo tiene sentido comparar
// contra el reloj cuando la fecha elegida es hoy.
export function slotYaPaso(fechaIso, horaInicio) {
  const hoy = hoyIso()
  if (fechaIso > hoy) return false
  if (fechaIso < hoy) return true
  const ahora = new Date()
  const horaActual = `${String(ahora.getHours()).padStart(2, '0')}:${String(ahora.getMinutes()).padStart(2, '0')}`
  return formatearHora(horaInicio) <= horaActual
}
