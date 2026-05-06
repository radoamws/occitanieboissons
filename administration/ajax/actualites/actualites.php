<?php
	require("../../includes/configuration.php");

	$tags = $bdd->query("SELECT * FROM ob_motsclefs ORDER BY motclef");
	$data_tags = array();
	while($t = $tags->fetch(PDO::FETCH_OBJ)) {
		$data_tags[] = array("id" => $t->id, "text" => $t->motclef);
	}

	$brasseries = $bdd->query("SELECT * FROM ob_motsclefs WHERE brasserie = '1' ORDER BY motclef");
	$data_brasseries = array();
	while($b = $brasseries->fetch(PDO::FETCH_OBJ)) {
		$data_brasseries[] = array("id" => $b->id, "text" => $b->motclef);
	}

	echo json_encode(array("tags" => $data_tags, "brasseries" => $data_brasseries));
?>