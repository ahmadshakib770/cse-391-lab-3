<?php

define('DB_HOST', 'sql204.infinityfree.com');
define('DB_USER', 'if0_41287560');
define('DB_PASS', '11lakhtaka');
define('DB_NAME', 'if0_41287560_car_workshop');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

function closeConnection($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}
?>
