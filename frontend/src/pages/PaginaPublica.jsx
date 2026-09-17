import { useEffect, useState } from 'react'
import GrillaHoras from '../components/GrillaHoras.jsx'
import Leyenda from '../components/Leyenda.jsx'
import SelectorCancha from '../components/SelectorCancha.jsx'
import SelectorFecha from '../components/SelectorFecha.jsx'
import { obtenerConfiguracionPublica } from '../api/configuracion.js'
import { useDisponibilidad } from '../hooks/useDisponibilidad.js'
import { formatearHora, hoyIso, slotYaPaso } from '../utils/horas.js'
import { armarLinkWhatsapp } from '../utils/whatsapp.js'

const REFRESCO_MS = 60000

export default function PaginaPublica() {
  const [config, setConfig] = useState(null)
  const [errorConfig, setErrorConfig] = useState('')
  const [canchaId, setCanchaId] = useState(null)
  const [fecha, setFecha] = useState(hoyIso())
  const [seleccion, setSeleccion] = useState(null)

  const { slots, cargando, error, recargar } = useDisponibilidad({ canchaId, fecha })

  useEffect(() => {
    obtenerConfiguracionPublica()
      .then((datos) => {
        setConfig(datos)
        setCanchaId(datos.canchas?.[0]?.id ?? null)
      })
      .catch((fallo) => setErrorConfig(fallo.message))
  }, [])

  // Refresco automático: otro cliente puede tomar la hora mientras miras la pantalla.
  useEffect(() => {
    const id = setInterval(recargar, REFRESCO_MS)
    return () => clearInterval(id)
  }, [recargar])

  // Al cambiar de día o de cancha la selección anterior ya no aplica.
  function cambiarFecha(nueva) {
    setFecha(nueva)
    setSeleccion(null)
  }

  function cambiarCancha(id) {
    setCanchaId(id)
    setSeleccion(null)
  }

  const cancha = config?.canchas?.find((item) => item.id === canchaId)

  // Si el refresco automático ocupó la hora elegida, el panel deja de ofrecerla.
  const slotElegido = seleccion ? slots.find((slot) => slot.hora_inicio === seleccion.hora_inicio) : null
  const elegido =
    slotElegido?.estado === 'libre' && !slotYaPaso(fecha, slotElegido.hora_inicio) ? slotElegido : null

  return (
    <div className="pagina">
      <header className="cabecera">
        <h1 className="cabecera__titulo">{config?.nombre_negocio ?? 'CanchaYa'}</h1>
        {config?.precio_hora ? <p className="cabecera__precio">S/ {config.precio_hora} por hora</p> : null}
      </header>

      <main className="contenido">
        <SelectorFecha fecha={fecha} onCambiar={cambiarFecha} />
        <SelectorCancha canchas={config?.canchas} canchaId={canchaId} onCambiar={cambiarCancha} />

        <div className="barra-acciones">
          <Leyenda />
          <button type="button" className="boton boton--secundario" onClick={recargar} disabled={cargando}>
            {cargando ? 'Actualizando…' : 'Actualizar'}
          </button>
        </div>

        {errorConfig ? <p className="mensaje mensaje--error">{errorConfig}</p> : null}
        {error ? <p className="mensaje mensaje--error">{error}</p> : null}

        {cargando && !slots.length ? (
          <p className="aviso">Cargando horarios…</p>
        ) : (
          <GrillaHoras slots={slots} fecha={fecha} seleccion={elegido} onSeleccionar={setSeleccion} />
        )}
      </main>

      {elegido ? (
        <div className="panel-inferior">
          <div className="panel-inferior__texto">
            <strong>
              {formatearHora(elegido.hora_inicio)} - {formatearHora(elegido.hora_fin)}
            </strong>
            <span>{cancha?.nombre ?? 'Cancha'}</span>
          </div>
          <a
            className="boton boton--whatsapp"
            href={armarLinkWhatsapp({
              numero: config?.whatsapp,
              cancha: cancha?.nombre ?? 'Cancha',
              fecha,
              horaInicio: elegido.hora_inicio,
              horaFin: elegido.hora_fin,
            })}
            target="_blank"
            rel="noreferrer"
          >
            Reservar por WhatsApp
          </a>
          <button type="button" className="boton boton--icono" aria-label="Cancelar selección" onClick={() => setSeleccion(null)}>
            ✕
          </button>
        </div>
      ) : null}
    </div>
  )
}
