<?php
session_start();
require '../db.php';

/* ================= AUTH ================= */
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id     = (int)$_SESSION['user_id'];
$user_role   = strtolower($_SESSION['role'] ?? 'user');
$user_office = $_SESSION['office_id'] ?? null;

if (!in_array($user_role, ['admin','staff','user'])) {
    exit('Permission denied');
}

/* ================= FILTER ================= */
$search = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

/* staff */
if ($user_role === 'staff') {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = $user_office;
}

/* user */
if ($user_role === 'user') {
    $where[] = 'r.user_id = :user_id';
    $params['user_id'] = $user_id;
}

/* admin + office */
if ($user_role === 'admin' && !empty($_GET['office_id'])) {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = (int)$_GET['office_id'];
}

/* 🔍 SEARCH */
if ($search !== '') {
    $where[] = "(
        u.name        LIKE :q1 OR
        r.title       LIKE :q2 OR
        r.description LIKE :q3 OR
        o.office_name LIKE :q4
    )";
    $params['q1'] = "%$search%";
    $params['q2'] = "%$search%";
    $params['q3'] = "%$search%";
    $params['q4'] = "%$search%";
}

$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

/* ================= QUERY ================= */
$sql = "
SELECT
    r.id,
    u.name AS user_name,
    u.phone,
    o.office_name,
    r.title,
    r.description,
    s.status_label,
    r.created_at,
    r.updated_at
FROM repair_tickets r
JOIN users u ON r.user_id = u.id
JOIN tablestatus s ON r.status_id = s.id
LEFT JOIN office o ON r.office_id = o.office_id
$whereSQL
ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= CSV HEADER ================= */
$filename = 'repair_tickets_'.date('Ymd_His').'.csv';

header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=$filename");
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

/* COLUMN */
fputcsv($output, [
    'รหัสแจ้งซ่อม',
    'ผู้แจ้ง',
    'เบอร์โทร',
    'สถานที่',
    'หัวข้อ',
    'รายละเอียด',
    'สถานะ',
    'วันที่แจ้ง',
    'อัปเดตล่าสุด'
]);

function thaiDate($dt){
    if(!$dt) return '-';
    $m = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $t = strtotime($dt);
    return date('j',$t).' '.$m[date('n',$t)].' '.(date('Y',$t)+543).' '.date('H.i',$t).' น.';
}

foreach ($rows as $r) {
    fputcsv($output, [
        $r['id'],
        $r['user_name'],
        $r['phone'],
        $r['office_name'],
        $r['title'],
        $r['description'],
        $r['status_label'],
        thaiDate($r['created_at']),
        thaiDate($r['updated_at'])
    ]);
}

fclose($output);
exit;
