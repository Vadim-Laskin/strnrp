<?php
include('db.php');
session_start();

// Проверяем авторизацию
if (!isset($_SESSION['name'])) {
    header('Location: index.php'); // Если не авторизован, редирект на страницу входа
    exit;
}

$notification = "";
$success = false;
$username = $_SESSION['username'];

// Проверяем, передан ли ник
if (!isset($_GET['nickname'])) {
    header("Location: leader_menu.php");
    exit();
}

$target_name = $_GET['nickname'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'delete') {
        // Получаем информацию о цели
        $query = "SELECT name, team FROM accounts WHERE name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $target_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $target_user = $result->fetch_assoc();

        if (!$target_user) {
            $notification = "Пользователь не найден!";
        } elseif ($target_name === $username) {
            $notification = "Нельзя удалить самого себя!";
        } elseif ($target_user['team'] < 1) {
            $notification = "Этот игрок не является лидером!";
        } else {
            // Удаляем лидерство (ставим team, job, org_skin в 0)
            $delete_query = "UPDATE accounts SET team = 0, job = 0, org_skin = 0 WHERE name = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("s", $target_name);

            if ($stmt->execute()) {
                $notification = "Игрок $target_name больше не является лидером!";
                $success = true;
            } else {
                $notification = "Ошибка при удалении лидера!";
            }
        }
    } elseif ($_POST['action'] === 'cancel') {
        header("Location: admin_menu.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200">
    <title>Удаление Лидера</title>
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

        .buttons {
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
            transition: 0.3s;
        }

        .delete-btn {
            background-color: #ff4d4d;
            color: white;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        .cancel-btn {
            background-color: #4CAF50;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #2e7d32;
        }

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

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Вы действительно хотите снять <strong><?php echo htmlspecialchars($target_name); ?></strong> с лидерки?</h2>
    
    <form method="post">
        <div class="buttons">
            <button type="submit" name="action" value="delete" class="button delete-btn">Снять</button>
            <button type="submit" name="action" value="cancel" class="button cancel-btn">Отмена</button>
        </div>
    </form>
</div>

<?php if ($notification): ?>
    <div class="notification <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo $notification; ?>
    </div>
    <?php if ($success): ?>
        <script>
            setTimeout(() => {
                window.location.href = "admin_menu.php";
            }, 6000);
        </script>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>