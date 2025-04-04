<?php
include 'db.php';

// Получение ID администратора для редактирования
$id = $_GET['id'];
$query = $conn->prepare("SELECT * FROM site_admins WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$admin = $result->fetch_assoc();

// Проверка, отправлена ли форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $level = $_POST['level'];
    $prefix = $_POST['prefix'];
    $appointed_date = $_POST['appointed_date'];
    $vk_link = $_POST['vk_link'];
    $inactive_until = !empty($_POST['inactive_until']) ? $_POST['inactive_until'] : NULL;

    // Проверка уровня админа
    if ($level < 1 || $level > 8) {
        echo "<script>alert('Ошибка: Уровень админа должен быть от 1 до 8!');</script>";
    } else {
        // Проверка на существующего администратора с таким же ником или ВКонтакте
        $stmt = $conn->prepare("SELECT * FROM site_admins WHERE (name = ? OR vk_link = ?) AND id != ?");
        $stmt->bind_param("ssi", $name, $vk_link, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Ошибка: Администратор с таким ником или ссылкой ВКонтакте уже существует!');</script>";
        } else {
            // Проверка уникальности должности "Куратор"
            if ($prefix == 'Куратор') {
                $checkCurator = $conn->prepare("SELECT * FROM site_admins WHERE prefix = 'Куратор'");
                $checkCurator->execute();
                $curatorResult = $checkCurator->get_result();
                
                if ($curatorResult->num_rows > 0) {
                    echo "<script>alert('Ошибка: Должность Куратор уже занята!');</script>";
                } else {
                    // Обновление данных администратора
                    $stmt = $conn->prepare("UPDATE site_admins SET name=?, level=?, prefix=?, appointed_date=?, vk_link=?, inactive_until=? WHERE id=?");
                    $stmt->bind_param("sissssi", $name, $level, $prefix, $appointed_date, $vk_link, $inactive_until, $id);
                    $stmt->execute();

                    header("Location: admin_table.php");
                    exit();
                }
            } else {
                // Обновление данных администратора без проверки на Куратор
                $stmt = $conn->prepare("UPDATE site_admins SET name=?, level=?, prefix=?, appointed_date=?, vk_link=?, inactive_until=? WHERE id=?");
                $stmt->bind_param("sissssi", $name, $level, $prefix, $appointed_date, $vk_link, $inactive_until, $id);
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
    <title>Редактировать администратора</title>
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
            color: black;
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
    </style>
</head>
<body>
    <h1>Редактировать администратора</h1>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
        <input type="number" name="level" value="<?= htmlspecialchars($admin['level']) ?>" min="1" max="8" required>
        
        <!-- Выпадающий список для должностей -->
        <label>Должность:</label>
        <select name="prefix" required>
            <option value="Гл. Админ" <?= $admin['prefix'] == 'Гл. Админ' ? 'selected' : '' ?>>Гл. Админ</option>
            <option value="Зам. Гл. Админа" <?= $admin['prefix'] == 'Зам. Гл. Админа' ? 'selected' : '' ?>>Зам. Гл. Админа</option>
            <option value="Куратор" <?= $admin['prefix'] == 'Куратор' ? 'selected' : '' ?>>Куратор</option>
            <option value="Ст. Админ" <?= $admin['prefix'] == 'Ст. Админ' ? 'selected' : '' ?>>Ст. Админ</option>
            <option value="ГС Гос" <?= $admin['prefix'] == 'ГС Гос' ? 'selected' : '' ?>>ГС Гос</option>
            <option value="ГС ОПГ" <?= $admin['prefix'] == 'ГС ОПГ' ? 'selected' : '' ?>>ГС ОПГ</option>
            <option value="зГС Гос" <?= $admin['prefix'] == 'зГС Гос' ? 'selected' : '' ?>>зГС Гос</option>
            <option value="зГС ОПГ" <?= $admin['prefix'] == 'зГС ОПГ' ? 'selected' : '' ?>>зГС ОПГ</option>
            <option value="Администратор" <?= $admin['prefix'] == 'Администратор' ? 'selected' : '' ?>>Администратор</option>
            <option value="Модератор" <?= $admin['prefix'] == 'Модератор' ? 'selected' : '' ?>>Модератор</option>
        </select>

        <label>Дата назначения:</label>
        <input type="date" name="appointed_date" value="<?= htmlspecialchars($admin['appointed_date']) ?>" required>
        <input type="url" name="vk_link" value="<?= htmlspecialchars($admin['vk_link']) ?>" required>
        <label>Неактив до (если есть):</label>
        <input type="date" name="inactive_until" value="<?= htmlspecialchars($admin['inactive_until']) ?>">
        <button type="submit" class="btn">Сохранить</button>
    </form>
</body>
</html>