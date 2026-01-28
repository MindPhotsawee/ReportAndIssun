
<?php
session_start();
require 'db.php';
require 'includes/auth_submit_ticket.php';

$role = $_SESSION['role'] ?? 'user';
$isStaff = in_array($role, ['staff']);
/* PHP logic ทั้งหมดอยู่ข้างบน */
?>

<?php 
require 'includes/header.php'; 
require 'includes/sidebar.php';
?>
<style>/* กล่องรูป */
.img-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #ddd;
    background: #f8f9fa;
}

/* ตัวรูป */
.img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;   /* สำคัญมาก */
}

/* ปุ่มลบ */
.img-remove-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border: none;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: #fff;
    font-size: 16px;
    line-height: 18px;
    cursor: pointer;
}
</style>
<div class="content submit-ticket-page">
    <div class="container py-4">

  <div class="card mx-auto" style="max-width:650px">
    <div class="card-body">

      <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
      <?php endif; ?>
      <?php if(isset($success)): ?>
        <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
      <?php endif; ?>

      <!-- ฟอร์มใหม่ -->
      <?php if(!$isStaff): ?>
    <!-- ฟอร์มของ USER -->
    <h4 class="card-title mb-4">ฟอร์มแจ้งซ่อม</h4>

<!-- ฟอร์มเลือกคณะ (GET) -->
<form method="get">
    <div class="mb-3">
        <label class="form-label">สถานที่แจ้งซ่อม</label>
        <select name="office_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- เลือกคณะ / หน่วยงาน --</option>
            <?php
            $offices = $pdo->query("SELECT office_id, office_name FROM office ORDER BY office_name")->fetchAll();
            foreach ($offices as $o) {
                $selected = ($_GET['office_id'] ?? '') == $o['office_id'] ? 'selected' : '';
                echo "<option value='{$o['office_id']}' $selected>{$o['office_name']}</option>";
            }
            ?>
        </select>
    </div>
</form>

<!-- ฟอร์มส่งแจ้งซ่อม (POST) -->
<form method="post" enctype="multipart/form-data">

    <input type="hidden" name="office_id" value="<?= $_GET['office_id'] ?? '' ?>">

    <!-- หัวข้อ -->
    <div class="mb-3">
        <label class="form-label">หัวข้อแจ้งซ่อม</label>
        <select name="title" class="form-select" required <?= empty($_GET['office_id']) ? 'disabled' : '' ?>>
            <option value="">-- เลือกหัวข้อ --</option>

            <?php
            if (!empty($_GET['office_id'])) {
                $stmt = $pdo->prepare("
                    SELECT issue_name 
                    FROM office_issues 
                    WHERE office_id = ? 
                    ORDER BY issue_name
                ");
                $stmt->execute([$_GET['office_id']]);

                foreach ($stmt->fetchAll() as $row) {
                    echo "<option value='{$row['issue_name']}'>{$row['issue_name']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <!-- รายละเอียด -->
    <div class="mb-3">
        <label class="form-label">รายละเอียดปัญหา</label>
        <textarea class="form-control" name="description" rows="4" required></textarea>
    </div>

    <input type="file" name="images[]" accept="image/*" multiple id="fileInputUser" style="display:none;">

    <div class="mb-3">
        <label class="form-label">รูปภาพประกอบ</label><br>
        <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="openCamera()">ถ่ายรูป</button>
        <button type="button" class="btn btn-outline-secondary w-100" onclick="openGallery()">เลือกรูปจากคลังภาพ</button>
    </div>

    <div class="mb-3">
        <div id="previewUser" class="d-flex flex-wrap mt-3"></div>
    </div>

    <button type="submit" class="btn btn-warning w-100">บันทึกข้อมูลแจ้งซ่อม</button>
</form>


<?php else: ?>

           <h4 class="card-title mb-4">ฟอร์มแจ้งซ่อม</h4>

<!-- ฟอร์มเลือกคณะ (GET) -->
<form method="get">
    <div class="mb-3">
        <label class="form-label">สถานที่แจ้งซ่อม</label>
        <select name="office_id" class="form-select" onchange="this.form.submit()">
            <option value="">-- เลือกคณะ / หน่วยงาน --</option>
            <?php
            $offices = $pdo->query("SELECT office_id, office_name FROM office ORDER BY office_name ASC")->fetchAll();
            foreach ($offices as $o) {
                $selected = ($_GET['office_id'] ?? '') == $o['office_id'] ? 'selected' : '';
                echo "<option value='{$o['office_id']}' $selected>{$o['office_name']}</option>";
            }
            ?>
        </select>
    </div>
</form>

<!-- ฟอร์มบันทึก (POST) -->
<form method="post" enctype="multipart/form-data">

    <input type="hidden" name="office_id" value="<?= $_GET['office_id'] ?? '' ?>">

    <!-- หัวข้อแจ้งซ่อม -->
    <div class="mb-3">
        <label class="form-label">หัวข้อแจ้งซ่อม</label>
        <select class="form-select" name="title" required <?= empty($_GET['office_id']) ? 'disabled' : '' ?>>
            <option value="">-- เลือกหัวข้อ --</option>

            <?php
            if (!empty($_GET['office_id'])) {
                $stmt = $pdo->prepare("
                    SELECT issue_name
                    FROM office_issues
                    WHERE office_id = ?
                    ORDER BY issue_name
                ");
                $stmt->execute([$_GET['office_id']]);

                foreach ($stmt->fetchAll() as $row) {
                    echo "<option value='{$row['issue_name']}'>{$row['issue_name']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <!-- รายละเอียด -->
    <div class="mb-3">
        <label class="form-label">รายละเอียดปัญหา</label>
        <textarea class="form-control" name="description" rows="4" required></textarea>
    </div>

    <input type="file" name="images[]" accept="image/*" multiple id="fileInputStaff" style="display:none;">

    <div class="mb-3">
        <label class="form-label">รูปภาพประกอบ (ถ้ามี)</label><br>
        <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="openCamera()">ถ่ายรูป</button>
        <button type="button" class="btn btn-outline-secondary w-100" onclick="openGallery()">เลือกรูปจากคลังภาพ</button>
    </div>

    <div class="mb-3">
        <div id="previewStaff" class="d-flex flex-wrap mt-3"></div>
    </div>

    <button type="submit" class="btn btn-warning w-100">บันทึกข้อมูลแจ้งซ่อม</button>
</form>


<?php endif; ?>

      <div class="mt-3 text-center">
        <?php if(in_array($role, ['staff'])): ?>
            <a href="dashboard.php">กลับหน้าหลัก</a>
        <?php else: ?>
    <a href="list_repair.php">กลับหน้าหลัก</a>
<?php endif; ?>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
