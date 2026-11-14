<?php
require_once "includes/funciones.php";   // inicia la sesión y trae la conexión

if (isset($_SESSION['usuario'])) {
    header('Location: home.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Oculus</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #F4F7F8;
            font-family: 'Nunito Sans', sans-serif;
        }

        h2 {
            font-family: 'DM Sans', sans-serif;
            color: #0F2A3D;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .login-card {
            width: 100%;
            max-width: 950px;
            min-height: 520px;
            display: flex;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
        }

        .panel-izquierdo {
            width: 45%;
            background-color: #0F2A3D;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 35px;
        }

        .logo {
            width: 130px;
            height: 130px;
            border: 12px solid #0B7F7B;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .logo-centro {
            width: 65px;
            height: 65px;
            background-color: #0B7F7B;
            border: 10px solid #075956;
            border-radius: 50%;
        }

        .panel-derecho {
            width: 55%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 55px;
        }

        .formulario {
            width: 100%;
            max-width: 350px;
        }

        .form-control {
            padding: 12px;
        }

        .form-control:focus {
            border-color: #0B7F7B;
            box-shadow: 0 0 0 0.2rem rgba(11,127,123,0.15);
        }

        .btn-ingresar {
            background-color: #0B7F7B;
            color: white;
            padding: 12px;
            border: none;
        }

        .btn-ingresar:hover {
            background-color: #086966;
            color: white;
        }

        @media (max-width: 700px) {
            .login-card {
                flex-direction: column;
            }

            .panel-izquierdo,
            .panel-derecho {
                width: 100%;
            }

            .panel-izquierdo {
                padding: 30px;
            }

            .panel-derecho {
                padding: 35px 25px;
            }

            .logo {
                width: 90px;
                height: 90px;
                border-width: 9px;
            }

            .logo-centro {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>

<body>

<main class="login-container">

    <div class="login-card">

        <section class="panel-izquierdo">

            <div class="logo">
                <div class="logo-centro"></div>
            </div>

            <h2 class="text-white">Clínica Oculus</h2>

            <p class="mt-2">
                Sistema de turnos<br>
                Uso interno del personal
            </p>

            <a class="text-white-50 small mt-3" href="../">← Volver a la web</a>

        </section>

        <section class="panel-derecho">

            <div class="formulario">

                <h2 class="mb-2">Iniciar sesión</h2>
                <p class="text-muted mb-4">
                    Ingresá tus datos para continuar.
                </p>

                <form id="formLogin">

                    <div class="mb-3">
                        <label class="form-label" for="usuario">Usuario</label>
                        <input class="form-control" id="usuario" name="usuario"
                               placeholder="Ingresá tu usuario" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="clave">Contraseña</label>
                        <input class="form-control" type="password" id="clave"
                               name="clave" placeholder="Ingresá tu contraseña" required>
                    </div>

                    <div id="mensaje" class="alert alert-danger py-2 mb-3"
                         role="alert" style="display: none;"></div>

                    <button class="btn btn-ingresar w-100" type="submit">
                        Ingresar
                    </button>

                </form>

            </div>

        </section>

    </div>

</main>

<script src="./login.js?v=2"></script>

</body>
</html>
