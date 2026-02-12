<?php
header('Content-Type: application/json; charset=utf-8');
require '../includes/auth_admin.php';
require '../db.php';

/* ================= VALIDATE ================= */
$id        = (int)($_POST['id'] ?? 0);
$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$office_id = (int)($_POST['office_id'] ?? 0);

if ($id <= 0 || $name === '' || $email === '') {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบ'
    ]);
    exit;
}

/* 🚫 ห้ามแก้ admin */
$stmtCheck = $pdo->prepare("
    SELECT t.role_name
    FROM users u
    JOIN tableroles t ON u.role_id = t.id
    WHERE u.id = ?
");
$stmtCheck->execute([$id]);
$role = $stmtCheck->fetchColumn();

if ($role === 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่อนุญาตให้แก้ไขผู้ดูแลระบบ'
    ]);
    exit;
}

/* ================= UPDATE ================= */
$stmt = $pdo->prepare("
    UPDATE users 
    SET 
        name = :name,
        email = :email,
        phone = :phone,
        office_id = :office_id
    WHERE id = :id
");

$stmt->execute([
    'name'      => $name,
    'email'     => $email,
    'phone'     => $phone ?: null,
    'office_id' => $office_id ?: null,
    'id'        => $id
]);

echo json_encode(['success' => true]);
