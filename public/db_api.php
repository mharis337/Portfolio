<?php
header('Content-Type: application/json');
require 'db/db.php';
require 'db/db_query.php';

$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : (isset($data['action']) ? $data['action'] : '');

if ($action === 'check_wait_time') {
    $name = $data['name'];
    $code = $data['code'];
    $response = check_wait_time($conn, $name, $code);
    echo json_encode($response);
} elseif ($action === 'get_queue_data') {
    echo json_encode(fetchQueueData($conn));
} elseif ($action === 'admin_login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $admin_id = admin_login($conn, $input);
    if ($admin_id) {
        $_SESSION['admin'] = $admin_id;
        echo json_encode(['success' => true, 'redirect' => '/app/admin_page.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} elseif ($action == 'admit_patient') {
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'No patient ID specified']);
            exit;
        }

        $patientId = $data['id'];

        $result = admitPatient($conn, $patientId);

    echo json_encode($result);
} elseif ($action == 'remove_patient') {
    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'No patient ID specified']);
        exit;
    }

    $patientId = $data['id'];
    $result = removePatient($conn, $patientId);

    echo json_encode($result);
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    echo json_encode(addToQueue($conn, $input));
}

$conn = null;
?>
