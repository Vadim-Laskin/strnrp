<?php
// Пример для авторизации
session_start();
require_once 'db.php'; // Подключение к базе данных

if ($_POST['name'] && $_POST['password']) {
    // Проводим проверку данных в базе данных
    $name = $_POST['name'];
    $password = $_POST['password'];
    
    // Проверяем в базе данных
    $query = "SELECT * FROM accounts WHERE name = '$name' AND password = '$password' AND admin >= 5";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['name'] = $user['name'];    // Сохраняем имя в сессию
        $_SESSION['admin'] = $user['admin'];  // Сохраняем уровень админа
        // Устанавливаем уровень админки в сессию
        $_SESSION['admin_level'] = $user['admin'];
        header('Location: menu.php'); // Редирект на меню
        exit;
    } else {
        echo 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <style>
        /* Стили для страницы авторизации */
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-form {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .auth-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .auth-form button {
            width: 100%;
            padding: 10px;
            background-color: #0072ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .auth-form button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>

<div class="auth-form">
    <h2>Авторизация</h2>
    <form action="index.php" method="POST">
        <label for="name">Ник:</label>
        <input type="text" name="name" required><br>
        <label for="password">Пароль:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Войти</button>
    </form>
</div>

</body>
</html>