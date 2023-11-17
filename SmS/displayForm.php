<?php
$user = explode("@", (strtolower($_SERVER['REMOTE_USER'])))[0];
$query = "SELECT memberof FROM AD WHERE email LIKE :user";
$stmt = $connMySQL->prepare($query);
$stmt->execute([
	'user' => "$user%"
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result && $result['memberof'] == 1) {
    $formStyle = 'display:block;';
} else {
	$formStyle = 'display:none;';
}
?>
