# Sistema de turnos · Clínica Oculus

Proyecto grupal de **Programación II** (Opción 2: Sistema de Gestión de Turnos) para una clínica oftalmológica.
Hecho en **PHP + MySQL (PDO)** con Bootstrap, para correr en **XAMPP**.

## Qué tiene

**Web pública** (pacientes)
- Landing de la clínica con los servicios que hay cargados en la base.
- Reserva online en 3 pasos: profesional y día → datos del paciente → confirmación.
- Los horarios ya reservados se muestran como ocupados.

**Sistema interno** (personal, con login)
- Panel principal con los turnos del día.
- Turnos: nuevo, consulta con filtros y total, detalle, modificar, confirmar y cancelar.
- ABM de pacientes, profesionales, servicios y usuarios del sistema (este último solo para administradores).

## Cómo instalarlo

1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\turnos_oftalmologia`.
2. Prender **Apache** y **MySQL** en XAMPP.
3. En `http://localhost/phpmyadmin` → **Importar** → `database/Sistema Turnos Oftalmologia.sql`.
4. Abrir:
   - Web pública: `http://localhost/turnos_oftalmologia/`
   - Sistema interno: `http://localhost/turnos_oftalmologia/admin/`
5. Usuario inicial: **admin** / contraseña **admin123** (cambiarla después de entrar).

## Equipo

| Parte | Quién |
|---|---|
| Base de datos, conexión, panel y ABM | Nahuel |
| Login del personal y módulo de turnos | Belén |
| Web pública (landing y diseño) | Sofía |
| Reserva online | Sofía y Belén |

Más detalle de la estructura y de cómo se conectan las partes en [LEEME.md](LEEME.md).
