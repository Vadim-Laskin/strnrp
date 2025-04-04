<?php
include('db.php');
session_start();

if ($_SESSION['name'] != 'Vadim_Laskin') {
    // Если не Vadim_Laskin, перенаправляем на главную или другую страницу
    header('Location: index.php');
    exit();
}

$notification = "";
$success = false;

// Обработка запроса для получения прав администратора
if (isset($_GET['nickname'])) {
    $nickname = mysqli_real_escape_string($conn, $_GET['nickname']);
    $access_query = "SELECT admin, admin_add, admin_edit, admin_delete, leader, leader_add, leader_edit, leader_delete, tester, tester_add, tester_edit, tester_delete FROM admin_access WHERE nickname = '$nickname'";
    $access_result = mysqli_query($conn, $access_query);

    if ($row = mysqli_fetch_assoc($access_result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["admin" => 0, "admin_add" => 0, "admin_edit" => 0, "admin_delete" => 0, "leader" => 0, "leader_add" => 0, "leader_edit" => 0, "leader_delete" => 0, "tester" => 0, "tester_add" => 0, "tester_edit" => 0, "tester_delete" => 0]);
    }
    exit();
}

$admins_query = "SELECT name, admin FROM accounts WHERE admin >= 5 AND name != '---'";
$admins_result = mysqli_query($conn, $admins_query);

