<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	if(isset($_SESSION['site'])) {
	 	if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['phone']) && isset($_POST['entreprise'])) {
	 		if(empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['phone'])) {
	 			$message = "Veuillez remplir les champs vides.";
	 			$couleur = "rouge";
	 		} else {
				$filter = preg_replace("/[^a-z \s -à]/i", "", $_POST['nom']);
				if($filter !== $_POST['nom']) {
					$message = "Veuillez entrer un nom valide.";
					$couleur = "rouge";
				} else {
	 				$filter = preg_replace("/[^a-z \s -à]/i", "", $_POST['prenom']);
		 			if($filter !== $_POST['prenom']) {
		 				$message = "Veuillez entrer un prénom valide.";
		 				$couleur = "rouge";
		 			} else {
		 				$filter = preg_replace("/[^a-z \s -à]/i", "", $_POST['entreprise']);
			 			if(($filter !== $_POST['entreprise']) && !empty($_POST['entreprise'])) {
			 				$message = "Veuillez entrer un nom d'entreprise valide.";
			 				$couleur = "rouge";
			 			} else {
		 					if(!preg_match("#\+[0-9]{2}[0-9 \s]#", $_POST['phone'])) {
		 						$message = "Veuillez entrer un numéro de téléphone valide.";
		 						$couleur = "rouge";
		 					} else {	
					 			### MODIFICATION DU COMPTE UTILISATEUR
					 			$modifUser = $bdd->prepare("UPDATE ob_users SET nom = :nom, prenom = :prenom, phone = :phone, entreprise = :entreprise WHERE id = '".$u->id."'");
								$modifUser->bindParam(":nom", $_POST['nom']);
								$modifUser->bindParam(":prenom", $_POST['prenom']);
								$modifUser->bindParam(":phone", $_POST['phone']);
								$modifUser->bindParam(":entreprise", $_POST['entreprise']);
								$modifUser->execute();

								$redirect = $url."/commande/etape/2";
							}
	 					}
	 				}
	 			}
	 		} 
	 	} else {
	 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
	 		$couleur = "rouge";
	 	}
 	} else {
		$redirect = $url."/connexion/redirection/commande";
	}
 	echo json_encode(array('message' => @$message, 'couleur' => @$couleur, 'redirect' => @$redirect));
?>