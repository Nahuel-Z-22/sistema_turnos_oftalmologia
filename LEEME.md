# Sistema de turnos · Clínica Oculus (proyecto grupal)

Proyecto de Programación II (Opción 2: Sistema de Gestión de Turnos).
El código sigue **la misma estructura que la carpeta `crud` que nos dio el profe**:
mismos archivos, mismas funciones y mismo orden dentro de cada archivo.

## Quién hace cada parte

| Parte | Quién | Estado |
|---|---|---|
| Base: conexión, includes, menú, panel principal | Nahuel | ✔ hecho |
| ABM pacientes, profesionales, servicios y usuarios | Nahuel | ✔ hecho |
| Base de datos (`database/`) | Nahuel | ✔ hecho |
| Login del personal (`admin/index.php`, `login.js`, `validar_login.php`, `logout.php`) | Belén | ✔ hecho |
| Módulo de turnos (`admin/turnos/`, `admin/includes/turnos_funciones.php`) | Belén | ✔ hecho |
| Web pública: landing y diseño (`index.php`, `parciales/`, `assets/estilos.css`) | Sofía | ✔ hecho |
| Web pública: reserva online (`reservar.php`, `datos.php`, `guardar_reserva.php`, `reserva_ok.php`) | Sofía y Belén | ✔ hecho |

## Cómo instalarlo

1. Copiar esta carpeta `turnos_oftalmologia` dentro de `C:\xampp\htdocs\`.
2. Prender Apache y MySQL en XAMPP.
3. En `http://localhost/phpmyadmin` → **Importar** → `database/Sistema Turnos Oftalmologia.sql`.
4. Abrir:
   - Web pública (pacientes): `http://localhost/turnos_oftalmologia/`
   - Sistema (personal): `http://localhost/turnos_oftalmologia/admin/` (o el botón «Acceso personal» de la web)
5. Usuario inicial: **admin** / contraseña **admin123**.

## Dónde está cada cosa

```
turnos_oftalmologia/
├─ LEEME.md
├─ index.php                               A · Landing (Sofía)
├─ reservar.php                            B · Reserva online: profesional, día y horario (Sofía + Belén)
├─ datos.php                               B · Reserva online: datos del paciente
├─ guardar_reserva.php                     guarda paciente (si es nuevo) + turno + detalle
├─ reserva_ok.php                          C · Confirmación
├─ parciales/
│  ├─ cabecera.php                         encabezado de Sofía (con «Acceso personal»)
│  └─ pie.php                              pie de página de Sofía
├─ assets/
│  ├─ estilos.css                          estilos de la web pública (Sofía, igual al original)
│  ├─ reserva.css                          lo agregado a la web: reserva online, logo, «Acceso personal»
│  ├─ admin.css                            estilos del sistema admin (colores de Belén)
│  └─ img/consultorio.jpg                  foto de la landing (Sofía)
├─ database/
│  └─ Sistema Turnos Oftalmologia.sql      la base: 10 tablas + datos de ejemplo
│
└─ admin/                                  SISTEMA CON LOGIN (copia adaptada de crud)
   ├─ index.php, login.js                  1 · Login del personal (Belén)
   ├─ validar_login.php, logout.php
   ├─ conexion.php                         conexión PDO a la base
   ├─ home.php                             2 · Panel principal
   ├─ includes/
   │  ├─ header.php                        menú (se incluye en todas las páginas)
   │  ├─ footer.php
   │  ├─ funciones.php                     seguridad, mensajes, CSRF (las de crud)
   │  └─ turnos_funciones.php              horarios_libres() y ayudas de turnos (Belén)
   ├─ clientes/                            3 · ABM Pacientes
   ├─ profesionales/                       4 · ABM Profesionales (+ sus servicios)
   ├─ servicios/                           5 · ABM Servicios
   ├─ usuarios_sistema/                    6 · ABM Usuarios (solo administradores)
   └─ turnos/                              7 a 10 · Turnos (Belén)
      ├─ listar.php                        consulta con filtros y total
      ├─ nuevo.php, guardar.php            nuevo turno (cabecera + detalle en una transacción)
      ├─ ver.php                           detalle del turno
      ├─ editar.php, modificar.php         modificar (también el estado)
      ├─ cancelar.php                      pasa el turno a Cancelado
      ├─ _formulario.php                   formulario de nuevo/editar con el resumen y el total
      └─ servicios.php, horarios.php       devuelven JSON al formulario (fetch)
```

