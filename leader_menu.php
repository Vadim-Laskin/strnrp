<?php
session_start();

// Проверка, авторизован ли администратор
if (!isset($_SESSION['name']) || $_SESSION['admin'] < 5) {
    header('Location: index.php'); // Если не авторизован или уровень админа ниже 6, редирект на страницу входа
    exit;
}

// Подключение к базе данных
require_once 'db.php';

// Получаем права текущего администратора
$current_user = $_SESSION['name'];
$query = "SELECT * FROM admin_access WHERE nickname = '$current_user'";
$result = mysqli_query($conn, $query);

// Если данные найдены, устанавливаем переменные доступа
if ($row = mysqli_fetch_assoc($result)) {
    $leader_add = $row['leader_add'];
    $leader_edit = $row['leader_edit'];
    $leader_delete = $row['leader_delete'];
} else {
    // Если доступа нет, устанавливаем все переменные в 0
    $leader_add = $leader_edit = $leader_delete = 0;
}

// Получаем всех администраторов (уровень от 1 до 13)
$query = "SELECT * FROM accounts WHERE team >= 1 AND job = 10 ORDER BY team DESC";
$result = mysqli_query($conn, $query);


// Массив соответствий team -> название организации
$team_names = [
    1 => 'Правительство',
    2 => 'Воинская часть',
    3 => 'Городская больница',
    4 => 'СМИ',
    5 => 'ГИБДД',
    6 => 'МВД',
    7 => 'ФСБ',
    8 => 'Арзамасская ОПГ',
    9 => 'Батыревская ОПГ',
    10 => 'Лыткаринская ОПГ'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200">
    <title>Лидеры</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .admin-panel {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            overflow-x: auto;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Кнопка "Назад" */
        .back-btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: #0072ff;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }

        .back-btn i {
            margin-right: 8px;
            font-size: 20px;
        }

        .back-btn:hover {
            color: #005bb5;
        }

        /* Кнопка "Добавить администратора" */
        .add-admin-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            background-color: #0072ff;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }

        .add-admin-btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        .add-admin-btn:hover {
            background-color: #005bb5;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0072ff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons a {
            text-decoration: none;
        }

        .action-buttons button {
            background-color: #0072ff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.2s;
        }

        .action-buttons button:hover {
            background-color: #005bb5;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            .admin-panel {
                width: 100%;
                padding: 20px;
            }
            .header {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        
        
    </style>
</head>
<body>

    <div class="admin-panel">
        <div class="header">
            <!-- Кнопка "Назад" -->
            <a href="menu.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Назад
            </a>

            <!-- Кнопка "Добавить администратора" -->
            <?php if ($leader_add): ?>
            <a href="add_leader.php" class="add-admin-btn">
                <i class="fas fa-user-plus"></i> Добавить лидера
            </a>
            <?php endif; ?>
        </div>

        <h2>Управление лидерами</h2>
        
        <?php if ($notification): ?>
        <div class="notification <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $notification; ?>
            <span class="timer">5</span>
        </div>
    <?php endif; ?>

        <table>
            <thead>
                <tr>
                	<th>№</th>
                    <th>Ник</th>
                    <th>Организация</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                	$num = 1; // Счётчик строк
                    while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <td><?php echo $num++; ?></td> <!-- Вывод номера строки -->
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo isset($team_names[$row['team']]) ? $team_names[$row['team']] : 'Неизвестно'; ?></td>
                        <td style="font-weight: bold; color: <?php echo $row['online'] ? 'green' : 'gray'; ?>;">
                        <?php echo $row['online'] ? 'Онлайн' : 'Оффлайн'; ?>
                        </td>
                        <td class="action-buttons">
    <?php if ($leader_edit): ?>
        <a href="edit_leader.php?nickname=<?php echo $row['name']; ?>">
            <button><i class="fas fa-pencil icon"></i> Редактировать</button>
        </a>
    <?php else: ?>
        <!--p>У вас нет прав на редактирование этого администратора.</p-->
    <?php endif; ?>

    <?php if ($leader_delete): ?>
        <a href="delete_leader.php?nickname=<?php echo $row['name']; ?>">
            <button><i class="fas fa-trash icon"></i> Удалить</button>
        </a>
    <?php else: ?>
        <!--p>У вас нет прав на удаление этого администратора.</p-->
    <?php endif; ?>
</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>