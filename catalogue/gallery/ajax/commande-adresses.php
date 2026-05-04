<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	if(isset($_SESSION['site'])) {
		if($_GET['action'] == "ajouter") {
			if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['entreprise']) && isset($_POST['tva']) && isset($_POST['adresse']) && isset($_POST['adressec']) && isset($_POST['codepostal']) && isset($_POST['ville']) && isset($_POST['pays']) && isset($_POST['phone'])) {
		 		if(empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['adresse']) || empty($_POST['codepostal']) || empty($_POST['ville']) || empty($_POST['pays']) || empty($_POST['phone'])) {
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
			 						if(!preg_match("#[0-9]{5}#", $_POST['codepostal'])) {
			 							$message = "Le code postal devrait ressembler à 'NNNNN'.";
			 							$couleur = "rouge";
			 						} else {
							 			### AJOUT ADRESSE
							 			$addAdresse = $bdd->prepare("INSERT INTO ob_users_adresses (nom, prenom, entreprise, numerotva, adresse, adressec, codepostal, ville, pays, phone, userid, time) VALUES (:nom, :prenom, :entreprise, :tva, :adresse, :adressec, :codepostal, :ville, :pays, :phone, '".$u->id."', '".time()."')");
										$addAdresse->bindParam(":nom", htmlentities($_POST['nom']));
										$addAdresse->bindParam(":prenom", htmlentities($_POST['prenom']));
										$addAdresse->bindParam(":entreprise", htmlentities(@$_POST['entreprise']));
										$addAdresse->bindParam(":tva", htmlentities(@$_POST['tva']));
										$addAdresse->bindParam(":adresse", htmlentities($_POST['adresse']));
										$addAdresse->bindParam(":adressec", htmlentities($_POST['adressec']));
										$addAdresse->bindParam(":codepostal", htmlentities($_POST['codepostal']));
										$addAdresse->bindParam(":ville", htmlentities($_POST['ville']));
										$addAdresse->bindParam(":pays", htmlentities($_POST['pays']));
										$addAdresse->bindParam(":phone", htmlentities($_POST['phone']));
										$addAdresse->execute();

										/*
										### MODIFIER DISTANCE
										$lastinserid = $bdd->lastInsertId();
										$modifDistance = $bdd->prepare("UPDATE ob_users_adresses SET distance = :distance WHERE id = '".$bdd->lastInsertId()."'");
										$modifDistance->bindParam(":distance",getDistance($lastinserid,"ob"));
										$modifDistance->execute();	
										*/
										if(@$_POST['facturation'] == "on") {
			 								$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_livraison = :adresse_livraison, adresse_facturation = :adresse_livraison, adresse_l_f = '1' WHERE id = '".$u->id."'");
											$modifUser->bindParam(":adresse_livraison", $lastinserid);
											$modifUser->execute();
											$redirect = $url."/commande/etape/3";
			 							} else {
			 								$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_livraison = :adresse_livraison, adresse_l_f = '0' WHERE id = '".$u->id."'");
											$modifUser->bindParam(":adresse_livraison", $lastinserid);
											$modifUser->execute();
											$redirect = $url."/commande/etape/2";
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
		} elseif($_GET['action'] == "modifier") {
			if(isset($_POST['idadresse']) && isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['entreprise']) && isset($_POST['tva']) && isset($_POST['adresse']) && isset($_POST['adressec']) && isset($_POST['codepostal']) && isset($_POST['ville']) && isset($_POST['pays']) && isset($_POST['phone'])) {
				$verifAdress = $bdd->prepare("SELECT * FROM ob_users_adresses WHERE id = :id AND userid = '".$u->id."'");
				$verifAdress->bindParam(":id", $_POST['idadresse']);
				$verifAdress->execute();
				$v = $verifAdress->fetch(PDO::FETCH_OBJ);
				if($verifAdress->rowCount() > 0) {
			 		if(empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['adresse']) || empty($_POST['codepostal']) || empty($_POST['ville']) || empty($_POST['pays']) || empty($_POST['phone'])) {
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
				 						if(!preg_match("#[0-9]{5}#", $_POST['codepostal'])) {
				 							$message = "Le code postal devrait ressembler à 'NNNNN'.";
				 							$couleur = "rouge";
				 						} else {
				 							/*
				 							// MODIFIER DISTANCE
				 							if($_POST['adresse'] != $v->adresse || $_POST['adressec'] != $v->adressec) {
				 								$modifDistance = $bdd->prepare("UPDATE ob_users_adresses SET distance = :distance WHERE id = '".$v->id."'");
												$modifDistance->bindParam(":distance",getDistance($v->id,"ob"));
												$modifDistance->execute();	
				 							}
				 							*/
								 			### MODIFICATION ADRESSE
								 			$modifAdresse = $bdd->prepare("UPDATE ob_users_adresses SET nom = :nom, prenom = :prenom, entreprise = :entreprise, numerotva = :tva, adresse = :adresse, adressec = :adressec, codepostal = :codepostal, ville = :ville, pays = :pays, phone = :phone WHERE id = '".$v->id."'");
											$modifAdresse->bindParam(":nom", $_POST['nom']);
											$modifAdresse->bindParam(":prenom", $_POST['prenom']);
											$modifAdresse->bindParam(":entreprise", @$_POST['entreprise']);
											$modifAdresse->bindParam(":tva", @$_POST['tva']);
											$modifAdresse->bindParam(":adresse", $_POST['adresse']);
											$modifAdresse->bindParam(":adressec", $_POST['adressec']);
											$modifAdresse->bindParam(":codepostal", $_POST['codepostal']);
											$modifAdresse->bindParam(":ville", $_POST['ville']);
											$modifAdresse->bindParam(":pays", $_POST['pays']);
											$modifAdresse->bindParam(":phone", $_POST['phone']);
											$modifAdresse->execute();

											if(@$_POST['facturation'] == "on") {
				 								$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_livraison = :adresse_livraison, adresse_facturation = :adresse_livraison, adresse_l_f = '1' WHERE id = '".$u->id."'");
												$modifUser->bindParam(":adresse_livraison", $v->id);
												$modifUser->execute();
												$redirect = $url."/commande/etape/3";
				 							} else {
				 								$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_livraison = :adresse_livraison, adresse_l_f = '0' WHERE id = '".$u->id."'");
												$modifUser->bindParam(":adresse_livraison", $v->id);
												$modifUser->execute();
												$redirect = $url."/commande/etape/2";
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
		 	} else {
		 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
		 		$couleur = "rouge";
		 	}
		} elseif($_GET['action'] == "verification") {
			if($u->adresse_l_f == 1) {
				$verifAdresse = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_livraison."'");
				if($verifAdresse->rowCount() > 0) {
					/* MODIFIER ADRESSE FACTURATION */
					$bdd->query("UPDATE ob_users SET adresse_facturation = '".$u->adresse_livraison."' WHERE id = '".$u->id."'");
					$redirect = $url."/commande/etape/3";
				} else {
					$message = "Veuillez sélectionner une adresse avant de passer à l'étape suivante."; 
	 				$couleur = "rouge";
				}
			} else {
				$verifAdresse = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_livraison."'");
				if($verifAdresse->rowCount() > 0) {
					$verifAdresse = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_facturation."'");
					if($verifAdresse->rowCount() > 0) {
						$redirect = $url."/commande/etape/3";
					} else {
						$message = "Veuillez sélectionner une adresse avant de passer à l'étape suivante."; 
	 					$couleur = "rouge";
					}
				} else {
					$message = "Veuillez sélectionner une adresse avant de passer à l'étape suivante."; 
	 				$couleur = "rouge";
				}
			}
		} elseif($_GET['action'] == "selected" && isset($_POST['id'])) {
			$verifAdresse = $bdd->prepare("SELECT * FROM ob_users_adresses WHERE id = :id");
			$verifAdresse->bindParam(":id", $_POST['id']);
			$verifAdresse->execute();
			if($verifAdresse->rowCount() > 0) {
				if($_GET['type'] == "livraison") {
					$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_livraison = :adresse_livraison WHERE id = '".$u->id."'");
					$modifUser->bindParam(":adresse_livraison", $_POST['id']);
					$modifUser->execute();
				} elseif($_GET['type'] == "facturation") {
					$modifUser = $bdd->prepare("UPDATE ob_users SET adresse_facturation = :adresse_facturation WHERE id = '".$u->id."'");
					$modifUser->bindParam(":adresse_facturation", $_POST['id']);
					$modifUser->execute();
				}
				$couleur = "vert";
			} else {
		 		$redirect = $url."/commande/etape/2";
		 	}
		} elseif($_GET['action'] == "livraison") {
			if(isset($_POST['option_livraison'])) {
				if($_POST['option_livraison'] == 0 || $_POST['option_livraison'] == 1) {
					$modifUser = $bdd->prepare("UPDATE ob_users SET option_livraison = :livraison, message_livraison = :message_livraison WHERE id = '".$u->id."'");
					$modifUser->bindParam(":livraison", $_POST['option_livraison']);
					$modifUser->bindParam(":message_livraison", $_POST['message_livraison']);
					$modifUser->execute();

					$redirect = $url."/commande/etape/4";
				} else {
					$redirect = $url."/commande/etape/3";
				}
			} else {
				$message = "Veuillez sélectionner une méthode de livraison."; 
	 			$couleur = "rouge";
			}
		} else {
	 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
	 		$couleur = "rouge";
	 	}
	} else {
		$redirect = $url."/connexion/redirection/commande/";
	}

 	echo json_encode(array('message' => @$message, 'couleur' => @$couleur, 'redirect' => @$redirect));
?>