<?php 
 	require("../../includes/configuration.php");

  	if(isset($_POST['id']) && isset($_POST['type']) && isset($_POST['nom'])) {
		if(empty($_POST['nom'])) {
		 	$message = "Veuillez remplir les champs vides."; 
 			$couleur = "rouge";
		} else {
			if($_POST['type'] == "nom") {
				$modifNom = $bdd->prepare("UPDATE ob_catalogue_produits SET nom = :nom WHERE id = :id");
				$modifNom->bindParam(":nom", htmlentities($_POST['nom']));
				$modifNom->bindParam(":id", htmlentities($_POST['id']));
				$modifNom->execute();

				$message = "Le libellé du produit a bien été modifié."; 
 				$couleur = "vert";
			} elseif($_POST['type'] == "sup") {
				$modifNom = $bdd->prepare("UPDATE ob_catalogue_produits SET nom_sup = :nom WHERE id = :id");
				$modifNom->bindParam(":nom", htmlentities($_POST['nom']));
				$modifNom->bindParam(":id", htmlentities($_POST['id']));
				$modifNom->execute();

				$message = "Le libellé complémentaire du produit a bien été modifié."; 
 				$couleur = "vert";
			} else {
				$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 				$couleur = "rouge";
			}
		}
  	} else {
  		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 		$couleur = "rouge";
  	}

 	echo json_encode(array('message' => $message, 'couleur' => $couleur, 'redirect' => @$redirect));
?>