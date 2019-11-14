<?php
	session_start();
	
	//ログイン状態でなければトップページへリダイレクト
	if(isset($_SESSION["ID"])){
		header("Location: magamane_presignup.php");
        exit();
	}
	
	if(isset($_GET["url_token"]))$_SESSION["URLTOKEN"]=$_GET["url_token"];
	
	if(!isset($_SESSION["URLTOKEN"])){
		header("Location: magamane_presignup.php");
        exit();
	}
	
	//再送信確認防止
	header('Expires:-1');
	header('Cache-Control:');
	header('Pragma:');

	require "magamane_dbConnect.php";
	$pdo=db_connect();
	
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS users"
	." ("
	. "user_id INT AUTO_INCREMENT PRIMARY KEY,"
	. "user_name varchar(20),"
	. "pass varchar(255),"
	. "gender varchar(3),"
	. "age varchar(5),"
	. "mail varchar(255)"
	.");";
	$pdo->query($sql);
	
	//仮登録24時間経過したものを削除
	$sql = 'DELETE FROM temp_users WHERE date < now() - interval 24 hour';
	$pdo->query($sql);
	
	//クロスサイトリクエストフォージェリ（CSRF）対策
	if(!isset($_SESSION["TOKEN"])) $_SESSION["TOKEN"] = base64_encode(openssl_random_pseudo_bytes(16));
	$token = $_SESSION["TOKEN"];
	//クリックジャッキング対策
	header('X-FRAME-OPTIONS: DENY');
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>新規登録</title>
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
		$output_judge=false;
		
		$sql = 'SELECT * FROM temp_users WHERE token="'.$_SESSION["URLTOKEN"].'"';
		$stmt = $pdo->query($sql);
		$temp_result = $stmt->fetch();
		if($temp_result==false){
			echo "下記の可能性があるため登録画面に進めません。お手数ですが初めからやり直して下さい。<br>"
					."・メールアドレスが登録されていない<br>"
					."・仮登録から24時間経過<br>"
					."・不正なトークン<br>";
			$output_judge=true;
		}else{
			$mail_address=$temp_result['mail'];	
			if(isset($_POST["signup_form"])){
				if($_POST["token"]!==$_SESSION["TOKEN"]){
					echo "不正アクセスの可能性あり";
					$output_judge=true;
				}else{
					$user_name = htmlspecialchars($_POST["user_name"]); //XSSを防ぐための処理
					
					//ユーザー名の重複チェック
					$sql = 'SELECT * FROM users WHERE user_name=:user_name';
					$stmt = $pdo -> prepare($sql);
					$stmt -> bindParam(':user_name', $user_name, PDO::PARAM_STR);
					$stmt -> execute();
					$name_result = $stmt->fetch();
					
					if($name_result==false){
						if($_POST["pass"]==$_POST["pass2"]){
							$pass = htmlspecialchars($_POST["pass"]);
							$pass = password_hash($pass, PASSWORD_DEFAULT); //パスワードのハッシュ化
							$sql = 'INSERT INTO users (user_name, pass, gender, age, mail) VALUES (:name, :pass, :gender, :age, :mail)';
							$sql = $pdo -> prepare($sql);
							$sql -> bindParam(':name', $user_name, PDO::PARAM_STR);
							$sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
							$sql -> bindParam(':gender', $_POST["gender"], PDO::PARAM_STR); //htmlspecialchars()を通さないのは、XSSの心配がないためである（名前、パスと違い選択式であるが故）
							$sql -> bindParam(':age', $_POST["age"], PDO::PARAM_STR);
							$sql -> bindParam(':mail', $mail_address, PDO::PARAM_STR);
							$sql -> execute();
							
							require 'send_test.php';
							$mail_subject = "登録完了しました";
							$body = "登録して頂きありがとうございます！<br>"
										."以下の内容で登録完了しました！ユーザー名とパスワードはログインする際に必要となるのでお控え下さい。<br>"
										."ユーザー名：{$user_name}"."<br>"
										."パスワード：{$_POST["pass"]}"."<br>"
										."性別：{$_POST["gender"]}"."<br>"
										."年齢：{$_POST["age"]}"."<br>";
							mail_send($mail_address, $mail_subject, $body);
							$_SESSION = array();
			 				session_destroy();
							echo "登録完了しました！登録情報を登録メールアドレス宛に送信しましたのでご確認下さい。";
							$sql = 'DELETE FROM temp_users WHERE mail="'.$mail_address.'"';
							$pdo->query($sql);
							$output_judge=true;
						}else echo "パスワードが異なります"."<br>";
					}else echo "同じ名前のユーザーが既に存在します。別のものを使用して下さい。"."<br>";
				}
			}
		}

	if($output_judge==false){
?>    <h1>新規登録画面</h1>
        <form action="magamane_signup.php" method="POST">
            <fieldset>
                <legend>新規登録</legend>
                メールアドレス：<?php echo $mail_address."<br>";?>
                ユーザー名：<input type="text" name="user_name" maxlength='20' required><br>
                パスワード：<input type="password" name="pass" required><br>
                パスワード(確認用)：<input type="password" name="pass2" required><br>
                性別：<input type="radio" name="gender" value="男">男
                		   <input type="radio" name="gender" value="女">女
                		   <input type="radio" name="gender" value="無回答" checked>無回答<br>
                年齢：<select name="age">
                		   <option value="10歳未満">10歳未満</option><option value="10代">10代</option>
                		   <option value="20代">20代</option><option value="30代">30代</option>
                		   <option value="40代">40代</option><option value="50代">50代</option>
                		   <option value="60代">60代</option><option value="70歳以上">70歳以上</option>
                		   </select><br>
                <input type="hidden" name="token" value="<?=$token?>">
                <input type="submit" name="signup_form" value="新規登録">
            </fieldset>
        </form>
<?php }
?>
    </body>
</html>

<?php
/*	echo "以下会員リスト(確認用)"."<br>";
	if(isset($pdo)){
		$sql = 'SELECT * FROM users';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row) echo "ID:{$row['user_id']}  {$row['user_name']} {$row['pass']}(ハッシュ化済) {$row['gender']} {$row['age']}"."<br>";
	 	$pdo=null; //接続切断
	}*/
?>