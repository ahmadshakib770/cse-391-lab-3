<?php

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_mechanics.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    $mechanic_id = intval($_POST['mechanic_id'] ?? 0);
    $max_clients = intval($_POST['max_clients'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    
    if ($mechanic_id <= 0 || $max_clients <= 0 || empty($status)) {
        header('Location: manage_mechanics.php?error=invalid_data');
        exit();
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        header('Location: manage_mechanics.php?error=invalid_data');
        exit();
    }
    
    $conn = getDBConnection();
    
    $check_stmt = $conn->prepare("SELECT mechanic_id FROM mechanics WHERE mechanic_id = ?");
    $check_stmt->bind_param("i", $mechanic_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $check_stmt->close();
        closeConnection($conn);
        header('Location: manage_mechanics.php?error=mechanic_not_found');
        exit();
    }
    $check_stmt->close();
    
    $update_stmt = $conn->prepare("UPDATE mechanics SET max_clients = ?, status = ? WHERE mechanic_id = ?");
    $update_stmt->bind_param("isi", $max_clients, $status, $mechanic_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        closeConnection($conn);
        header('Location: manage_mechanics.php?success=updated');
        exit();
    } else {
        $update_stmt->close();
        closeConnection($conn);
        header('Location: manage_mechanics.php?error=update_failed');
        exit();
    }
}

header('Location: manage_mechanics.php');
exit();
?>
