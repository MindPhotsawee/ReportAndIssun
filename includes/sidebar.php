<?php $role = $_SESSION['role'] ?? 'user'; ?>

<div class="sidebar" id="sidebar">

    <!-- ปุ่มปิด (เฉพาะมือถือ) -->
    <button class="sidebar-close d-xl-none" id="sidebarClose">×</button>

    <?php if (!in_array($role, ['admin'])): ?>
        <a href="submit_ticket.php">➕ แจ้งซ่อม</a>
    <?php endif; ?>
    
    <?php if(in_array($role, ['staff','admin'])): ?>
        <a href="dashboard.php"> 📝 รายการแจ้งซ่อม</a>
    <?php endif; ?>

    <?php if(in_array($role, ['user'])): ?>
        <a href="list_repair.php"> 🔧 รายการแจ้งซ่อม</a>
    <?php endif; ?>

    <?php if(in_array($role, ['admin','staff'])): ?>
        <a href="users.php"> 👥 ผู้ใช้ทั้งหมด</a>
    <?php endif; ?>

    <?php if(in_array($role, ['admin'])): ?>
        <a href="add_user.php"> ➕ จัดการบัญชีผู้ใช้</a>
    <?php endif; ?>

    <?php if(in_array($role, ['admin'])): ?>
        <a href="add_office.php"> 🏢 จัดการหน่วยงาน</a>
    <?php endif; ?>

    <?php if(in_array($role, ['admin'])): ?>
        <a href="add_repair.php"> ⚙ จัดการแจ้งซ่อม</a>
    <?php endif; ?>

</div>
