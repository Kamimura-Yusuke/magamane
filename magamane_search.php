<?php
	session_start();
	
	require "magamane_dbConnect.php";
	$pdo=db_connect();
	
	//テーブル作成
	$sql = "CREATE TABLE IF NOT EXISTS shelves"
	." ("
	. "user_id INT,"
	. "comic_id INT,"
	. "read_index INT(1),"
	. "fav_flag BOOL"
	.");";
	$pdo->query($sql);
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>○○○○~漫画管理&レビューサイト~</title>
		<link rel="stylesheet" type="text/css" href="magamane_head.css">
	</head>
	<body>
	<div class="header">
<?php
		require "magamane_head_output.php";
		head_output();
		head_output2();
?>
	</div>
<?php
	ranking_list();
?>
	
	<div class="contents">
	<!--検索欄-->
	<form action="magamane_search.php" method="GET">
		<p style="text-align: center">
			<input type="text" name="contents_search" size="50" placeholder="作品名、作者名、またはその一部を入力" required>
			<input type="submit" value="検索">
		</p>
	</form>
	
<?php
	$sql = 'SELECT * FROM comics';
	$stmt = $pdo->query($sql);
	$comic_results = $stmt->fetchAll();

	//漫画登録処理
	require "magamane_comic_signup.php";
	if((isset($_SESSION["ID"]))&&(isset($_POST["comic_to_shelf"]))){
		foreach($comic_results as $comic_row){
			if(!isset($_POST["comic_id{$comic_row['comic_id']}"])) continue;
			$post_comicid=$_POST["comic_id{$comic_row['comic_id']}"];
			comic_to_shelf($pdo,$comic_row['comic_name'],$post_comicid);
		}
	}else if((!isset($_SESSION["NAME"]))&&(isset($_POST["comic_to_shelf"]))) echo "本棚への登録はログイン必須です。"."<br>";


	//検索結果の抽出表示
	if(isset($_GET["contents_search"])){
		$contents_search = htmlspecialchars($_GET["contents_search"]);
		echo "「{$contents_search}」の検索結果"."<br>";
		$search_count=0;
?>
		<hr>
		<form action="magamane_search.php?contents_search=<?php echo $_GET["contents_search"];?>" method="POST">
<?php
			$contents_search_k = mb_convert_kana($contents_search, "c");	//カタカナ→平仮名に変換
			$contents_search_h = strtoupper($contents_search); //小文字→大文字
			foreach($comic_results as $comic_row){
				//作品名(フリガナ含む)、作者、またはそれらの一部が一致すれば表示
				if((strpos($comic_row['comic_name'],"$contents_search")!==false)||(strpos($comic_row['author'],"$contents_search")!==false)||(strpos($comic_row['comic_ruby'],"$contents_search")!==false)||(strpos($comic_row['comic_ruby'],"$contents_search_k")!==false)||(strpos($comic_row['comic_name'],"$contents_search_h")!==false)){
	
					 //検索結果が本棚に登録されているマンガかチェック(comic_signup.phpのfunction)
					 $exist_flag=check_shelf($pdo,$comic_row['comic_id']);

					//本棚にそのマンガが存在しないならば、登録用のチェックボックスを表示する
					if($exist_flag==false){
?>					<input type="checkbox" name="comic_id<?php echo $comic_row['comic_id'];?>" value="<?php echo $comic_row['comic_id'];?>">
<?php			}else echo "登録済";
?>				<img src="<?php echo $comic_row['comic_image'];?>" width="140" height="200">
				 	<a href="magamane_review.php?comic_id=<?php echo $comic_row['comic_id'];?>" target="_self"> <?php echo $comic_row['comic_name'];?> </a>
<?php
					echo $comic_row['author']."<br>";
					$search_count++;
?>
					<hr>
<?php
				}
			}
		if($search_count==0) echo "検索結果0件。キーワードを変更して下さい";
		else{
?>		<div class="signup_button">
				<input type="submit" name="comic_to_shelf" value="本棚に登録">
			</form>
			</div>
<?php 
		}
	}else echo "検索結果0件。キーワードを変更して下さい";
?>	
	</div>
	</body>
</html>
