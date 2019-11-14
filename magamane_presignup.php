<?php
	session_start();
	
	//ログイン状態でなければトップページへリダイレクト
	if(isset($_SESSION["ID"])){
		header("Location: magamane_main.php");
        exit();
	}
	
	//再送信確認防止
	header('Expires:-1');
	header('Cache-Control:');
	header('Pragma:');
	
	require "magamane_dbConnect.php";
	$pdo=db_connect();
	
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS temp_users"
	." ("
	. "mail varchar(255) NOT NULL,"
	. "token varchar(255) NOT NULL,"
	. "date DATETIME NOT NULL"
	.");";
	$pdo->query($sql);
	
	function mail_check($pdo, $table_name, $mail_address){
		$sql = 'SELECT * FROM '.$table_name.' WHERE mail=:mail';
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':mail', $mail_address, PDO::PARAM_STR);
		$stmt -> execute();
		return $stmt->fetch();
	}
	
	//クロスサイトリクエストフォージェリ（CSRF）対策
	if(!isset($_SESSION["TOKEN"])) $_SESSION["TOKEN"] = base64_encode(openssl_random_pseudo_bytes(16));
	$token = $_SESSION["TOKEN"];
	//クリックジャッキング対策
	header('X-FRAME-OPTIONS: DENY');
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>仮登録</title>
	</head>
	<body>
		<p style="text-align:center">
			○○○○~漫画管理&レビューサイト~
		</p>
		<p style="text-align: right">
			<a href="magamane_main.php" target="_self">トップページ</a>
			/ 
			<a href="magamane_login.php" target="_self">ログイン</a>
		</p>
		<hr>
	
<?php
		if(isset($_POST["mail_address"])){
			if($_POST["token"]!==$_SESSION["TOKEN"]){
				echo "不正アクセスの可能性あり";
				exit();
			}
			$mail_address=htmlspecialchars($_POST["mail_address"]);
			if(($mail_address!=="")&&(filter_var($mail_address, FILTER_VALIDATE_EMAIL))){			
				$mail_result = mail_check($pdo,"users",$mail_address);
				$mail_result2 = mail_check($pdo,"temp_users",$mail_address);	
				if(($mail_result==false)&&($mail_result2==false)){
					$url_token = hash('sha256',uniqid(rand(),true));
					$url = "https://tb-210334.tech-base.net/magamane_signup.php?url_token=".$url_token;
					$sql = 'INSERT INTO temp_users (mail, token, date) VALUES (:mail, :url_token, now())';
					$sql = $pdo -> prepare($sql);
					$sql -> bindParam(':mail', $mail_address, PDO::PARAM_STR);
					$sql -> bindParam(':url_token', $url_token, PDO::PARAM_STR);
					$sql -> execute();
	
					require 'send_test.php';
					$mail_subject = "仮登録完了しました";
					$body = "仮登録して頂きありがとうございます！<br>24時間以内に下記のURLよりご登録下さい。<br>{$url}";
					mail_send($mail_address, $mail_subject, $body);
					$_SESSION = array();
	 				session_destroy();
					echo "メールを送信しました。24時間以内にメールに記載されたURLからご登録下さい。";
				}else echo "既に使用されているメールアドレスです。別のものを使用して下さい。<br>";
			}else echo "不正なメールアドレスです。メールアドレスを入力し直して下さい。<br>";
		}
?>
		<h1>メール登録画面</h1>
		<form action="" method="POST">
			<fieldset>
               <legend>新規登録</legend>
               メールアドレスを入力して下さい<br>
               メールアドレス：<input type="text" name="mail_address" size="50" required>
				<input type="hidden" name="token" value="<?=$token?>">
				<input type="submit" value="登録する"> 
			</fieldset>
		</form>
	</body>
</html>