// Solo tiene sentido mostrarlo cuando el negocio tiene más de una cancha.
export default function SelectorCancha({ canchas, canchaId, onCambiar }) {
  if (!canchas || canchas.length < 2) return null

  return (
    <label className="campo-etiqueta">
      Cancha
      <select className="campo" value={canchaId ?? ''} onChange={(evento) => onCambiar(Number(evento.target.value))}>
        {canchas.map((cancha) => (
          <option key={cancha.id} value={cancha.id}>
            {cancha.nombre}
          </option>
        ))}
      </select>
    </label>
  )
}
