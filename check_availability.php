<?php

header('Content-Type: application/json');
include 'db_config.php';

$date = $_GET['date'] ?? '';
$mechanic_id = intval($_GET['mechanic_id'] ?? 0);

if (empty($date) || $mechanic_id <= 0) {
    echo json_encode(['available' => 0, 'error' => 'Invalid parameters']);
    exit();
}

$conn = getDBConnection();

$mechanic_stmt = $conn->prepare("SELECT max_clients FROM mechanics WHERE mechanic_id = ? AND status = 'active'");
$mechanic_stmt->bind_param("i", $mechanic_id);
$mechanic_stmt->execute();
$mechanic_result = $mechanic_stmt->get_result();

if ($mechanic_result->num_rows === 0) {
    $mechanic_stmt->close();
    closeConnection($conn);
    echo json_encode(['available' => 0, 'error' => 'Invalid mechanic']);
    exit();
}

$mechanic_data = $mechanic_result->fetch_assoc();
$max_capacity = $mechanic_data['max_clients'];
$mechanic_stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as booked FROM appointments WHERE mechanic_id = ? AND appointment_date = ?");
$stmt->bind_param("is", $mechanic_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$booked = $row['booked'];
$available = $max_capacity - $booked;

$stmt->close();
closeConnection($conn);

echo json_encode([
    'available' => $available,
    'booked' => $booked,
    'max' => $max_capacity
]);
?>
