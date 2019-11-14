<?php
	session_start();
	
	//ログイン状態であればトップページへリダイレクト
	if(isset($_SESSION["ID"])){
		header("Location: magamane_main.php");
        exit();
	}

	require "magamane_dbConnect.php";
	$pdo=db_connect();
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>本登録</title>
	</head>
	<body>
		<p style="text-align:center">
			○○○○~漫画管理&レビューサイト~
		</p>
		<p style="text-align: right">
			<a href="magamane_main.php" target="_self">トップページ</a>
			/ 
			<a href="magamane_presignup.php" target="_self">新規登録</a>
		</p>
		<hr>
		<h1>ログイン画面</h1>
		<form action="magamane_login.php" method="POST">
	    	<fieldset>
	        	<legend>ログイン</legend>
	            ユーザー名：<input type="text" name="user_name" required><br>
	            パスワード：<input type="password" name="pass" required><br>
				<input type="submit" name="login_form" value="ログイン">
			</fieldset>
		</form>
    </body>
</html>

<?php
	//名前とパスがデータベースに保存されているものと一致しているかの確認→ログイン
	if(isset($_POST["login_form"])){
		$user_name = htmlspecialchars($_POST["user_name"]);
		$pass = htmlspecialchars($_POST["pass"]);
		$sql = 'SELECT * FROM users WHERE user_name=:user_name';
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':user_name', $user_name, PDO::PARAM_STR);
		$stmt -> execute();
		$user_result = $stmt->fetch();
		
		if($user_result!==false){
			if(password_verify($pass, $user_result['pass'])){
				$_SESSION["ID"]=$user_result['user_id'];
				$_SESSION["NAME"]=$user_name;
				header("Location: magamane_main.php");
	            exit();
			}else echo "パスワードが間違えています"."<br>";
		}else echo "ユーザ名またはパスワードが間違えています"."<br>";
	}
?>