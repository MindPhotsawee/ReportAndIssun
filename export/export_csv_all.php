<?php
session_start();
require '../db.php';

/* ==================== AUTH ==================== */
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id     = (int)$_SESSION['user_id'];
$user_role   = strtolower($_SESSION['role'] ?? 'user');
$user_office = $_SESSION['office_id'] ?? null;

/* ==================== PERMISSION ==================== */
if (!in_array($user_role, ['admin','staff','user'])) {
    exit('Permission denied');
}

/* ==================== FILTER ==================== */
$where  = [];
$params = [];

/* admin : ดูทั้งหมด */
if ($user_role === 'staff') {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = $user_office;
}

/* user : ดูเฉพาะของตัวเอง */
if ($user_role === 'user') {
    $where[] = 'r.user_id = :user_id';
    $params['user_id'] = $user_id;
}

$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

/* ==================== DATE FUNCTION ==================== */
function thaiDate($datetime)
{
    if (!$datetime) return '-';

    $months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    $ts = strtotime($datetime);
    return date('j', $ts).' '.$months[date('n',$ts)].' '.(date('Y',$ts)+543)
           .' '.date('H.i',$ts).' น.';
}

/* ==================== QUERY ==================== */
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

/* ==================== CSV HEADER ==================== */
$filename = 'repair_tickets_'.date('Ymd_His').'.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename='.$filename);
echo "\xEF\xBB\xBF"; // BOM

$output = fopen('php://output', 'w');

/* ==================== COLUMN ==================== */
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
