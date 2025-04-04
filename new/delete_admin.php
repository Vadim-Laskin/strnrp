<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Удаляем администратора
    $query = "DELETE FROM site_admins WHERE id = $id";
    if ($conn->query($query)) {
        // Пересортировать ID в таблице
        $query = "SET @id := 0;";
        $conn->query($query);
        $query = "UPDATE site_admins SET id = @id := (@id + 1);";
        $conn->query($query);

        echo "Администратор удалён! <a href='admin_table.php'>Вернуться</a>";
    } else {
        echo "Ошибка: " . $conn->error;
    }
}
?>