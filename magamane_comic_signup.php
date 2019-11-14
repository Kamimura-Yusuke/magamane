<?php
	function comic_to_shelf($pdo,$comic_name,$post_comicid){
		//漫画登録処理
		$sql = "INSERT INTO shelves (user_id, comic_id, read_index, fav_flag) VALUES ({$_SESSION["ID"]}, {$post_comicid}, NULL, FALSE)";
		$pdo->query($sql);
		
		//登録時点で空のタプルを追加(本棚におけるソート機能の改善)
		$sql = 'SELECT * FROM reviews WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$post_comicid;
		$stmt = $pdo->query($sql);
		$r_exsist_confirm = $stmt->fetch();
		if($r_exsist_confirm==false){
			$sql = "INSERT INTO reviews (user_id, comic_id, total_score, content_score, illust_score, chara_score, read_score, original_score, review, memo) VALUES ({$_SESSION["ID"]}, {$post_comicid}, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
			$pdo->query($sql);
		}		
		echo "「{$comic_name}」を追加しました"."<br>";
	}
	
	//comic_idが本棚に登録されているマンガかチェック
	function check_shelf($pdo,$comic_id){
		$exist_flag=false;
		if(isset($_SESSION["NAME"])){
		 	$sql = 'SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"].' AND comic_id='.$comic_id;
		 	$stmt = $pdo->query($sql);
			$shelf_result = $stmt->fetch();
			if($shelf_result!==false)$exist_flag=true;
		}
		return $exist_flag;
	}
?>