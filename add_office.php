<?php
session_start();
require 'db.php';   // ← ต้องมีบรรทัดนี้
?>
<?php require 'includes/header.php'; ?>
<?php require 'includes/sidebar.php'; ?>
<?php
/* ================== เพิ่มหน่วยงาน ================== */
if (isset($_POST['add_office'])) {

    $office_name = trim($_POST['office_name'] ?? '');

    if (!empty($office_name)) {
        $stmt = $pdo->prepare("INSERT INTO office (office_name) VALUES (?)");
        $stmt->execute([$office_name]);

        header("Location: add_office.php");
        exit;
    }
}


/* ================== แก้ไขหน่วยงาน ================== */
if (isset($_POST['update_office'])) {

    $office_id   = $_POST['office_id'] ?? '';
    $office_name = trim($_POST['office_name'] ?? '');

    if ($office_id && $office_name) {
        $stmt = $pdo->prepare("UPDATE office SET office_name = ? WHERE office_id = ?");
        $stmt->execute([$office_name, $office_id]);

        header("Location: add_office.php");
        exit;
    }
}

/* ================== ลบหน่วยงาน ================== */
if (isset($_GET['delete_office'])) {

    $office_id = $_GET['delete_office'];

    // ลบหัวข้อของหน่วยงานนั้นก่อน (กัน error foreign key)
    $stmt = $pdo->prepare("DELETE FROM office_issues WHERE office_id = ?");
    $stmt->execute([$office_id]);

    $stmt = $pdo->prepare("DELETE FROM office WHERE office_id = ?");
    $stmt->execute([$office_id]);

    header("Location: add_office.php");
    exit;

}

$offices = $pdo->query("
    SELECT office_id, office_name 
    FROM office 
    ORDER BY office_name
")->fetchAll();

$filterOffice = $_GET['filter_office'] ?? '';

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

} else {

    $issues = $pdo->query("
        SELECT oi.*, o.office_name
        FROM office_issues oi
        JOIN office o ON oi.office_id = o.office_id
        ORDER BY o.office_name
    ")->fetchAll();
}

?>
<div class="content">
<div class="container py-4">

<h4 class="mb-4">จัดการหน่วยงาน</h4>

<div class="card mb-4">
<div class="card-body">

<!-- เพิ่มหน่วยงาน -->
<form method="post" class="row mb-3">
    <div class="col-md-8">
        <input type="text" name="office_name" class="form-control"
               placeholder="ชื่อหน่วยงาน / คณะ" required>
    </div>
    <div class="col-md-4">
        <button type="submit" name="add_office"
                class="btn btn-primary w-100">
            ➕ เพิ่มหน่วยงาน
        </button>
    </div>
</form>

<!-- ตารางหน่วยงาน -->
<table class="table table-bordered">
<thead>
<tr>
    <th>ชื่อหน่วยงาน</th>
    <th width="180">จัดการ</th>
</tr>
</thead>
<tbody>
<?php foreach($offices as $o): ?>
<tr>
    <td>
        <form method="post" class="d-flex">
            <input type="hidden" name="office_id"
                   value="<?=$o['office_id']?>">
            <input type="text" name="office_name"
                   value="<?=$o['office_name']?>"
                   class="form-control me-2">
            <button type="submit"
                    name="update_office"
                    class="btn btn-warning btn-sm">
                💾
            </button>
        </form>
    </td>

    <td class="text-center">
        <a href="?delete_office=<?=$o['office_id']?>"
           onclick="return confirm('ลบหน่วยงานนี้? (หัวข้อจะถูกลบด้วย)')"
           class="btn btn-danger btn-sm">
            🗑 ลบ
        </a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>

</div>
<?php require 'includes/footer.php'; ?>

