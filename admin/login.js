// Escucha el evento 'submit' (envío) del formulario con ID 'formLogin'
document
  .getElementById("formLogin")
  .addEventListener("submit", async function (event) {
    // Evita que el formulario realice el envío tradicional (previene la recarga de la página)
    event.preventDefault();

    // Captura el elemento HTML donde se mostrarán los mensajes de error
    const mensaje = document.getElementById("mensaje");

    // Limpia cualquier mensaje de error previo antes de procesar el nuevo intento
    mensaje.textContent = "";

    try {
      // Envía una petición HTTP POST asíncrona a 'validar_login.php'
      // 'new FormData(this)' empaqueta automáticamente los inputs del formulario (usuario y clave)
      const respuesta = await fetch("validar_login.php", {
        method: "POST",
        body: new FormData(this),
      });

      // Verifica si la respuesta HTTP fue exitosa (código 200-299).
      // Si el servidor devolvió un error (ej. 405 o 500), lanza una excepción
      if (!respuesta.ok) throw new Error("Error del servidor");

      // Convierte la respuesta del servidor de JSON a un objeto JavaScript
      const datos = await respuesta.json();

      // Evalúa la propiedad 'ok' enviada por PHP
      if (datos.ok) {
        // Si el login fue exitoso ({ok: true}), redirige al usuario a la página principal
        window.location.href = "home.php";
      } else {
        // Si hubo error de credenciales ({ok: false}), muestra el mensaje devuelto por PHP
        mensaje.textContent = "Usuario o contraseña incorrectos.";
        mensaje.style.display = "block";
      }
    } catch (error) {
      // Captura fallos de red (sin conexión, servidor caído, etc.) o errores lanzados en el try
      mensaje.textContent = "No se pudo conectar con el servidor.";
      mensaje.style.display = "block";
    }
  });
