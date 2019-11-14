<?php
	function db_connect(){
		//DB接続
		$dsn = 'mysql:dbname=********;host=localhost';
		$user = '*******';
		$password = '*********';
		$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		return $pdo;
	}
?>