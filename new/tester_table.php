<?php
session_start();

// Проверка, авторизован ли администратор
if (!isset($_SESSION['name']) || $_SESSION['admin'] < 5) {
    header('Location: ../index.php'); // Если не авторизован или уровень админа ниже 6, редирект на страницу входа
    exit;
}

// Подключение к базе данных
include 'db.php';

// Получаем права текущего администратора
$current_user = $_SESSION['name'];
$query = "SELECT * FROM admin_access WHERE nickname = '$current_user'";
$result = mysqli_query($conn, $query);

// Если данные найдены, устанавливаем переменные доступа
if ($row = mysqli_fetch_assoc($result)) {
    $tester_add = $row['tester_add'];
    $tester_edit = $row['tester_edit'];
    $tester_delete = $row['tester_delete'];
} else {
    // Если доступа нет, устанавливаем все переменные в 0
    $tester_add = $tester_edit = $tester_delete = 0;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200">
    <title>Таблица тестеров</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 90%;
            margin: auto;
            max-width: 1200px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        th {
            background: rgba(255, 255, 255, 0.3);
            font-size: 18px;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin: 5px;
            transition: 0.3s;
        }
        .btn-add {
            background: #28a745;
            color: white;
        }
        .btn-edit {
            background: #ffc107;
            color: black;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .fa {
            font-size: 16px;
        }
        .vk-link {
            color: #4c75a3;
            font-weight: bold;
            text-decoration: none;
        }
        .vk-link:hover {
            text-decoration: underline;
        }
    </style>
    <style>
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        position: relative;
    }
    .btn-back {
        background: #007bff;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-back:hover {
        opacity: 0.8;
    }
    .title {
        flex-grow: 1;
        text-align: center;
        margin: 0;
    }
    .spacer {
        width: 90px; /* Делаем ширину равной кнопке назад, чтобы компенсировать пространство */
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
    <a href="../menu.php" class="btn btn-back"><i class="fa fa-arrow-left"></i> Назад</a>
    <h1 class="title">Список тестеров</h1>
    <div class="spacer"></div> <!-- Пустой блок для выравнивания -->
</div>


        <!--a href="add_admin.php" class="btn btn-add"><i class="fa fa-plus"></i> Добавить</a>
        <!-- Кнопка "Добавить администратора" -->
            <?php if ($tester_add): ?>
            <a href="add_tester.php" class="btn btn-add">
                <i class="fa fa-plus"></i> Добавить тестера
            </a>
            <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Уровень</th>
                <th>Должность</th>
                <th>Дата назначения</th>
                <th>ВКонтакте</th>
                <th>Неактив</th>
                <th>Наказания</th>
                <th>Действия</th>
            </tr>
            <?php
            // Измененный SQL-запрос для сортировки по уровню (в порядке убывания) и дате (по возрастанию)
            $query = "SELECT * FROM site_testers ORDER BY level DESC, appointed_date ASC"; 
            $result = $conn->query($query);
            $counter = 1; // Инициализация счетчика
            while ($row = $result->fetch_assoc()):
                // Получаем ссылку на ВКонтакте и имя администратора
                $vk_link = $row['vk_link'];
                $vk_name = !empty($row['vk_name']) ? $row['vk_name'] : 'Нет имени';
            ?>
            <tr>
                <td><?= $counter ?></td> <!-- Здесь выводится счетчик -->
                <td><?= $row['name'] ?></td>
                <td><?= $row['level'] ?></td>
                <td><?= $row['prefix'] ?></td>
                <td><?= date("d.m.Y", strtotime($row['appointed_date'])) ?></td>
                <td>
                    <?php if ($vk_link): ?>
                        <a href="<?= $vk_link ?>" class="vk-link" target="_blank"><?= $vk_name ?></a>
                    <?php else: ?>
                        Нет ссылки
                    <?php endif; ?>
                </td>
                <td><?= $row['inactive_until'] ? date("d.m.Y", strtotime($row['inactive_until'])) : 'Нет' ?></td>                
                <td>
                            <a href="tester_punish.php?id=<?= $row['id'] ?>" class="btn btn-warning">Посмотреть</a>
                </td>
                <td>
                <?php if ($tester_edit): ?>
                    <a href="edit_tester.php?id=<?= $row['id'] ?>" class="btn btn-edit"><i class="fa fa-pen"></i></a>
                <?php else: ?>
                      <!--p>У вас нет прав на редактирование этого администратора.</p-->
                <?php endif; ?>
    	
                 <?php if ($tester_delete): ?>
                    <a href="delete_tester.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить тестера?')"><i class="fa fa-trash"></i></a>
                 <?php else: ?>
                       <!--p>У вас нет прав на удаление этого администратора.</p-->
                 <?php endif; ?>
                </td>
            </tr>
            <?php
                $counter++; // Увеличиваем счетчик на 1 для следующей строки
            endwhile;
            ?>
        </table>
    </div>
</body>
</html>
