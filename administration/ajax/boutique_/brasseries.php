<?php
	require("../../includes/configuration.php");

	$brasseries = $bdd->query("SELECT * FROM ob_brasseries ORDER BY nom");
	$data_brasseries = array();
	while($b = $brasseries->fetch(PDO::FETCH_OBJ)) {
		$data_brasseries[] = array("id" => $b->id, "text" => $b->nom);
	}

	echo json_encode(array("brasseries" => $data_brasseries));
?>