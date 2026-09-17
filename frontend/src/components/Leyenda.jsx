const ESTADOS = [
  { clave: 'libre', texto: 'Libre' },
  { clave: 'separado', texto: 'Separado' },
  { clave: 'ocupado', texto: 'Ocupado' },
  { clave: 'pasado', texto: 'Ya pasó' },
]

export default function Leyenda() {
  return (
    <ul className="leyenda">
      {ESTADOS.map((estado) => (
        <li key={estado.clave} className="leyenda__item">
          <span className={`leyenda__punto leyenda__punto--${estado.clave}`} aria-hidden="true" />
          {estado.texto}
        </li>
      ))}
    </ul>
  )
}
