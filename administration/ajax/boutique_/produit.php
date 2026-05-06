<?php
	require("../../includes/configuration.php");

	$produit = $bdd->query("SELECT * FROM ob_boutique_produit");
	$data_produit = array();
	while($p = $produit->fetch(PDO::FETCH_OBJ)) {
		$data_produit[] = array("id" => $p->id, "text" => $p->nom);
	}

	echo json_encode(array("produit" => $data_produit));
?>