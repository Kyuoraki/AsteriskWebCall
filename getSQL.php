<?php
require('../../passAD.php'); // пароль для подключения к MySQL
require('sql_connect.php'); // подключение к MySQL
$tableName = $_SERVER['REMOTE_USER']; // имя пользователя

$sql = "SHOW TABLES LIKE '':tableName''"; // проверка таблицы пользователя
$stmt = $connMySQL->prepare($sql);
$stmt->bindParam(':tableName', $tableName);
$stmt->execute();
if ($stmt->rowCount() == 0) {// Создание таблицы, если она не существует
    $sql = "CREATE TABLE `:tableName` (email VARCHAR(255));";
    $stmt = $connMySQL->prepare($sql);
    $stmt->execute([':tableName' => $tableName]);
}

$sql_origin = "SELECT * FROM `AD`"; // эталон таблица
$sql_fav = "SELECT * FROM `'$tableName'`"; // таблица избранного 
$stmt_origin = $connMySQL->query($sql_origin);
$stmt_fav = $connMySQL->query($sql_fav);

$data_origin = array(); // Формирование массива данных
while ($row_origin = $stmt_origin->fetch(PDO::FETCH_ASSOC)) {
    $row_origin['fav'] = " "; // Add a new column 'fav' and set its initial value to false
    $data_origin[] = $row_origin;
}

while ($row_fav = $stmt_fav->fetch(PDO::FETCH_ASSOC)) { // Сравнение по полю email и добавление столбца fav
    $email_fav = $row_fav['email'];
    foreach ($data_origin as &$row_origin) {
        if ($row_origin['email'] === $email_fav) {
            $row_origin['fav'] = "★"; // Set 'fav' to true if the email exists in $sql_origin and $sql_fav
            break;
        }
    }
}
header("Content-type: application/json");
echo json_encode($data_origin);
$stmt_fav = null;
$stmt_origin = null;
$connMySQL = null;
?>
