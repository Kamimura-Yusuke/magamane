<?php
	session_start();
	
	require "magamane_dbConnect.php";
	$pdo=db_connect();
	
	$sql = 'SELECT * FROM comics WHERE '.$_SERVER['QUERY_STRING'];
	$stmt = $pdo->query($sql);
	$comics_result = $stmt->fetch();
	
	if($comics_result==false){
		header("Location: magamane_main.php");
        exit();
	}
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>○○○○~漫画管理&レビューサイト~</title>
		<style>
			.comic_info{
	 			padding: 10px 160px 50px; <!--height width 下-->
			}
		</style>
	</head>
	<body>
<?php
	require "magamane_head_output.php";
	head_output();
	head_output2();
?>
	<div>
		<p style="float: left">
			<img src="<?php echo $comics_result['comic_image'];?>" width="140" height="200">
		</p>
		<div class="comic_info">
<?php
			echo "<br>作品名：{$comics_result['comic_name']}<br>";
			echo "著者：{$comics_result['author']}<br>";
			echo "コミック誌：{$comics_result['magazine']}<br>";
			echo "発表年：{$comics_result['year']}<br>";
			echo "ジャンル：{$comics_result['genre']}<br>";
			if($comics_result['fin_frag']==0) echo "未完結<br>";
			else echo "完結済<br>";		
?>
		</div>
	</div>
	<hr>
<?php
	//全体評価の平均値を求める
	$score_arr=array(0,0,0,0,0,0);
	$score_string=array("total_score","content_score","illust_score","chara_score","read_score","original_score");
	for($i=0;$i<count($score_arr);$i++){
		$sql = 'SELECT AVG('.$score_string[$i].') FROM reviews WHERE '.$_SERVER['QUERY_STRING'];
		$stmt = $pdo->query($sql);
		$score_arr[$i] = $stmt->fetch();	//['**_score']と[0]のパターンが格納される（二重配列）
	}
	//全体評価の平均値を表示
	echo '総合評価：'.number_format($score_arr[0][0],2,null,'').'<br>';
	echo '内容評価：'.number_format($score_arr[1][0],2,null,'').'<br>';
	echo 'イラスト評価：'.number_format($score_arr[2][0],2,null,'').'<br>';
	echo 'キャラクター評価：'.number_format($score_arr[3][0],2,null,'').'<br>';
	echo '読みやすさ評価：'.number_format($score_arr[4][0],2,null,'').'<br>';
	echo '独創性評価：'.number_format($score_arr[5][0],2,null,'').'<br>';
?>
	<hr>
<?php
	echo "レビュー一覧<br>";
	$sql = 'SELECT * FROM reviews WHERE '.$_SERVER['QUERY_STRING'];
	$stmt = $pdo->query($sql);
	$review_results = $stmt->fetchAll();
	foreach($review_results as $review_row){
		if(($review_row['review']!==NULL)&&($review_row['review']!=="")){
			$sql='SELECT * FROM users WHERE user_id='.$review_row['user_id'];
			$stmt = $pdo->query($sql);
			$user_result = $stmt->fetch();
			echo "{$user_result['user_name']}:<br>{$review_row['review']}<br>";
		}
	}
?>
	<hr>
	</body>
</html>