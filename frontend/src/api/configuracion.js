import { pedir } from './cliente.js'

export function obtenerConfiguracionPublica() {
  return pedir('/configuracion-publica')
}

export function obtenerConfiguracionAdmin() {
  return pedir('/admin/configuracion')
}

export function guardarConfiguracion(datos) {
  return pedir('/admin/configuracion', { metodo: 'PUT', cuerpo: datos })
}
