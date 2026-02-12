<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title     = trim($_POST['title'] ?? '');
    $otherTitle = trim($_POST['other_title'] ?? '');   
    $desc      = trim($_POST['description'] ?? '');
    $office_id = $_POST['office_id'] ?? null;
    $user_id   = $_SESSION['user_id'] ?? null;

    if ($title === 'other' && !empty($otherTitle)) {
        $title = $otherTitle;
    }

    if (!$title || !$desc || !$office_id || !$user_id) {
        $error = "กรุณากรอกข้อมูลให้ครบ";
        return;
    }

    /* ================= INSERT TICKET ================= */


    /* ================= INSERT TICKET ================= */
    $stmt = $pdo->prepare("
        INSERT INTO repair_tickets (user_id, office_id, title, description)
        VALUES (:u, :o, :t, :d)
    ");

    $stmt->execute([
        ':u' => $user_id,
        ':o' => $office_id,
        ':t' => $title,
        ':d' => $desc
    ]);

    $ticket_id = $pdo->lastInsertId();

    /* ================= UPLOAD IMAGES ================= */
    $uploadedImages = [];

    if (!empty($_FILES['images']['name'][0])) {

        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {

            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','heic','heif'];

            if (!in_array($ext, $allowed)) continue;

            $newName = 'repair_' . time() . '_' . rand(1000,9999) . '.jpg';
            $path = $uploadDir . $newName;

            if (move_uploaded_file($tmp, $path)) {
                $uploadedImages[] = 'uploads/' . $newName;
            }
        }
    }

    /* ================= UPDATE IMAGE PATH ================= */
    if (!empty($uploadedImages)) {
        $stmt = $pdo->prepare("
            UPDATE repair_tickets
            SET image_path = :img
            WHERE id = :id
        ");

        $stmt->execute([
            ':img' => json_encode($uploadedImages, JSON_UNESCAPED_UNICODE),
            ':id'  => $ticket_id
        ]);
    }

    $success = "บันทึกข้อมูลเรียบร้อย";
}
?>