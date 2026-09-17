const LLAVE_TOKEN = 'canchaya_token'
const RUTA_LOGIN = '/admin/login'

export function obtenerToken() {
  try {
    return localStorage.getItem(LLAVE_TOKEN)
  } catch {
    return null // navegador con almacenamiento bloqueado
  }
}

export function guardarToken(token) {
  try {
    localStorage.setItem(LLAVE_TOKEN, token)
  } catch {
    // sin localStorage la sesión dura lo que dure la pestaña
  }
}

export function borrarToken() {
  try {
    localStorage.removeItem(LLAVE_TOKEN)
  } catch {
    // nada que borrar
  }
}

// Error con el status y el mensaje del backend, para que la página lo muestre tal cual.
export class ErrorApi extends Error {
  constructor(mensaje, status, errores) {
    super(mensaje)
    this.name = 'ErrorApi'
    this.status = status
    this.errores = errores || {}
  }
}

async function leerJson(respuesta) {
  try {
    return await respuesta.json()
  } catch {
    return null
  }
}

// Único punto de salida al API: siempre rutas relativas /api/... (el proxy de Vite hace el resto).
export async function pedir(ruta, { metodo = 'GET', cuerpo } = {}) {
  const cabeceras = { Accept: 'application/json' }
  if (cuerpo !== undefined) cabeceras['Content-Type'] = 'application/json'

  const token = obtenerToken()
  if (token) cabeceras.Authorization = `Bearer ${token}`

  let respuesta
  try {
    respuesta = await fetch(`/api${ruta}`, {
      method: metodo,
      headers: cabeceras,
      body: cuerpo === undefined ? undefined : JSON.stringify(cuerpo),
    })
  } catch {
    throw new ErrorApi('No se pudo conectar con el servidor. Revisa tu conexión.', 0)
  }

  // 401 = el token murió; no vale la pena seguir en el panel.
  if (respuesta.status === 401) {
    borrarToken()
    if (window.location.pathname !== RUTA_LOGIN) window.location.assign(RUTA_LOGIN)
    throw new ErrorApi('Tu sesión expiró. Vuelve a iniciar sesión.', 401)
  }

  if (respuesta.status === 204) return null

  const datos = await leerJson(respuesta)

  if (!respuesta.ok) {
    const mensaje = datos?.mensaje || datos?.message || `Ocurrió un error (${respuesta.status}).`
    throw new ErrorApi(mensaje, respuesta.status, datos?.errors)
  }

  return datos
}