if (isset($_POST['submit'])) {
    $nickname = $_POST['nickname'];
    $admin = isset($_POST['admin']) ? 1 : 0;
    $admin_add = isset($_POST['admin_add']) ? 1 : 0;
    $admin_edit = isset($_POST['admin_edit']) ? 1 : 0;
    $admin_delete = isset($_POST['admin_delete']) ? 1 : 0;
    $leader = isset($_POST['leader']) ? 1 : 0;
    $leader_add = isset($_POST['leader_add']) ? 1 : 0;
    $leader_edit = isset($_POST['leader_edit']) ? 1 : 0;
    $leader_delete = isset($_POST['leader_delete']) ? 1 : 0;
    $tester = isset($_POST['tester']) ? 1 : 0;
    $tester_add = isset($_POST['tester_add']) ? 1 : 0;
    $tester_edit = isset($_POST['tester_edit']) ? 1 : 0;
    $tester_delete = isset($_POST['tester_delete']) ? 1 : 0;

    // Проверка, существует ли запись для этого администратора
    $check_query = "SELECT * FROM admin_access WHERE nickname = '$nickname'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Обновление прав администратора
        $update_query = "UPDATE admin_access SET admin = '$admin', admin_add = '$admin_add', admin_edit = '$admin_edit', admin_delete = '$admin_delete', leader = '$leader', leader_add = '$leader_add', leader_edit = '$leader_edit', leader_delete = '$leader_delete' WHERE nickname = '$nickname'";
        if (mysqli_query($conn, $update_query)) {
            $notification = "Доступы для администратора $nickname обновлены!";
            $success = true;
        } else {
            $notification = "Ошибка при обновлении доступов: " . mysqli_error($conn);
        }
    } else {
        // Добавление нового администратора
        $insert_query = "INSERT INTO admin_access (nickname, admin, admin_add, admin_edit, admin_delete, leader, leader_add, leader_edit, leader_delete, tester, tester_add, tester_edit, tester_delete) VALUES ('$nickname', '$admin_add', '$admin_edit', '$admin_delete', '$leader_add', '$leader_edit', '$leader_delete', '$tester_add', '$tester_edit', '$tester_delete')";
        if (mysqli_query($conn, $insert_query)) {
            $notification = "Доступы для администратора $nickname успешно добавлены!";
            $success = true;
        } else {
            $notification = "Ошибка при добавлении новых доступов: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Настройка доступов</title>
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
            flex-direction: column;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: #0072ff;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
        }

        .back-btn i {
            margin-right: 8px;
            font-size: 20px;
        }

        .back-btn:hover {
            color: #005bb5;
        }

        h2 {
            margin-bottom: 20px;
            color: #0072ff;
        }

        .notification {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #0072ff;
            border-radius: 5px;
            font-size: 16px;
            background: #d1e5ff;
        }

        label {
            display: flex;
            align-items: center;
            width: 100%;
            background: #d1e5ff;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 10px;
        }

        label:hover {
            background: #8bb8fe;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        button {
            padding: 10px 15px;
            background-color: #0072ff;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.2s;
            width: 100%;
        }

        button:hover {
            background-color: #005bb5;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
	<!-- Кнопка "Назад" -->
            <a href="menu.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
            
    <h2>Настройка доступов</h2>

    <?php if ($notification): ?>
        <div class="notification <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $notification; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nickname">Выберите администратора:</label>
        <select name="nickname" id="nickname" required>
            <option value="" disabled selected>---</option> <!-- Значение по умолчанию -->
            <?php while ($row = mysqli_fetch_assoc($admins_result)): ?>
                <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?> (Уровень: <?php echo $row['admin']; ?>)</option>
            <?php endwhile; ?>
        </select>

        <label><input type="checkbox" name="admin" id="admin"> Управление администраторами</label>
        <label><input type="checkbox" name="admin_add" id="admin_add"> Может добавлять администраторов</label>
        <label><input type="checkbox" name="admin_edit" id="admin_edit"> Может редактировать администраторов</label>
        <label><input type="checkbox" name="admin_delete" id="admin_delete"> Может удалять администраторов</label>
        
        <label><input type="checkbox" name="leader" id="leader"> Управление лидерами</label>
        <label><input type="checkbox" name="leader_add" id="leader_add"> Может добавлять лидеров</label>
        <label><input type="checkbox" name="leader_edit" id="leader_edit"> Может редактировать лидеров</label>
        <label><input type="checkbox" name="leader_delete" id="leader_delete"> Может удалять лидеров</label>

	<label><input type="checkbox" name="tester" id="leader"> Управление лидерами</label>
        <label><input type="checkbox" name="tester_add" id="leader_add"> Может добавлять лидеров</label>
        <label><input type="checkbox" name="tester_edit" id="leader_edit"> Может редактировать лидеров</label>
        <label><input type="checkbox" name="tester_delete" id="leader_delete"> Может удалять лидеров</label>

        <button type="submit" name="submit">Сохранить</button>
    </form>
</div>

<script>
    document.getElementById('nickname').addEventListener('change', function () {
        let nickname = this.value;

        if (nickname === '') {
            // Если выбран "---", сбрасываем чекбоксы
            document.getElementById('admin').checked = false;
            document.getElementById('admin_add').checked = false;
            document.getElementById('admin_edit').checked = false;
            document.getElementById('admin_delete').checked = false;
            document.getElementById('leader').checked = false;
            document.getElementById('leader_add').checked = false;
            document.getElementById('leader_edit').checked = false;
            document.getElementById('leader_delete').checked = false;
	    document.getElementById('tester').checked = false;
            document.getElementById('tester_add').checked = false;
            document.getElementById('tester_edit').checked = false;
            document.getElementById('tester_delete').checked = false;
            return;
        }

        fetch('dostup_menu.php?nickname=' + nickname)
            .then(response => response.json())
            .then(data => {
            	document.getElementById('admin').checked = data.admin == 1;
                document.getElementById('admin_add').checked = data.admin_add == 1;
                document.getElementById('admin_edit').checked = data.admin_edit == 1;
                document.getElementById('admin_delete').checked = data.admin_delete == 1;
                document.getElementById('leader').checked = data.leader == 1;
                document.getElementById('leader_add').checked = data.leader_add == 1;
                document.getElementById('leader_edit').checked = data.leader_edit == 1;
                document.getElementById('leader_delete').checked = data.leader_delete == 1;
                document.getElementById('tester').checked = data.tester == 1;
                document.getElementById('tester_add').checked = data.tester_add == 1;
                document.getElementById('tester_edit').checked = data.tester_edit == 1;
                document.getElementById('tester_delete').checked = data.tester_delete == 1;
            });
    });
</script>

</body>
</html>
