import { useCallback, useEffect, useState } from 'react'
import { obtenerDisponibilidad } from '../api/disponibilidad.js'
import { obtenerReservasAdmin } from '../api/reservas.js'

// Trae los slots de una cancha y fecha. Con admin=true pide la versión con datos del cliente.
export function useDisponibilidad({ canchaId, fecha, admin = false }) {
  const [slots, setSlots] = useState([])
  const [cargando, setCargando] = useState(false)
  const [error, setError] = useState('')
  const [intento, setIntento] = useState(0)

  // Recargar = pedir otra vez; el efecto hace el fetch y descarta respuestas viejas.
  const recargar = useCallback(() => setIntento((numero) => numero + 1), [])

  useEffect(() => {
    if (!canchaId || !fecha) return undefined

    let vigente = true
    const pedirSlots = admin ? obtenerReservasAdmin : obtenerDisponibilidad

    async function cargar() {
      setCargando(true)
      try {
        const datos = await pedirSlots(canchaId, fecha)
        if (!vigente) return
        setSlots(datos?.slots ?? [])
        setError('')
      } catch (fallo) {
        if (vigente) setError(fallo.message)
      } finally {
        if (vigente) setCargando(false)
      }
    }

    cargar()

    // Si cambia la fecha o la cancha, la respuesta en vuelo ya no interesa.
    return () => {
      vigente = false
    }
  }, [canchaId, fecha, admin, intento])

  return { slots, cargando, error, recargar }
}
