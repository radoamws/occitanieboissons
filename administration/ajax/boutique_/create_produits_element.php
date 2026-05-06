<?php
	require("../../includes/configuration.php");

	if(isset($_POST['nom']) && isset($_POST['descr']) && isset($_POST['contenance']) && isset($_POST['stock']) && isset($_POST['prix']) && isset($_POST['tva']) && isset($_POST['brasserie']) && isset($_POST['alcool'])) {
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

  			// CREATION DE L'ELEMENT
			$create_element = $bdd->prepare("INSERT INTO ob_boutique_produit_element (nom, description, contenance, stock, prixht, tva, brasserie, time, auteur, alcool, hide) VALUES (:nom, :descr, :contenance, :stock, :prix, :tva, :brasserie, '".time()."', '".$_SESSION['username']."', :alcool, :hide)");
			$create_element->bindParam(":nom", $_POST['nom']);
			$create_element->bindParam(":descr", $_POST['descr']);
			$create_element->bindParam(":contenance", $_POST['contenance']);
			$create_element->bindParam(":stock", $_POST['stock']);
			$create_element->bindParam(":prix", str_replace(',', '.', $_POST['prix']));
			$create_element->bindParam(":tva", $_POST['tva']);
			$create_element->bindParam(":brasserie", $_POST['brasserie']);
			$create_element->bindParam(":alcool", str_replace(',', '.', $_POST['alcool']));
			$create_element->bindParam(":hide", $hide);
			$create_element->execute();

			$message = "L'élément a bien été crée.";
			$couleur = "vert";
			$redirect = $admurl."/boutique_element.php";		
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