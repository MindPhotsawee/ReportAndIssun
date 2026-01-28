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
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เพิ่มผู้ใช้ — ระบบแจ้งซ่อม</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
</head>

<body>
<div class="container p-3">
  <div class="auth-card mx-auto d-flex">

    <!-- ซ้าย -->
    <div class="left col-md-5 d-none d-md-block">
      <div class="brand">
        <div class="logo">รภพ</div>
        <div>
          <div style="font-weight:800">มหาวิทยาลัยราชภัฏเพชรบุรี</div>
          <div style="font-size:.9rem;opacity:.9">ระบบแจ้งซ่อมออนไลน์</div>
        </div>
      </div>
      <div class="title">เพิ่มบัญชีผู้ใช้งาน</div>
      <div class="subtitle">
        สำหรับเจ้าหน้าที่และผู้ดูแลระบบ
      </div>
    </div>

    <!-- ขวา -->
    <div class="right col-md-7">

      <h4 class="mb-3">เพิ่มผู้ใช้ใหม่</h4>

      <form method="post" action="add_user_save.php">

        <div class="mb-3">
          <label class="form-label">ชื่อ-สกุล</label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">ชื่อบัญชีผู้ใช้</label>
          <input type="text" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">บทบาทผู้ใช้</label>
          <select name="role" class="form-control" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['role_name'] ?>">
                <?= ucfirst($r['role_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">หน่วยงาน</label>
          <select name="office_id" class="form-control" required>
            <option value="">-- เลือกหน่วยงาน --</option>
            <?php foreach ($offices as $o): ?>
              <option value="<?= $o['office_id'] ?>">
                <?= $o['office_name'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">เบอร์โทร</label>
          <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">LINE ID</label>
          <input type="text" name="line_id" class="form-control">
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-success w-100">
            บันทึกผู้ใช้
          </button>
          <a href="dashboard.php" class="btn btn-outline-secondary w-100">
            ยกเลิก
          </a>
        </div>

      </form>

      <div class="footer-small text-center mt-3">
        ระบบพัฒนาโดยฝ่ายเทคโนโลยีสารสนเทศ — มรภ.เพชรบุรี
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
