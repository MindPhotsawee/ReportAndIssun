<?php
session_start();
require '../db.php';

/* ==================== PERMISSION ==================== */
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id     = (int)$_SESSION['user_id'];
$user_role   = strtolower($_SESSION['role'] ?? 'user');
$user_office = $_SESSION['office_id'] ?? null;

if (!in_array($user_role, ['admin','staff','user'])) {
    exit('Permission denied');
}


/* ==================== SEARCH / FILTER ==================== */
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

//* จำกัดตามสิทธิ์ */
if ($user_role === 'user') {
    // user เห็นเฉพาะ ticket ของตัวเอง
    $where[] = 'r.user_id = :user_id';
    $params['user_id'] = $_SESSION['user_id'];
}
elseif ($user_role === 'staff') {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = $user_office_id;
}
elseif ($user_role === 'admin' && !empty($_GET['office_id'])) {
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

/* ==================== QUERY ==================== */
$sql = "
SELECT
    r.id,
    u.name          AS user_name,
    u.phone         AS user_phone,
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
$whereSQL
ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

$logoPath = __DIR__ . '/pbru.png';

$logoBase64 = '';
if (file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $data = file_get_contents($logoPath);
    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}


/* ==================== WORD HEADER ==================== */
$filename = 'repair_tickets_'.date('Ymd_His').'.doc';

header("Content-Type: application/vnd.ms-word; charset=UTF-8");
header("Content-Disposition: attachment; filename={$filename}");
header("Pragma: no-cache");
header("Expires: 0");
?>


<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<link href="https://fonts.cdnfonts.com/css/th-sarabunpsk" rel="stylesheet">
<style>
@page {
    size: A4 landscape;
    margin: 15mm;
}

body {
        font-family: 'TH SarabunPSK', sans-serif;
        font-size: 12pt;
    }

    h3.report-title {
        text-align: center;
        font-size: 26pt;   /* ใหญ่ ชัด */
        font-weight: bold;
        margin: 20px 0 30px 0;
    }

    .page-break {
        page-break-before: always;
        break-before: page;
    }

h3 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 20pt;
    font-weight: bold;
}

/* ===== TABLE ===== */
table {
    border-collapse: collapse;
    border: 1px solid #000;
    width: 100%;
    table-layout: fixed;
}


th, td {
    border: 1px solid #000 !important;
    mso-table-lspace: 0pt;
    mso-table-rspace: 0pt;
    mso-line-height-rule: exactly;
}
tr {
    page-break-inside: avoid;
}


th {
    background-color: #f2f2f2;
    text-align: center;
    font-weight: bold;
    font-size: 16pt;
}


/* กำหนดความกว้างแต่ละคอลัมน์ */
.col-id        { width: 6%;  text-align:center; }
.col-user      { width: 12%; }
.col-phone     { width: 10%; text-align:center; }
.col-office    { width: 12%; }
.col-title     { width: 14%; }
.col-desc {
    width: 22%;
    max-width: 22%;
    white-space: normal;
    word-break: break-all;
    overflow-wrap: anywhere;
}

.col-status    { width: 8%;  text-align:center; }
.col-date      { width: 8%;  text-align:center; }

.footer {
    margin-top: 15px;
    text-align: right;
    font-size: 14pt;
}

.logo {
    text-align: center;
    margin-top: 30px;
    margin-bottom: 20px;
}

.logo img {
    width: 180px;
    height: auto;
}


.first-page {
    page-break-after: always;  /* บังคับขึ้นหน้าใหม่ */
}

</style>
</head>
<body>

<div class="first-page">
    <div class="logo">
        <img src="<?= $logoBase64 ?>"
     width="80"
     height="auto"
     style="mso-width-source:userset; mso-height-source:userset;"
     alt="PBRU Logo">

    </div>
    <h3>รายงานการแจ้งซ่อม</h3>
</div>




<table>
<thead>
<tr>
    <th class="col-id">รหัส</th>
    <th class="col-user">ผู้แจ้ง</th>
    <th class="col-phone">เบอร์โทร</th>
    <th class="col-office">สถานที่</th>
    <th class="col-title">หัวข้อ</th>
    <th class="col-desc">รายละเอียด</th>
    <th class="col-status">สถานะ</th>
    <th class="col-date">วันที่แจ้ง</th>
    <th class="col-date">อัปเดตล่าสุด</th>
</tr>
</thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
    <td class="col-id"><?= $r['id'] ?></td>
    <td class="col-user"><?= htmlspecialchars($r['user_name']) ?></td>
    <td class="col-phone"><?= htmlspecialchars($r['user_phone']) ?></td>
    <td class="col-office"><?= htmlspecialchars($r['office_name']) ?></td>
    <td class="col-title"><?= htmlspecialchars($r['title']) ?></td>

    <!-- FIX ล้นหน้า -->
    <td class="col-desc">
        <div style="
            max-width:100%;
            word-break:break-word;
            overflow-wrap:break-word;
            white-space:normal;
        ">
            <?= nl2br(htmlspecialchars($r['description'])) ?>
        </div>
    </td>

    <td class="col-status"><?= htmlspecialchars($r['status_label']) ?></td>
    <td class="col-date"><?= thaiDate($r['created_at']) ?></td>
    <td class="col-date"><?= thaiDate($r['updated_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>


<div class="footer">
    พิมพ์รายงานเมื่อ <?= thaiDate(date('Y-m-d H:i:s')) ?>
</div>

</body>
</html>

<?php exit; ?>
