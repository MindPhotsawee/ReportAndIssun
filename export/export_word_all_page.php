<?php
require '../includes/auth_admin.php';
require '../db.php';

/* ==================== PERMISSION ==================== */
if (!in_array($user_role, ['admin','staff'])) {
    exit('Permission denied');
}

/* ==================== SEARCH / FILTER ==================== */
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($user_role === 'staff') {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = $user_office_id;
}
elseif ($user_role === 'admin' && !empty($_GET['office_id'])) {
    $where[] = 'r.office_id = :office_id';
    $params['office_id'] = (int)$_GET['office_id'];
}

if ($search !== '') {
    $where[] = "(
        u.name LIKE :q1 OR
        r.title LIKE :q2 OR
        o.office_name LIKE :q3
    )";
    $params['q1'] = "%$search%";
    $params['q2'] = "%$search%";
    $params['q3'] = "%$search%";
}

$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

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
JOIN office o ON r.office_id = o.office_id
JOIN tablestatus s ON r.status_id = s.id
$whereSQL
ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function thaiDate($dt){
    if(!$dt) return '-';
    $m = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $t = strtotime($dt);
    return date('j',$t).' '.$m[date('n',$t)].' '.(date('Y',$t)+543).' '.date('H.i',$t).' น.';
}

/* ==================== WORD HEADER ==================== */
$filename = 'repair_report_' . date('Ymd_His') . '.doc';
header("Content-Type: application/vnd.ms-word; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page { size:A4; margin:20mm; }
body 
{
    font-family: 'TH SarabunPSK', sans-serif;
    font-size: 16pt;
}

h2 { text-align:center; margin-bottom:15px; }

table { width:100%; border-collapse:collapse; }
th, td {
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
}
th { background:#f2f2f2; text-align:center; }


.page-break {
    page-break-before: always;   /* Word รุ่นเก่า */
    break-before: page;          /* Word รุ่นใหม่ */
    margin-top: 0;
}

.label { font-weight:bold; font-size:18pt; margin-top:12px; }
.value { margin-left:20px; font-size:16pt; white-space:pre-wrap; }
</style>
</head>
<body>


<h2>รายงานสรุปการแจ้งซ่อม</h2>

<table>
<tr>
    <th width="8%">รหัส</th>
    <th width="18%">ผู้แจ้ง</th>
    <th width="20%">สถานที่</th>
    <th width="34%">หัวข้อ</th>
    <th width="10%">สถานะ</th>
    <th width="10%">วันที่แจ้ง</th>
</tr>

<?php foreach ($rows as $r): ?>
<tr>
    <td align="center"><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['user_name']) ?></td>
    <td><?= htmlspecialchars($r['office_name']) ?></td>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td align="center"><?= htmlspecialchars($r['status_label']) ?></td>
    <td align="center"><?= thaiDate($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php foreach ($rows as $r): ?>

<div class="page-break"></div>

<h2 class="page-break">รายละเอียดใบแจ้งซ่อม</h2>

<div class="label">รหัสแจ้งซ่อม</div>
<div class="value"><?= $r['id'] ?></div>

<div class="label">ผู้แจ้ง</div>
<div class="value"><?= htmlspecialchars($r['user_name']) ?></div>

<div class="label">เบอร์โทร</div>
<div class="value"><?= htmlspecialchars($r['phone']) ?></div>

<div class="label">หน่วยงาน</div>
<div class="value"><?= htmlspecialchars($r['office_name']) ?></div>

<div class="label">หัวข้อ</div>
<div class="value"><?= htmlspecialchars($r['title']) ?></div>

<div class="label">รายละเอียด</div>
<div class="value"><?= htmlspecialchars($r['description']) ?></div>

<div class="label">สถานะ</div>
<div class="value"><?= htmlspecialchars($r['status_label']) ?></div>

<div class="label">วันที่แจ้ง</div>
<div class="value"><?= thaiDate($r['created_at']) ?></div>

<div class="label">อัปเดตล่าสุด</div>
<div class="value"><?= thaiDate($r['updated_at']) ?></div>

<br><br>
<div style="text-align:right;">ลงชื่อ ................................................</div>

<?php endforeach; ?>


</body>
</html>
<?php exit; ?>
