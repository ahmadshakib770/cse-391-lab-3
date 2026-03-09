<?php

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$client_name = trim($_POST['client_name'] ?? '');
$client_address = trim($_POST['client_address'] ?? '');
$client_phone = trim($_POST['client_phone'] ?? '');
$car_license = trim($_POST['car_license'] ?? '');
$car_engine = trim($_POST['car_engine'] ?? '');
$appointment_date = trim($_POST['appointment_date'] ?? '');
$mechanic_id = intval($_POST['mechanic_id'] ?? 0);

$errors = [];

if (empty($client_name) || strlen($client_name) < 3) {
    $errors[] = 'Invalid name';
}

if (empty($client_address)) {
    $errors[] = 'Address is required';
}

if (!preg_match('/^01[0-9]{9}$/', $client_phone)) {
    $errors[] = 'Invalid phone number format';
}

if (empty($car_license)) {
    $errors[] = 'Car license is required';
}

if (empty($car_engine)) {
    $errors[] = 'Engine number is required';
}

if (empty($appointment_date) || strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
    $errors[] = 'Invalid appointment date';
}

if ($mechanic_id <= 0) {
    $errors[] = 'Please select a mechanic';
}

if (!empty($errors)) {
    header('Location: index.php?error=invalid_data');
    exit();
}

$conn = getDBConnection();

$conn->begin_transaction();

try {
    $mechanic_check = $conn->prepare("SELECT mechanic_id FROM mechanics WHERE mechanic_id = ? AND status = 'active'");
    $mechanic_check->bind_param("i", $mechanic_id);
    $mechanic_check->execute();
    $mechanic_result = $mechanic_check->get_result();
    
    if ($mechanic_result->num_rows === 0) {
        throw new Exception('invalid_mechanic');
    }
    $mechanic_check->close();

    $car_check = $conn->prepare("SELECT appointment_id FROM appointments WHERE car_license = ? AND appointment_date = ?");
    $car_check->bind_param("ss", $car_license, $appointment_date);
    $car_check->execute();
    $car_result = $car_check->get_result();
    
    if ($car_result->num_rows > 0) {
        throw new Exception('duplicate_car');
    }
    $car_check->close();

    $max_check = $conn->prepare("SELECT max_clients FROM mechanics WHERE mechanic_id = ? AND status = 'active'");
    $max_check->bind_param("i", $mechanic_id);
    $max_check->execute();
    $max_result = $max_check->get_result();
    
    if ($max_result->num_rows === 0) {
        throw new Exception('invalid_mechanic');
    }
    
    $max_data = $max_result->fetch_assoc();
    $max_capacity = $max_data['max_clients'];
    $max_check->close();

    $capacity_check = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE mechanic_id = ? AND appointment_date = ?");
    $capacity_check->bind_param("is", $mechanic_id, $appointment_date);
    $capacity_check->execute();
    $capacity_result = $capacity_check->get_result();
    $capacity_row = $capacity_result->fetch_assoc();
    
    if ($capacity_row['count'] >= $max_capacity) {
        throw new Exception('mechanic_full');
    }
    $capacity_check->close();

    $insert_stmt = $conn->prepare("INSERT INTO appointments (client_name, client_address, client_phone, car_license, car_engine, appointment_date, mechanic_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
    $insert_stmt->bind_param("ssssssi", $client_name, $client_address, $client_phone, $car_license, $car_engine, $appointment_date, $mechanic_id);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('db_error');
    }
    
    $insert_stmt->close();

    $conn->commit();
    
    closeConnection($conn);
    header('Location: index.php?success=1');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    closeConnection($conn);
    
    $error_type = $e->getMessage();
    header('Location: index.php?error=' . $error_type);
    exit();
}
?>
