<?php
session_start();
require '../db.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id   = (int)$_SESSION['user_id'];
$user_role = strtolower($_SESSION['role'] ?? 'user');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid ID');
}

/* ================= QUERY ================= */
$sql = "
SELECT
    r.id,
    r.title,
    r.description,
    r.created_at,
    r.updated_at,
    u.name AS user_name,
    u.phone,
    o.office_name,
    s.status_label,
    r.user_id
FROM repair_tickets r
JOIN users u       ON r.user_id = u.id
JOIN office o      ON r.office_id = o.office_id
JOIN tablestatus s ON r.status_id = s.id
WHERE r.id = :id
";

/* user เห็นได้เฉพาะของตัวเอง */
$params = ['id' => $id];

if ($user_role === 'user') {
    $sql .= " AND r.user_id = :user_id";
    $params['user_id'] = $user_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    exit('Permission denied or data not found');
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

    return "$day $month $year $time น. ";
}

/* ================= WORD HEADER ================= */
$filename = 'repair_ticket_'.$data['id'].'.doc';

header("Content-Type: application/vnd.ms-word; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");

/* ================= WORD CONTENT ================= */
echo "
<html>
<head>
<meta charset='UTF-8'>
<style>
body {
    font-family: 'TH SarabunPSK', 'Sarabun', sans-serif;
    font-size: 16pt;
    line-height: 1.6;
}
.h-title {
    font-size: 20pt;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
}
.label {
    font-size: 18pt;
    font-weight: bold;
    margin-top: 14px;
}
.value {
    font-size: 16pt;
    margin-left: 20px;
}
</style>
</head>
<body>

<div class='h-title'>ใบแจ้งซ่อม</div>

<div class='label'>รหัสแจ้งซ่อม</div>
<div class='value'>{$data['id']}</div>

<div class='label'>ผู้แจ้ง</div>
<div class='value'>{$data['user_name']}</div>

<div class='label'>เบอร์โทร</div>
<div class='value'>{$data['phone']}</div>

<div class='label'>หน่วยงาน</div>
<div class='value'>{$data['office_name']}</div>

<div class='label'>หัวข้อ</div>
<div class='value'>{$data['title']}</div>

<div class='label'>รายละเอียด</div>
<div class='value'>{$data['description']}</div>

<div class='label'>สถานะ</div>
<div class='value'>{$data['status_label']}</div>

<div class='label'>วันที่แจ้ง</div>
<div class='value'>".thaiDate($data['created_at'])."</div>

<div class='label'>อัปเดตล่าสุด</div>
<div class='value'>".thaiDate($data['updated_at'])."</div>


<br><br>
<div style='text-align:right;font-size:16pt;'>
ลงชื่อ ......................................................
</div>

</body>
</html>
";
exit;

