import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { guardarConfiguracion, obtenerConfiguracionAdmin } from '../api/configuracion.js'

const CAMPOS = [
  { nombre: 'nombre_negocio', etiqueta: 'Nombre del negocio', tipo: 'text' },
  { nombre: 'whatsapp', etiqueta: 'WhatsApp (ej. 51987654321)', tipo: 'text' },
  { nombre: 'hora_apertura', etiqueta: 'Hora de apertura', tipo: 'time' },
  { nombre: 'hora_cierre', etiqueta: 'Hora de cierre', tipo: 'time' },
  { nombre: 'precio_hora', etiqueta: 'Precio por hora (S/)', tipo: 'text' },
]

const VACIO = { nombre_negocio: '', whatsapp: '', hora_apertura: '', hora_cierre: '', precio_hora: '' }

export default function Configuracion() {
  const [valores, setValores] = useState(VACIO)
  const [error, setError] = useState('')
  const [errores, setErrores] = useState({})
  const [exito, setExito] = useState('')
  const [cargando, setCargando] = useState(true)
  const [guardando, setGuardando] = useState(false)

  useEffect(() => {
    obtenerConfiguracionAdmin()
      .then((datos) => setValores({ ...VACIO, ...datos }))
      .catch((fallo) => setError(fallo.message))
      .finally(() => setCargando(false))
  }, [])

  function cambiar(nombre, valor) {
    setValores((previo) => ({ ...previo, [nombre]: valor }))
  }

  async function enviar(evento) {
    evento.preventDefault()
    setGuardando(true)
    setError('')
    setErrores({})
    setExito('')
    try {
      const datos = await guardarConfiguracion(valores)
      if (datos) setValores({ ...VACIO, ...datos })
      setExito('Configuración guardada.')
    } catch (fallo) {
      setError(fallo.message)
      setErrores(fallo.errores)
    } finally {
      setGuardando(false)
    }
  }

  return (
    <div className="pagina">
      <header className="cabecera cabecera--admin">
        <h1 className="cabecera__titulo">Configuración</h1>
        <Link className="boton boton--secundario" to="/admin">
          Volver
        </Link>
      </header>

      <main className="contenido">
        {cargando ? (
          <p className="aviso">Cargando…</p>
        ) : (
          <form className="tarjeta" onSubmit={enviar}>
            {CAMPOS.map((campo) => (
              <label key={campo.nombre} className="campo-etiqueta">
                {campo.etiqueta}
                <input
                  className="campo"
                  type={campo.tipo}
                  value={valores[campo.nombre] ?? ''}
                  onChange={(evento) => cambiar(campo.nombre, evento.target.value)}
                />
                {/* El 422 trae los errores por campo: se muestran junto al input que los causó. */}
                {errores[campo.nombre] ? <span className="campo__error">{errores[campo.nombre][0]}</span> : null}
              </label>
            ))}

            {error ? <p className="mensaje mensaje--error">{error}</p> : null}
            {exito ? <p className="mensaje mensaje--exito">{exito}</p> : null}

            <button type="submit" className="boton boton--principal boton--ancho" disabled={guardando}>
              {guardando ? 'Guardando…' : 'Guardar'}
            </button>
          </form>
        )}
      </main>
    </div>
  )
}
