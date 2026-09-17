import CeldaHora from './CeldaHora.jsx'
import { slotYaPaso } from '../utils/horas.js'

export default function GrillaHoras({ slots, fecha, seleccion, onSeleccionar, modoAdmin = false }) {
  if (!slots.length) return <p className="aviso">No hay horarios para este día.</p>

  return (
    <div className="grilla">
      {slots.map((slot) => {
        const pasado = slotYaPaso(fecha, slot.hora_inicio)
        // En público solo se toca lo libre y no pasado; en admin todo abre el modal.
        const clicable = modoAdmin || (slot.estado === 'libre' && !pasado)
        return (
          <CeldaHora
            key={slot.hora_inicio}
            slot={slot}
            pasado={pasado}
            clicable={clicable}
            seleccionada={seleccion?.hora_inicio === slot.hora_inicio}
            detalle={modoAdmin ? slot.cliente_nombre : null}
            onSeleccionar={onSeleccionar}
          />
        )
      })}
    </div>
  )
}
