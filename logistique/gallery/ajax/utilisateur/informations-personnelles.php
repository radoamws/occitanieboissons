<?php
	require("../../../includes/configuration.php");
	require("../../../includes/functions.php");

	if(!isset($_SESSION['logistique'])) {
		echo json_encode(array('redirect' => $url.'/connexion/'));
		exit();
	}

	if(isset($_GET['action'])) {
		if($_GET['action'] == "identifiants_pwd") {
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
							if(strlen($_POST['pwd']) < 7) {
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
		 			$message = "Attention, tu n'as pas remplis tous les champs.";
		 			$couleur = "rouge";
		 		} else {
					if($_POST['email'] != $_POST['reemail']) {
						$message = "Les adresses emails saisies ne correspondent pas."; 
	 					$couleur = "rouge";
					} else {
						if(!preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $_POST['email'])) {
			 				$message = "Le format de l'adresse email est invalide.";
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
								$_SESSION['logistique'] = $_POST['email'];
								$message = "L'adresse email a bien été modifiée pour : ".$u->email; 
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