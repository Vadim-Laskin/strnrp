<?php
include 'db.php';

// Проверка, что форма отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $organization = $_POST['organization'];
    $vk_link = $_POST['vk_link'];
    $inactive_until = !empty($_POST['inactive_until']) ? $_POST['inactive_until'] : NULL;

    // Проверяем, есть ли уже лидер в этой организации
    $stmt = $conn->prepare("SELECT * FROM site_leader WHERE organization = ?");
    $stmt->bind_param("s", $organization);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Лидер для организации '$organization' уже назначен!";
    } else {
        // Извлекаем ID пользователя из ссылки
        if (preg_match('/vk\.com\/([a-zA-Z0-9_]+)/', $vk_link, $matches)) {
            $vk_username = $matches[1];

            // Ваш токен доступа ВКонтакте (замените на реальный)
            $access_token = '6fdc08326fdc08326fdc0832b36cc7974166fdc6fdc0832083db2eed067f4fcf6a4a495';

            // Формируем URL для запроса к API ВКонтакте
            $api_url = "https://api.vk.com/method/users.get?user_ids=$vk_username&access_token=$access_token&v=5.131";

            // Получаем данные о пользователе
            $response = file_get_contents($api_url);
            $data = json_decode($response, true);

            // Проверяем, что данные получены успешно
            if (isset($data['response'][0])) {
                $vk_name = $data['response'][0]['first_name'] . ' ' . $data['response'][0]['last_name'];
            } else {
                $vk_name = 'Не удалось получить имя';
            }
        } else {
            $vk_name = 'Некорректная ссылка';
        }

        // Проверка на существование лидера с таким же ником или ссылкой
        $stmt = $conn->prepare("SELECT * FROM site_leader WHERE name = ? OR vk_link = ?");
        $stmt->bind_param("ss", $name, $vk_link);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Лидер с таким ником или ссылкой ВКонтакте уже существует!";
        } else {
            // Добавляем нового лидера
            $stmt = $conn->prepare("INSERT INTO site_leader (name, organization, vk_link, vk_name, inactive_until) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $organization, $vk_link, $vk_name, $inactive_until);
            $stmt->execute();

            header("Location: leader_table.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить лидера</title>
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
    <h1>Добавить лидера</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Имя" required>

        <select name="organization" required>
            <optgroup label="Гос. Структуры">
                <option value="Правительство">Правительство</option>
                <option value="ФСБ">ФСБ</option>
                <option value="Армия России">Армия России</option>
                <option value="Больница">Больница</option>
                <option value="МВД">МВД</option>
                <option value="СМИ">СМИ</option>
            </optgroup>
            <optgroup label="Нелегальные структуры">
                <option value="Название1 ОПГ">Название1 ОПГ</option>
                <option value="Название2 ОПГ">Название2 ОПГ</option>
                <option value="Название3 ОПГ">Название3 ОПГ</option>
            </optgroup>
        </select>

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