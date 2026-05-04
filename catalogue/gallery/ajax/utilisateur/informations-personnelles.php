<?php
	require("../../../includes/configuration.php");
	require("../../../includes/functions.php");

	if(isset($_GET['action'])) {
		if($_GET['action'] == "informations") {
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
									$modifUser->bindParam(":entreprise", @$_POST['entreprise']);
									$modifUser->execute();

									$message = "Vos informations personnelles ont bien été mises à jour.";
			 						$couleur = "vert";
								}
		 					}
		 				}
		 			}
		 		}  
		 	} else {
		 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
		 		$couleur = "rouge";
		 	}
		} elseif($_GET['action'] == "identifiants_pwd") {
			if(isset($_POST['actualpwd']) && isset($_POST['pwd']) && isset($_POST['repwd'])) {
		 		if(empty($_POST['actualpwd']) || empty($_POST['pwd']) || empty($_POST['repwd'])) {
		 			$message = "Veuillez remplir les champs vides.";
		 			$couleur = "rouge";
		 		} else {
					if(password_verify($_POST['actualpwd'], $u->mdp)) {
						if($_POST['pwd'] != $_POST['repwd']) {
							$message = "Les mots de passes saisis ne correspondent pas."; 
		 					$couleur = "rouge";
						} else {
							if(strlen($_POST['pwd']) <= 6) {
								$message = "Le mot de passe doit contenir au moins 6 caractères."; 
		 						$couleur = "rouge";
							} else {
								$updatePassword = $bdd->prepare("UPDATE ob_users SET mdp = :password WHERE id = '".$u->id."'");
								$updatePassword->bindParam(":password", password_hash($_POST['pwd'], PASSWORD_DEFAULT));
								$updatePassword->execute();
								$message = "Le mot de passe a bien été modifié."; 
		 						$couleur = "vert";
							}
						}
					} else {
						$message = "Le mot de passe actuel est invalide."; 
		 				$couleur = "rouge";
					}
		 		}  
		 	} else {
		 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
		 		$couleur = "rouge";
		 	}
		} elseif($_GET['action'] == "identifiants_mail") {
			if(isset($_POST['email']) && isset($_POST['reemail'])) {
		 		if(empty($_POST['email']) || empty($_POST['reemail'])) {
		 			$message = "Veuillez remplir les champs vides.";
		 			$couleur = "rouge";
		 		} else {
					if($_POST['email'] != $_POST['reemail']) {
						$message = "Les adresses emails saisies ne correspondent pas."; 
	 					$couleur = "rouge";
					} else {
						if(!preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $_POST['email'])) {
			 				$message = "Veuillez entrer une adresse email valide.";
			 				$couleur = "rouge";
			 			} else {
							$verif = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
		 					$verif->bindParam(":email", $_POST['email']);
		 					$verif->execute();
		 					if($verif->rowCount() > 0) {
		 						$message = "Cette adresse email est déjà utilisée pour un autre compte.";
			 					$couleur = "rouge";
		 					} else {
								$updateEmail = $bdd->prepare("UPDATE ob_users SET email = :email WHERE id = '".$u->id."'");
								$updateEmail->bindParam(":email", $_POST['email']);
								$updateEmail->execute();
								$message = "L'adresse email a bien été modifiée par : ".$u->email; 
		 						$couleur = "vert";
		 					}
						}
					}
		 		}  
		 	} else {
		 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
		 		$couleur = "rouge";
		 	}
		} else {
		 	$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
		 	$couleur = "rouge";
		}
	} else {
	 	$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
	 	$couleur = "rouge";
	 }
 	echo json_encode(array('message' => @$message, 'couleur' => @$couleur, 'redirect' => @$redirect));
?>