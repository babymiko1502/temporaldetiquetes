<?php
$transactionId = $_GET['id'] ?? '';

if (!$transactionId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$filename = __DIR__ . "/verificaciones/$transactionId.json";

if (!file_exists($filename)) {
    http_response_code(404);
    echo json_encode(['estado' => 'pendiente']);
    exit;
}

$data = json_decode(file_get_contents($filename), true);
echo json_encode($data);
?>
