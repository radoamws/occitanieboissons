<?php
	require("../../includes/configuration.php");

	$data_pays = array();
	foreach($pays_brasseries as $nom => $d) {
		$data_pays[] = array("id" => $nom, "text" => $nom);
	}

	echo json_encode(array("pays" => $data_pays));
?>