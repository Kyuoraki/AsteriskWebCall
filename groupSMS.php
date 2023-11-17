<?php
$server = "1.1.1.1"; // сервер Active Directory
$username = "CN=Telecom Operator,OU=Special,OU=Users,OU=Example Objects,DC=example,DC=local"; // логин для подключения к Active Directory
$ldap = ldap_connect($server); // соединение с сервером Active Directory
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3); // установка версии протокола LDAP
ldap_bind($ldap, $username, $passKey); // подключение к Active Directory
$search = ldap_search(
	$ldap,
	"OU=Example Objects,DC=example,DC=local",
	"(&(objectClass=group)(cn=internal_webcall_sms))",
	["member","name"]
); // поиск групп
$results = ldap_get_entries($ldap, $search); // получение результатов поиска
$group = []; // массив для хранения найденных данных

foreach ($results as $result) {
  // пропустить, если не найдены данные
	if (!isset($result["member"])) {
		continue;
	}
$group = array_map(function($value) { // очистка массива групп от всего лишнего
	preg_match('/CN=([^,]+)/', $value, $matches);
	return isset($matches[1]) ? $matches[1] : null;
}, array_values($result["member"]));
}
ldap_unbind($ldap);
header("Content-type: application/json"); // установка заголовка HTTP для возвращаемых данных
?>