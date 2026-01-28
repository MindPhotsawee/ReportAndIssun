<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$body = json_decode(file_get_contents('php://input'), true);
$user = trim($body['user'] ?? '');
$pass = $body['pass'] ?? '';

if(!$user || !$pass){
    echo json_encode(['success'=>false,'message'=>'กรุณากรอกข้อมูล']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=repair_system;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch(PDOException $e){
    echo json_encode(['success'=>false,'message'=>'ไม่สามารถเชื่อมฐานข้อมูลได้']);
    exit;
}

// Find user + join role + office
$stmt = $pdo->prepare("
    SELECT u.*, o.office_name, r.role_name
    FROM users u
    LEFT JOIN office o ON u.office_id = o.office_id
    LEFT JOIN tableroles r ON u.role_id = r.id
    WHERE u.email = :u OR u.id = :u
    LIMIT 1
");
$stmt->execute([':u'=>$user]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$row || !password_verify($pass, $row['password_hash'])){
    echo json_encode(['success'=>false,'message'=>'รหัสผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $row['id'];
$_SESSION['role'] = strtolower($row['role_name']);
$_SESSION['name'] = $row['name'];
$_SESSION['email'] = $row['email'];
$_SESSION['phone'] = $row['phone'] ?? '';
$_SESSION['avatar'] = $row['avatar'] ?? 'profile.png';
$_SESSION['office_id'] = $row['office_id'];
$_SESSION['office_name'] = $row['office_name'];

/* ==================== 🔥 UPDATE LOGIN COUNT ==================== */
$stmt = $pdo->prepare("
    UPDATE users
    SET login_count = login_count + 1
    WHERE id = :id
");
$stmt->execute([
    'id' => $_SESSION['user_id']
]);

/* =============================================================== */

echo json_encode(['success'=>true,'role'=>$_SESSION['role']]);
