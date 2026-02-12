<?php
require 'includes/auth_admin.php';
require 'db.php';

// 🔹 เซ็ตตัวแปรจาก session
$user_role      = strtolower($_SESSION['role'] ?? 'user');
$user_office_id = $_SESSION['office_id'] ?? null;

// 🔹 ตรวจสอบสิทธิ์
if ($user_role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// 🔹 รับค่าจาก POST
$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$status_id = (int)($_POST['status_id'] ?? 0);
$office_id = (int)($_POST['office_id'] ?? 0);
$search    = trim($_POST['q'] ?? '');

if (!$ticket_id || !$status_id) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

/* ==================== UPDATE STATUS ==================== */
$updateStmt = $pdo->prepare("
    UPDATE repair_tickets
    SET status_id = ?, updated_at = NOW()
    WHERE id = ?
");
$updateStmt->execute([$status_id, $ticket_id]);

/* ==================== BUILD FILTERS ==================== */
$where = [];
$params = [];

// office filter
if ($office_id) {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = $office_id;
}

// search filter
if ($search !== '') {
    $where[] = "(
        u.name LIKE :q1 OR
        r.title LIKE :q2 OR
        r.description LIKE :q3 OR
        o.office_name LIKE :q4 OR
        s.status_label LIKE :q5
    )";
    $params['q1'] = "%$search%";
    $params['q2'] = "%$search%";
    $params['q3'] = "%$search%";
    $params['q4'] = "%$search%";
    $params['q5'] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ==================== FETCH STATUS COUNTS ตาม filter/search ==================== */
$stmtCounts = $pdo->prepare("
    SELECT s.id, COUNT(*) AS count
    FROM repair_tickets r
    JOIN tablestatus s ON r.status_id = s.id
    JOIN users u       ON r.user_id = u.id
    JOIN office o      ON r.office_id = o.office_id
    $whereSQL
    GROUP BY s.id
");
$stmtCounts->execute($params);
$status_counts = $stmtCounts->fetchAll(PDO::FETCH_KEY_PAIR);

/* ==================== FETCH CURRENT STATUS INFO ==================== */
$stmtCur = $pdo->prepare("
    SELECT status_label, status_color
    FROM tablestatus
    WHERE id = ?
");
$stmtCur->execute([$status_id]);
$current = $stmtCur->fetch(PDO::FETCH_ASSOC);

/* ==================== RETURN JSON ==================== */
echo json_encode([
    'success' => true,
    'ticket_id' => $ticket_id,
    'counts'  => $status_counts,
    'label'   => $current['status_label'] ?? '',
    'color'   => $current['status_color'] ?? 'secondary'
]);
exit;
