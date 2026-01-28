<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'user';
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';
$success = '';
$error   = '';

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT name, email, phone, avatar FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die('ไม่พบข้อมูลผู้ใช้');

// เมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $error = 'กรุณากรอกชื่อ';
    } else {
        $avatarPath = $user['avatar'];

        if (!empty($_FILES['avatar']['name'])) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = $_FILES['avatar']['type'];
    $fileSize = $_FILES['avatar']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        $error = 'อนุญาตเฉพาะไฟล์ JPG, PNG, WEBP';
    } elseif ($fileSize > 2 * 1024 * 1024) {
        $error = 'ขนาดไฟล์ต้องไม่เกิน 2MB';
    } else {

        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
        $uploadDir = 'uploads/avatars/';
        $uploadPath = $uploadDir . $newFileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {

            // 🔥 ลบรูปเก่า
            if (
                !empty($user['avatar']) &&
                $user['avatar'] !== 'profile.png' &&
                file_exists($user['avatar'])
            ) {
                unlink($user['avatar']);
            }

            $avatarPath = $uploadPath;
        } else {
            $error = 'อัปโหลดรูปไม่สำเร็จ';
        }
    }
}


        if (!$error) {
            $stmt = $pdo->prepare("UPDATE users SET name = :name, phone = :phone, avatar = :avatar WHERE id = :id");
            $stmt->execute([
                ':name'   => $name,
                ':phone'  => $phone,
                ':avatar' => $avatarPath,
                ':id'     => $user_id
            ]);

            $_SESSION['name']   = $name;
            $_SESSION['phone']  = $phone;
            $_SESSION['avatar'] = $avatarPath;

            $user['name']   = $name;
            $user['phone']  = $phone;
            $user['avatar'] = $avatarPath;

            $redirectUrl = in_array($role, ['staff', 'admin']) ? 'dashboard.php' : 'list_repair.php';
            header('Location: ' . $redirectUrl . '?v=' . time());
            exit;
        }
    }
}

// ==================== HEADER + SIDEBAR ====================
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="content py-7">
    <div class="container">
        <div class="card shadow-sm mx-auto" style="max-width:500px;">
            <div class="card-body">
                <h4 class="mb-3">แก้ไขโปรไฟล์</h4>

                <?php if($success): ?>
                    <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <img id="avatarPreview"
                            src="<?=htmlspecialchars($user['avatar'] ?? 'profile.png')?>?v=<?=time()?>"
                            class="rounded-circle mb-2"
                            width="96" height="96"
                            style="object-fit:cover; cursor:pointer;"
                            onclick="document.getElementById('avatarInput').click()">


                        <input type="file"
                            id="avatarInput"
                            name="avatar"
                            class="d-none"
                            accept="image/png,image/jpeg,image/webp">

                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="document.getElementById('avatarInput').click()">
                            เปลี่ยนรูปโปรไฟล์
                        </button>
                        </div>
                        
                    </div>


                    <div class="mb-3">
                        <label class="form-label">ชื่อ</label>
                        <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['name'])?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เบอร์โทร</label>
                        <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($user['phone'] ?? '')?>" placeholder="เช่น 0812345678">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= in_array($role, ['staff', 'admin']) ? 'dashboard.php' : 'list_repair.php' ?>" class="btn btn-secondary">กลับ</a>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('avatarInput').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php require 'includes/footer.php'; ?>
