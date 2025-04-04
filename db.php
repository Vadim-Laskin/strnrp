<?php
$host = '94.198.51.12'; // Хост
$user = 'gs80717'; // Логин
$password = '0Bkj4EBWYf'; // Пароль
$dbname = 'gs80717'; // Название базы данных

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}
?>