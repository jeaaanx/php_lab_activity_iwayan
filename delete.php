<?php
require_once 'dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php?msg=invalid");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: index.php?msg=notfound");
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM users WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: index.php?msg=deleted");
exit;
?>