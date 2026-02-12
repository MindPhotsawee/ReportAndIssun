<?php
session_start();
require 'db.php';

/* 🔐 อนุญาตเฉพาะ admin / staff */
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','staff'])) {
    header("Location: index.php");
    exit;
}

/* ดึง role */
$roles = $pdo->query("SELECT role_name FROM tableroles ORDER BY role_name ASC")->fetchAll();

/* ดึงหน่วยงาน */
$offices = $pdo->query("SELECT office_id, office_name FROM office ORDER BY office_name ASC")->fetchAll();
?>
<?php require 'includes/header.php'; ?>
<?php require 'includes/sidebar.php'; ?>

<div class="content">
<div class="container py-4">

<h4 class="mb-4">เพิ่มผู้ใช้ใหม่</h4>

<div class="card shadow-sm">
<div class="card-body">

<form method="post">

<div class="row g-3">

  <div class="col-md-6">
    <label class="form-label">ชื่อ-สกุล</label>
    <input type="text" name="name" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">ชื่อบัญชีผู้ใช้</label>
    <input type="text" name="email" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">รหัสผ่าน</label>
    <input type="password" name="pass" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">บทบาทผู้ใช้</label>
    <select name="role" class="form-select" required>
      <?php foreach ($roles as $r): ?>
        <option value="<?= $r['role_name'] ?>">
          <?= ucfirst($r['role_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">หน่วยงาน</label>
    <select name="office_id" class="form-select" required>
      <option value="">-- เลือกหน่วยงาน --</option>
      <?php foreach ($offices as $o): ?>
        <option value="<?= $o['office_id'] ?>">
          <?= $o['office_name'] ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">เบอร์โทร</label>
    <input type="text" name="phone" class="form-control">
  </div>

  <div class="col-md-6">
    <label class="form-label">LINE ID</label>
    <input type="text" name="line" class="form-control">
  </div>

</div>

<div class="mt-4 d-flex justify-content-end">
  <button type="submit" class="btn btn-primary">
    บันทึกผู้ใช้
  </button>
</div>

</form>

</div>
</div>

</div>
<?php require 'includes/footer.php'; ?>
</div>


