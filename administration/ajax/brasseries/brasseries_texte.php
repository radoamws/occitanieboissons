<?php
	require("../../includes/configuration.php");

	if(isset($_POST['brasseries'])) {
		$all_brasseries = array();
		$brasseries = explode(",", $_POST['brasseries']);
		$motcle = $bdd->query("SELECT * FROM ob_motsclefs");
		while($m = $motcle->fetch(PDO::FETCH_OBJ)) {
			foreach($brasseries as $b) {
				if($b == $m->id) $all_brasseries[] = "<a style='text-decoration: none;' href='#'>".$m->motclef."</a>";
			}
		}
		echo implode(" ", $all_brasseries);
	} elseif(isset($_POST['brasseriesid'])) {
		$all_brasseries = array();
		$brasseries = explode(",", $_POST['brasseries']);
		$motcle = $bdd->query("SELECT * FROM ob_motsclefs");
		while($m = $motcle->fetch(PDO::FETCH_OBJ)) {
			foreach($brasseries as $b) {
				if($b == $m->id) $all_brasseries[] = "<a style='text-decoration: none;' href='#'>".$m->motclef."</a>";
			}
		}
		echo implode(" ", $all_brasseries);
	}
?>