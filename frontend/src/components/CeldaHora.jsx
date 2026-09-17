import { formatearHora } from '../utils/horas.js'

const ETIQUETAS = { libre: 'Libre', separado: 'Separado', ocupado: 'Ocupado' }

export default function CeldaHora({ slot, pasado, clicable, seleccionada, detalle, onSeleccionar }) {
  // Una hora pasada sin reserva es solo "ya pasó"; si hubo reserva hay que seguir
  // viendo si quedó separada u ocupada, porque el admin todavía puede tocarla.
  const vacioYPasado = pasado && slot.estado === 'libre'

  const clases = [
    'celda',
    `celda--${slot.estado}`,
    vacioYPasado ? 'celda--pasado' : '',
    pasado && !vacioYPasado ? 'celda--historico' : '',
    seleccionada ? 'celda--activa' : '',
  ].join(' ')

  const estado = vacioYPasado
    ? 'Ya pasó'
    : `${ETIQUETAS[slot.estado] ?? slot.estado}${pasado ? ' · ya pasó' : ''}`

  return (
    <button type="button" className={clases} disabled={!clicable} onClick={() => onSeleccionar(slot)}>
      <span className="celda__hora">
        {formatearHora(slot.hora_inicio)} - {formatearHora(slot.hora_fin)}
      </span>
      <span className="celda__estado">{estado}</span>
      {detalle ? <span className="celda__detalle">{detalle}</span> : null}
    </button>
  )
}
