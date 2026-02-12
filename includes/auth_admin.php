<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ต้อง login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// อนุญาตเฉพาะ staff และ admin
if (!in_array($_SESSION['role'], ['staff', 'admin'])) {
    header('Location: dashboard.php'); // หรือหน้าอื่นที่เหมาะสม
    exit;
}

// ตัวแปรกลาง ใช้ต่อได้ทุกหน้า
$user_id        = $_SESSION['user_id'];
$user_role      = $_SESSION['role'];
$user_name      = $_SESSION['name'] ?? '';
$user_office_id = $_SESSION['office_id'] ?? null;
$user_office    = $_SESSION['office_name'] ?? '';
?>