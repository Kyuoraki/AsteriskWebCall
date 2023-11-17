<?php
mb_internal_encoding('UTF-8');
$server = "1.1.1.1"; // сервер Active Directory
$username = "CN=Telecom Operator,OU=Special,OU=Users,OU=Example Objects,DC=example,DC=local"; // логин для подключения к Active Directory
require('../passAD.php'); // пароль для подключения к MySQL
$connMySQL = new PDO('mysql:host=localhost;dbname=db_original', 'root', "$passMySQL"); // подключение к MySQL
$ldap = ldap_connect($server); // соединение с сервером Active Directory
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3); // установка версии протокола LDAP
ldap_bind($ldap, $username, $passKey); // подключение к Active Directory
$objectsDN = "OU=Example Objects,DC=example,DC=local";
$llcDN = "OU=Example LLC,DC=example,DC=local";
$search = ldap_search($ldap,$llcDN,"(&(mail=*)(!(mail=admin@example.ru)))",
        ["givenName", "sn", "cn", "telephoneNumber", "description", "mail", "memberof"]); // поиск данных пользователей
$results = ldap_get_entries($ldap, $search); // получение результатов поиска
$data = []; // массив для хранения найденных данных
$group = []; // массив для хранения найденных данных
$connMySQL->query("TRUNCATE `db_original`.`AD`"); // очистка эталон таблицы
$sql = "INSERT INTO AD (firstName, lastName, fatherName, phone, description, email, memberof, callerID, context) VALUES (:firstName, :lastName, :fatherName, :phone, :description, :email, :memberof, :callerID, :context)"; //заполнение таблицы эталон
$stmt = $connMySQL->prepare($sql);


$searchGroup = ldap_search($ldap,$objectsDN,"(&(objectClass=group)(cn=internal_webcall_sms))",["member","name"]); // поиск групп
$accessGroups = ldap_get_entries($ldap, $searchGroup); // получение результатов поиска
foreach ($accessGroups as $accessGroup) { if (!isset($accessGroup["member"], $accessGroup["name"])) continue; // пропустить, если не найдены данные
$group = array_map(function($value) { preg_match('/CN=([^,]+)/', $value, $matches); return $matches[1] ?? null;}, $accessGroup["member"]);}//очистка массива разрешённых групп от всего лишнего

foreach ($results as $result) {
  // пропустить, если не найдены данные
        if (!isset($result["sn"][0]) || !isset($result["givenname"][0]) || !isset($result["cn"][0]) || !isset($result["telephonenumber"][0]) || !isset($result["description"][0]) || !isset($result["mail"][0]) || !isset($result["memberof"])) {
                continue;
        }
        $arrMemberof = array_map(function($value) { preg_match('/CN=([^,]+)/', $value, $matches); return $matches[1] ?? null;}, $result["memberof"]);//очистка массива групп пользователя
        $memberof = !empty(array_intersect(array_filter($group), array_filter($arrMemberof))) ? 1 : 0; // сравнение массивов групп для доступа

        $nameParts = explode(' ', $result["cn"][0]); //делим ФИО по частям
        $callerID = $nameParts[0]." ".mb_substr($nameParts[1], 0, 1).".".mb_substr($nameParts[2], 0, 1)."."; // собираем ФИО для CallerID
        $fatherName = $nameParts[2];  // забираем Отчество

        $distinguishedName = str_replace($llcDN, '', ($result["dn"])); // отсекаем лишнее от dn
        preg_match_all('/OU=([^,]+)/', $distinguishedName, $matches); // массив всех OU из DN
        if (count($matches[1]) >= 2) { // Присваиваем значения переменным
    $department = $matches[1][count($matches[1]) - 2]; // предпоследняя OU - под отдел
    $distinguishedName = $matches[1][count($matches[1]) - 1]; // последняя OU - отдел
  }
        switch ($distinguishedName) { // выдача контекста по отделам
		case "value1":
		$context = "value1";
		break;
		case "value2":
		$context = "value2";
		break;
		case "value3":
		$context = "value3";
		break;
		default:
		if ($distinguishedName == "value4" && $department == "value4.5") {
			$context = "value4";
		} else {
			$context = "value5";
		}
		break;
	}


        $data[] = [
                "lastName" => $result["sn"][0],
                "firstName" => $result["givenname"][0],
                "fatherName" => $fatherName,
                "phone" => $result["telephonenumber"][0],
                "description" => $result["description"][0],
                "email" => $result["mail"][0],
                "memberof" => $memberof,
                "callerID" => $callerID,
                "context" => $context
        ]; //собираем из ldap в массив
}
foreach ($data as $row) { // из массива в sql
    $stmt->execute([
        ':lastName' => $row["lastName"],
        ':firstName' => $row["firstName"],
        ':fatherName' => $row["fatherName"],
        ':phone' => $row["phone"],
        ':description' => $row["description"],
        ':email' => $row["email"],
        ':memberof' => $row["memberof"],
        ':callerID' => $row["callerID"],
        ':context' => $row["context"]
    ]);
}
$stmt = null;
$connMySQL = null;
ldap_unbind($ldap);
// header("Content-type: application/json"); // установка заголовка HTTP для возвращаемых данных
// echo json_encode($data); // вывод данных в формате JSON
?>
