<?php
session_start();
require '../db.php';

/* ================= PERMISSION ================= */
$user_role = strtolower($_SESSION['role'] ?? '');
$user_id   = (int)($_SESSION['user_id'] ?? 0);

if (!in_array($user_role, ['admin','staff','user'])) {
    exit('Permission denied');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid ID');
}

/* ================= QUERY ================= */
$sql = "
SELECT
    r.id,
    r.user_id,
    u.name AS user_name,
    u.phone,
    o.office_name,
    r.title,
    r.description,
    s.status_label,
    r.created_at,
    r.updated_at
FROM repair_tickets r
JOIN users u       ON r.user_id = u.id
JOIN office o      ON r.office_id = o.office_id
JOIN tablestatus s ON r.status_id = s.id
WHERE r.id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$r) {
    exit('Data not found');
}

/* 🔐 user โหลดได้เฉพาะของตัวเอง */
if ($user_role === 'user' && $r['user_id'] != $user_id) {
    exit('Permission denied');
}

function thaiDate($datetime)
{
    if (!$datetime) return '-';

    $months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    $ts = strtotime($datetime);
    $day   = date('j', $ts);
    $month = $months[date('n', $ts)];
    $year  = date('Y', $ts) + 543;
    $time  = date('H.i', $ts);

    return "$day $month $year $time น.";
}

/* ================= CSV HEADER ================= */
$filename = 'repair_ticket_'.$r['id'].'.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename='.$filename);

// BOM สำหรับ Excel ภาษาไทย
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

/* ================= COLUMN ================= */
fputcsv($output, [
    'รหัสแจ้งซ่อม',
    'ผู้แจ้ง',
    'เบอร์โทร',
    'หน่วยงาน',
    'หัวข้อ',
    'รายละเอียด',
    'สถานะ',
    'วันที่แจ้ง',
    'อัปเดตล่าสุด'
]);

/* ================= DATA ================= */
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

fclose($output);
exit;
