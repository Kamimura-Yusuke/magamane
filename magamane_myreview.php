<?php
	session_start();
	
	//ログイン状態でなければトップページへリダイレクト
	if(!isset($_SESSION["ID"])){
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
	$sql = "CREATE TABLE IF NOT EXISTS reviews"
	." ("
	. "user_id INT,"
	. "comic_id INT,"
	. "total_score DOUBLE(3,2),"
	. "content_score INT(1),"
	. "illust_score INT(1),"
	. "chara_score INT(1),"
	. "read_score INT(1),"
	. "original_score INT(1),"
	. "review TEXT,"
	. "memo TEXT"
	.");";
	$pdo->query($sql);
	
	//sql実行
	function sql_execute($pdo,$sql,$user_id,$comic_id,$total_score,$content_score,$illust_score,$chara_score,$read_score,$original_score,$review,$memo){
		$sql = $pdo -> prepare($sql);
		$sql -> bindParam(':user_id', $user_id, PDO::PARAM_INT);
		$sql -> bindParam(':comic_id', $comic_id, PDO::PARAM_INT);
		$sql -> bindParam(':total_score', $total_score, PDO::PARAM_STR);
		$sql -> bindParam(':content_score', $content_score, PDO::PARAM_INT);
		$sql -> bindParam(':illust_score', $illust_score, PDO::PARAM_INT);
		$sql -> bindParam(':chara_score', $chara_score, PDO::PARAM_INT);
		$sql -> bindParam(':read_score', $read_score, PDO::PARAM_INT);
		$sql -> bindParam(':original_score', $original_score, PDO::PARAM_INT);
		$sql -> bindParam(':review', $review, PDO::PARAM_STR);
		$sql -> bindParam(':memo', $memo, PDO::PARAM_STR);
		$sql -> execute();
	}	
?>

<html>
	<head>
		<meta charset="utf-8">
		<style>
			.comic_info{
	 			padding: 10px 160px 50px; <!--height width 下-->
			}
		</style>
	</head>
	<body>
	<p style="text-align:center">
		○○○○~漫画管理&レビューサイト~
	</p>
	<p style="text-align: right">
		<a href="magamane_mypage.php" target="_self">マイページ</a>
		/ 
		<a href="magamane_main.php" target="_self">トップページ</a>
	</p>
	<hr>
<?php
	$sql = 'SELECT * FROM comics WHERE '.$_SERVER['QUERY_STRING'];
	$stmt = $pdo->query($sql);
	$comics_result = $stmt->fetch();
?>
	<div>
		<p style="float: left">
			<img src="<?php echo $comics_result['comic_image'];?>" width="140" height="200">
		</p>
		<br>
		<div class="comic_info">
			作品名：<a href="magamane_review.php?comic_id=<?php echo $comics_result['comic_id'];?>" target="_self"> <?php echo $comics_result['comic_name'];?> </a><br>
<?php
			echo "著者：{$comics_result['author']}<br>";
			echo "コミック誌：{$comics_result['magazine']}<br>";
			echo "発表年：{$comics_result['year']}<br>";
			echo "ジャンル：{$comics_result['genre']}<br>";
			if($comics_result['fin_frag']==0) echo "未完結<br>";
			else echo "完結済<br>";		
		
			//評価の更新処理
			if(isset($_POST["score"])){
				$content_score=NULL; $illust_score=NULL; $chara_score=NULL; $read_score=NULL; $original_score=NULL;
				if(isset($_POST["content_score"])) $content_score=$_POST["content_score"];
				if(isset($_POST["illust_score"])) $illust_score=$_POST["illust_score"];
				if(isset($_POST["chara_score"])) $chara_score=$_POST["chara_score"];
				if(isset($_POST["read_score"])) $read_score=$_POST["read_score"];
				if(isset($_POST["original_score"])) $original_score=$_POST["original_score"];
				
				//総合評価算出
				$rate_num=0;$total_score=0;
				if(isset($content_score)){$rate_num++; $total_score+=$content_score;}
				if(isset($illust_score)){$rate_num++; $total_score+=$illust_score;}
				if(isset($chara_score)){$rate_num++; $total_score+=$chara_score;}
				if(isset($read_score)){$rate_num++; $total_score+=$read_score;}
				if(isset($original_score)){$rate_num++; $total_score+=$original_score;}
				if($total_score!==0)$total_score=$total_score/$rate_num;
				
				if(isset($_POST["review"]))$review = htmlspecialchars($_POST["review"]);
				else $review=NULL;
				if(isset($_POST["memo"]))$memo = htmlspecialchars($_POST["memo"]);
				else $memo=NULL;
				
				//評価更新	*search.php,ranking.phpで空のタプルを予め挿入している
				$sql = 'UPDATE reviews SET user_id=:user_id ,comic_id=:comic_id ,total_score=:total_score, content_score=:content_score, illust_score=:illust_score, chara_score=:chara_score, read_score=:read_score, original_score=:original_score, review=:review, memo=:memo where user_id='.$_SESSION["ID"].' AND '.$_SERVER['QUERY_STRING'];
				sql_execute($pdo,$sql,$_SESSION["ID"],$comics_result['comic_id'],$total_score,$content_score,$illust_score,$chara_score,$read_score,$original_score,$review,$memo);
			}
?>	</div>
	</div>
	<hr>
<?php
	//評価表示
	$sql = 'SELECT * FROM reviews WHERE user_id='.$_SESSION["ID"].' AND '.$_SERVER['QUERY_STRING'];
	$stmt = $pdo->query($sql);
	$reviews_result = $stmt->fetch();
	echo "総合評価："; if(isset($reviews_result['total_score'])) echo $reviews_result['total_score'];
	echo "<br>";
	echo "内容評価："; if(isset($reviews_result['content_score'])) echo $reviews_result['content_score'];
	echo "<br>";
	echo "イラスト評価："; if(isset($reviews_result['illust_score'])) echo $reviews_result['illust_score'];
	echo "<br>";
	echo "キャラクター評価："; if(isset($reviews_result['chara_score'])) echo $reviews_result['chara_score'];
	echo "<br>";
	echo "読みやすさ評価："; if(isset($reviews_result['read_score'])) echo $reviews_result['read_score'];
	echo "<br>";
	echo "独創性評価："; if(isset($reviews_result['original_score'])) echo $reviews_result['original_score'];
	echo "<br>";
	echo "レビュー："; if(isset($reviews_result['review'])) echo $reviews_result['review'];
	echo "<br>";
	echo "メモ："; if(isset($reviews_result['memo'])) echo $reviews_result['memo'];
	echo "<br>";
	$pdo=null;
?>
	<hr>
	<!--評価更新フォーム-->
    <form action="magamane_myreview.php?<?php echo $_SERVER['QUERY_STRING'];?>" method="POST">
    	<fieldset>
            <legend>評価を更新して下さい</legend>
	        内容：<input type="radio" name="content_score" value="1">1
	        		   <input type="radio" name="content_score" value="2">2
	    		       <input type="radio" name="content_score" value="3">3
	        		   <input type="radio" name="content_score" value="4">4
	        		   <input type="radio" name="content_score" value="5">5<br>
	 イラスト：<input type="radio" name="illust_score" value="1">1
	        		   <input type="radio" name="illust_score" value="2">2
	    		       <input type="radio" name="illust_score" value="3">3
	        		   <input type="radio" name="illust_score" value="4">4
	        		   <input type="radio" name="illust_score" value="5">5<br>
	    キャラ：<input type="radio" name="chara_score" value="1">1
	        		   <input type="radio" name="chara_score" value="2">2
	    		       <input type="radio" name="chara_score" value="3">3
	        		   <input type="radio" name="chara_score" value="4">4
	        		   <input type="radio" name="chara_score" value="5">5<br>		   
 読みやすさ：<input type="radio" name="read_score" value="1">1
	        		   <input type="radio" name="read_score" value="2">2
	    		       <input type="radio" name="read_score" value="3">3
	        		   <input type="radio" name="read_score" value="4">4
	        		   <input type="radio" name="read_score" value="5">5<br>
	    独創性：<input type="radio" name="original_score" value="1">1
	        		   <input type="radio" name="original_score" value="2">2
	    		       <input type="radio" name="original_score" value="3">3
	        		   <input type="radio" name="original_score" value="4">4
	        		   <input type="radio" name="original_score" value="5">5<br>
	   レビュー：<textarea name="review" rows="10" cols="100"></textarea><br>
	   メモ：<textarea name="memo" rows="10" cols="100"></textarea><br>
	         <input type="submit" name="score" value="更新">
    	</fieldset>
	</form>
	<hr>
	</body>
</html>