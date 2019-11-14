<?php
	session_start();
	
	if(isset($_SESSION["ID"])){
		$_SESSION = array(); //空配列を代入する事で全てのセッション変数を削除
		//unset($_SESSION("NAME")) ←削除する変数が一つならこれでも可
		session_destroy();
	}
	header("Location: magamane_main.php");
?>