<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['pass'] ?? '';
$role  = $_POST['role'] ?? 'user';
$phone = trim($_POST['phone'] ?? '');
$line  = trim($_POST['line'] ?? '');
$office_id = isset($_POST['office_id']) && $_POST['office_id'] !== '' ? (int)$_POST['office_id'] : null;

// Validation
if(!$name || !$email || !$pass || !$office_id){
    echo json_encode(['success'=>false,'message'=>'กรอกข้อมูลไม่ครบ']);
    exit;
}
if(strlen($pass) < 8){
    echo json_encode(['success'=>false,'message'=>'รหัสผ่านต้องไม่น้อยกว่า 8 ตัว']);
    exit;
}

// Email ซ้ำ
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e'=>$email]);
if($stmt->fetch()){
    echo json_encode(['success'=>false,'message'=>'บัญชีนี้มีผู้ใช้แล้ว']);
    exit;
}

// Map role string -> role_id
$stmtRole = $pdo->prepare('SELECT id FROM tableroles WHERE role_name = :role LIMIT 1');
$stmtRole->execute([':role' => $role]);
$roleData = $stmtRole->fetch();
if(!$roleData){
    echo json_encode(['success'=>false,'message'=>'Role ไม่ถูกต้อง']);
    exit;
}
$role_id = (int)$roleData['id'];

// Hash password
$hash = password_hash($pass, PASSWORD_DEFAULT);

// Insert
$stmt = $pdo->prepare('
    INSERT INTO users (name,email,password_hash,line_id,role_id,office_id,phone,created_at,approved_to_edit,avatar)
    VALUES (:n,:e,:p,:l,:r,:o,:ph,NOW(),0,"profile.png")
');
$ok = $stmt->execute([
    ':n'=>$name,
    ':e'=>$email,
    ':p'=>$hash,
    ':l'=>$line,
    ':r'=>$role_id,
    ':o'=>$office_id,
    ':ph'=>$phone
]);

echo json_encode($ok ? ['success'=>true] : ['success'=>false,'message'=>'ไม่สามารถลงทะเบียนได้']);
