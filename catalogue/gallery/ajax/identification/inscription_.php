<?php
	require("../../../includes/configuration.php");
	require("../../../includes/functions.php");

 	$ip = $_SERVER['REMOTE_ADDR'];

 	if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['email']) && isset($_POST['mdp']) && isset($_POST['remdp']) && isset($_POST['phone']) && isset($_POST['entreprise']) && isset($_POST['siret'])) {
 		if(empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['mdp']) || empty($_POST['remdp']) || empty($_POST['phone']) || empty($_POST['entreprise']) || empty($_POST['siret'])) {
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
	 				if(!preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $_POST['email'])) {
		 				$message = "Veuillez entrer une adresse email valide.";
		 				$couleur = "rouge";
		 			} else {
		 				if($_POST['mdp'] !== $_POST['remdp']) {
		 					$message = "Les mots de passes ne correspondent pas.";
		 					$couleur = "rouge";
		 				} else {
		 					if(strlen($_POST['mdp']) < 6) {
		 						$message = "Le mot de passe doit contenir au moins 6 caractères.";
		 						$couleur = "rouge";
		 					} else {
			 					$verif = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
			 					$verif->bindParam(":email", $_POST['email']);
			 					$verif->execute();
			 					if($verif->rowCount() > 0) {
			 						$message = "Cette adresse email est déjà utilisée pour un autre compte.";
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
					 						if(!is_siret($_POST['siret']) == 0) {
					 							$message = "Veuillez entrer un numéro de SIRET valide.";
					 							$couleur = "rouge";
					 						} else {
						 						// ENVOIE DU MAIL AU CLIENT
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
											                        <div style='display: inline-block;color: #000;padding: 5px;font-size: 1.7em;background-color: #fff;font-weight: bold;margin-left: auto;'>#OCCITANIEBOISSONS</div>
											                    </td>
															</table>
														</a>
														<table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
															<td style='width: 100%;vertical-align: top;'>
																<h3>Création d'un compte client</h3>
																<p>
																	Bonjour,<br/><br/>
																	Nous avons le plaisir de vous confirmer votre inscription au site internet d’Occitanie Boissons et nous vous remercions pour votre confiance !<br/><br/>
																	Nous vous invitons dès à présent à télécharger notre catalogue que vous trouverez sur la page d’accueil de notre site internet : <a target='meta' href='https://www.occitanieboissons.com/accueil/'>https://www.occitanieboissons.com/accueil/</a><br/><br/>
																	A très bientôt,<br/><br/>
																	<strong>L’équipe d'Occitanie Boissons</strong>
																</p>
															</td>
														</table>
														<table style='width: 100%;text-align: center;'>
															<td style='border: none;position: relative;font-size: 0.8em;text-align: center;margin-top: 20px;color: #404040;width: 100%;'>L'abus d'alcool est dangereux pour la santé, à consommer avec modération. Copyright 2017 © Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu</td>
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
														<hr style='margin-top: 8px;'>
														<table style='width: 100%;text-align: center;'>
															<td style='border: none;position: relative;font-size: 0.9em;text-align: center;color: #404040;width: 100%;'>
																<p>Si vous souhaitez vous déscrinscrire de la newsletter, veuillez vous rendre <a href='https://www.occitanieboissons.com/newsletter/desinscription/'>sur cette page</a>.</p>
															</td>
														</table>
													</body>
												</html>
												";
												$message_txt = "";

												if(!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn|outlook|gmail).[a-z]{2,4}$#", $_POST['email'])) {
													$passage_ligne = "\r\n";
												} else {
													$passage_ligne = "\n";
												}

												//========== CREATION DE LA BOUNDARY
													$boundary = "-----=".md5(rand());
													$boundary_alt = "-----=".md5(rand());
												//==========

												//========== DEFINITION DU SUJET
													$sujet = "Occitanie Boissons - Inscription";
												//==========

												//========== EN TÊTE
													$en_tete = "From: \"$sitename\" <".$mail_newsletter.">".$passage_ligne;
													$en_tete .= "Reply-To: \"$sitename\" <".$mail_newsletter.">".$passage_ligne;
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
													mail($_POST['email'], mb_encode_mimeheader($sujet, 'UTF-8'), $affichage, $en_tete);


												// ENVOIE DU MAIL A L'ENTREPRISE
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
											                        <div style='display: inline-block;color: #000;padding: 5px;font-size: 1.7em;background-color: #fff;font-weight: bold;margin-left: auto;'>#OCCITANIEBOISSONS</div>
											                    </td>
															</table>
														</a>
														<table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
															<td style='width: 100%;vertical-align: top;'>
																<h3>Nouvelle inscription</h3>
																<p>
																	".htmlentities($_POST['nom']." ".$_POST['prenom'])."<br/>
																	".htmlentities($_POST['email'])."<br/>
																	<strong>".htmlentities($_POST['phone'])."</strong><br/>
																	".htmlentities($_POST['entreprise'])."
																</p>
															</td>
														</table>
														<table style='width: 100%;text-align: center;'>
															<td style='border: none;position: relative;font-size: 0.8em;text-align: center;margin-top: 20px;color: #404040;width: 100%;'>L'abus d'alcool est dangereux pour la santé, à consommer avec modération. Copyright 2017 © Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu</td>
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
														<hr style='margin-top: 8px;'>
														<table style='width: 100%;text-align: center;'>
															<td style='border: none;position: relative;font-size: 0.9em;text-align: center;color: #404040;width: 100%;'>
																<p>Si vous souhaitez vous déscrinscrire de la newsletter, veuillez vous rendre <a href='https://www.occitanieboissons.com/newsletter/desinscription/'>sur cette page</a>.</p>
															</td>
														</table>
													</body>
												</html>
												";
												$message_txt = "";

												if(!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn|outlook|gmail).[a-z]{2,4}$#", $mail_newsletter)) {
													$passage_ligne = "\r\n";
												} else {
													$passage_ligne = "\n";
												}

												//========== CREATION DE LA BOUNDARY
													$boundary = "-----=".md5(rand());
													$boundary_alt = "-----=".md5(rand());
												//==========

												//========== DEFINITION DU SUJET
													$sujet = "Occitanie Boissons - Nouvelle inscription";
												//==========

												//========== EN TÊTE
													$en_tete = "From: \"$sitename\" <".$mail_newsletter.">".$passage_ligne;
													$en_tete .= "Reply-To: \"$sitename\" <".$mail_newsletter.">".$passage_ligne;
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
													mail($mail_newsletter, mb_encode_mimeheader($sujet, 'UTF-8'), $affichage, $en_tete);
													$nom = $_POST['prenom']." ".$_POST['nom'];

									 			### CREATION DU COMPTE UTILISATEUR
									 			$addUser = $bdd->prepare("INSERT INTO ob_users (nom, prenom, mdp, email, ip, ip_lastconnexion, time_creation, time_lastconnexion, phone, entreprise, siret) VALUES (:nom, :prenom, :mdp, :email, '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['REMOTE_ADDR']."', '".time()."', '".time()."', :phone, :entreprise, :siret)");
												$addUser->bindParam(":nom", $_POST['nom']);
												$addUser->bindParam(":prenom", $_POST['prenom']);
												$addUser->bindParam(":mdp", password_hash($_POST['mdp'], PASSWORD_DEFAULT));
												$addUser->bindParam(":email", $_POST['email']);
												$addUser->bindParam(":phone", $_POST['phone']);
												$addUser->bindParam(":entreprise", $_POST['entreprise']);
												$addUser->bindParam(":siret", $_POST['siret']);
												$addUser->execute();

												// MODIFICATION DU PANIER
								  				if(isset($_COOKIE['panier'])) {
								  					$modifPanier = $bdd->prepare("UPDATE ob_users SET panier = :panier WHERE id = :id");
								  					$modifPanier->bindParam(":panier", $_COOKIE['panier']);
								  					$modifPanier->bindParam(":id", $bdd->lastInsertId());
								  					$modifPanier->execute();	
								  				}

								  				$_SESSION['site'] = $_POST['email'];
								  				if(isset($_POST['redirect'])) {
								  					$redirect = $url."/".$_POST['redirect'];
								  				} else {
								  					$redirect = $url;
								  				}

												$message = "Félicitations ! Votre compte a bien été crée."; 
			 									$couleur = "vert";
			 								}
								 		}
				 					}
				 				}
			 				}
		 				}
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