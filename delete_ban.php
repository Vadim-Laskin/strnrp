<?php
session_start();
require 'db.php'; // Подключение к БД

// Проверка авторизации
if (!isset($_SESSION['name'])) {
    header('Location: index.php');
    exit;
}

// Проверка наличия параметра ban_id
if (isset($_GET['ban_id'])) {
    $ban_id = $_GET['ban_id'];
} else {
    header('Location: ban_list.php');
    exit;
}

// Проверка, если была нажата кнопка удаления
if (isset($_POST['delete'])) {
    // Подготовка и выполнение запроса на удаление
    $stmt = $conn->prepare("DELETE FROM ban_list WHERE id = ?");
    $stmt->bind_param("i", $ban_id);

    if ($stmt->execute()) {
        $message = "Бан успешно удален.";
        $success = true;
    } else {
        $message = "Бан не найден.";
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление бана</title>
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
            max-width: 500px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #000;
        }
        .btn {
            background-color: #0072ff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin: 5px;
        }
        .btn:hover {
            background-color: #005bb5;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #b02a37;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Подтверждение удаления бана</h2>

        <?php if (isset($message)): ?>
            <p><?= $message ?></p>
            <?php if ($success): ?>
                <script>
                    setTimeout(function() {
                        window.location.href = 'ban_list.php';
                    }, 3000);
                </script>
            <?php else: ?>
                <a href="ban_list.php" class="btn">Назад</a>
            <?php endif; ?>
        <?php else: ?>
            <p>Вы уверены, что хотите удалить этот бан?</p>
            <form method="POST">
                <button type="submit" name="delete" class="btn btn-danger">Удалить</button>
                <a href="ban_list.php" class="btn">Назад</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>