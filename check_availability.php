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
$stmt = $conn->prepare("SELECT COUNT(*) as booked FROM appointments WHERE mechanic_id = ? AND appointment_date = ?");
$stmt->bind_param("is", $mechanic_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$booked = $row['booked'];
$max_capacity = 4;
$available = $max_capacity - $booked;

$stmt->close();
closeConnection($conn);

echo json_encode([
    'available' => $available,
    'booked' => $booked,
    'max' => $max_capacity
]);
?>
