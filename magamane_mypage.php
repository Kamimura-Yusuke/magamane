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

	//漫画の抽出機能 	*SESSIONを用いてるのは、編集モードを切り替えても(edit_modeをPOSTしても)、抽出状態を維持するため
	function extraction_judge($pdo){
		if(isset($_SESSION["EXTRACT"])||(isset($_POST["extraction"]))){
			if(isset($_POST["extraction"])) $_SESSION["EXTRACT"]=$_POST["extraction"];
		 	if($_SESSION["EXTRACT"]=="extract_fav"){
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND fav_flag=1';
			}else if($_SESSION["EXTRACT"]=="extract_was"){
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND read_index=1';
			}else if($_SESSION["EXTRACT"]=="extract_now"){
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND read_index=2';
			}else if($_SESSION["EXTRACT"]=="extract_will"){
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND read_index=3';
			}else if($_SESSION["EXTRACT"]=="extract_quit"){
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND read_index=4';
			}else{
				$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"];
			}
		}else $sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"];
		$stmt = $pdo->query($sql);
		$shelf_results = $stmt->fetchAll();
		return $shelf_results;
}
	
	//編集の内容(削除かお気に入り登録かなど)をチェックし、それに応じたsqlを返す
	function editmode_judge($comic_id){
		if(isset($_POST["delete"])){
			$sql = 'DELETE FROM shelves WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["favorite"])){
			$sql = 'UPDATE shelves SET fav_flag=true WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["favorite_delete"])){
			$sql = 'UPDATE shelves SET fav_flag=false WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["read_was"])){
			$sql = 'UPDATE shelves SET read_index=1 WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["read_now"])){
			$sql = 'UPDATE shelves SET read_index=2 WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["read_will"])){
			$sql = 'UPDATE shelves SET read_index=3 WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}else if(isset($_POST["read_quit"])){
			$sql = 'UPDATE shelves SET read_index=4 WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$_POST["comic_id{$comic_id}"];
		}
		return $sql;
	}
	
	//漫画の編集機能
	 if((isset($_POST["delete"]))||(isset($_POST["favorite"]))||(isset($_POST["favorite_delete"]))||(isset($_POST["read_was"]))||(isset($_POST["read_now"]))||(isset($_POST["read_will"]))||(isset($_POST["read_quit"]))){
		$sql = 'SELECT * FROM comics';
		$stmt = $pdo->query($sql);
		$comic_results = $stmt->fetchAll();
		foreach($comic_results as $comic_row){
			if(!isset($_POST["comic_id{$comic_row['comic_id']}"])) continue;
			$sql=editmode_judge($comic_row['comic_id']);
			$pdo->query($sql);
		}
	}
?>

<html>
	<head>
		<meta charset="utf-8">
		<title>マイページ~</title>
		<link rel="stylesheet" type="text/css" href="magamane_score_rate.css">
		<style>
			.base_image{
				display:inline-block;
				position:relative;
			}
			.rate {
				position: absolute;
				display: inline-block;
				width: 100px;
				height: 20px;
				font-size: 20px;
				left: 14%;
				top: 5px;
				z-index: 2;
			}
			.fav{
				position: absolute;
				width:40px;
		    	bottom:10px;
		    	left:10px;
			}
			.read_index{
				position: absolute;
				width:40px;
		    	bottom:10px;
		    	right:10px;
			}
			.user_info{
				top: 180px;
				float: left;
				border: solid 3px #C0C0C0;	
				position: fixed;
			}
			.left_form{
				top: 150px;
				position: fixed;
				z-index: 2;
			}
			.right_form{
				top: 150px;
		  		width: 98%;
		  		text-align: right;	
				position: fixed;
				z-index: 1;
			}
			.edit_form{
				top: 350px;
				float: left;
				position: fixed;
			}
			.contents{
				padding: 20px 220px 0;
			}
		</style>
	</head>
	<body>
	<p style="text-align:center">
		○○○○~漫画管理&レビューサイト~
	</p>
<?php 
	echo "{$_SESSION["NAME"]}さんの本棚";
?>
	<p style="text-align: right">
		<a href="magamane_main.php" target="_self">トップページ</a>
		/ 
		<a href="magamane_logout.php" target="_self">ログアウト</a>
	</p>
	<hr>
<?php
	//編集モード切り替え(true→ON, false→OFF)
	if(!isset($_POST["edit_mode"]))$edit_mode=false;
	else if($_POST["edit_mode"]==true)$edit_mode=false;
	else if($_POST["edit_mode"]==false)$edit_mode=true;
?>
	<div class="left_form">
		<form action="" method="POST">
			<input type="hidden" name="edit_mode" value="<?php echo $edit_mode;?>" >
			<input type="submit" value="編集モード">
		</form>
	</div>
	
	<div class="right_form">
		<form action="" method="POST">
			<select name="extraction">
    		   <option value="extract_all">全て</option>
    		   <option value="extract_fav">お気に入り</option>
    		   <option value="extract_was">読み終えた</option>
    		   <option value="extract_now">読んでいる</option>
    		   <option value="extract_will">読みたい</option>
    		   <option value="extract_quit">読むのを止めた</option>
        	</select>
			<input type="submit" value="抽出">
		</form>
			
		<form action="" method="POST">
			<select name="sort">
    		   <option value="sort_signup">登録順</option>
    		   <option value="sort_aiueo">カナ順</option>
    		   <option value="sort_totalscore">総合評価順</option>
        	</select>
			<input type="submit" value="ソート">
		</form>
	</div>
	
	<div class="user_info">
