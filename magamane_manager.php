<?php
	session_start();

	require "magamane_dbConnect.php";
	$pdo=db_connect();
	
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS comics"
	." ("
	. "comic_id INT AUTO_INCREMENT PRIMARY KEY,"
	. "comic_name varchar(64),"
	. "comic_ruby varchar(64),"
	. "author varchar(64),"
	. "magazine varchar(32),"
	. "year varchar(8),"
	. "genre varchar(16),"
	. "fin_frag bit,"
	. "comic_image text,"
	. "comic_outline text"
	.");";
	$pdo->query($sql);
	
	//画像保存用フォルダの作成
	if(!file_exists('comic_image_directory')) mkdir('comic_image_directory');
	
	if(isset($_POST["comic_signup"])){
		$sql = 'SELECT * FROM comics';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		$exist_frag=false;
		foreach($results as $row){
			 if($_POST["comic_name"]==$row['comic_name']){
			 	$exist_frag=true;
				 break;
			}
		}
		if($exist_frag==false){
			//画像保存
			$image_path = 'comic_image_directory/'.date("YmdHis"); //画像保存のためのパス date()で名前の重複を防ぐ
			move_uploaded_file($_FILES['comic_image']['tmp_name'], $image_path); //指定したパスへ保存
			
			$sql = 'INSERT INTO comics (comic_name, comic_ruby, author, magazine, year, genre, fin_frag, comic_image, comic_outline) VALUES (:comic_name, :comic_ruby, :author, :magazine, :year, :genre, :fin_frag, :comic_image, :comic_outline)';
			$sql = $pdo -> prepare($sql);
			$sql -> bindParam(':comic_name', $_POST["comic_name"], PDO::PARAM_STR);
			$sql -> bindParam(':comic_ruby', $_POST["comic_ruby"], PDO::PARAM_STR);
			$sql -> bindParam(':author', $_POST["author"], PDO::PARAM_STR);
			$sql -> bindParam(':magazine', $_POST["magazine"], PDO::PARAM_STR);
			$sql -> bindParam(':year', $_POST["year"], PDO::PARAM_STR);
			$sql -> bindParam(':genre', $_POST["genre"], PDO::PARAM_STR);
			$sql -> bindParam(':fin_frag', $_POST["fin_frag"], PDO::PARAM_BOOL);
			$sql -> bindParam(':comic_image', $image_path, PDO::PARAM_STR);
			$sql -> bindParam(':comic_outline', $_POST["comic_outline"], PDO::PARAM_STR);
			$sql -> execute();
		}else echo "既に存在しています"."<br>";
	}
	
?>

<html>
	<meta charset="utf-8">
<?php
	if((isset($_SESSION["NAME"]))&&($_SESSION["NAME"]=="管理者")){
?>
		<form action="magamane_manager.php" method="POST" enctype="multipart/form-data">
			漫画名：<input type="text" name="comic_name" required><br>
			フリガナ：<input type="text" name="comic_ruby" required><br>
			作者：<input type="text" name="author" required><br>
			コミック誌：<input type="text" name="magazine" required><br>
			連載開始年：<input type="text" name="year" required><br>
			ジャンル：<input type="text" name="genre" required><br>
			完結したか：<input type="radio" name="fin_frag" value="0">未完結
								  <input type="radio" name="fin_frag" value="1">完結済<br>
			参考画像：<input type="file" name="comic_image" required><br>
			あらすじ：<textarea name="comic_outline" rows="10" cols="100" required></textarea><br>
			<input type="submit" name="comic_signup" value="漫画登録">
		</form>
		
		<!--
		<form action="magamane_manager.php" method="POST" enctype="multipart/form-data">
			ID：<input type="text" name="comic_id" required><br>
			フリガナ：<input type="text" name="comic_ruby" required><br>
			<input type="submit" name="comic_update" value="変更">
		</form>
		-->

<?php
	}else echo "ここを操作できるのは管理者だけです。";
?>
		<a href="magamane_main.php" target="_self">トップページに戻る</a>
<hr>
</html>

<?php
	echo "以下漫画リスト(確認用)"."<br>";
	if(isset($pdo)){
		$sql = 'SELECT * FROM comics';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row) echo "ID:{$row['comic_id']}  {$row['comic_name']} {$row['comic_ruby']} {$row['author']} {$row['magazine']} {$row['year']} {$row['genre']} {$row['fin_frag']} {$row['comic_image']}"."<br>";
	 	$pdo=null; //接続切断
	}