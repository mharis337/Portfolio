<?php
require_once '../app/Game.php';

if (!isset($_SESSION['board'])) {
    initializeGame();
}

$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';

if ($action === 'makeMove') {
    makeMove($input['index']);
} elseif ($action === 'restart') {
    initializeGame();
}

header('Content-Type: application/json');
echo json_encode(getGameState());
?>
