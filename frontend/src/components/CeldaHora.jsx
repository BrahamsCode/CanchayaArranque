import { formatearHora } from '../utils/horas.js'

const ETIQUETAS = { libre: 'Libre', separado: 'Separado', ocupado: 'Ocupado' }

export default function CeldaHora({ slot, pasado, clicable, seleccionada, detalle, onSeleccionar }) {
  const clases = [
    'celda',
    `celda--${slot.estado}`,
    pasado ? 'celda--pasado' : '',
    seleccionada ? 'celda--activa' : '',
  ].join(' ')

  return (
    <button type="button" className={clases} disabled={!clicable} onClick={() => onSeleccionar(slot)}>
      <span className="celda__hora">
        {formatearHora(slot.hora_inicio)} - {formatearHora(slot.hora_fin)}
      </span>
      <span className="celda__estado">{pasado ? 'Ya pasó' : ETIQUETAS[slot.estado] ?? slot.estado}</span>
      {detalle ? <span className="celda__detalle">{detalle}</span> : null}
    </button>
  )
}
