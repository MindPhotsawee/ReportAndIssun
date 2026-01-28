
<?php
require 'includes/auth_admin.php';
require 'db.php';

$user_office_id = $_SESSION['office_id'] ?? null;

/* ==================== HANDLE STATUS UPDATE (AJAX) ==================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $user_role === 'admin' &&
    isset($_POST['ticket_id'], $_POST['status_id'])
) {
    $ticketId = (int)$_POST['ticket_id'];
    $statusId = (int)$_POST['status_id'];

    // อัปเดตสถานะ
    $stmt = $pdo->prepare("
        UPDATE repair_tickets
        SET status_id = :status_id, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        'status_id' => $statusId,
        'id'        => $ticketId
    ]);

    // 🔥 สร้าง WHERE และ params เหมือนกับตอนแสดงผล
    $whereCount = [];
    $paramsCount = [];

    // 🔹 จำกัดตามสิทธิ์ (เหมือนบรรทัด 149-156)
    if ($user_role === 'staff') {
        $whereCount[] = 'r.office_id = :office_id';
        $paramsCount['office_id'] = $user_office_id;
    }
    elseif ($user_role === 'admin' && !empty($_POST['office_id'])) {
        $whereCount[] = 'r.office_id = :office_id';
        $paramsCount['office_id'] = (int)$_POST['office_id'];
    }

    // 🔍 เพิ่ม search condition
    if (!empty($_POST['search'])) {
        $search = trim($_POST['search']);
        $whereCount[] = "(
            u.name             LIKE :q1 OR
            r.title            LIKE :q2 OR
            r.description      LIKE :q3 OR
            o.office_name      LIKE :q4 OR
            s.status_label     LIKE :q5 
        )";
        $paramsCount['q1'] = "%$search%";
        $paramsCount['q2'] = "%$search%";
        $paramsCount['q3'] = "%$search%";
        $paramsCount['q4'] = "%$search%";
        $paramsCount['q5'] = "%$search%";
    }

    $whereSQL = $whereCount ? 'WHERE ' . implode(' AND ', $whereCount) : '';

    // นับ status ใหม่ (ตาม filter + search)
    $stmtStatus = $pdo->prepare("
        SELECT s.id AS status_id, COUNT(*) AS count
        FROM repair_tickets r
        JOIN users u ON r.user_id = u.id
        JOIN office o ON r.office_id = o.office_id
        JOIN tablestatus s ON r.status_id = s.id
        $whereSQL
        GROUP BY s.id
    ");
    $stmtStatus->execute($paramsCount);
    $counts = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);

    // ดึงข้อมูล status ปัจจุบัน
    $stmtCur = $pdo->prepare("
        SELECT status_label, status_color
        FROM tablestatus
        WHERE id = ?
    ");
    $stmtCur->execute([$statusId]);
    $current = $stmtCur->fetch(PDO::FETCH_ASSOC);

    // ส่ง JSON กลับ
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        echo json_encode([
            'success' => true,
            'ticket_id' => $ticketId,
            'counts' => $counts,
            'label' => $current['status_label'] ?? '',
            'color' => $current['status_color'] ?? 'secondary'
        ]);
        exit;
    }
}

/* ==================== FETCH OFFICES (ADMIN) ==================== */
$offices = [];
if ($user_role === 'admin') {
    $offices = $pdo->query("
        SELECT office_id, office_name, office_code
        FROM office
        ORDER BY office_name
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ==================== PAGINATION ==================== */
function buildQuery(array $replace = []) {
    $query = $_GET;

    foreach ($replace as $k => $v) {
        if ($v === null) {
            unset($query[$k]);
        } else {
            $query[$k] = $v;
        }
    }

    return http_build_query($query);
}

// ตัวเลือกจำนวนรายการต่อหน้า
$perPageOptions = [10, 15, 20, 25, 30];

// รับค่าจำนวนรายการต่อหน้า
$perPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $perPageOptions)
    ? (int)$_GET['per_page']
    : 15; // ค่าเริ่มต้น

// รับค่าหน้าปัจจุบัน
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
    ? (int)$_GET['page']
    : 1;

// คำนวณ OFFSET สำหรับ SQL
$offset = ($page - 1) * $perPage;
/* ==================== search ==================== */
$search = trim($_GET['q'] ?? '');

/* ==================== FILTER TICKETS ==================== */
$whereTickets = [];
$paramsTickets = [];

/* จำกัดตามสิทธิ์ */
if ($user_role === 'staff') {
    $whereTickets[] = 'r.office_id = :office_id';
    $paramsTickets['office_id'] = $user_office_id;
}
elseif ($user_role === 'admin' && !empty($_GET['office_id'])) {
    $whereTickets[] = 'r.office_id = :office_id';
    $paramsTickets['office_id'] = (int)$_GET['office_id'];
}

/* 🔍 SEARCH */
if ($search !== '') {
    

    $whereTickets[] = "(
        u.name             LIKE :q1 OR
        r.title            LIKE :q2 OR
        r.description      LIKE :q3 OR
        o.office_name      LIKE :q4 OR
        s.status_label     LIKE :q5 
    )";

    $paramsTickets['q1'] = "%$search%";
    $paramsTickets['q2'] = "%$search%";
    $paramsTickets['q3'] = "%$search%";
    $paramsTickets['q4'] = "%$search%";
    $paramsTickets['q5'] = "%$search%";
}

