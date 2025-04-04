<?php
include('db.php');

$notification = "";
$success = false;

if (!isset($_GET['nickname'])) {
    header("Location: leader_menu.php");
    exit();
}

$nickname = $_GET['nickname'];

// Получаем текущие данные администратора
$query = "SELECT name, team FROM accounts WHERE name = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $nickname);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin_data = mysqli_fetch_assoc($result);

if (isset($_POST['submit'])) {
    $new_team = $_POST['team'];

    // Обновляем только team
    $update_query = "UPDATE accounts SET team = ? WHERE name = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "is", $new_team, $nickname);

    if (mysqli_stmt_execute($stmt)) {
        $notification = "Организация успешно обновлена!";
        $success = true;
        $admin_data['team'] = $new_team; // Обновляем локально
    } else {
        $notification = "Ошибка при обновлении организации!";
    }
}


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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать администратора</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #007BFF, #6610f2);
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 40%;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            margin: 50px auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            font-size: 16px;
            margin-top: 10px;
        }

        input, select {
            width: 80%;
            padding: 10px;
            margin-top: 5px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            box-shadow: 0px 0px 8px rgba(255, 255, 255, 0.5);
        }

        button {
            background: #ffcc00;
            color: #000;
            font-size: 18px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }

        button:hover {
            background: #ff9900;
        }

        .back-btn {
            display: block;
            text-decoration: none;
            color: white;
            font-size: 18px;
            margin-top: 10px;
            transition: 0.3s;
        }

        .back-btn:hover {
            color: #ffcc00;
        }

        @media screen and (max-width: 768px) {
            .container {
                width: 80%;
            }

            input, select {
                width: 100%;
            }
        }

        /* Уведомления */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateX(100%);
            animation: slideIn 0.5s forwards, fadeOut 0.5s forwards 5s;
        }

        .notification.error {
            background-color: #ff4d4d;
            color: white;
        }

        .notification.success {
            background-color: #4CAF50;
            color: white;
        }

        /* Прогресс-бар таймера */
        .notification .progress-bar {
            height: 4px;
            width: 100%;
            background: rgba(255, 255, 255, 0.5);
            position: absolute;
            bottom: 0;
            left: 0;
            animation: progress 5s linear forwards;
        }

        /* Анимация появления */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Анимация исчезновения */
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        /* Анимация для прогресс-бара */
        @keyframes progress {
            from {
                width: 100%;
            }
            to {
                width: 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Редактировать администратора</h2>

    <?php if ($notification): ?>
        <div class="notification <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $notification; ?>
            <span class="timer">5</span>
        </div>
    <?php endif; ?>

    <form method="POST">
    <label for="nickname">Ник:</label>
    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($admin_data['name']); ?>" readonly>

    <label for="team">Организация:</label>
    <select id="team" name="team" required>
        <?php foreach ($team_names as $team_id => $team_name): ?>
            <option value="<?php echo $team_id; ?>" <?php echo ($admin_data['team'] == $team_id) ? "selected" : ""; ?>>
                <?php echo $team_name; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" name="submit">Сохранить</button>
</form>

    <a href="leader_menu.php" class="back-btn">Назад</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let notification = document.querySelector(".notification");
    if (notification) {
        setTimeout(() => notification.style.opacity = "0", 5000);
    }
});
</script>

</body>
</html>