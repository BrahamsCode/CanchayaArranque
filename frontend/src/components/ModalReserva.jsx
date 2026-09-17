import { useState } from 'react'
import MensajeError from './MensajeError.jsx'
import { actualizarReserva, crearReserva, eliminarReserva } from '../api/reservas.js'
import { formatearFecha, formatearHora } from '../utils/horas.js'

export default function ModalReserva({ slot, fecha, canchaId, onCerrar, onGuardado }) {
  const [nombre, setNombre] = useState(slot.cliente_nombre ?? '')
  const [telefono, setTelefono] = useState(slot.cliente_telefono ?? '')
  const [nota, setNota] = useState(slot.nota ?? '')
  const [error, setError] = useState('')
  const [errores, setErrores] = useState({})
  const [guardando, setGuardando] = useState(false)

  const libre = slot.estado === 'libre'
  const datosCliente = {
    cliente_nombre: nombre.trim(),
    cliente_telefono: telefono.trim(),
    nota: nota.trim() || null,
  }

  // Un solo lugar para el estado de guardado y los errores (incluido el 409).
  async function ejecutar(accion) {
    setGuardando(true)
    setError('')
    setErrores({})
    try {
      await accion()
      onGuardado()
    } catch (fallo) {
      setError(fallo.message)
      setErrores(fallo.errores)
    } finally {
      setGuardando(false)
    }
  }

  const crear = (estado) =>
    ejecutar(() =>
      crearReserva({ cancha_id: canchaId, fecha, hora_inicio: formatearHora(slot.hora_inicio), estado, ...datosCliente }),
    )

  const cambiarEstado = (estado) => ejecutar(() => actualizarReserva(slot.id, { estado, ...datosCliente }))

  const guardarDatos = () => ejecutar(() => actualizarReserva(slot.id, datosCliente))

  const liberar = () => {
    if (!window.confirm('¿Seguro que quieres liberar esta hora?')) return
    ejecutar(() => eliminarReserva(slot.id))
  }

  return (
    <div className="modal-fondo" role="dialog" aria-modal="true" aria-label="Editar reserva">
      <div className="modal">
        <header className="modal__cabecera">
          <div>
            <h2 className="modal__titulo">
              {formatearHora(slot.hora_inicio)} - {formatearHora(slot.hora_fin)}
            </h2>
            <p className="modal__subtitulo">
              {formatearFecha(fecha)} · <span className={`etiqueta etiqueta--${slot.estado}`}>{slot.estado}</span>
            </p>
          </div>
          <button type="button" className="boton boton--icono" aria-label="Cerrar" onClick={onCerrar}>
            ✕
          </button>
        </header>

        <div className="modal__cuerpo">
          <label className="campo-etiqueta">
            Nombre del cliente
            <input className="campo" value={nombre} onChange={(e) => setNombre(e.target.value)} autoComplete="off" />
          </label>
          <label className="campo-etiqueta">
            Teléfono
            <input className="campo" value={telefono} onChange={(e) => setTelefono(e.target.value)} inputMode="tel" autoComplete="off" />
          </label>
          <label className="campo-etiqueta">
            Nota (opcional)
            <textarea className="campo" rows="2" value={nota} onChange={(e) => setNota(e.target.value)} />
          </label>

          <MensajeError error={error} errores={errores} />
        </div>

        <footer className="modal__acciones">
          {libre ? (
            <>
              <button type="button" className="boton boton--separado" disabled={guardando} onClick={() => crear('separado')}>
                Marcar separado
              </button>
              <button type="button" className="boton boton--ocupado" disabled={guardando} onClick={() => crear('ocupado')}>
                Marcar ocupado
              </button>
            </>
          ) : (
            <>
              <button type="button" className="boton boton--principal" disabled={guardando} onClick={guardarDatos}>
                Guardar datos
              </button>
              {slot.estado === 'separado' ? (
                <button type="button" className="boton boton--ocupado" disabled={guardando} onClick={() => cambiarEstado('ocupado')}>
                  Pasar a ocupado
                </button>
              ) : (
                <button type="button" className="boton boton--separado" disabled={guardando} onClick={() => cambiarEstado('separado')}>
                  Volver a separado
                </button>
              )}
              <button type="button" className="boton boton--peligro" disabled={guardando} onClick={liberar}>
                Liberar
              </button>
            </>
          )}
        </footer>
      </div>
    </div>
  )
}
