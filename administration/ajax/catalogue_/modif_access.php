<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id']) && isset($_POST['paiement'])) {
		if($_POST['paiement'] != 0 && $_POST['paiement'] != 1 && $_POST['paiement'] != 2) {
			$message = "Une erreur est survenue, veuillez réessayer plus tard.";
			$couleur = "rouge";
		} else {
			// HIDE
			if(@$_POST['access'] == "on") {
				$access = 1;
			} else {
				$access = 0;
			}

			$utilisateur = $bdd->prepare("SELECT * FROM ob_users WHERE id = :id");
			$utilisateur->bindParam(":id", htmlentities($_POST['id']));
			$utilisateur->execute();
			if($utilisateur->rowCount() > 0) {
				$modifUtilisateur = $bdd->prepare("UPDATE ob_users SET paiement_default = '".$_POST['paiement']."', catalogue = '".$access."', numero_client = :numero_client, categorie = :categorie WHERE id = :id");
				$modifUtilisateur->bindParam(":id", htmlentities($_POST['id']));
				$modifUtilisateur->bindParam(":numero_client", htmlentities($_POST['numero_client']));
				$modifUtilisateur->bindParam(":categorie", htmlentities($_POST['categorie']));
				$modifUtilisateur->execute();

				$message = "L'utilisateur a bien été modifié.";
				$couleur = "vert";
				$redirect = $admurl."/catalogue_access.php";
			} else {
				$message = "Une erreur est survenue, veuillez réessayer plus tard.";
				$couleur = "rouge";
			}
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