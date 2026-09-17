import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import GrillaHoras from '../components/GrillaHoras.jsx'
import Leyenda from '../components/Leyenda.jsx'
import ModalReserva from '../components/ModalReserva.jsx'
import SelectorCancha from '../components/SelectorCancha.jsx'
import SelectorFecha from '../components/SelectorFecha.jsx'
import { obtenerConfiguracionPublica } from '../api/configuracion.js'
import { useDisponibilidad } from '../hooks/useDisponibilidad.js'
import { useAuth } from '../hooks/useAuth.js'
import { hoyIso } from '../utils/horas.js'

export default function PanelAdmin() {
  const { logout } = useAuth()
  const navegar = useNavigate()
  const [canchas, setCanchas] = useState([])
  const [canchaId, setCanchaId] = useState(null)
  const [fecha, setFecha] = useState(hoyIso())
  const [slotAbierto, setSlotAbierto] = useState(null)

  const { slots, cargando, error, recargar } = useDisponibilidad({ canchaId, fecha, admin: true })

  // La lista de canchas solo está en la configuración pública.
  useEffect(() => {
    obtenerConfiguracionPublica()
      .then((datos) => {
        setCanchas(datos.canchas ?? [])
        setCanchaId(datos.canchas?.[0]?.id ?? null)
      })
      .catch(() => setCanchas([]))
  }, [])

  async function salir() {
    await logout()
    navegar('/admin/login', { replace: true })
  }

  return (
    <div className="pagina">
      <header className="cabecera cabecera--admin">
        <h1 className="cabecera__titulo">Reservas</h1>
        <nav className="cabecera__nav">
          <Link className="boton boton--secundario" to="/admin/configuracion">
            Configuración
          </Link>
          <button type="button" className="boton boton--secundario" onClick={salir}>
            Salir
          </button>
        </nav>
      </header>

      <main className="contenido">
        <SelectorFecha fecha={fecha} onCambiar={setFecha} />
        <SelectorCancha canchas={canchas} canchaId={canchaId} onCambiar={setCanchaId} />

        <div className="barra-acciones">
          <Leyenda />
          <button type="button" className="boton boton--secundario" onClick={recargar} disabled={cargando}>
            {cargando ? 'Actualizando…' : 'Actualizar'}
          </button>
        </div>

        {error ? <p className="mensaje mensaje--error">{error}</p> : null}

        {cargando && !slots.length ? (
          <p className="aviso">Cargando horarios…</p>
        ) : (
          <GrillaHoras slots={slots} fecha={fecha} onSeleccionar={setSlotAbierto} modoAdmin />
        )}
      </main>

      {slotAbierto ? (
        <ModalReserva
          slot={slotAbierto}
          fecha={fecha}
          canchaId={canchaId}
          onCerrar={() => setSlotAbierto(null)}
          onGuardado={() => {
            setSlotAbierto(null)
            recargar()
          }}
        />
      ) : null}
    </div>
  )
}
