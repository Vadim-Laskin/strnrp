<?php
session_start();
require 'db.php'; // Подключение к БД

$conn->set_charset("utf8mb4");

// Проверяем, если пользователь это Vadim_Laskin
$is_vadim = ($_SESSION['name'] === 'Vadim_Laskin');

$username = $_SESSION['name']; 

$query = "SELECT admin, leader FROM admin_access WHERE nickname = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$access = $result->fetch_assoc();

$admin_access = $access['admin'] ?? 0;
$leader_access = $access['leader'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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

        .menu {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container button {
            width: 100%;
            padding: 15px;
            background-color: #0072ff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin: 10px 0;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu button:hover {
            background-color: #005bb5;
        }

        /* Иконка внутри кнопки */
        .menu button .icon {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .dashboard-menu {
            background: #d1e5ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .dashboard-menu h2 {
            color: #0056b3;
            margin-bottom: 10px;
        }

        /* Кнопка выхода */
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            color: white;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            transition: 0.3s;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #b02a37;
        }
    </style>
</head>
<body>
    <div class="menu">
        <div class="container">
            <h2>Добро пожаловать, <?php echo $_SESSION['name']; ?></h2>
            <div class="dashboard-menu">
            	<?php if ($is_vadim): ?>
        <button onclick="window.location.href='dostup_menu.php'">
            <i class="fas fa-users-gear icon"></i> Доступы
        </button>
    <?php endif; ?>
    	
                <?php if ($admin_access == 1): ?>
                    <button onclick="window.location.href='./new/admin_table.php'">
                        <i class="fas fa-user-shield icon"></i> Администрация
                    </button>
                <?php endif; ?>

                <?php if ($leader_access == 1): ?>
                    <button onclick="window.location.href='./new/leader_table.php'">
                        <i class="fas fa-user-tie icon"></i> Лидеры
                    </button>
                <?php endif; ?>

                <?php if ($admin_access == 0 && $leader_access == 0): ?>
                    <p>К сожалению, у вас нет доступа!</p>
                <?php endif; ?>
            </div>
        </div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">
            <i class="fas fa-right-from-bracket icon"></i> Выйти
        </button>
    </div>
</body>
</html>