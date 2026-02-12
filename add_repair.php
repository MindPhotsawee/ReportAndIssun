<?php
session_start();
require 'db.php';   // ← ต้องมีบรรทัดนี้
?>
<?php require 'includes/header.php'; ?>
<?php require 'includes/sidebar.php'; ?>
<?php

/* ================== เพิ่มหัวข้อ ================== */
if (isset($_POST['add_issue'])) {

    $office_id  = $_POST['office_id'] ?? '';
    $issue_name = trim($_POST['issue_name'] ?? '');

    if (!empty($office_id) && !empty($issue_name)) {

        $stmt = $pdo->prepare("
            INSERT INTO office_issues (office_id, issue_name) 
            VALUES (?, ?)
        ");
        $stmt->execute([$office_id, $issue_name]);

        // รีเฟรชหน้า ป้องกันกด F5 แล้วเพิ่มซ้ำ
        header("Location: add_repair.php?filter_office=" . $office_id);
        exit;
    }
}

/* ================== แก้ไข ================== */
if (isset($_POST['update_issue'])) {

    $id = $_POST['id'] ?? '';
    $issue_name = trim($_POST['issue_name'] ?? '');
    $filterOffice = $_POST['filter_office'] ?? '';

    if ($id && $issue_name) {
        $stmt = $pdo->prepare("UPDATE office_issues SET issue_name = ? WHERE id = ?");
        $stmt->execute([$issue_name, $id]);

        header("Location: add_repair.php?filter_office=" . $filterOffice);
        exit;
    }
}

/* ================== ลบ ================== */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];
    $filterOffice = $_GET['filter_office'] ?? '';

    $stmt = $pdo->prepare("DELETE FROM office_issues WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: add_repair.php?filter_office=" . $filterOffice);
    exit;
}

$offices = $pdo->query("
    SELECT office_id, office_name 
    FROM office 
    ORDER BY office_name
")->fetchAll();

$filterOffice = $_GET['filter_office'] ?? '';
$issues = [];

if (!empty($filterOffice)) {

    $stmt = $pdo->prepare("
        SELECT oi.*, o.office_name
        FROM office_issues oi
        JOIN office o ON oi.office_id = o.office_id
        WHERE oi.office_id = ?
        ORDER BY o.office_name
    ");
    $stmt->execute([$filterOffice]);
    $issues = $stmt->fetchAll();
}

?>
<div class="content">
<div class="container py-4">

<h4 class="mb-4">จัดการหัวข้อแจ้งซ่อม</h4>

<!-- ฟอร์มเพิ่ม -->
<div class="card mb-4">
<div class="card-body">
<form method="post">
    <div class="row">
        <div class="col-md-4">
            <select name="office_id" class="form-select" required>
                <option value="">-- เลือกหน่วยงาน --</option>
                <?php foreach($offices as $o): ?>
                    <option value="<?=$o['office_id']?>">
                        <?=$o['office_name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <input type="text" name="issue_name" class="form-control" placeholder="ชื่อหัวข้อแจ้งซ่อม" required>
        </div>

        <div class="col-md-2">
            <button type="submit" name="add_issue" class="btn btn-success w-100">
                ➕ เพิ่ม
            </button>
        </div>
    </div>
</form>
</div>
</div>

<h4 class="mb-4">เลือกดูรายการ</h4>
<!-- ตัวกรอง -->
<form method="get" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <select name="filter_office" class="form-select" onchange="this.form.submit()">
                <option value="">-- แสดงทุกหน่วยงาน --</option>
                <?php foreach($offices as $o): ?>
                    <option value="<?=$o['office_id']?>"
                        <?= ($_GET['filter_office'] ?? '') == $o['office_id'] ? 'selected' : '' ?>>
                        <?=$o['office_name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<!-- ตารางรายการ -->
<div class="card">
<div class="card-body">

<table class="table table-bordered">
<thead>
<tr>
    <th>หน่วยงาน</th>
    <th>หัวข้อแจ้งซ่อม</th>
    <th width="150">จัดการ</th>
</tr>
</thead>
<tbody>

<?php if (empty($filterOffice)): ?>
    <tr>
        <td colspan="3" class="text-center text-muted">
            กรุณาเลือกหน่วยงานก่อน
        </td>
    </tr>

<?php elseif (empty($issues)): ?>
    <tr>
        <td colspan="3" class="text-center text-danger">
            ไม่พบรายการ
        </td>
    </tr>

<?php else: ?>
    <?php foreach($issues as $row): ?>
    <tr>
        <td><?=$row['office_name']?></td>

        <td>
            <form method="post" class="d-flex">
                <input type="hidden" name="id" value="<?=$row['id']?>">
                <input type="hidden" name="filter_office" value="<?=$filterOffice?>">
                <input type="text" name="issue_name" 
                       value="<?=$row['issue_name']?>" 
                       class="form-control me-2">
                <button type="submit" name="update_issue" 
                        class="btn btn-warning btn-sm">
                    💾
                </button>
            </form>
        </td>

        <td class="text-center">
            <a href="?delete=<?=$row['id']?>&filter_office=<?=$filterOffice?>" 
               onclick="return confirm('ยืนยันการลบ?')" 
               class="btn btn-danger btn-sm">
               🗑 ลบ
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>

</tbody>

</table>

</div>

</div>
<?php require 'includes/footer.php'; ?>
</div>
</div>


