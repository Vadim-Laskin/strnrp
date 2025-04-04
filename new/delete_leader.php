<?php
include 'db.php';

// Проверяем, передан ли ID лидера
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Ошибка: ID лидера не указан!");
}

$leader_id = $_GET['id'];

// Проверяем, существует ли лидер
$stmt = $conn->prepare("SELECT * FROM site_leader WHERE id = ?");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ошибка: Лидер не найден!");
}

// Удаляем лидера
$stmt = $conn->prepare("DELETE FROM site_leader WHERE id = ?");
$stmt->bind_param("i", $leader_id);
$stmt->execute();

header("Location: leader_table.php");
exit();
?>