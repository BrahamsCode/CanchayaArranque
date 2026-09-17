import { useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import MensajeError from '../components/MensajeError.jsx'
import { useAuth } from '../hooks/useAuth.js'

export default function Login() {
  const { estaAutenticado, login } = useAuth()
  const navegar = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [errores, setErrores] = useState({})
  const [enviando, setEnviando] = useState(false)

  if (estaAutenticado) return <Navigate to="/admin" replace />

  async function enviar(evento) {
    evento.preventDefault()
    setEnviando(true)
    setError('')
    setErrores({})
    try {
      await login(email, password)
      navegar('/admin', { replace: true })
    } catch (fallo) {
      setError(fallo.message)
      setErrores(fallo.errores)
    } finally {
      setEnviando(false)
    }
  }

  return (
    <div className="pagina pagina--centrada">
      <form className="tarjeta" onSubmit={enviar}>
        <h1 className="tarjeta__titulo">Panel de administración</h1>

        <label className="campo-etiqueta">
          Correo
          <input
            className="campo"
            type="email"
            value={email}
            onChange={(evento) => setEmail(evento.target.value)}
            autoComplete="username"
            required
          />
        </label>

        <label className="campo-etiqueta">
          Contraseña
          <input
            className="campo"
            type="password"
            value={password}
            onChange={(evento) => setPassword(evento.target.value)}
            autoComplete="current-password"
            required
          />
        </label>

        <MensajeError error={error} errores={errores} />

        <button type="submit" className="boton boton--principal boton--ancho" disabled={enviando}>
          {enviando ? 'Entrando…' : 'Entrar'}
        </button>
      </form>
    </div>
  )
}
