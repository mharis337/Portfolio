<?php
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $conn->beginTransaction();

    // Drop the admins table
    $stmt = $conn->prepare("DROP TABLE IF EXISTS admins");
    $stmt->execute();

    // Drop the queue table
    $stmt = $conn->prepare("DROP TABLE IF EXISTS queue");
    $stmt->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Tables dropped successfully']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
