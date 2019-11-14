<?php
	function score_sort($pdo,$sql){
		$stmt=$pdo->query($sql);
		$comic_results = $stmt->fetchAll();
		$score_arr=array();
		$comicid_arr=array();
		$arr_count=0;
		foreach($comic_results as $comic_row){
			$sql = 'SELECT AVG(total_score) FROM reviews WHERE comic_id='.$comic_row['comic_id'];
			$stmt = $pdo->query($sql);
			$score_arr[$arr_count] = $stmt->fetch();
			$comicid_arr[$arr_count]=$comic_row['comic_id'];
			$arr_count++;
		}
		array_multisort($score_arr, SORT_DESC, $comicid_arr);
		return [$score_arr,$comicid_arr];
	}
?>