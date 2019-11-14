<?php
	function head_output(){
?>	<p style="text-align:center">
			○○○○~漫画管理&レビューサイト~
		</p>
<?php
	}
	
	function head_output2(){
		if(!isset($_SESSION["NAME"])){
?>		<p style="text-align: right">
				<a href="magamane_main.php" target="_self">トップページ</a>
				/
				<a href="magamane_login.php" target="_self">ログイン</a><br>
			</p>
<?php
		}else{
?>		<p style="text-align: right">
				<a href="magamane_mypage.php" target="_self">マイページ</a>
				/ 
				<a href="magamane_main.php" target="_self">トップページ</a>
			</p>
<?php
		}
?>	<hr>
<?php
	}
	
	function ranking_list(){
?>
		<div class="ranking_genre">
			ランキング<br>
			・<a href="magamane_ranking.php?total_score" target="_self">総合</a><br>
			ジャンル<br>
			・<a href="magamane_ranking.php?genre=fantasy" target="_self">ファンタジー</a><br>
			・<a href="magamane_ranking.php?genre=love" target="_self">ラブコメディー</a><br>
			・<a href="magamane_ranking.php?genre=comedy" target="_self">ギャグ・コメディー</a><br>
			・<a href="magamane_ranking.php?genre=sf" target="_self">SF</a><br>
			・<a href="magamane_ranking.php?genre=mystery" target="_self">サスペンス・ミステリー</a><br>
			・<a href="magamane_ranking.php?genre=sport" target="_self">スポーツ</a><br>	
			・<a href="magamane_ranking.php?genre=history" target="_self">歴史</a><br>
		</div>
<?php
	}
?>