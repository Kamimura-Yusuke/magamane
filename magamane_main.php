<?php
	session_start();

	require "magamane_dbConnect.php";
	$pdo=db_connect();
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>○○○○~漫画管理&レビューサイト~</title>
		<style>
			.ranking_genre{
				min-height:100%;
				float: left;
				text-align: left;
				border-right: solid 3px #C0C0C0;	
			}
		</style>
	</head>
	<body>
	<p style="text-align:center">
		○○○○~漫画管理&レビューサイト~
	</p>
<?php 
	if(!isset($_SESSION["NAME"])){
?>	<p style="text-align: right">
			<a href="magamane_presignup.php" target="_self">新規登録</a>
			/
			<a href="magamane_login.php" target="_self">ログイン</a><br>
		</p>
<?php
	}else {
			echo "ようこそ！{$_SESSION["NAME"]}さん！"
?>	<p style="text-align: right">
			<a href="magamane_mypage.php" target="_self">マイページ</a>
			/ 
			<a href="magamane_logout.php" target="_self">ログアウト</a>
<?php
	}
	if((isset($_SESSION["NAME"]))&&($_SESSION["NAME"]=="管理者")){
?>		/<a href="magamane_manager.php" target="_self">管理者ページ</a>
		</p>
<?php
	}
?>
	<hr>
<?php	
	require "magamane_head_output.php";
	ranking_list();
?>
	<!--検索欄-->
	<form action="magamane_search.php" method="GET">
		<p style="text-align: center">
			<input type="text" name="contents_search" size="50" placeholder="作品名、作者名、またはその一部を入力" required>
			<input type="submit" value="検索">
		</p>
	</form>

	<!--ユーザー情報出力-->
	<div style="float: right;">
		<fieldset>
	    	<legend>ユーザー情報</legend>
<?php 
			if(isset($_SESSION["NAME"])){
				require "magamane_userinfo_extraction.php";
				userinfo_extraction($pdo);
			}else{
				echo "ログインすればできること"."<br>";
				echo "・漫画管理"."<br>";
				echo "・レビュー投稿"."<br>";
				echo "登録所要時間30秒!!登録でき次第すぐに使えます!!"."<br>";
			}
?>
        </fieldset>
    </div>

	<!--データベース情報出力-->
	<div style="float: right;">
		<fieldset>
	    	<legend>データベース情報</legend>
<?php
			$sql='SELECT COUNT(*) FROM users';
			$stmt=$pdo->query($sql);
			$user_num = $stmt->fetchColumn();
			$sql='SELECT COUNT(*) FROM comics';
			$stmt=$pdo->query($sql);
			$comic_num = $stmt->fetchColumn();
			echo "ユーザー数：{$user_num}"."<br>";
			echo "コミック数：{$comic_num}"."<br>";
?>
		</fieldset>
	</div>

	<!--総合ランキング-->
    <div>
        総合ランキングBEST10<br>
<?php
		$sql='SELECT * FROM comics';
		require "magamane_scoresort.php";
		list($score_arr,$comicid_arr)=score_sort($pdo,$sql);
		
		for($i=0;$i<10;$i++){
			$sql = 'SELECT * FROM comics WHERE comic_id='.$comicid_arr[$i];
			$stmt = $pdo->query($sql);
			$comic_result = $stmt->fetch();
			echo $i+1;
?>
			<a href="magamane_review.php?comic_id=<?php echo $comic_result['comic_id'];?>" target="_self">
		 		<?php echo $comic_result['comic_name'];?>
		 	</a>
<?php		
			echo '&nbsp総合点：'.number_format($score_arr[$i][0],2,null,'')."<br>";
		}
?>        
		<br>
	</div>
        
    <!--ピックアップ漫画-->
    <div>
<?php
		$sql='SELECT * FROM comics';
		$stmt=$pdo->query($sql);
		$comic_results = $stmt->fetchAll();
		$comic_num=count($comic_results);
		$comic_arr=array(0,0,0,0);
		
		//重複無しで乱数生成
		for($i=0;$i<4;$i++){
			while(true){
				$rand_num=mt_rand(1,$comic_num);
				$check_flag=false;
				for($j=0;$j<$i;$j++){
					if($rand_num==$comic_arr[$j]) $check_flag=true;
				}
				if($check_flag==false){
					$comic_arr[$i]=$rand_num;
					break;
				}
			}
		}
		
		$sql='SELECT * FROM comics WHERE comic_id='.$comic_arr[0].' OR comic_id='.$comic_arr[1].' OR comic_id='.$comic_arr[2].' OR comic_id='.$comic_arr[3].' ORDER BY RAND()';
		$stmt=$pdo->query($sql);
		$comic_results = $stmt->fetchAll();
		echo "ピックアップ漫画"."<br>";
		foreach($comic_results as $comic_row){
?>
		 	<a href="magamane_review.php?comic_id=<?php echo $comic_row['comic_id'];?>" target="_self">
		 		<img src="<?php echo $comic_row['comic_image'];?>" width="140" height="200">
		 	</a>
<?php
		}
?>
    </div>
    </body>
</html>