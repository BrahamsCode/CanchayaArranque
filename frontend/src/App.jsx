import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import Configuracion from './pages/Configuracion.jsx'
import Login from './pages/Login.jsx'
import PaginaPublica from './pages/PaginaPublica.jsx'
import PanelAdmin from './pages/PanelAdmin.jsx'
import { hayToken } from './hooks/useAuth.js'

// Sin token no se entra al panel; el 401 del API también manda de vuelta aquí.
function RutaProtegida({ children }) {
  return hayToken() ? children : <Navigate to="/admin/login" replace />
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<PaginaPublica />} />
        <Route path="/admin/login" element={<Login />} />
        <Route
          path="/admin"
          element={
            <RutaProtegida>
              <PanelAdmin />
            </RutaProtegida>
          }
        />
        <Route
          path="/admin/configuracion"
          element={
            <RutaProtegida>
              <Configuracion />
            </RutaProtegida>
          }
        />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
