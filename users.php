<?php
require 'includes/auth_admin.php';
require 'db.php';

$search = trim($_GET['q'] ?? '');

/* ==================== FILTER ==================== */
$where = [];
$params = [];

/* 🔍 SEARCH */
if ($search !== '') {
    $where[] = "(
        u.name        LIKE :q1 OR
        u.email       LIKE :q2 OR
        u.phone       LIKE :q3 OR
        o.office_name LIKE :q4 OR
        t.role_name   LIKE :q5
    )";

    $params['q1'] = "%$search%";
    $params['q2'] = "%$search%";
    $params['q3'] = "%$search%";
    $params['q4'] = "%$search%";
    $params['q5'] = "%$search%";
}

/* 🔒 จำกัดตามสิทธิ์ */
if ($user_role !== 'admin') {
    $where[] = "u.office_id = :office_id";
    $params['office_id'] = (int)$user_office_id;
}

/* รวม WHERE */
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* ==================== SQL ==================== */
$sql = "
    SELECT 
    u.id,
    u.name,
    u.email,
    u.phone,
    u.login_count,
    t.role_name AS role,
    o.office_name
    FROM users u
    LEFT JOIN tableroles t ON u.role_id = t.id
    LEFT JOIN office o ON u.office_id = o.office_id
    $whereSQL
    ORDER BY u.id ASC
";

/* ==================== EXECUTE ==================== */
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
/* ======================================== */
$offices = $pdo->query("
    SELECT office_id, office_name 
    FROM office 
    ORDER BY office_name
")->fetchAll(PDO::FETCH_ASSOC);

require 'includes/header.php';
require 'includes/sidebar.php';
?>
<!-- ===== USERS ===== -->
<div class="content">
    <h4>ผู้ใช้ทั้งหมด</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="get" class="d-flex gap-2" style="max-width:360px;width:100%;">
            <input type="text"
                name="q"
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                class="form-control form-control-sm"
                placeholder="ค้นหา ชื่อ / Email / คณะ ...">

            <button class="btn btn-sm btn-primary">ค้นหา</button>

            <?php if (!empty($_GET['q'])): ?>
                <a href="users.php" class="btn btn-sm btn-outline-secondary">ล้าง</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">สิทธิ์</th>
                    <th>ชื่อ-สกุล</th>
                    <th>User ID</th>
                    <th>เบอร์ติดต่อ</th>
                    <th>คณะ</th>
                    <th class="text-center">จำนวนครั้งที่เข้าใช้งาน</th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if($users): foreach($users as $u): ?>
                <tr>
                    <td class="text-center">
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge bg-danger">ADMIN</span>
                        <?php elseif ($u['role'] === 'staff'): ?>
                            <span class="badge bg-warning text-dark">STAFF</span>
                        <?php else: ?>
                            <span class="badge bg-primary">USER</span>
                        <?php endif; ?>
                    </td>
                    <td><?=htmlspecialchars($u['name'])?></td>
                    <td><?=htmlspecialchars($u['email'])?></td>
                    <td><?=htmlspecialchars($u['phone'] ?? '-')?></td>
                    <td><?=htmlspecialchars($u['office_name'] ?? '-')?></td>
                    <td class="text-center"><?= (int)$u['login_count'] ?></td>
                    <td class="text-center">
                    <?php if ($u['role'] !== 'admin'): ?>
                        <button class="btn btn-sm btn-warning"
                                onclick="openEditUser(<?= $u['id'] ?>)">
                            ✏️
                        </button>

                        <button class="btn btn-sm btn-danger"
                                onclick="confirmDelete(<?= $u['id'] ?>)">
                            🗑️
                        </button>
                    <?php else: ?>
                        <span class="badge bg-secondary">ADMIN</span>
                    <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center">ไม่มีผู้ใช้</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php require 'includes/footer.php'; ?>
</div>
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editUserForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">แก้ไขผู้ใช้</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">

        <div class="mb-2">
            <label>ชื่อ-สกุล</label>
            <input type="text" class="form-control" name="name" id="edit_name">
        </div>

        <div class="mb-2">
            <label>User ID</label>
            <input type="text" class="form-control" name="email" id="edit_email">
        </div>

        <div class="mb-2">
            <label>เบอร์ติดต่อ</label>
            <input type="text" class="form-control" name="phone" id="edit_phone">
        </div>

        <div class="mb-2">
            <label>คณะ</label>
            <select class="form-select" name="office_id" id="edit_office">
                <?php foreach($offices as $o): ?>
                    <option value="<?= $o['office_id'] ?>">
                        <?= htmlspecialchars($o['office_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button class="btn btn-primary" type="submit">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditUser(id){
    fetch('ajax/get_user.php?id=' + id)
    .then(res => res.json())
    .then(u => {
        document.getElementById('edit_id').value     = u.id;
        document.getElementById('edit_name').value   = u.name;
        document.getElementById('edit_email').value  = u.email;
        document.getElementById('edit_phone').value  = u.phone ?? '';
        document.getElementById('edit_office').value = u.office_id;

        new bootstrap.Modal(
            document.getElementById('editUserModal')
        ).show();
    });
}

document.getElementById('editUserForm').addEventListener('submit', function(e){
    e.preventDefault();

    fetch('ajax/update_user.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(res => res.json())
    .then(r => {
        if(r.success){
            location.reload(); // หรืออัปเดตเฉพาะแถวก็ได้
        }else{
            alert(r.message);
        }
    });
});

function confirmDelete(id){
    if(!confirm('ต้องการลบผู้ใช้นี้หรือไม่?')) return;

    fetch('ajax/delete_user.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id:id})
    })
    .then(res=>res.json())
    .then(r=>{
        if(r.success) location.reload();
        else alert(r.message);
    });
}
</script>

