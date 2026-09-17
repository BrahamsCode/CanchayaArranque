import { useState } from 'react'
import { pedir, obtenerToken, guardarToken, borrarToken } from '../api/cliente.js'

// Lectura directa para las rutas protegidas (no necesitan estado de React).
export function hayToken() {
  return Boolean(obtenerToken())
}

export function useAuth() {
  const [token, setToken] = useState(() => obtenerToken())

  async function login(email, password) {
    const datos = await pedir('/admin/login', { metodo: 'POST', cuerpo: { email, password } })
    guardarToken(datos.token)
    setToken(datos.token)
    return datos.user
  }

  async function logout() {
    try {
      await pedir('/admin/logout', { metodo: 'POST' })
    } catch {
      // Si el token ya estaba muerto, igual cerramos sesión localmente.
    }
    borrarToken()
    setToken(null)
  }

  return { token, estaAutenticado: Boolean(token), login, logout }
}
