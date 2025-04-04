<?php
session_start();
include 'db.php';

if (!isset($_SESSION['name']) || $_SESSION['admin'] < 5) {
    header('Location: ../index.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Ошибка: Не указан ID администратора.');
}

$admin_id = intval($_GET['id']);

// Получаем данные администратора
$query = "SELECT * FROM site_admins WHERE id = $admin_id";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    die('Ошибка: Администратор не найден.');
}

// Получаем текущие наказания
$punish_query = "SELECT * FROM admin_punishments WHERE admin_id = $admin_id";
$punish_result = mysqli_query($conn, $punish_query);
$punish = mysqli_fetch_assoc($punish_result);

// Если данных нет, создаем запись
if (!$punish) {
    mysqli_query($conn, "INSERT INTO admin_punishments (admin_id) VALUES ($admin_id)");
    $punish = ['reprimands' => 0, 'warnings' => 0, 'logs' => 0];
}

// Получаем историю наказаний
$history_query = "SELECT * FROM admin_punishment_history WHERE admin_id = $admin_id ORDER BY date DESC";
$history_result = mysqli_query($conn, $history_query);

// Обработка формы наказания
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type']; // reprimand, warning, log
    $reason = trim($_POST['reason']);

    if (!empty($type) && !empty($reason)) {
        if ($type == "reprimand") {
            mysqli_query($conn, "UPDATE admin_punishments SET reprimands = reprimands + 1 WHERE admin_id = $admin_id");
        } elseif ($type == "warning") {
            // Если предупреждений 3, то добавить выговор
            if ($punish['warnings'] >= 2) {
                mysqli_query($conn, "UPDATE admin_punishments SET reprimands = reprimands + 1 WHERE admin_id = $admin_id");
                // После того как добавим выговор, сбрасываем предупреждения
                mysqli_query($conn, "UPDATE admin_punishments SET warnings = 0 WHERE admin_id = $admin_id");
            } else {
                mysqli_query($conn, "UPDATE admin_punishments SET warnings = warnings + 1 WHERE admin_id = $admin_id");
            }
        } elseif ($type == "log") {
            // Обрабатываем логи. Если их 3, сбрасываем их и добавляем предупреждение.
            if ($punish['logs'] >= 2) {
                // Сбросить логи
                mysqli_query($conn, "UPDATE admin_punishments SET logs = 0 WHERE admin_id = $admin_id");
                // Добавить предупреждение
                mysqli_query($conn, "UPDATE admin_punishments SET warnings = warnings + 1 WHERE admin_id = $admin_id");
            } else {
                // Добавляем лог, если их меньше 3
                mysqli_query($conn, "UPDATE admin_punishments SET logs = logs + 1 WHERE admin_id = $admin_id");
            }
        }

        // Получаем имя администратора, который выдал наказание
        $punished_by = mysqli_real_escape_string($conn, $_SESSION['name']);

        // Добавляем запись в историю наказаний
        mysqli_query($conn, "INSERT INTO admin_punishment_history (admin_id, type, reason, punished_by) 
            VALUES ($admin_id, '$type', '$reason', '$punished_by')");

        // Обновляем страницу
        header("Location: admin_punish.php?id=$admin_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200">
    <title>Наказания администратора</title>
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
            width: 80%;
            margin: auto;
            max-width: 900px;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            transition: 0.5s;
        }
        h2 {
            margin-bottom: 10px;
        }
        .punishment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        th {
            background: rgba(255, 255, 255, 0.3);
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
            cursor: pointer;
            border: none;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .hidden {
            display: none;
            opacity: 0;
            transition: opacity 0.5s;
        }
        .form-container {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
        }
        select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_table.php" class="btn"><i class="fa fa-arrow-left"></i> Назад</a>
        <div class="card" id="mainTable">
            <h2>Наказания администратора: <?= htmlspecialchars($admin['name']) ?></h2>
            <table class="punishment-table">
                <tr>
                    <th>Выговоры</th>
                    <th>Предупреждения</th>
                    <th>Логи</th>
                </tr>
                <tr>
                    <td><?= $punish['reprimands'] ?></td>
                    <td><?= $punish['warnings'] ?></td>
                    <td><?= $punish['logs'] ?></td>
                </tr>
            </table>
            <button class="btn" onclick="toggleHistory()">История наказаний</button>
            <button class="btn" onclick="toggleForm()">Выдать наказание</button>
        </div>

        <div class="card hidden" id="historyTable">
            <h2>История наказаний</h2>
            <table class="punishment-table">
                <tr>
                    <th>Выдал наказание</th>
                    <th>Дата</th>
                    <th>Тип</th>
                    <th>Причина</th>
                </tr>
                <?php while ($history = mysqli_fetch_assoc($history_result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($history['punished_by']) ?></td>
                        <td><?= date("d.m.Y", strtotime($history['date'])) ?></td>
                        <?php
                          $type_translation = [
                              'warning' => 'Предупреждение',
                              'reprimand' => 'Выговор',
                              'log' => 'Лог'
                          ];
                        ?>
                        <td><?= $type_translation[$history['type']] ?? htmlspecialchars($history['type']) ?></td>
                        <td><?= htmlspecialchars($history['reason']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button class="btn" onclick="toggleHistory()">Назад</button>
        </div>

        <div class="card hidden" id="punishForm">
            <h2>Выдать наказание</h2>
            <form method="POST">
                <select name="type" required>
                    <option value="reprimand">Выговор</option>
                    <option value="warning">Предупреждение</option>
                    <option value="log">Лог</option>
                </select>
                <textarea name="reason" placeholder="Введите причину..." required></textarea>
                <button type="submit" class="btn">Выдать</button>
            </form>
            <button class="btn" onclick="toggleForm()">Отмена</button>
        </div>
    </div>

    <script>
        function toggleHistory() {
            document.getElementById("mainTable").classList.toggle("hidden");
            document.getElementById("historyTable").classList.toggle("hidden");
        }
        function toggleForm() {
            document.getElementById("mainTable").classList.toggle("hidden");
            document.getElementById("punishForm").classList.toggle("hidden");
        }
    </script>
</body>
</html>