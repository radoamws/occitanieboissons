<?php 
 	require("../../../includes/configuration.php");
  
  	$ip = $_SERVER['REMOTE_ADDR'];

  	if(isset($_POST['email']) && isset($_POST['mdp'])) {
		if(empty($_POST['email']) || empty($_POST['mdp'])) {
		 	$message = "Veuillez remplir les champs vides."; 
 			$couleur = "rouge";
		} else {
			$verif = $bdd->prepare("SELECT mdp,prenom,id,acces_logistique FROM ob_users WHERE email = :email");
			$verif->bindParam(":email", $_POST['email']);
			$verif->execute();
			$v = $verif->fetch(PDO::FETCH_OBJ); 
		  	if($verif->rowCount() < 1) {
				$message = "Cette adresse email ne correspond à aucun compte."; 
 				$couleur = "rouge";
		  	} else {
	  			if(!password_verify($_POST['mdp'], $v->mdp)) {
					$message = "Oops ! Le mot de passe est incorect."; 
					$couleur = "rouge";
	  			} else {
	  				if($v->acces_logistique == 0) {
	  					$message = "Vous n'avez pas les autorisations requises."; 
						$couleur = "rouge";
	  				} else {
		  				// MODIFICATION DU PANIER
		  				if(isset($_POST['redirect'])) {
		  					$redirect = $url."/".$_POST['redirect'];
		  				} else {
		  					$redirect = $url."/accueil/";
		  				}
		  				// IP DERNIER CONNEXION
		  				$modifIp = $bdd->query("UPDATE ob_users SET ip_lastconnexion = '".$ip."' AND time_lastconnexion = '".time()."' WHERE id = '".$v->id."'");

						$_SESSION['logistique'] = $_POST['email'];
						$message = "Content de te revoir ".$v->prenom." !"; 
						$couleur = "vert";
					}
			    }
			}
		}
  	} else {
  		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 		$couleur = "rouge";
  	}

 	echo json_encode(array('message' => $message, 'couleur' => $couleur, 'redirect' => @$redirect));
?>