
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
<div class="content">
<div class="container py-4">

<h4 class="mb-4">ฟอร์มแจ้งซ่อม</h4>

<div class="card shadow-sm">
<div class="card-body">

<?php if(isset($error)): ?>
  <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
<?php endif; ?>

<?php if(isset($success)): ?>
  <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
<?php endif; ?>


<?php if(!$isStaff): ?>

<!-- ================= USER ================= -->

<form method="get" class="mb-4">
  <div class="row">
    <div class="col-md-6">
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
  </div>
</form>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="office_id" value="<?= $_GET['office_id'] ?? '' ?>">

<div class="row g-3">

<div class="col-md-6">
    <label class="form-label">หัวข้อแจ้งซ่อม</label>
    <select name="title" class="form-select"  onchange="toggleOtherInput()"
        required <?= empty($_GET['office_id']) ? 'disabled' : '' ?>>

    <option value="">-- เลือกหัวข้อ --</option>      <?php
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
       <option value="other">อื่นๆ </option>
    </select>
  </div>
    <div class="col-md-6" id="otherTitleWrapper" style="display:none;">
        <label class="form-label">หัวข้อเพิ่มเติม </label>
        <input type="text" name="other_title" 
            class="form-control"
            placeholder="กรอกหัวข้อแจ้งซ่อม">
    </div>


  <div class="col-md-12">
    <label class="form-label">รายละเอียดปัญหา</label>
    <textarea class="form-control" name="description" rows="4" required></textarea>
  </div>

</div>

<hr class="my-4">

<input type="file" name="images[]" accept="image/*" multiple id="fileInputUser" hidden>

<div class="mb-3">
  <label class="form-label">รูปภาพประกอบ</label>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary"
      onclick="openCamera()">ถ่ายรูป</button>
    <button type="button" class="btn btn-outline-secondary"
      onclick="openGallery()">เลือกรูปจากคลังภาพ</button>
  </div>
</div>

<div id="previewUser" class="d-flex flex-wrap gap-2 mb-3"></div>

<div class="d-flex justify-content-end">
  <button type="submit" class="btn btn-warning">
    บันทึกข้อมูลแจ้งซ่อม
  </button>
</div>

</form>

<?php else: ?>

<!-- ================= STAFF ================= -->

<!-- โครงเหมือนกันทุกอย่าง -->
<form method="get" class="mb-4">
  <div class="row">
    <div class="col-md-6">
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
  </div>
</form>


<form method="post" enctype="multipart/form-data">

<input type="hidden" name="office_id" value="<?= $_GET['office_id'] ?? '' ?>">

<div class="row g-3">

  <div class="col-md-6">
    <div class="col-md-6">
    <label class="form-label">หัวข้อแจ้งซ่อม</label>
    <select name="title" class="form-select"  onchange="toggleOtherInput()"
        required <?= empty($_GET['office_id']) ? 'disabled' : '' ?>>

    <option value="">-- เลือกหัวข้อ --</option>      <?php
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
       <option value="other">อื่นๆ </option>
    </select>
  </div>
    <div class="col-md-6" id="otherTitleWrapper" style="display:none;">
        <label class="form-label">หัวข้อเพิ่มเติม </label>
        <input type="text" name="other_title" 
            class="form-control"
            placeholder="กรอกหัวข้อแจ้งซ่อม">
    </div>

  <div class="col-md-12">
    <label class="form-label">รายละเอียดปัญหา</label>
    <textarea class="form-control" name="description" rows="4" required></textarea>
  </div>

</div>

<hr class="my-4">

<input type="file" name="images[]" accept="image/*" multiple id="fileInputStaff" hidden>

<div class="mb-3">
  <label class="form-label">รูปภาพประกอบ (ถ้ามี)</label>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary"
      onclick="openCamera()">ถ่ายรูป</button>
    <button type="button" class="btn btn-outline-secondary"
      onclick="openGallery()">เลือกรูปจากคลังภาพ</button>
  </div>
</div>

<div id="previewStaff" class="d-flex flex-wrap gap-2 mb-3"></div>

<div class="d-flex justify-content-end">
  <button type="submit" class="btn btn-warning">
    บันทึกข้อมูลแจ้งซ่อม
  </button>
</div>

</form>

<?php endif; ?>

</div>
</div>
<script>
function toggleOtherInput() {
    const select = document.querySelector('select[name="title"]');
    const wrapper = document.getElementById('otherTitleWrapper');

    if (!select || !wrapper) return;

    if (select.value === 'other') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    toggleOtherInput();
});
</script>

<?php require 'includes/footer.php'; ?>
</div>
</div>

