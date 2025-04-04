<?php
session_start();
require 'db.php'; // Подключение к БД

// Проверка авторизации
if (!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit;
}

// Функция для форматирования timestamp в формат дд.мм.гггг чч:мм:сс
function formatTimestamp($timestamp) {
    return date("d.m.Y H:i:s", $timestamp);
}

// Форматирование времени в формате чч:мм:сс
function formatTime($timestamp) {
    return date('H:i:s', $timestamp); // Час:Минуты:Секунды
}

// Форматирование даты в формате дд.мм.гггг
function formatDate($timestamp) {
    return date('d.m.Y', $timestamp); // День.Месяц.Год
}

// Фильтры
$where = [];
$params = [];
if (!empty($_GET['user_id'])) {
    $where[] = "user_id = ?";
    $params[] = $_GET['user_id'];
}
if (!empty($_GET['ip'])) {
    $where[] = "ip LIKE ?";
    $params[] = "%" . $_GET['ip'] . "%";
}
if (!empty($_GET['admin'])) {
    $where[] = "admin LIKE ?";
    $params[] = "%" . $_GET['admin'] . "%";
}
if (!empty($_GET['description'])) {
    $where[] = "description LIKE ?";
    $params[] = "%" . $_GET['description'] . "%";
}
if (!empty($_GET['time'])) {
    $where[] = "time >= ?";
    $params[] = strtotime($_GET['time']);
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Пагинация
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Запрос с JOIN для получения ника из таблицы accounts
$query = "SELECT bl.*, a.name FROM ban_list bl 
          LEFT JOIN accounts a ON bl.user_id = a.id
          $where_sql ORDER BY bl.time DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$bans = $result->fetch_all(MYSQLI_ASSOC);

// Количество записей для пагинации
$count_query = "SELECT COUNT(*) FROM ban_list bl $where_sql";
$count_stmt = $conn->prepare($count_query);
if ($params) {
    $count_stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);


$ban_time = strtotime($ban['ban_time']); // Преобразуем строку в timestamp
$ban_time_formatted = formatTime($ban_time); // Далее уже форматируем

echo 'ban_time: ' . $ban['ban_time']; // Выводим значение перед форматированием
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200">
    <title>Бан-лист</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            margin: 0;
            padding: 20px;
            color: #fff;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #000;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background: #0072ff;
            color: white;
        }
        .filters input {
            padding: 5px;
            margin: 5px;
            width: 150px;
        }
        .pagination {
            margin-top: 20px;
        }
        .pagination a {
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            background: #0072ff;
            border-radius: 5px;
            margin: 2px;
        }
        .pagination a:hover {
            background: #005bb5;
        }
        .btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #b02a37;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Бан-лист</h2>

        <!-- Фильтры -->
        <form method="GET" class="filters">
            <input type="text" name="user_id" placeholder="ID игрока" value="<?= $_GET['user_id'] ?? '' ?>">
            <input type="text" name="ip" placeholder="IP" value="<?= $_GET['ip'] ?? '' ?>">
            <input type="text" name="admin" placeholder="Админ" value="<?= $_GET['admin'] ?? '' ?>">
            <input type="text" name="description" placeholder="Причина" value="<?= $_GET['description'] ?? '' ?>">
            <input type="date" name="time" value="<?= $_GET['time'] ?? '' ?>">
            <button type="submit" class="btn">Фильтр</button>
        </form>

        <table>
            <tr>
                <th>Ник</th>
                <th>Дата блокировки</th>
                <th>Дата разблокировки</th>
                <th>IP</th>
                <th>Причина</th>
                <th>Админ</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($bans as $ban): ?>
            <tr>
                <?php
                    // Форматируем данные
                    $ban_time = formatTime($ban['ban_time']);
                    $time = formatTime($ban['time']);
                    $ban_date = formatDate($ban['ban_time']);
                    $block_date = formatDate($ban['time']);
                    $user_name = htmlspecialchars($ban['name']);
                ?>
                <td><?= $user_name ?></td>
                <td><?= $block_date . ' ' . $time ?></td>
                <td><?= $ban_date . ' ' . $ban_time ?></td>
                <td><?= htmlspecialchars($ban['ip']) ?></td>
                <td><?= htmlspecialchars($ban['description']) ?></td>
                <td><?= htmlspecialchars($ban['admin']) ?></td>
                <td><a href="delete_ban.php?ban_id=<?= $ban['id'] ?>" class="btn">Удалить</a></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Пагинация -->
        
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=1&<?= http_build_query($_GET) ?>">1</a>
        <a href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>">&laquo; Назад</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == $page): ?>
            <span><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>">Вперёд &raquo;</a>
    <?php endif; ?>
</div>

        <button class="btn" onclick="window.location.href='menu.php'">Назад в меню</button>
    </div>
</body>
</html>