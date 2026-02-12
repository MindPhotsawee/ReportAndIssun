<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = strtolower($_SESSION['role'] ?? 'user');

/* 🔐 อนุญาตเฉพาะ user */
if ($role !== 'user') {
    header("Location: dashboard.php");
    exit;
}

require 'db.php';

/* ===== SESSION ===== */
$user_id     = (int)$_SESSION['user_id'];
$user_role   = strtolower($_SESSION['role'] ?? 'user');
$user_office = $_SESSION['office_id'] ?? null;

/* ==================== PAGINATION ==================== */
function buildQuery(array $replace = []) {
    $query = $_GET;
    foreach ($replace as $k => $v) {
        if ($v === null) unset($query[$k]);
        else $query[$k] = $v;
    }
    return http_build_query($query);
}

$perPageOptions = [10,15,20,25,30];

$perPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $perPageOptions)
    ? (int)$_GET['per_page']
    : 15;

$page = isset($_GET['page']) && (int)$_GET['page'] > 0
    ? (int)$_GET['page']
    : 1;

$offset = ($page - 1) * $perPage;

/* ==================== SEARCH ==================== */
$search = trim($_GET['q'] ?? '');

/* ==================== FILTER (เหมือน ADMIN) ==================== */
$whereTickets   = [];
$paramsTickets  = [];

/* จำกัด user */
$whereTickets[] = 'r.user_id = :user_id';
$paramsTickets['user_id'] = $user_id;

/* 🔍 SEARCH แบบ ADMIN 100% */
if ($search !== '') {
    

    $whereTickets[] = "(
        r.title            LIKE :q1 OR
        r.description      LIKE :q2 OR
        o.office_name      LIKE :q3 OR
        s.status_label     LIKE :q4 
    )";

    $paramsTickets['q1'] = "%$search%";
    $paramsTickets['q2'] = "%$search%";
    $paramsTickets['q3'] = "%$search%";
    $paramsTickets['q4'] = "%$search%";
}

/* รวม WHERE */
$whereSQL = $whereTickets
    ? 'WHERE ' . implode(' AND ', $whereTickets)
    : '';

/* ==================== COUNT ==================== */
$stmtCount = $pdo->prepare("
    SELECT COUNT(*)
    FROM repair_tickets r
    JOIN tablestatus s ON r.status_id = s.id
    JOIN users u       ON r.user_id = u.id
    JOIN office o      ON r.office_id = o.office_id
    $whereSQL
");

$stmtCount->execute($paramsTickets);
$totalRows  = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

/* ==================== FETCH DATA ==================== */
$stmt = $pdo->prepare("
    SELECT 
        r.id,
        r.title,
        r.description,
        r.image_path,
        r.created_at,
        r.updated_at,
        s.status_label,
        s.status_color,
        o.office_name
    FROM repair_tickets r
    JOIN tablestatus s ON r.status_id = s.id
    LEFT JOIN office o ON r.office_id = o.office_id
    $whereSQL
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($paramsTickets as $k => $v) {
    $stmt->bindValue(
    ":$k",
    $v,
    is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR
);

}

$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==================== THAI DATE ==================== */
function thaiDate($datetime)
{
    if (!$datetime) return '-';

    $months = [
        '', 'ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
        'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'
    ];

    $ts = strtotime($datetime);

    return date('j', $ts).' '.
           $months[date('n',$ts)].' '.
           (date('Y',$ts)+543).' '.
           date('H.i',$ts).' น.';
}
?>


<?php require 'includes/header.php'; ?>
<?php require 'includes/sidebar.php'; ?>

<div class="content">
<section id="section-tickets">
    <div class="card">
        
        <div class="card-body">
            <h5 class="card-title mb-3">รายการแจ้งซ่อมทั้งหมด</h5>
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
                <a href="list_repair.php" class="btn btn-sm btn-outline-secondary">ล้าง</a>
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
    <!-- CSV 
    <a href="export/export_csv_all.php?<?= buildQuery() ?>"
       class="btn btn-sm btn-success d-flex align-items-center gap-1">
        <i class="bi bi-file-earmark-excel"></i>
        <span>ดาวน์โหลด Excel ทั้งหมด</span>
    </a>-->
    <a href="export/export_word_search_ex.php?<?= buildQuery() ?>"
       class="btn btn-sm btn-success d-flex align-items-center gap-1">
        <i class="bi bi-file-earmark-excel"></i>
        <span>ดาวน์โหลด Word แบบ Excel </span>
    </a>
</div>

            <div class="d-flex gap-2 mb-3">
                 
            </div>
            <?php if (count($tickets) === 0): ?>
                <p class="text-muted">ไม่มีข้อมูลการแจ้งซ่อม</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>รูปภาพ</th>
                                <th>หัวข้อ</th>
                                <th>สถานที่</th>
                                <th>รายละเอียด</th>
                                <th>สถานะ</th>
                                <th>วันที่แจ้ง</th>
                                <th>วันที่อัพเดต</th>
                                <th class="text-center">ดาวน์โหลด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <div class="thumb-wrapper">
                                        <?php
                                        $images = json_decode($t['image_path'], true);
                                        if (!$images && !empty($t['image_path'])) {
                                            $images = explode(',', $t['image_path']);
                                        }

                                        if ($images && is_array($images)):
                                            foreach ($images as $i => $img):
                                        ?>
                                            <img src="<?=htmlspecialchars(trim($img))?>"
                                                 class="thumbnail clickable-image"
                                                 data-images='<?=json_encode($images, JSON_UNESCAPED_UNICODE)?>'
                                                 data-index="<?=$i?>">
                                        <?php 
                                            endforeach; 
                                        else: 
                                        ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                        </div>
                                    </td>

                                    <td><?=htmlspecialchars($t['title'])?></td>
                                    <td><?=htmlspecialchars($t['office_name'])?></td>
                                    <td class="text-truncate desc-preview"
                                        style="max-width:200px; cursor:pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#descriptionModal"
                                        data-description="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>">

                                        <?= htmlspecialchars(mb_substr($t['description'], 0, 50)) ?>…
                                    </td>

                                    <td>
                                        <span class="badge bg-<?= htmlspecialchars($t['status_color'] ?? 'secondary') ?>">
                                            <?= htmlspecialchars($t['status_label'] ?? 'ไม่ระบุ') ?>
                                        </span>
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
                </div>
            <?php endif; ?>

        </div>
    </div>
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
<!-- Modal สำหรับแสดงรูปภาพ -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">

            <button class="btn-close"
                    data-bs-dismiss="modal"
                    style="position:absolute;top:10px;right:10px;z-index:10;filter:invert(1)">
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

