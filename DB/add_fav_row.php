<?php
require('../../passAD.php'); // пароль для подключения к MySQL
require('sql_connect.php'); // подключение к MySQL
$tableName = $_SERVER['REMOTE_USER']; // имя пользователя
$mailCell = $_POST["mailCell"]; // почта избранного
$sql = "INSERT IGNORE INTO `'$tableName'` VALUES (:email)"; // добавление избранного
$stmt = $connMySQL->prepare($sql); 
$stmt->bindParam(':email', $mailCell);
$stmt->execute();
$connMySQL = null;
$stmt = null;
