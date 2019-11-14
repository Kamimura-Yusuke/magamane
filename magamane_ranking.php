<?php
	session_start();
	
	require "magamane_dbConnect.php";
	$pdo=db_connect();
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
		<form action="" method="POST">
<?php
			//漫画登録処理
			require "magamane_comic_signup.php";
			if((isset($_SESSION["ID"]))&&(isset($_POST["comic_to_shelf"]))){
				$sql = 'SELECT * FROM comics';
				$stmt = $pdo->query($sql);
				$comic_results = $stmt->fetchAll();
				foreach($comic_results as $comic_row){
					if(!isset($_POST["comic_id{$comic_row['comic_id']}"])) continue;
					$post_comicid=$_POST["comic_id{$comic_row['comic_id']}"];
					comic_to_shelf($pdo,$comic_row['comic_name'],$post_comicid);
				}
			}else if((!isset($_SESSION["NAME"]))&&(isset($_POST["comic_to_shelf"]))) echo "本棚への登録はログイン必須です。"."<br>";

		if(strpos($_SERVER['QUERY_STRING'],"genre")!==false){
			if(strpos($_SERVER['QUERY_STRING'],"fantasy")!==false) $genre_extraction="ファンタジー";
			if(strpos($_SERVER['QUERY_STRING'],"love")!==false) $genre_extraction="ラブコメディー";
			if(strpos($_SERVER['QUERY_STRING'],"comedy")!==false) $genre_extraction="ギャグ・コメディー";
			if(strpos($_SERVER['QUERY_STRING'],"sf")!==false) $genre_extraction="SF";
			if(strpos($_SERVER['QUERY_STRING'],"mystery")!==false) $genre_extraction="サスペンス・ミステリー";
			if(strpos($_SERVER['QUERY_STRING'],"sport")!==false) $genre_extraction="スポーツ";
			if(strpos($_SERVER['QUERY_STRING'],"history")!==false) $genre_extraction="歴史";
		}
		if(isset($genre_extraction)){
			echo "『{$genre_extraction}』"."<br>";
			$sql='SELECT * FROM comics WHERE genre="'.$genre_extraction.'"';
		}else{
			echo "『総合ランキング』"."<br>";
			$sql='SELECT * FROM comics';
		}
		require "magamane_scoresort.php";
		list($score_arr,$comicid_arr)=score_sort($pdo,$sql);
		
		for($i=0;$i<count($comicid_arr);$i++){
			$sql = 'SELECT * FROM comics WHERE comic_id='.$comicid_arr[$i];
			$stmt = $pdo->query($sql);
			$comic_result = $stmt->fetch();
			echo $i+1;
			echo '&nbsp総合点：'.number_format($score_arr[$i][0],2,null,'')."<br>";
			
			//検索結果が本棚に登録されているマンガかチェック(comic_signup.phpのfunction)
			$exist_flag=check_shelf($pdo,$comic_result['comic_id']);
			
			if($exist_flag==false){
?>
			 	<input type="checkbox" name="comic_id<?php echo $comic_result['comic_id'];?>" value="<?php echo $comic_result['comic_id'];?>">
<?php	}else echo "登録済";
?>
			<img src="<?php echo $comic_result['comic_image'];?>" width="140" height="200">
			<a href="magamane_review.php?comic_id=<?php echo $comic_result['comic_id'];?>" target="_self">
		 		<?php echo $comic_result['comic_name'];?>
		 	</a>
<?php	echo $comic_result['author']."<br>";
?>		<hr>
<?php
		}
?>    	<div class="signup_button">
			<input type="submit" name="comic_to_shelf" value="本棚に登録">
			</div>
		</form>
	</div>
	</body>
</html>