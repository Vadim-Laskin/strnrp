<?php
include 'db.php';

// Список должностей
$positions = [
    "Гл. Админ", "Зам. Гл. Админа", "Куратор", "Ст. Админ",
    "ГС Гос", "ГС ОПГ", "зГС Гос", "зГС ОПГ", "Администратор", "Модератор"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $level = $_POST['level'];
    $prefix = $_POST['prefix'];
    $appointed_date = $_POST['appointed_date'];
    $vk_link = $_POST['vk_link'];
    $inactive_until = !empty($_POST['inactive_until']) ? $_POST['inactive_until'] : NULL;

    // Проверка уровня админа
    if ($level < 1 || $level > 8) {
        $error_message = "Уровень администратора должен быть от 1 до 8!";
    } else {
        // Проверка на существование администратора с таким же ником или ссылкой
        $stmt = $conn->prepare("SELECT * FROM site_admins WHERE name = ? OR vk_link = ?");
        $stmt->bind_param("ss", $name, $vk_link);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Администратор с таким ником или ссылкой ВКонтакте уже существует!";
        } else {
            // Проверяем, есть ли уже "Куратор" в базе
            if ($prefix === "Куратор") {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM site_admins WHERE prefix = 'Куратор'");
                $stmt->execute();
                $stmt->bind_result($curator_count);
                $stmt->fetch();
                if ($curator_count > 0) {
                    $error_message = "Должность 'Куратор' уже занята!";
                }
                $stmt->close();
            }

            if (!isset($error_message)) {
                // Получаем имя и фамилию из ВК
                $vk_name = "Не удалось получить имя"; 
                if (preg_match('/vk\.com\/([a-zA-Z0-9_]+)/', $vk_link, $matches)) {
                    $vk_username = $matches[1];
                    $access_token = '6fdc08326fdc08326fdc0832b36cc7974166fdc6fdc0832083db2eed067f4fcf6a4a495';
                    $api_url = "https://api.vk.com/method/users.get?user_ids=$vk_username&access_token=$access_token&v=5.131";
                    $response = file_get_contents($api_url);
                    $data = json_decode($response, true);
                    if (isset($data['response'][0])) {
                        $vk_name = $data['response'][0]['first_name'] . ' ' . $data['response'][0]['last_name'];
                    }
                }

                // Добавляем администратора
                $stmt = $conn->prepare("INSERT INTO site_admins (name, level, prefix, appointed_date, vk_link, vk_name, inactive_until) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sisssss", $name, $level, $prefix, $appointed_date, $vk_link, $vk_name, $inactive_until);
                $stmt->execute();
                
                header("Location: admin_table.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить администратора</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        form {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        .btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Добавить администратора</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Имя" required>
        <input type="number" name="level" placeholder="Уровень (1-8)" min="1" max="8" required>
        
        <select name="prefix" required>
            <option value="" disabled selected>Выберите должность</option>
            <?php foreach ($positions as $position): ?>
                <option value="<?= $position ?>"><?= $position ?></option>
            <?php endforeach; ?>
        </select>

        <label>Дата назначения:</label>
        <input type="date" name="appointed_date" value="<?= date('Y-m-d') ?>" required>
        <input type="url" name="vk_link" placeholder="Ссылка на ВКонтакте" required>
        <label>Неактив до (если есть):</label>
        <input type="date" name="inactive_until">
        <button type="submit" class="btn">Добавить</button>

        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= $error_message ?></p>
        <?php endif; ?>
    </form>
</body>
</html>