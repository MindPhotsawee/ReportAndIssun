<?php
require '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)$data['id'];

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

echo json_encode(['success'=>true]);
