<html>
<head>
    <meta charset="utf8">
    <title>Звонок из браузера</title>
    <style>
        body {
            text-align: center;
        }
        form {
            margin: auto;
            width: 500px;
        }
        input {
            padding: 10px;
            font-size: 20;
        }
        </head>
    </style>
    <body>
        <?php

require('../passAD.php'); // der пароль
require('DB/sql_connect.php'); // der пароль

$clientNumber = $_POST["clientNumber"]; // номера вызова с главной страницы

$user = preg_replace('/local$/', 'ru', strtolower($_SERVER['REMOTE_USER'])); // имя пользователя

$sql = "SELECT phone, callerID, context FROM AD WHERE email = :user"; // запрос данных пользователя

$stmt = $connMySQL->prepare($sql); 
$stmt->execute([':user' => $user]);

if ($stmt->rowCount() > 0) { // сбор ответа sql
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $phone = $row['phone'];
    $callerID = $row['callerID'];
    $context = $row['context'];
}


$callDir = '/var/spool/asterisk/callfolder/'; //папка для создания call-файлов
$outDir = '/var/spool/asterisk/outgoing/'; // удалённая папка на астериске
$fn = "php" . date('His') . ".call"; // имя call-файла


$clientNumber = preg_replace('/[^0-9]/', '', $clientNumber); // удаляем всё кроме цифор
if (strlen($clientNumber) == 11): // если 11 цифир
        $clientNumber = substr_replace($clientNumber, 8, 0, 1); // заменить первый цифир на 8
elseif (strlen($clientNumber) == 10): // а если 10 цифир
        $clientNumber ="8" . $clientNumber; // добавить в начало 8
    endif;


if($_POST['call'] == "Позвонить") { // условие для кнопок
    $channel = "PJSIP";
    $numberChannel = $phone;
} elseif ($_POST['call'] == "Позвонить из SKYPE") {
    $channel = "SIP/SFB";
    $numberChannel = substr_replace($phone, 6, 0, 1); //замена 2 на 6
}

$text = "Channel: $channel/$numberChannel\n";
$text .= "CallerID: $callerID<$phone>\n";
$text .= "Context: $context\n";
$text .= "Extension: $clientNumber\n";
$text .= "Priority: 1\n";
$text .= "Archive:yes";
$file = fopen($callDir . $fn, 'w'); // открываем файл
fwrite($file, $text . "\n"); // пишем в файл
fclose($file); // закрываем файл

$cmd = "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=quiet -i ~/.ssh/id_rsa $callDir$fn webcall-adm@1.1.1.2:$outDir$fn"; //  строка отправки файла

shell_exec($cmd); // вызов строки отправки файла

echo "    <p>Астериск выполнит вызов с номера $numberChannel на номер $clientNumber.</p>\n"; // инфо

?>
<meta http-equiv="refresh" content="10;url=https://webcall.example.local" /> <!-- 10 секунд и уходим на главную -->
<input type="button" onclick="history.back();" value="Назад" /> </body><!-- кнопка назад -->
</html>