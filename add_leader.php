<?php
include('db.php');

$notification = "";
$success = false;

if (isset($_POST['submit'])) {  
    $nickname = $_POST['nickname'];  
    $team = $_POST['team'];  

    // Проверяем, зарегистрирован ли игрок
    $user_query = "SELECT * FROM accounts WHERE name = '$nickname'";  
    $user_result = mysqli_query($conn, $user_query);  
    $user = mysqli_fetch_assoc($user_result);

    if (!$user) {  
        $notification = "Пользователь не найден!";  
    } elseif ($user['team'] >= 1) {  
        // Если игрок уже лидер, пишем сообщение с названием организации
        $team_names = [
            1 => "Правительство",
            2 => "Воинская часть",
            3 => "Городская больница",
            4 => "СМИ",
            5 => "ГИБДД",
            6 => "МВД",
            7 => "ФСБ",
            8 => "Арзамасская ОПГ",
            9 => "Батыревская ОПГ",
            10 => "Лыткаринская ОПГ"
        ];
        $notification = "Этот игрок уже лидер организации: " . $team_names[$user['team']];
    } else {  
        // Проверяем, занята ли уже организация
        $check_query = "SELECT * FROM accounts WHERE team = '$team'";  
        $result = mysqli_query($conn, $check_query);
        $existing_leader = mysqli_fetch_assoc($result);

        if ($existing_leader) {
            $notification = "Эта организация уже имеет лидера!";
        } else {
            // Назначаем игрока лидером
            $update_query = "UPDATE accounts SET team = '$team', job = 10 WHERE name = '$nickname'";
            if (mysqli_query($conn, $update_query)) {
                $notification = "Игрок успешно назначен лидером!";
                $success = true;
            } else {
                $notification = "Ошибка при назначении лидера!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить Администратора</title>
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
    <h2>Добавить администратора</h2>

    <?php if ($notification): ?>
        <div class="notification <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $notification; ?>
            <span class="timer">5</span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nickname">Ник:</label>
        <input type="text" id="nickname" name="nickname" required>

        <label for="team">Организация:</label>
<select id="team" name="team" required>
    <option value="1">Правительство</option>
    <option value="2">Воинская часть</option>
    <option value="3">Городская больница</option>
    <option value="4">СМИ</option>
    <option value="5">ГИБДД</option>
    <option value="6">МВД</option>
    <option value="7">ФСБ</option>
    <option value="8">Арзамасская ОПГ</option>
    <option value="9">Батыревская ОПГ</option>
    <option value="10">Лыткаринская ОПГ</option>
</select>

        <button type="submit" name="submit">Добавить</button>
    </form>

    <a href="leader_menu.php" class="back-btn">Назад</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let notification = document.querySelector(".notification");
    if (notification) {
        let timer = notification.querySelector(".timer");
        let timeLeft = 5;

        let countdown = setInterval(() => {
            timeLeft--;
            timer.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(countdown);
                notification.style.animation = "fadeOut 0.5s forwards";
            }
        }, 1000);
    }
});
</script>

</body>
</html>