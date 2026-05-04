<?php 
 	require("../../../includes/configuration.php");
 	require("../../../includes/functions.php");
  
  	$ip = $_SERVER['REMOTE_ADDR'];

  	if(isset($_POST['email']) && isset($_POST['mdp'])) {
		if(empty($_POST['email']) || empty($_POST['mdp'])) {
		 	$message = "Veuillez remplir les champs vides."; 
 			$couleur = "rouge";
		} else {
			$verif = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
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
	  				// MODIFICATION DU PANIER
	  				if(isset($_COOKIE['panier']) && isset($_POST['redirect'])) {
	  					if($_POST['redirect'] == "panier" || $_POST['redirect'] == "commande") {
		  					$modifPanier = $bdd->prepare("UPDATE ob_users SET panier = :panier WHERE id = '".$v->id."'");
		  					$modifPanier->bindParam(":panier", $_COOKIE['panier']);
		  					$modifPanier->execute();
		  				}
	  					$redirect = $url."/".$_POST['redirect'];
	  				} else {
	  					$redirect = $url;
	  					setcookie("panier",$v->panier,time()+60*60*24*30, '/');
	  				}
	  				// IP DERNIER CONNEXION
	  				$modifIp = $bdd->query("UPDATE ob_users SET ip_lastconnexion = '".$ip."' WHERE id = '".$v->id."'");

					$_SESSION['site'] = $_POST['email'];
					$message = "Content de vous revoir ".$v->prenom." !"; 
					$couleur = "vert";
			    }
			}
		}
  	} else {
  		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 		$couleur = "rouge";
  	}

 	echo json_encode(array('message' => $message, 'couleur' => $couleur, 'redirect' => @$redirect));
?>