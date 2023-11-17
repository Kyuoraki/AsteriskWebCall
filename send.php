<html>
<head>
    <meta charset="utf8">
    <title>Звонок из браузера</title>
    <style>
        body {
            text-align: center;
        }
        input {
            padding: 10px;
            font-size: 20;
        }
        </head>
    </style>
    <body>
<?php
$number = $_POST['number'];
$message = $_POST['message'];
$smsDir = '/var/spool/sms/'; // папка
$outSmsDir = '/var/spool/sms/outgoing/'; // удалённая папка
$fn = "sms" . date('His') . ".txt";
$number = preg_replace('/[^0-9]/', '', $number); // удаляем всё кроме цифор
if (strlen($number) == 11): // если 11 цифир
    $number = substr_replace($number, "+7", 0, 1); // заменить первый цифир на 8
elseif (strlen($number) == 10): // а если 10 цифир
    $number ="+7" . $number; // добавить в начало 8
endif;
$text = "To: $number\n\n";
$text .= "$message";
$file = fopen($smsDir . $fn, 'w'); // открываем файл
fwrite($file, $text);
fclose($file);
$cmd = "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o LogLevel=quiet -i ~/.ssh/webcall-sms-adm.key $smsDir$fn webcall-sms-adm@sms.example.local:$outSmsDir$fn"; //  строка отправки файла
shell_exec($cmd); // вызов строки отправки файла
echo "    <p>Сообщение $message будет отправлено на номер $number.</p>\n";
header("Refresh: 10; URL=https://webcall.example.local");
?>
<input type="button" onclick="history.back();" value="Назад" /> </body>