<?php 
require('../passAD.php'); // пароль для подключения к MySQL
require('DB/sql_connect.php'); // подключение к MySQL
require('SmS/displayForm.php'); // доступ к смс
?>
<html>
<head>
  <meta charset="utf8">
  <title>Звонок из браузера</title>
  <link rel="stylesheet" href="node_modules/contactable/contactable.css"/>
  <link rel="stylesheet" href="style.css">
  <script src="node_modules/jquery-1.7.min.js"></script>
  <script src="node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="node_modules/contactable/jquery.contactable.js"></script>
</head>
<body>
  <h1 id="h1_header"> WEBCALL </h1>
  <div class="container">
    <form class="form" action="call_sql.php" method="post"> 
      <p>
        <input id="number" name="clientNumber" placeholder="Номер вызова..." />
      </p>
      <input id="button" class="btn" type="submit" name="call" value="Позвонить" />
      <input id="skypeButton" class="btn" type="submit" name="call" value="Позвонить из SKYPE" />
      <input id="favBtn" class="btn" type="button" value="Мои Избранные" />
    </form>
    <form class="sms" style="<?php echo $formStyle; ?>" id="messageForm" action="send.php" method="post">
      <p>
        <input id="numberSMS" name="number" type="text" required placeholder="Отправить СМС на номер">
      </p>
      <p>
        <div style="position: relative;">
          <textarea maxlength="70" name="message" id="message" rows="4" oninput="updateCounter(this)" required placeholder="Текст сообщения..."></textarea>
          <span id="counter" style="position: absolute; bottom: 10px; right: 5px;"></span>
        </div>
      </p>
      <p>
        <input class="btn" type="submit" value="Отправить"/>
      </p>
    </form>
  </div>
  <table id="tableAD" class="table" style="font-size: 16px">
    <thead>
      <tr>
        <th>&nbsp;&nbsp;&nbsp;&nbsp;☆&nbsp;&nbsp;&nbsp;&nbsp;</th>
        <th>Фамилия</th>
        <th>Имя</th>
        <th>Отчество</th>
        <th>Телефон</th>
        <th>Должность</th>
        <th>E-mail</th>
      </tr>
    </thead>
    <tbody class="tbody" id="table-data"> </tbody>
  </table>
  <div id="contactable"><!-- форма отзывов --></div>
  <script src="/node_modules/getAD.js"></script>
  <script src="/SmS/smsForm.js"></script><!-- счётчик символов -->
</body>
</html>