Cada ABM tiene los mismos archivos que en crud:
`listar.php`, `nuevo.php`, `guardar.php`, `editar.php`, `modificar.php`, `eliminar.php` y `_formulario.php`.

## De dónde sale cada módulo

| Módulo | Sale de | Qué cambió |
|---|---|---|
| `admin/conexion.php`, `includes/*` | crud | nombre de la base, menú, rol «Administrador» |
| `home.php` | crud `home.php` | contadores de turnos y turnos de hoy |
| `clientes/` | crud `socios/` | fecha de nacimiento y campo activo |
| `profesionales/` | crud `autores/` + `libros/` | especialidad y servicios (tabla puente, como libro_autor) |
| `servicios/` | crud `categorias/` | precio, duración y activo |
| `usuarios_sistema/` | crud `usuarios_sistema/` | el rol sale de la tabla `roles` |
| Login | Belén (`Oculus/index.php`, `login.js`, `validar_login.php`, `logout.php`) | usa `funciones.php`, JOIN con `roles`, entran todos los roles activos |
| `turnos/` | Belén (`Oculus/turnos/`) | `reservas.php` → `listar.php` (+ filtros), `reservar.php` → `nuevo.php`, `detalle.php` → `ver.php`, `modificar.php` → `editar.php` (+ estado), `actualizar_turno.php` → `modificar.php`, `cancelar_turno.php` → `cancelar.php`, `obtener_*.php` → `servicios.php` / `horarios.php` |
| Web pública | Sofía (`index.html`, `estilos.css`) | pasó a PHP; cabecera y pie en `parciales/`; el formulario de turnos ahora reserva de verdad |

## Cómo se conectan las partes

- **Login → todo el sistema:** `validar_login.php` guarda en la sesión `id_usuario`, `usuario`, `nombre` y `rol`
  (nombre del rol, por ejemplo `Administrador`). `header.php` y `funciones.php` usan esas claves.
- **Web pública → admin:** las páginas de Sofía usan `admin/includes/funciones.php` (conexión, CSRF, mensajes)
  y `horarios_libres()` de `admin/includes/turnos_funciones.php`. Los turnos que reserva un paciente aparecen
  en el admin con la etiqueta **Web** y estado **Pendiente**, para que recepción los confirme.
- **Estilos:** la web usa `assets/estilos.css` (Sofía) + `assets/reserva.css`, y el admin `assets/admin.css`
  (Bootstrap + colores de Belén). Van separados porque `estilos.css` cambia el aspecto de todos los `form`, `input` y `button`.
  Los links llevan `?v=` con la fecha del archivo, así el navegador siempre toma la última versión.

## Cosas a saber

- **Horario de atención:** lunes a viernes de 8 a 12 y de 14 a 18 hs, turnos cada 20 minutos
  (se cambia en `FRANJAS_ATENCION` y `DIAS_ATENCION` de `admin/includes/turnos_funciones.php`).
  Un horario está libre si el turno entero (según la duración de los servicios) no se pisa con otro turno
  no cancelado del mismo profesional. Los turnos cancelados liberan el horario.
- **Reserva online:** siempre es una Consulta oftalmológica (`id_servicio` 1), con `id_usuario` NULL y estado Pendiente.
  Si el DNI ya existe se usa ese paciente; solo se le completan teléfono o email si no los tenía.
- **Al modificar un turno**, los servicios que ya tenía conservan su precio original; los nuevos toman el precio actual.
- **Eliminar vs. desactivar:** como en crud, «Eliminar» borra el registro, pero la base no deja borrar
  un paciente, profesional o servicio que ya tiene turnos. En ese caso se lo desactiva desde **Editar**.
- **El total** de un turno no se guarda en la base: se calcula con `SUM(cantidad * precio_unitario)`.
