# Sistema de turnos · Clínica oftalmológica (proyecto grupal)

Proyecto de Programación II (Opción 2: Sistema de Gestión de Turnos).
El código sigue **la misma estructura que la carpeta `crud` que nos dio el profe**:
mismos archivos, mismas funciones y mismo orden dentro de cada archivo.

## Quién hace cada parte

| Parte | Quién | Estado |
|---|---|---|
| Base: conexión, includes, menú, panel principal | [Tu nombre] | ✔ hecho |
| ABM pacientes, profesionales, servicios y usuarios | [Tu nombre] | ✔ hecho |
| Base de datos (`database/`) | [Tu nombre] | ✔ hecho |
| Login del personal (`admin/index.php`, `login.js`, `validar_login.php`, `logout.php`) | Belén | pendiente |
| Módulo de turnos (`admin/turnos/`, `admin/includes/turnos_funciones.php`) | Belén | pendiente |
| Web pública: landing y diseño (`index.php`, `parciales/`, `assets/`) | Sofía | pendiente |
| Web pública: reserva online (`reservar.php`, `datos.php`, `guardar_reserva.php`, `reserva_ok.php`) | Sofía y Belén | pendiente |

Cada parte pendiente tiene un archivo **`PENDIENTE - ….txt`** en la carpeta donde va,
con la lista de archivos a crear y cómo se conecta con lo que ya está hecho.

## Cómo instalarlo

1. Copiar esta carpeta `turnos_oftalmologia` dentro de `C:\xampp\htdocs\`.
2. Prender Apache y MySQL en XAMPP.
3. En `http://localhost/phpmyadmin` → **Importar** → `database/Sistema Turnos Oftalmologia.sql`.
4. Abrir:
   - Web pública (pacientes): `http://localhost/turnos_oftalmologia/` (cuando Sofía suba su parte)
   - Sistema (personal): `http://localhost/turnos_oftalmologia/admin/` (cuando Belén suba el login)
5. Usuario inicial: **admin** / contraseña **admin123**.

## Dónde está cada cosa

```
turnos_oftalmologia/
├─ LEEME.md
├─ PENDIENTE - Sofia (web publica).txt     ← acá van los archivos de Sofía
├─ database/
│  └─ Sistema Turnos Oftalmologia.sql      la base: 10 tablas + datos de ejemplo
│
└─ admin/                                  SISTEMA CON LOGIN (copia adaptada de crud)
   ├─ PENDIENTE - Belen (login).txt        ← acá va el login de Belén
   ├─ conexion.php                         conexión PDO a la base
   ├─ home.php                             2 · Panel principal
   ├─ includes/
   │  ├─ header.php                        menú (se incluye en todas las páginas)
   │  ├─ footer.php
   │  └─ funciones.php                     seguridad, mensajes, CSRF (las de crud)
   ├─ clientes/                            3 · ABM Pacientes
   ├─ profesionales/                       4 · ABM Profesionales (+ sus servicios)
   ├─ servicios/                           5 · ABM Servicios
   ├─ usuarios_sistema/                    6 · ABM Usuarios (solo administradores)
   └─ turnos/
      └─ PENDIENTE - Belen.txt             ← acá va el módulo de turnos de Belén
```

Cada ABM tiene los mismos archivos que en crud:
`listar.php`, `nuevo.php`, `guardar.php`, `editar.php`, `modificar.php`, `eliminar.php` y `_formulario.php`.

## De qué archivo de crud sale cada módulo

| Módulo | Copiado de crud | Qué cambió |
|---|---|---|
| `admin/conexion.php`, `includes/*` | igual | nombre de la base, menú, rol «Administrador» |
| `home.php` | `home.php` | contadores de turnos y turnos de hoy |
| `clientes/` | `socios/` | fecha de nacimiento y campo activo |
| `profesionales/` | `autores/` + `libros/` | especialidad y servicios (tabla puente, como libro_autor) |
| `servicios/` | `categorias/` | precio, duración y activo |
| `usuarios_sistema/` | `usuarios_sistema/` | el rol sale de la tabla `roles` |
| Login (Belén) | `index.php`, `login.js`, `validar_login.php`, `logout.php` | diseño y JOIN con `roles` |
| `turnos/` (Belén) | `prestamos/` | horarios libres, total y cancelar en vez de devolver |

## Cómo se conectan las partes

- **Login → todo el sistema:** `validar_login.php` guarda en la sesión `id_usuario`, `usuario`, `nombre` y `rol`
  (nombre del rol, por ejemplo `Administrador`). `header.php` y `funciones.php` usan esas claves.
- **Menú y panel → turnos:** ya tienen los links a `turnos/listar.php`, `turnos/nuevo.php` y `turnos/ver.php?id=`.
- **Web pública ↔ admin:** las páginas de Sofía usan `admin/includes/funciones.php` (conexión y seguridad)
  y `horarios_libres()` de `admin/includes/turnos_funciones.php` (Belén).
- **Estilos:** `assets/estilos.css` (Sofía) lo usan la web y el sistema admin.

## Cosas a saber

- **Eliminar vs. desactivar:** como en crud, «Eliminar» borra el registro, pero la base no deja borrar
  un paciente, profesional o servicio que ya tiene turnos. En ese caso se lo desactiva desde **Editar**.
- **El total** de un turno no se guarda en la base: se calcula con `SUM(cantidad * precio_unitario)`.
- Hasta que estén el login y los estilos, el sistema no se puede abrir ni se ve con los colores de la clínica.
