<?php
include 'db.php';

// Проверка, что передан ID лидера
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Ошибка: ID лидера не указан!");
}

$leader_id = $_GET['id'];

// Получаем данные о лидере
$stmt = $conn->prepare("SELECT * FROM site_leader WHERE id = ?");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ошибка: Лидер не найден!");
}

$leader = $result->fetch_assoc();

// Если форма отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $organization = $_POST['organization'];
    $vk_link = $_POST['vk_link'];
    $inactive_until = !empty($_POST['inactive_until']) ? $_POST['inactive_until'] : NULL;

    // Проверка, существует ли уже лидер в этой организации (кроме текущего)
    $stmt = $conn->prepare("SELECT * FROM site_leader WHERE organization = ? AND id != ?");
    $stmt->bind_param("si", $organization, $leader_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Лидер для организации '$organization' уже назначен!";
    } else {
        // Проверка на существование лидера с такой же ссылкой (кроме текущего)
        $stmt = $conn->prepare("SELECT * FROM site_leader WHERE vk_link = ? AND id != ?");
        $stmt->bind_param("si", $vk_link, $leader_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Лидер с такой ссылкой ВКонтакте уже существует!";
        } else {
            // Обновляем данные лидера (без изменения имени)
            $stmt = $conn->prepare("UPDATE site_leader SET organization = ?, vk_link = ?, inactive_until = ? WHERE id = ?");
            $stmt->bind_param("sssi", $organization, $vk_link, $inactive_until, $leader_id);
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
    <title>Редактировать лидера</title>
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
            background: #ffc107;
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
    <h1>Редактировать лидера</h1>
    <form method="POST">
        <p><strong>Ник:</strong> <?= htmlspecialchars($leader['name']) ?></p>

        <select name="organization" required>
            <optgroup label="Гос. Структуры">
                <option value="Правительство" <?= $leader['organization'] == "Правительство" ? "selected" : "" ?>>Правительство</option>
                <option value="ФСБ" <?= $leader['organization'] == "ФСБ" ? "selected" : "" ?>>ФСБ</option>
                <option value="Армия России" <?= $leader['organization'] == "Армия России" ? "selected" : "" ?>>Армия России</option>
                <option value="Больница" <?= $leader['organization'] == "Больница" ? "selected" : "" ?>>Больница</option>
                <option value="МВД" <?= $leader['organization'] == "МВД" ? "selected" : "" ?>>МВД</option>
            </optgroup>
            <optgroup label="Нелегальные структуры">
                <option value="Название1 ОПГ" <?= $leader['organization'] == "Название1 ОПГ" ? "selected" : "" ?>>Название1 ОПГ</option>
                <option value="Название2 ОПГ" <?= $leader['organization'] == "Название2 ОПГ" ? "selected" : "" ?>>Название2 ОПГ</option>
                <option value="Название3 ОПГ" <?= $leader['organization'] == "Название3 ОПГ" ? "selected" : "" ?>>Название3 ОПГ</option>
            </optgroup>
        </select>

        <input type="url" name="vk_link" value="<?= htmlspecialchars($leader['vk_link']) ?>" required>
        <label>Неактив до (если есть):</label>
        <input type="date" name="inactive_until" value="<?= $leader['inactive_until'] ?>">

        <button type="submit" class="btn">Сохранить</button>

        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= $error_message ?></p>
        <?php endif; ?>
    </form>
</body>
</html>