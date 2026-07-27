<?php
header('Content-Type: application/json');
$notesFile = __DIR__ . '/files/.notes.json';
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'load') {
    if (!file_exists($notesFile)) {
        echo json_encode([]);
        exit;
    }
    $data = file_get_contents($notesFile);
    if ($data === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to read notes']);
        exit;
    }
    echo $data;
    exit;
} elseif ($action === 'save') {
    $json = file_get_contents('php://input');
    if ($json === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    $decoded = json_decode($json, true);
    if ($decoded === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }
    $result = file_put_contents($notesFile, json_encode($decoded, JSON_PRETTY_PRINT));
    if ($result === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to write notes']);
        exit;
    }
    echo json_encode(['ok' => true]);
    exit;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>