<?php
		//ユーザー情報
		require "magamane_userinfo_extraction.php";
		userinfo_extraction($pdo);
?>
	</div>
	
	<div class="contents">
<?php
		//抽出
		$shelf_results = extraction_judge($pdo);
		if(!isset($_SESSION["EXTRACT"])) $_SESSION["EXTRACT"]="extract_all";
		if($_SESSION["EXTRACT"]!=="extract_all"){
			if($_SESSION["EXTRACT"]=="extract_fav") echo "「お気に入り」を抽出中&nbsp&nbsp";
			else if($_SESSION["EXTRACT"]=="extract_was") echo "「読み終えた」を抽出中&nbsp&nbsp";
			else if($_SESSION["EXTRACT"]=="extract_now") echo "「読んでいる」を抽出中&nbsp&nbsp";
			else if($_SESSION["EXTRACT"]=="extract_will") echo "「読みたい」を抽出中&nbsp&nbsp";
			else if($_SESSION["EXTRACT"]=="extract_quit") echo "「読むのを止めた」を抽出中&nbsp&nbsp";
		}
		
		//ソート
		if(!isset($_SESSION["SORT"])) $_SESSION["SORT"]="sort_signup";
		if(isset($_POST["sort"])) $_SESSION["SORT"]=$_POST["sort"];
		if($_SESSION["SORT"]!=="sort_signup"){
			$comics_or_reviews_arr=array();
			$shelves_num=count($shelf_results);
			for($i=0;$i<$shelves_num;$i++){
				if($_SESSION["SORT"]=="sort_aiueo") $sql='SELECT * FROM comics WHERE comic_id='.$shelf_results[$i]['comic_id'];
				else $sql='SELECT * FROM reviews WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$shelf_results[$i]['comic_id'];
				$stmt = $pdo->query($sql);
				$comics_or_reviews_arr[$i] = $stmt->fetch();  
			}
			$merge_arr=array_replace_recursive($shelf_results, $comics_or_reviews_arr);
			if($_SESSION["SORT"]=="sort_aiueo") array_multisort(array_column($merge_arr, 'comic_ruby'), SORT_ASC, $merge_arr);
			else array_multisort(array_column($merge_arr, 'total_score'), SORT_DESC, $merge_arr);
			$shelf_results =array();
			for($i=0;$i<$shelves_num;$i++){
				$shelf_results[$i]['comic_id']=$merge_arr[$i]['comic_id'];
				$shelf_results[$i]['read_index']=$merge_arr[$i]['read_index'];
				$shelf_results[$i]['fav_flag']=$merge_arr[$i]['fav_flag'];
			}
		}
		if($_SESSION["SORT"]=="sort_signup") echo "並び：登録順"."<br>";
		else if($_SESSION["SORT"]=="sort_aiueo") echo "並び：名前順"."<br>";
		else if($_SESSION["SORT"]=="sort_totalscore") echo "並び：総合評価順"."<br>";
		
		foreach($shelf_results as $shelf_row){
			if($edit_mode==true){
?>			<form action="" method="POST">
					<input type="checkbox" name="comic_id<?php echo $shelf_row['comic_id'];?>" value="<?php echo $shelf_row['comic_id'];?>">
<?php	}

			$sql = 'SELECT * FROM comics WHERE comic_id='.$shelf_row['comic_id'];
			$stmt = $pdo->query($sql);
			$comics_result = $stmt->fetch();
			$sql = 'SELECT * FROM reviews WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$shelf_row['comic_id'];
			$stmt = $pdo->query($sql);
			$reviews_result = $stmt->fetch(); 
?>
			<div class="base_image">
<?php		if($edit_mode==true){
?>				<img src="<?php echo $comics_result['comic_image'];?>" width="140" height="200">
<?php		}else{
?>				<a href="magamane_myreview.php?comic_id=<?php echo $shelf_row['comic_id'];?>" target="_self">
						<img src="<?php echo $comics_result['comic_image'];?>" width="140" height="200">
					</a>
<?php		}

				//スコアレートの表示
				for($i=0;$i<5;$i++){
					for($j=0;$j<10;$j++){
						$score_index=$i+$j/10;
						if(($score_index<=$reviews_result['total_score'])&&($reviews_result['total_score']<($score_index+0.1))){
?>						<span class="rate rate<?php echo $i.'-'.$j;?>"></span>
<?php					break;
						}
					}
				}
				
				if($shelf_row['read_index']==1){
?>				<img src="comic_image_directory/was.png" class="read_index">
<?php		}else if($shelf_row['read_index']==2){
?>				<img src="comic_image_directory/now.png" class="read_index">
<?php		}else if($shelf_row['read_index']==3){
?>				<img src="comic_image_directory/will.png" class="read_index">
<?php		}else if($shelf_row['read_index']==4){
?>				<img src="comic_image_directory/quit.png" class="read_index">
<?php		}
				if($shelf_row['fav_flag']==true){
?>				<img src="comic_image_directory/fav.png" class="fav">
<?php		}
?>		</div>
<?php
		}
?>	</div>

<?php
	if($edit_mode==true){
?>
	<br>
	<div class="edit_form">
		<input type="submit" name="delete" value="削除">
		<input type="submit" name="favorite" value="お気に入り登録"><br>
		<input type="submit" name="favorite_delete" value="お気に入り解除">
		<input type="submit" name="read_was" value="読み終えた"><br>
		<input type="submit" name="read_now" value="読んでいる">
		<input type="submit" name="read_will" value="読みたい"><br>
		<input type="submit" name="read_quit" value="読むのを止めた">
	</form>
	</div>
<?php
	}
?>
</body>
</html>