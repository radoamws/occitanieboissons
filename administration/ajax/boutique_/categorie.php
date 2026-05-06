<?php
	require("../../includes/configuration.php");

	$categorie = $bdd->query("SELECT * FROM ob_boutique_categorie");
	$data_categorie = array();
	while($t = $categorie->fetch(PDO::FETCH_OBJ)) {
		$data_categorie[] = array("id" => $t->id, "text" => $t->nom);
	}

	echo json_encode(array("categorie" => $data_categorie));
?>