/* รวม WHERE */
$whereSQL = $whereTickets
    ? 'WHERE ' . implode(' AND ', $whereTickets)
    : '';


/* ==================== COUNT TICKETS ==================== */
$stmtCount = $pdo->prepare("
    SELECT COUNT(*)
    FROM repair_tickets r
    JOIN users u ON r.user_id = u.id
    JOIN office o ON r.office_id = o.office_id
    JOIN tablestatus s ON r.status_id = s.id

    $whereSQL
");
$stmtCount->execute($paramsTickets);
$totalRows = $stmtCount->fetchColumn();

// จำนวนหน้าทั้งหมด
$totalPages = ceil($totalRows / $perPage);


/* ==================== FETCH TICKETS ==================== */
$stmt = $pdo->prepare("
    SELECT r.id,
       u.name AS user_name,
       t.role_name,
       t.role_label,
       t.role_color,
       u.phone AS user_phone,
       o.office_name,
       r.title,
       r.description,
       r.image_path,
       r.status_id,
       s.status_name AS status,
       r.created_at,
       r.updated_at
    FROM repair_tickets r
    JOIN users u ON r.user_id = u.id
    JOIN tableroles t ON u.role_id = t.id
    JOIN office o ON r.office_id = o.office_id
    JOIN tablestatus s ON r.status_id = s.id
    $whereSQL
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($paramsTickets as $key => $val) {
    $stmt->bindValue(
        ":$key",
        $val,
        is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR
    );
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==================== STATUS COUNT ====================
// ดึง count ตาม search + office
$stmtStatus = $pdo->prepare("
    SELECT s.id AS status_id, COUNT(*) AS count
    FROM repair_tickets r
    JOIN tablestatus s ON r.status_id = s.id
    JOIN users u       ON r.user_id = u.id
    JOIN office o      ON r.office_id = o.office_id
    $whereSQL
    GROUP BY s.id
");
$stmtStatus->execute($paramsTickets);
$tmp = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

// แปลงเป็น [status_id => count] ชัวร์ ๆ
$status_counts = [];
foreach($tmp as $row){
    $status_counts[(int)$row['status_id']] = (int)$row['count'];
}

/* ==================== FETCH STATUSES ==================== */
$statuses = $pdo->query("
    SELECT id, status_name, status_label, status_color
    FROM tablestatus
    ORDER BY id
")->fetchAll(PDO::FETCH_ASSOC);

/* ==================== FETCH USERS ==================== */
if ($user_role === 'admin') {
    $users = $pdo->query("
        SELECT u.id, u.name, u.email, u.phone, t.role_name AS role, o.office_name
        FROM users u
        LEFT JOIN tableroles t ON u.role_id = t.id
        LEFT JOIN office o ON u.office_id = o.office_id
        ORDER BY u.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtUsers = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.phone, t.role_name AS role, o.office_name
        FROM users u
        LEFT JOIN tableroles t ON u.role_id = t.id
        LEFT JOIN office o ON u.office_id = o.office_id
        WHERE u.office_id = :office_id
        ORDER BY u.id ASC
    ");
    $stmtUsers->execute(['office_id' => $user_office_id]);
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
}

function thaiDate($datetime)
{
    if (!$datetime) return '-';

    $months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    $ts = strtotime($datetime);

    $day   = date('j', $ts);
    $month = $months[date('n', $ts)];
    $year  = date('Y', $ts) + 543;
    $time  = date('H.i', $ts);

    return "$day $month $year $time น. ";
}


/* ==================== HEADER / SIDEBAR ==================== */
require 'includes/header.php';
require 'includes/sidebar.php';
?>
<style>
.status-card {
    position: relative;
    overflow: hidden;
}

/* เส้นขอบขวา แสดงตลอด */
.status-card::after {
    content: "";
    position: absolute;
    top: 12%;
    right: 0;
    width: 6px;
    height: 76%;
    border-radius: 4px 0 0 4px;
    background-color: var(--line-color);
}

</style>
<!-- ==================== CONTENT ==================== -->
<div class="content">

<!-- ===== STATUS CARDS ===== -->
<div class="row g-3 mb-4">
<?php foreach ($statuses as $s): ?>
    <div class="col-md-3">
        <div class="card status-card"
        data-status-id="<?= $s['id'] ?>"
        data-status="<?= htmlspecialchars($s['status_name']) ?>"
        data-color="<?= htmlspecialchars($s['status_color'] ?? 'secondary') ?>"
        style="--line-color: var(--bs-<?= htmlspecialchars($s['status_color'] ?? 'secondary') ?>);">

            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">
                        <?= htmlspecialchars($s['status_label'] ?? $s['status_name']) ?>
                    </div>
                    <div class="fs-3 fw-bold count">
                        <?= $status_counts[$s['id']] ?? 0 ?>
                    </div>
                </div>
        
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
    <!-- 🔍 SEARCH -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <form method="get" class="d-flex gap-2" style="max-width:500px;width:100%;">
            <?php foreach ($_GET as $k => $v): ?>
                <?php if (!in_array($k, ['q','page'])): ?>
                    <input type="hidden"
                        name="<?= htmlspecialchars($k) ?>"
                        value="<?= htmlspecialchars($v) ?>">
                <?php endif; ?>
            <?php endforeach; ?>

            <input type="text"
                name="q"
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                class="form-control form-control-sm"
                placeholder="ค้นหา ชื่อผู้แจ้ง / หัวข้อ / สถานที่ ...">

            <button class="btn btn-sm btn-primary">
                ค้นหา
            </button>

            

            <?php if (!empty($_GET['q'])): ?>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">ล้าง</a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="d-flex gap-2 mb-3">
    <!-- CSV -->
            <a href="export/export_csv_search.php?<?= buildQuery() ?>"
            class="btn btn-sm btn-success d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i>
            <span> Excel</span>
            <!-- WORD -->
            <a href="export/export_word_search.php?<?= buildQuery() ?>"
            class="btn btn-sm btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-word"></i>
            <span> Word</span>
            </a>
     <!-- CSV --> 
    <a href="export/export_word_search_ex.php?<?= buildQuery() ?>"
       class="btn btn-sm btn-success d-flex align-items-center gap-1">
        <i class="bi bi-file-earmark-excel"></i>
        <span>ดาวน์โหลด Word แบบ Excel </span>
    </a>
    <!--
     WORD 
    <a href="export/export_word_all_page.php"
       class="btn btn-sm btn-primary d-flex align-items-center gap-1">
        <i class="bi bi-file-earmark-word"></i>
        <span>ดาวน์โหลด Word ทั้งหมด</span>
    </a>-->
</div>


      
    <!-- ===== TICKETS ===== -->
    <section id="section-tickets">
        <h4>รายการแจ้งซ่อมทั้งหมด</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>รูป</th><th>ผู้แจ้ง</th><th>สิทธิ์</th><th>สถานที่</th>
                        <th>หัวข้อ</th><th>รายละเอียด</th><th>สถานะ</th>
                        <th>สร้าง</th><th>อัปเดต</th>
                        <th class="text-center">ดาวน์โหลด</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($tickets as $t): ?>
                    <tr>
                        <td>
                            <?php
                            $images = json_decode($t['image_path'], true) ?: [];
                            if(!$images && $t['image_path']) $images = [$t['image_path']];
                            if($images){
                                foreach($images as $i=>$img){
                                    echo '<img src="'.htmlspecialchars($img).'"
                                    class="thumbnail clickable-image"
                                    data-images="'.htmlspecialchars(json_encode($images), ENT_QUOTES, 'UTF-8').'"
                                    data-index="'.$i.'">';
                                }
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td><?=htmlspecialchars($t['user_name'])?></td>
                        <td>
                             <span class="badge bg-<?= htmlspecialchars($t['role_color'] ?? 'secondary') ?>">
                                <?= htmlspecialchars($t['role_label'] ?? $t['role_name']) ?>
                            </span>
                        </td>
                        <td><?=htmlspecialchars($t['office_name'])?></td>
                        <td><?=htmlspecialchars($t['title'])?></td>
                        <td class="text-truncate desc-preview"
                            style="max-width:200px; cursor:pointer;"
                            data-bs-toggle="modal"
                            data-bs-target="#descriptionModal"
                            data-description="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>">

                            <?= htmlspecialchars(mb_substr($t['description'], 0, 50)) ?>…
                        </td>
                        <td>
                    <?php
                    $status_id = $t['status_id'] ?? 1;
                    ?>

                    <?php if ($user_role === 'admin'): ?>
                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">

                            <?php if (!empty($_GET['office_id'])): ?>
                                <input type="hidden" name="office_id" value="<?= (int)$_GET['office_id'] ?>">
                            <?php endif; ?>

                            <?php if (!empty($_GET['page'])): ?>
                                <input type="hidden" name="page" value="<?= (int)$_GET['page'] ?>">
                            <?php endif; ?>

                            <select class="form-select form-select-sm status-select"
                                    name="status_id"
                                    data-ticket="<?= $t['id'] ?>">
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s['id'] ?>"
                                        <?= $status_id == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['status_label'] ?? $s['status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                    <?php else: ?>

                        <?php
                        $currentStatus = null;
                        foreach ($statuses as $s) {
                            if ($s['id'] == $status_id) {
                                $currentStatus = $s;
                                break;
                            }
                        }
                        ?>

                        <?php if ($currentStatus): ?>
                            <span class="badge bg-<?= htmlspecialchars($currentStatus['status_color'] ?? 'secondary') ?>">
                                <?= htmlspecialchars($currentStatus['status_label'] ?? $currentStatus['status_name']) ?>
                            </span>
                        <?php endif; ?>

                    <?php endif; ?>   <!-- 🔥 จุดที่ขาดอยู่ตรงนี้ -->
                    </td>

                        <td><?=thaiDate($t['created_at'])?></td>
                        <td><?=thaiDate($t['updated_at'])?></td>
                       
                       
                    <!-- WORD -->            
                    <td align="center">
                    <div class="btn-group gap-2" role="group">
                        <a href="export/export_ticket_word1.php?id=<?= $t['id'] ?>" 
                        class="btn-sm text-primary" 
                        target="_blank" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="ดาวน์โหลด word" 
                        style="font-size:18px;cursor: pointer;">
                        <i class="fas fa-file-word"></i></a>
                                        
                        <a href="export/export_ticket_csv1.php?id=<?= $t['id'] ?>" 
                        class="btn-sm text-success" 
                        target="_blank" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="ดาวน์โหลดไฟล์ Excel" 
                        style="font-size:18px;cursor: pointer;">
                        <i class="fas fa-file-excel"></i>
                        </a>
                                        
                       
                    </div>
                    
                    </td>


                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- ===== PAGINATION ===== -->
            <div class="d-flex justify-content-end mb-3">
    <form method="get" class="d-flex align-items-center gap-2">

        <?php foreach ($_GET as $k => $v): ?>
            <?php if (!in_array($k, ['per_page','page'])): ?>
                <input type="hidden"
                       name="<?= htmlspecialchars($k) ?>"
                       value="<?= htmlspecialchars($v) ?>">
            <?php endif; ?>
        <?php endforeach; ?>

        <input type="hidden" name="page" value="1">

        <select name="per_page"
                class="form-select form-select-sm"
                style="width:130px"
                onchange="this.form.submit()">
            <?php foreach ([10,15,20,25,30] as $n): ?>
                <option value="<?= $n ?>" <?= $perPage == $n ? 'selected' : '' ?>>
                    <?= $n ?> รายการ
                </option>
            <?php endforeach; ?>
        </select>

    </form>
</div>
<?php if ($totalPages > 1): ?>
<nav>
<ul class="pagination justify-content-center">

    <!-- PREV -->
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link"
           href="?<?= buildQuery(['page' => $page - 1]) ?>">
            &lt;
        </a>
    </li>

    <?php
    $range = 1; // แสดงรอบ ๆ หน้าปัจจุบัน
    $start = max(1, $page - $range);
    $end   = min($totalPages, $page + $range);
    ?>

    <!-- หน้าแรก -->
    <?php if ($start > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?<?= buildQuery(['page' => 1]) ?>">1</a>
        </li>
        <?php if ($start > 2): ?>
            <li class="page-item disabled">
                <span class="page-link">…</span>
            </li>
        <?php endif; ?>
    <?php endif; ?>

    <!-- หน้ากลาง -->
    <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link"
               href="?<?= buildQuery(['page' => $i]) ?>">
                <?= $i ?>
            </a>
        </li>
    <?php endfor; ?>

    <!-- หน้าสุดท้าย -->
    <?php if ($end < $totalPages): ?>
        <?php if ($end < $totalPages - 1): ?>
            <li class="page-item disabled">
                <span class="page-link">…</span>
            </li>
        <?php endif; ?>
        <li class="page-item">
            <a class="page-link"
               href="?<?= buildQuery(['page' => $totalPages]) ?>">
                <?= $totalPages ?>
            </a>
        </li>
    <?php endif; ?>

    <!-- NEXT -->
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link"
           href="?<?= buildQuery(['page' => $page + 1]) ?>">
            &gt;
        </a>
    </li>

</ul>
</nav>
<?php endif; ?>
</section>


</div>
<!-- /.content -->
<div class="modal fade" id="descriptionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable description-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="descriptionModalBody" style="white-space:pre-wrap;">
                <!-- ข้อความเต็มจะถูกเติมด้วย JS -->
            </div>
        </div>
    </div>
</div>


<!-- ===== IMAGE MODAL ===== -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <button type="button" class="btn-close"
                    data-bs-dismiss="modal"
                    style="position:absolute;top:10px;right:10px;z-index:1000;filter:invert(1);">
            </button>
            <div class="modal-body p-0">
                <div id="carouselImages" class="carousel slide">
                    <div class="carousel-inner" id="carouselInner"></div>
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#carouselImages" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#carouselImages" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el)
});
</script>

<?php require 'includes/footer.php'; ?>