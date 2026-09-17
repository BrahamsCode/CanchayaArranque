import { esHoy, formatearFechaLegible, hoyIso, sumarDias } from '../utils/horas.js'

export default function SelectorFecha({ fecha, onCambiar }) {
  return (
    <div className="selector-fecha">
      <div className="selector-fecha__controles">
        <button type="button" className="boton boton--icono" aria-label="Día anterior" onClick={() => onCambiar(sumarDias(fecha, -1))}>
          ←
        </button>
        <button type="button" className="boton boton--secundario" disabled={esHoy(fecha)} onClick={() => onCambiar(hoyIso())}>
          Hoy
        </button>
        <button type="button" className="boton boton--icono" aria-label="Día siguiente" onClick={() => onCambiar(sumarDias(fecha, 1))}>
          →
        </button>
      </div>
      <input
        type="date"
        className="campo selector-fecha__input"
        aria-label="Fecha"
        value={fecha}
        onChange={(evento) => evento.target.value && onCambiar(evento.target.value)}
      />
      <p className="selector-fecha__texto">{formatearFechaLegible(fecha)}</p>
    </div>
  )
}
