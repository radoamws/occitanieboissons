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
							<a style='text-decoration: none;' href='".$image_url."'>
								<table style='width: 100%;box-sizing: border-box;padding: 25px;background-color: #000;'>
					    			<td><img style='width: 200px;height: 200px;' src='".$image_url."/gallery/images/ob2.png'/></td>
					    			<td align='right' style='border: none;vertical-align: bottom;'>
				                        <div style='margin-bottom: 10px;'>
				                          <a href='https://www.instagram.com/occitanieboissons/' style='margin-right: 10px;'><img alt='insta' src='".$image_url."/gallery/images/insta.png'></a>
				                          <a href='https://www.facebook.com/Occitanie-Boissons-1619195328335124'><img alt='fb' src='".$image_url."/gallery/images/fb.png'></a>
				                        </div>
				                        <div style='display: inline-block;color: #000;padding: 5px;font-size: 1.7em;background-color: #fff;font-weight: bold;margin-left: auto;'>#OccitanieBoissons</div>
				                    </td>
								</table>
							</a>
							<table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
								<td style='width: 100%;vertical-align: top;'>
									<h3>Modifier mon mot de passe</h3>
									<p>
										Bonjour,<br/><br/>
										Si vous avez fait une demande de modification de mot de passe, veuillez cliquer sur le lien ci-dessous. Sinon, nous vous avertissons que quelqu'un essaie de se connecter à votre compte. Dans ce cas, changez d'identifiant par sécurité dans la section <a href='".$image_url."/informations-personnelles/'>\"Informations personnelles\"</a> de notre site web.<br/><br/>
										Pour modifier votre mot de passe cliquez sur ce lien : <a target='meta' href='".$url_modification."'>".$url_modification."</a><br/><br/>
										Cordialement,<br/><br/>
										<strong>L’équipe d'Occitanie Boissons</strong>
									</p>
								</td>
							</table>
							<table style='width: 100%;text-align: center;'>
								<td style='border: none;position: relative;font-size: 0.8em;text-align: center;margin-top: 20px;color: #404040;width: 100%;'>L'abus d'alcool est dangereux pour la santé, à consommer avec modération. Copyright 2020 © Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu</td>
							</table>
							<hr>
							<table style='width: 100%;'>
								<td style='vertical-align: top;border: none;'>
									<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>Occitanie Boissons</div>
									<div style='font-weight: bold;'>05 61 82 50 78</div>
									<div>contact@occitanieboissons.com</div>
									<div>ZA-Le Grand, 3 rue des Artisants</div>
									<div>31140 PECHBONNIEU</div>
								</td>
								<td style='vertical-align: top;border: none;'>
									<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>Baptiste Perrinet</div>
									<div style='font-weight: bold;'>06 73 43 50 20</div>
									<div>commercial@occitanieboissons.com</div>          
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
						$sujet = "Occitanie Boissons - Modification du mot de passe";
					//==========

					//========== EN TÊTE
						$en_tete = "From: \"$sitename\" <".$mail_contact.">".$passage_ligne;
						$en_tete .= "Reply-To: \"$sitename\" <".$mail_contact.">".$passage_ligne;
						$en_tete .= "MIME-Version: 1.0".$passage_ligne;
						$en_tete .= "Content-Type: multipart/mixed;".$passage_ligne." boundary=".$boundary."".$passage_ligne;
					//==========

					//=====Création du message.
						$affichage = $passage_ligne."--".$boundary.$passage_ligne;
						$affichage.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary_alt\"".$passage_ligne;
						$affichage.= $passage_ligne."--".$boundary_alt.$passage_ligne;
					//=====Ajout du message au format texte.
						$affichage.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
						$affichage.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
						$affichage.= $passage_ligne.$message_txt.$passage_ligne;
					//==========
						$affichage.= $passage_ligne."--".$boundary_alt.$passage_ligne;
					//=====Ajout du message au format HTML.
						$affichage.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
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