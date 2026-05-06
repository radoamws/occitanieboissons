<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id']) && isset($_POST['nom']) && isset($_POST['descr']) && isset($_POST['contenance']) && isset($_POST['stock']) && isset($_POST['prix']) && isset($_POST['tva']) && isset($_POST['brasserie']) && isset($_POST['alcool'])) {	
		if(empty($_POST['nom']) || empty($_POST['contenance']) || empty($_POST['stock']) || empty($_POST['prix']) || empty($_POST['tva']) || empty($_POST['alcool'])) {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		} else {
			// HIDE
			if(@$_POST['hide'] == "on") {
				$hide = 1;
			} else {
				$hide = 0;
			}

			$modif_element = $bdd->prepare("UPDATE ob_boutique_produit_element SET nom = :nom, description = :descr, contenance = :contenance, stock = :stock, prixht = :prix, brasserie = :brasserie, alcool = :alcool, tva = :tva, hide = '".$hide."' WHERE id = :id");
			$modif_element->bindParam(":nom", $_POST['nom']);
			$modif_element->bindParam(":descr", $_POST['descr']);
			$modif_element->bindParam(":contenance", $_POST['contenance']);
			$modif_element->bindParam(":stock", $_POST['stock']);
			$modif_element->bindParam(":prix", str_replace(',', '.', $_POST['prix']));
			$modif_element->bindParam(":tva", $_POST['tva']);
			$modif_element->bindParam(":brasserie", $_POST['brasserie']);
			$modif_element->bindParam(":alcool", str_replace(',', '.', $_POST['alcool']));
			$modif_element->bindParam(":id", $_POST['id']);
			$modif_element->execute();

			$message = "L'élément a bien été modifié.";
			$couleur = "vert";
			$redirect = $admurl."/boutique.php";	
		}
	} else {
		$message = "Une erreur est survenue, veuillez réessayer plus tard.";
		$couleur = "rouge";
	}

	echo json_encode(array(
		'message' => $message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>