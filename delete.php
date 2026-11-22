<?php
require_once 'config.php';
require_login();

// DELETE (POST only, so a link or crawler can't remove records)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = mysqli_prepare(db(), "DELETE FROM sales WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}
header('Location: welcome.php?msg=deleted');
exit;
