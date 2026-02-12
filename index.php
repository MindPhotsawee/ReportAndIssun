
<?php
require 'db.php'; // ต้องมี $pdo
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login / Register — ระบบแจ้งซ่อม ม.ราชภัฏเพชรบุรี</title>

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
          <div style="font-size:0.9rem;opacity:0.9">ระบบแจ้งซ่อมออนไลน์</div>
        </div>
      </div>
      <div class="title">สะดวก รวดเร็ว ตรวจสอบได้</div>
      <div class="subtitle">ลงชื่อเข้าใช้เพื่อแจ้งซ่อม ติดตามสถานะ และรับการแจ้งเตือนผ่าน LINE</div>
      <div style="margin-top:24px;font-size:0.9rem;opacity:0.95">
        <ul>
          <li>ฟอร์มแจ้งซ่อมที่เข้าใจง่าย</li>
          <li>ระบบติดตามสถานะและหน้าจัดการสำหรับเจ้าหน้าที่</li>
          <li>เชื่อมต่อ Messaging API</li>
        </ul>
      </div>
    </div>

    <!-- ขวา -->
    <div class="right col-md-7">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">เข้าสู่ระบบ</h4>
      </div>

      <div class="form-toggle mb-3">
        <button class="btn btn-outline-primary active" id="btnLogin">Login</button>
        <button class="btn btn-outline-secondary" id="btnRegister">Register</button>
      </div>

      <!-- Login Form -->
      <form id="loginForm">
        <div class="mb-3">
          <label class="form-label">ชื่อบัญชีผู้ใช้</label>
          <input type="text" class="form-control" id="loginUser" required>
        </div>
        <div class="mb-3">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" class="form-control" id="loginPass" required>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <input type="checkbox" id="remember"> <label for="remember" class="mb-0">จดจำฉัน</label>
          </div>
          <small class="text-muted">ยังไม่มีบัญชี? <a href="#" id="openRegister">ลงทะเบียน</a></small>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        </div>
      </form>

      <!-- Register Form -->
      <form id="registerForm" class="d-none">
        <div class="mb-3">
          <label class="form-label">ชื่อ-สกุล</label>
          <input type="text" class="form-control" id="regName" name="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">สร้างบัญชีผู้ใช้</label>
          <input type="text" class="form-control" id="regEmail" name="email" autocomplete="username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" class="form-control" id="regPass" name="pass" required>
        </div>
        <div class="mb-3">
          <label class="form-label">ยืนยันรหัสผ่าน</label>
          <input type="password" class="form-control" id="regPass2" required>
        </div>

        <!-- Role Dropdown -->
        <div class="mb-3">
          <label class="form-label">บทบาทผู้ใช้</label>
          <select id="regRole" name="role" class="form-control" required>
            <?php
              $stmt = $pdo->prepare("
                  SELECT role_name 
                  FROM tableroles 
                  WHERE role_name = 'user'
                  LIMIT 1
              ");
              $stmt->execute();
              $role = $stmt->fetch();

              if ($role) {
                  echo "<option value=\"{$role['role_name']}\" selected>{$role['role_name']}</option>";
              }
            ?>
          </select>
        </div>


        <!-- Office Dropdown -->
        <div class="mb-3">
          <label class="form-label">หน่วยงาน</label>
          <select class="form-control" id="regOffice" name="office_id" required>
            <option value="">-- เลือกหน่วยงาน --</option>
            <?php
              $offices = $pdo->query("SELECT office_id, office_name FROM office ORDER BY office_name ASC")->fetchAll();
              foreach($offices as $o){
                  echo "<option value=\"{$o['office_id']}\">{$o['office_name']}</option>";
              }
            ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">เบอร์โทร (บังคับ)</label>
          <input type="text" class="form-control" id="regPhone" name="phone">
        </div>
        <div class="mb-3">
          <label class="form-label">ID LINE (ไม่บังคับ)</label>
          <input type="text" class="form-control" id="lineId" name="line">
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-success w-100">ลงทะเบียน</button>
        </div>
      </form>

      <div class="footer-small text-center mt-3">
        ระบบพัฒนาโดยฝ่ายเทคโนโลยีสารสนเทศ — มหาวิทยาลัยราชภัฏเพชรบุรี
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/index.js"></script>
</body>
</html>