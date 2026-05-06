<?php
	require("../../includes/configuration.php");

    if(isset($_POST['id_commande']) && isset($_POST['optionmoved'])) {
    	if($_POST['optionmoved'] > 2) {
			$message = "Une erreur est survenue, veuillez réessayer plus tard.";
			$couleur = "rouge";
		} else {
			$commandeExist = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id_commande");
			$commandeExist->bindParam(":id_commande", $_POST['id_commande']);
			$commandeExist->execute();
			if($commandeExist->rowCount() > 0) {
				$commandeDeplace = $bdd->prepare("UPDATE ob_users_commande SET statut = :optionmoved WHERE id = :id_commande");
				$commandeDeplace->bindParam(":id_commande", $_POST['id_commande']);
				$commandeDeplace->bindParam(":optionmoved", $_POST['optionmoved']);
				$commandeDeplace->execute();

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

	echo json_encode(array(
		'message' => @$message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>