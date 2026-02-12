<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$role        = $_SESSION['role'] ?? 'user';
$user_office = $_SESSION['office_name'] ?? '';
$user_name   = $_SESSION['name'] ?? 'Guest';
$user_avatar = $_SESSION['avatar'] ?? 'profile.png';
$user_email  = $_SESSION['email'] ?? '';

switch ($role) {
    case 'admin':
        $role_text = 'ระบบแจ้งซ่อมสำหรับผู้ดูแลระบบ'; break;
    case 'staff':
        $role_text = 'ระบบแจ้งซ่อมสำหรับเจ้าหน้าที่'; break;
    default:
        $role_text = 'ระบบแจ้งซ่อมสำหรับนักศึกษา';
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PBRU — ระบบแจ้งซ่อม</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/dashboard.css" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>

<div class="header d-flex align-items-center px-3 border-bottom">
    <button id="sidebarToggle" class="btn btn-outline-secondary btn-sm d-xl-none me-3">☰</button>

    <h5 class="mb-0 ">
        <?=htmlspecialchars($role_text)?>
        <?php if ($role === 'staff' && $user_office): ?>
            <span class="text-muted fs-6">(<?=htmlspecialchars($user_office)?>)</span>
        <?php endif; ?>
    </h5>

    <div class="dropdown d-flex align-items-center gap-3">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" 
           data-bs-toggle="dropdown" role="button" aria-expanded="false">
            <span class="fw-medium d-none d-md-inline"><?=htmlspecialchars($user_name)?></span>
            <img src="<?=htmlspecialchars($user_avatar)?>" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow p-3" style="min-width:260px;">
            <li class="text-center mb-2">
                <img src="<?=htmlspecialchars($user_avatar)?>" class="rounded-circle mb-2" width="64" height="64" style="object-fit:cover;">
                <div class="fw-semibold"><?=htmlspecialchars($user_name)?></div>
                <div class="text-muted small"><?=htmlspecialchars($role_text)?></div>
                <div class="text-muted small"><?=htmlspecialchars($user_email)?></div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="edit_profile.php">แก้ไขโปรไฟล์</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php">ออกจากระบบ</a></li>
        </ul>
    </div>
</div>
