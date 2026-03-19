# Reglas de Estandarizacion CSS

Este documento define las reglas para escribir y mantener estilos en este proyecto.
El objetivo es conservar estilos semanticos por componente/pagina, con una base global consistente y escalable por capas.

## 1) Arquitectura por capas

El proyecto usa `@layer` para controlar prioridad y evitar conflictos de especificidad.

Orden oficial de capas (de menor a mayor prioridad):

1. `theme`
2. `base`
3. `components`
4. `pages`

Archivo de entrada:

- `public/assets/css/main.css`

Reglas por capa:

- `theme`:
  - Solo tokens (`:root`), sin estilos de layout ni componentes.
  - Variables de color, tipografia, spacing, radios, sombras, transiciones, contenedores.
- `base`:
  - Reset y estilos elementales (`body`, `h1..h6`, `img`, controles nativos).
  - No estilos visuales de pantallas concretas.
- `components`:
  - Bloques reutilizables (`.btn`, `.card`, `.form-control`, etc.).
  - No dependencias de una pagina en particular.
- `pages`:
  - Estilos exclusivos de una vista (`login`, `welcome`, `dashboard`).
  - Se permite ajustar layout/estructura de la pagina sin modificar componentes globales.

## 2) Reglas de definicion de estilos

### 2.1 Regla principal

- Todo valor visual debe venir de tokens globales (`var(--...)`) cuando exista token equivalente.
- Evitar valores magicos repetidos (`#123456`, `17px`, `13px`) dentro de componentes/paginas.

### 2.2 Especificidad y selectores

- Priorizar clases semanticas sobre IDs para estilos.
- IDs solo para hooks de JS o anclas de accesibilidad.
- Evitar anidamiento excesivo de selectores (maximo recomendado: 2 niveles).
- Evitar `!important` salvo caso excepcional y documentado.

### 2.3 Convenciones de nombres

- Componentes reutilizables: prefijos claros (ej. `.btn-*`, `.card-*`, `.form-*`).
- Bloques de pagina: prefijo por contexto (ej. `.auth-*`, `.welcome-*`, `.dashboard-*`).
- Estados: modificadores explicitos (ej. `--active`, `--error`, `--disabled`, `.is-collapsed`).

### 2.4 Responsividad

- Mobile first cuando se agreguen nuevos estilos.
- Usar breakpoints consistentes por pagina/componente.
- Evitar duplicar reglas responsivas si pueden resolverse en un componente global.

### 2.5 Animaciones y transiciones

- Usar tokens de transicion (`--transition-base`, `--transition-slow`).
- Evitar animaciones innecesarias o demasiado largas.
- Priorizar transiciones de `opacity`, `transform`, `color`, `box-shadow`.

## 3) Estilos globales (obligatorios)

Los estilos globales viven en:

- `public/assets/css/global/theme.css`
- `public/assets/css/global/reset.css`
- `public/assets/css/global/typography.css`

Reglas:

- `theme.css` es la unica fuente de verdad de tokens.
- Si se necesita un nuevo color/espaciado/radio/sombra, se define primero en `theme.css`.
- `reset.css` no debe incluir estilos de componentes.
- `typography.css` define base tipografica compartida; no estilos de paginas.

## 4) Flujo para agregar estilos nuevos

1. Revisar si existe token global reutilizable.
2. Si no existe, agregar token en `theme.css`.
3. Si el estilo aplica a varios contextos, crear/editar en `components/`.
4. Si aplica solo a una vista, agregar en `pages/<vista>.css` dentro de `@layer pages`.
5. Validar que no se rompan otras vistas.

## 5) Buenas practicas de mantenimiento

- Mantener cada archivo enfocado en su responsabilidad por capa.
- Evitar duplicar componentes con nombres distintos pero misma funcion visual.
- Si un estilo de pagina se repite en 2+ vistas, promoverlo a `components/`.
- Documentar cambios de tokens relevantes cuando impacten varias pantallas.

## 6) Checklist rapido antes de cerrar cambios CSS

- [ ] El archivo esta en la capa correcta.
- [ ] Se usan tokens globales en lugar de valores repetidos.
- [ ] No se introducen IDs para estilos nuevos.
- [ ] Los componentes siguen nombres semanticos y consistentes.
- [ ] La vista funciona en desktop y mobile.
- [ ] No se afecta visualmente otra pagina sin intencion.
