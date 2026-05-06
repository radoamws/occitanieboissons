<?php
	require("../../includes/configuration.php");

	$element = $bdd->query("SELECT * FROM ob_boutique_produit_element");
	$data_element = array();
	while($e = $element->fetch(PDO::FETCH_OBJ)) {
		$data_element[] = array("id" => $e->id, "text" => $e->nom);
	}

	echo json_encode(array("element" => $data_element));
?>