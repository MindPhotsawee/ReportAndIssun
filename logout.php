<?php
session_start();
session_unset();
session_destroy();

// เปลี่ยน path ให้ตรงกับตำแหน่งไฟล์ login
header('Location: index.php');
exit;
?>
