<?php
 	require("../../../includes/configuration.php");
  
  	$ip = $_SERVER['REMOTE_ADDR'];

  	if($_GET['action'] == "demande") {
		if(isset($_POST['email'])) {
			if(empty($_POST['email'])) {
			 	$message = "Veuillez remplir les champs vides."; 
	 			$couleur = "rouge";
			} else {
				$verif = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
				$verif->bindParam(":email", htmlentities($_POST['email']));
				$verif->execute();
				$v = $verif->fetch(PDO::FETCH_OBJ); 
			  	if($verif->rowCount() < 1) {
					$message = "Cette adresse email ne correspond à aucun compte."; 
	 				$couleur = "rouge";
			  	} else {
			  		// SUPRESSION DES AUTRES DEMANDES
			  		$bdd->query("DELETE FROM ob_users_password WHERE email = '".$v->email."'");
	  				// AJOUT DEMANDE AVEC TOKEN
	  				$token = random_bytes(24);
	  				$bdd->query("INSERT INTO ob_users_password (email, token, time) VALUES ('".$v->email."','".bin2hex($token)."','".time()."')");
	  				if(isset($_POST['redirect'])) {
	  					$url_modification = $image_url."/mot-de-passe/modification/".$v->email."/token/".bin2hex($token)."/redirection/".htmlentities($_POST['redirect']);
	  				} else {
	  					$url_modification = $image_url."/mot-de-passe/modification/".$v->email."/token/".bin2hex($token);
	  				}

	  				// ENVOIE DU MAIL
					$contenu = "
					<!DOCTYPE html>
					<html lang='fr'>
						<body style='margin: 0;padding: 0;font-family: Helvetica !important;'>
							<table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
								<td style='width: 100%;vertical-align: top;'>
									<h3>Modification du mot de passe</h3>
									<p>
										Bonjour,<br/><br/>
										Si tu as fait une demande de modification de mot de passe, clique sur le lien ci-dessous.<br/><br/>
										Pour modifier ton mot de passe : <a target='meta' href='".$url_modification."'>".$url_modification."</a><br/><br/>
										Sinon, nous t'avertissons que quelqu'un essaie de se connecter à ton compte. Dans ce cas, change d'identifiant par sécurité dans la section <a href='".$image_url."/informations-personnelles/'>\"Informations personnelles\"</a> de notre site web.<br/><br/>
										À très bientôt,<br/><br/>
										<strong>L’équipe de l'AOOIC</strong>
									</p>
								</td>
							</table>
						</body>
					</html>
					";
					$message_txt = "";

					if(!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn|outlook|gmail).[a-z]{2,4}$#", $v->email)) {
						$passage_ligne = "\r\n";
					} else {
						$passage_ligne = "\n";
					}

					//========== CREATION DE LA BOUNDARY
						$boundary = "-----=".md5(rand());
					//==========

					//========== DEFINITION DU SUJET
						$sujet = "LogistiqueOB - Modification du mot de passe";
					//==========

					//========== EN TÊTE
						$en_tete = "From: \"$sitename\" <".$mail_cppiades.">".$passage_ligne;
						$en_tete .= "Reply-To: \"$sitename\" <".$mail_cppiades.">".$passage_ligne;
						$en_tete .= "MIME-Version: 1.0".$passage_ligne;
						$en_tete .= "Content-Type: multipart/mixed;".$passage_ligne." boundary=".$boundary."".$passage_ligne;
					//==========

					//=====Création du message.
						$affichage = $passage_ligne."--".$boundary.$passage_ligne;
						$affichage.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary_alt\"".$passage_ligne;
						$affichage.= $passage_ligne."--".$boundary_alt.$passage_ligne;
					//=====Ajout du message au format texte.
						$affichage.= "Content-Type: text/plain; charset=\"utf-8\"".$passage_ligne;
						$affichage.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
						$affichage.= $passage_ligne.$message_txt.$passage_ligne;
					//==========
						$affichage.= $passage_ligne."--".$boundary_alt.$passage_ligne;
					//=====Ajout du message au format HTML.
						$affichage.= "Content-Type: text/html; charset=\"utf-8\"".$passage_ligne;
						$affichage.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
						$affichage.= $passage_ligne.$contenu.$passage_ligne;
					//==========
					//=====On ferme la boundary alternative.
						$affichage.= $passage_ligne."--".$boundary_alt."--".$passage_ligne;
					//==========
						$affichage.= $passage_ligne."--".$boundary.$passage_ligne;
						$affichage.= $passage_ligne."--".$boundary."--".$passage_ligne; 
					//========== ENVOIE DU MAIL
						mail($v->email, mb_encode_mimeheader($sujet, 'UTF-8'), $affichage, $en_tete);
	
					// REDIRECTION
	  				if(isset($_POST['redirect'])) {
	  					$redirect = $url."/mot-de-passe/modification/".$v->email."/redirection/".htmlentities($_POST['redirect']);
	  				} else {
	  					$redirect = $url."/mot-de-passe/modification/".$v->email;
	  				}	
			  	}
			}
		} else {
	  		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
	 		$couleur = "rouge";
	  	}
	} elseif($_GET['action'] == "modification") {
		if(isset($_POST['email']) && isset($_POST['token']) && isset($_POST['pwd']) && isset($_POST['repwd'])) {
			if(empty($_POST['pwd']) || empty($_POST['repwd'])) {
			 	$message = "Veuillez remplir les champs vides."; 
	 			$couleur = "rouge";
			} else {
				$verif = $bdd->prepare("SELECT * FROM ob_users_password WHERE email = :email AND token = :token");
				$verif->bindParam(":email", htmlentities($_POST['email']));
				$verif->bindParam(":token", htmlentities($_POST['token']));
				$verif->execute();
				$v = $verif->fetch(PDO::FETCH_OBJ); 
			  	if($verif->rowCount() < 1) {
					if(isset($_POST['redirect'])) {
						$redirect = $url."/mot-de-passe/redirection/".htmlentities($_POST['redirect']);
					} else {
						$redirect = $url."/mot-de-passe/";
					}
				} else {
			  		if($_POST['pwd'] != $_POST['repwd']) {
			  			$message = "Les mots de passes saisis ne correspondent pas."; 
	 					$couleur = "rouge";
			  		} else {
			  			if(strlen($_POST['pwd']) < 6) {
			  				$message = "Le mot de passe doit contenir au moins 6 caractères."; 
	 						$couleur = "rouge";
			  			} else {
			  				// MODIFICATION DU MOT DE PASSE
			  				$updatePwd = $bdd->prepare("UPDATE ob_users SET mdp = :password WHERE email = '".$v->email."'");
			  				$updatePwd->bindParam(":password", password_hash($_POST['pwd'], PASSWORD_DEFAULT));
			  				$updatePwd->execute();
			  				// SUPPRESSION DU TOKEN
			  				$bdd->query("DELETE FROM ob_users_password WHERE email = '".$v->email."'");

			  				if(isset($_POST['redirect'])) {
								$redirect = $url."/connexion/redirection/".htmlentities($_POST['redirect']);
							} else {
								$redirect = $url."/connexion/";
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
		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
	 	$couleur = "rouge";
	}

 	echo json_encode(array('message' => $message, 'couleur' => $couleur, 'redirect' => @$redirect));
?>