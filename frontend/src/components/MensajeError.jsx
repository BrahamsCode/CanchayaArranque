// Muestra el mensaje del backend y, si vino un 422, el detalle campo por campo.
export default function MensajeError({ error, errores }) {
  const detalles = Object.values(errores ?? {}).flat()
  if (!error && !detalles.length) return null

  return (
    <div className="mensaje mensaje--error" role="alert">
      {error ? <p>{error}</p> : null}
      {detalles.length ? (
        <ul className="mensaje__lista">
          {detalles.map((detalle) => (
            <li key={detalle}>{detalle}</li>
          ))}
        </ul>
      ) : null}
    </div>
  )
}
