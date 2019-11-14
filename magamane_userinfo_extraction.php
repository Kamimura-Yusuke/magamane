<?php
	function userinfo_extraction($pdo){
		$sql='SELECT * FROM shelves WHERE user_id='.$_SESSION["ID"];
		$stmt=$pdo->query($sql);
		$shelf_results = $stmt->fetchAll();
		$comic_num=count($shelf_results);
		$was_num=0; $now_num=0; $will_num=0; $quit_num=0; $fav_num=0;
		foreach($shelf_results as $shelf_row){
			if($shelf_row['read_index']==1) $was_num++;
			else if($shelf_row['read_index']==2) $now_num++;
			else if($shelf_row['read_index']==3) $will_num++;
			else if($shelf_row['read_index']==4) $quit_num++;
			if($shelf_row['fav_flag']==1) $fav_num++;			
		}
		echo "・漫画登録数：{$comic_num}"."<br>";
		echo "&nbsp読み終えた数：{$was_num}"."<br>";
		echo "&nbsp読んでいる数：{$now_num}"."<br>";
		echo "&nbsp読みたい数：{$will_num}"."<br>";
		echo "&nbsp読むのを止めた数：{$quit_num}"."<br>";
		echo "&nbspお気に入り数：{$fav_num}"."<br>";
	}
?>