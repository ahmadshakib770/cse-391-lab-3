<?php

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $mechanic_id = intval($_POST['mechanic_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    if ($appointment_id <= 0 || empty($appointment_date) || $mechanic_id <= 0 || empty($status)) {
        header('Location: admin.php?error=invalid_data');
        exit();
    }
    
    $conn = getDBConnection();
    $conn->begin_transaction();
    
    try {
        $check_appt = $conn->prepare("SELECT car_license FROM appointments WHERE appointment_id = ?");
        $check_appt->bind_param("i", $appointment_id);
        $check_appt->execute();
        $result = $check_appt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('appointment_not_found');
        }
        
        $car_data = $result->fetch_assoc();
        $car_license = $car_data['car_license'];
        $check_appt->close();
        
        $check_mechanic = $conn->prepare("SELECT mechanic_id FROM mechanics WHERE mechanic_id = ? AND status = 'active'");
        $check_mechanic->bind_param("i", $mechanic_id);
        $check_mechanic->execute();
        $mechanic_result = $check_mechanic->get_result();
        
        if ($mechanic_result->num_rows === 0) {
            throw new Exception('invalid_mechanic');
        }
        $check_mechanic->close();
        
        $check_duplicate = $conn->prepare("SELECT appointment_id FROM appointments WHERE car_license = ? AND appointment_date = ? AND appointment_id != ?");
        $check_duplicate->bind_param("ssi", $car_license, $appointment_date, $appointment_id);
        $check_duplicate->execute();
        $duplicate_result = $check_duplicate->get_result();
        
        if ($duplicate_result->num_rows > 0) {
            throw new Exception('duplicate_car');
        }
        $check_duplicate->close();
        
        $max_check = $conn->prepare("SELECT max_clients FROM mechanics WHERE mechanic_id = ?");
        $max_check->bind_param("i", $mechanic_id);
        $max_check->execute();
        $max_result = $max_check->get_result();
        $max_data = $max_result->fetch_assoc();
        $max_capacity = $max_data['max_clients'];
        $max_check->close();
        
        $check_capacity = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE mechanic_id = ? AND appointment_date = ? AND appointment_id != ?");
        $check_capacity->bind_param("isi", $mechanic_id, $appointment_date, $appointment_id);
        $check_capacity->execute();
        $capacity_result = $check_capacity->get_result();
        $capacity_row = $capacity_result->fetch_assoc();
        
        if ($capacity_row['count'] >= $max_capacity) {
            throw new Exception('mechanic_full');
        }
        $check_capacity->close();
        
        $update_stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, mechanic_id = ?, status = ? WHERE appointment_id = ?");
        $update_stmt->bind_param("sisi", $appointment_date, $mechanic_id, $status, $appointment_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('update_failed');
        }
        
        $update_stmt->close();
        $conn->commit();
        closeConnection($conn);
        
        header('Location: admin.php?success=updated');
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        closeConnection($conn);
        header('Location: admin.php?error=' . $e->getMessage());
        exit();
    }
    
} elseif ($action === 'delete') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    
    if ($appointment_id <= 0) {
        header('Location: admin.php?error=invalid_id');
        exit();
    }
    
    $conn = getDBConnection();
    
    $delete_stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $delete_stmt->bind_param("i", $appointment_id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        closeConnection($conn);
        header('Location: admin.php?success=deleted');
        exit();
    } else {
        $delete_stmt->close();
        closeConnection($conn);
        header('Location: admin.php?error=delete_failed');
        exit();
    }
    
} else {
    header('Location: admin.php?error=invalid_action');
    exit();
}
?>
