<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	if(isset($_SESSION['site'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	 	if(ArticlePanier() > 0 && PrixPanier() > 0) {	
			// VERIFICATION DE LA DISPONIBILITE DES PRODUITS
			$panier = json_decode($u->panier, true);
			$verifStock = TRUE;
			$newpanier = array();
			foreach($panier as $p) {
				foreach($p as $p2 => $qte) {
					$elementStock = $bdd->query("SELECT * FROM ob_catalogue_produits WHERE id = '".$p2."'");
					if($elementStock->rowCount() > 0) {
						$e = $elementStock->fetch(PDO::FETCH_OBJ);
						if($qte > floor($e->stock/$e->uv_caisse) && $e->marque != 2) {$verifStock = FALSE;$qte = floor($e->stock/$e->uv_caisse);}	
						$newpanier[] = array($e->id => $qte);
					} else {
						$verifStock = FALSE;
					}
				}
			}

			// SI LE STOCK EST VALIDE
			if($verifStock) {
				// RECUPERATION ADRESSE LIVRAISON & FACTURATION
				$a_l = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_livraison."'")->fetch(PDO::FETCH_OBJ);
				if($u->adresse_l_f == 1) {$a_f = $a_l;} else { 
					$a_f = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_facturation."'")->fetch(PDO::FETCH_OBJ);
				}

				// CREATION DE LA COMMANDE
				$total = LivraisonPrix(CodePostal(),"ttc")+PrixPanier("ttc",TRUE)+Consigne(TRUE);
				$createCommande = $bdd->prepare("INSERT INTO ob_users_commande (userid, prixht, prixdroits, prixttc, livraison, email, ip, nom, prenom, phone, entreprise, articles, time, panier, option_livraison, message_livraison, adresse_l, adressec_l, codepostal_l, ville_l, pays_l, nom_l, prenom_l, phone_l, entreprise_l, adresse_f, adressec_f, codepostal_f, ville_f, pays_f, nom_f, prenom_f, phone_f, entreprise_f, total_paiement, paiement_default, consigne, numero_client) VALUES ('".$u->id."', '".PrixPanier("ht",TRUE)."', '".PrixPanier("droits",TRUE)."', '".PrixPanier("ttc",TRUE)."', '".LivraisonPrix(CodePostal())."', '".$u->email."', '".$ip."', :nom, :prenom, '".$u->phone."', :entreprise, '".ArticlePanier()."', '".time()."', '".$u->panier."', '".$u->option_livraison."', :message_livraison, :adresse_l, :adressec_l, '".$a_l->codepostal."', '".$a_l->ville."', '".$a_l->pays."', :nom_l, :prenom_l, '".$a_l->phone."', :entreprise_l, :adresse_f, :adressec_f, '".$a_f->codepostal."', '".$a_f->ville."', '".$a_f->pays."', :nom_f, :prenom_f, '".$a_f->phone."', :entreprise_f, :total_paiement, '".$u->paiement_default."', '".Consigne(TRUE)."', '".$u->numero_client."')");
				$createCommande->bindParam(":nom", $u->nom);
				$createCommande->bindParam(":prenom", $u->prenom);
				$createCommande->bindParam(":entreprise", $u->entreprise);
				$createCommande->bindParam(":nom_l", $a_l->nom);
				$createCommande->bindParam(":prenom_l", $a_l->prenom);
				$createCommande->bindParam(":adresse_l", $a_l->adresse);
				$createCommande->bindParam(":entreprise_l", $a_l->entreprise);
				$createCommande->bindParam(":adressec_l", $a_l->adressec);
				$createCommande->bindParam(":nom_f", $a_f->nom);
				$createCommande->bindParam(":prenom_f", $a_f->prenom);
				$createCommande->bindParam(":adresse_f", $a_f->adresse);
				$createCommande->bindParam(":adressec_f", $a_f->adressec);
				$createCommande->bindParam(":entreprise_f", $a_f->entreprise);
				$createCommande->bindParam(":message_livraison", $u->message_livraison);
				$createCommande->bindParam(":total_paiement", $total);
				$createCommande->execute();

				// ID COMMANDE
				$commande_id = $bdd->lastInsertId();

				// AJOUTER LES PRODUITS EN MEMOIRE
				foreach($panier as $p) {
					foreach($p as $p2 => $qte) {
						$i = $bdd->query("SELECT * FROM ob_catalogue_produits WHERE id = '".$p2."'")->fetch(PDO::FETCH_OBJ);
						$createElement = $bdd->prepare("INSERT INTO ob_users_commande_element (commandeid,prix_ht,code_tva,qte,degre,nom,nom_sup,contenance,code_produit,brasserie,droits,condition_vente,consigne_caisse,uv_caisse,marque) VALUES ('".$commande_id."','".$i->prix_ht."','".$i->code_tva."','".$qte."','".$i->degre."',:nom,:nom_sup,'".$i->contenance."','".$p2."','".$i->brasserie."','".$i->droits."','".$i->condition_vente."','".$i->consigne_caisse."','".$i->uv_caisse."','".$i->marque."')");
						$createElement->bindParam(":nom", $i->nom);
						$createElement->bindParam(":nom_sup", $i->nom_sup);
						$createElement->execute();
					}
				}

				/* CHECK CONSIGNE */
				$consigne = FALSE;
				foreach(json_decode($u->panier, true) as $p) {
					foreach($p as $p2 => $qte) {
						$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
						$element->bindParam(":id", $p2);
						$element->execute();
						$e = $element->fetch(PDO::FETCH_OBJ);
						if($e->consigne_caisse != 0) {$consigne = TRUE;}
					}
				}

				// ENVOIE DU MAIL A OB + CLIENT
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
							<h3>Commande n°".$commande_id." - ".mPaiement($u->paiement_default)."</h3>
							<a href='".$image_url."/pdf/facturation/".$commande_id."' download>Télécharger la facture</a>
							<h4>Informations du client</h4>
							<table style='width: 100%;'>
								<td style='vertical-align: top;border: none;'>
									<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>".$u->nom." ".$u->prenom."</div>
									<div style='font-weight: bold;'>".$u->entreprise."</div>
									<div>".$u->email."</div>
									<div>".$u->phone."</div>
								</td>
							</table>
							<h4>Adresses</h4>
							<table style='width: 100%;'>
								<thead>
									<th>Livraison</th>
									<th>Facturation</th>
								</thead>
								<tbody>
									<tr>
										<td style='text-align: center;vertical-align: top;border: none;'>
											<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>".$a_l->nom." ".$a_l->prenom."</div>
											<div style='font-weight: bold;font-size: 1.1em;margin: 8px 0;'>".$a_l->adresse.", ".$a_l->adressec."</div>
											<div style='font-weight: bold;'>".$a_l->codepostal." ".$a_l->ville.", ".$a_l->pays."</div>
											<div style='font-weight: bold;'>".$a_l->phone."</div>
											<div>".$a_l->entreprise."</div>
										</td>
										<td style='text-align: center;vertical-align: top;border: none;'>
											<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>".$a_f->nom." ".$a_f->prenom."</div>
											<div style='font-weight: bold;font-size: 1.1em;margin: 8px 0;'>".$a_f->adresse.", ".$a_f->adressec."</div>
											<div style='font-weight: bold;'>".$a_f->codepostal." ".$a_f->ville.", ".$a_f->pays."</div>
											<div style='font-weight: bold;'>".$a_f->phone."</div>
											<div>".$a_f->entreprise."</div>
										</td>
									</tr>
								</tbody>
							</table>
							<h4>Produits (".ArticlePanier().")											
							<table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>
								<thead style='width: 100%;'>
									<tr>
				";
				if($consigne) {
					$contenu .= "
											<th width='22%'>Produit</th>
											<th width='15%'>Contenance</th>
											<th width='8%'>% Alcool</th>
											<th width='10%'>Prix HT HD</th>
											<th width='10%'>Droits accises</th>
											<th width='10%'>Prix HT DC</th>
											<th width='10%'>Consigne</th>
											<th width='15%'>Quantité</th>
								";
				} else {
					$contenu .= "
											<th width='25%'>Produit</th>
											<th width='15%'>Contenance</th>
											<th width='10%'>% Alcool</th>
											<th width='12.5%'>Prix HT HD</th>
											<th width='10%'>Droits accises</th>
											<th width='12.5%'>Prix HT DC</th>
											<th width='15%'>Quantité</th>
					";
				}
				$contenu .= "
									</tr>
								</thead>
								<tbody>
				";
				foreach(json_decode($u->panier, true) as $p) {
					foreach($p as $p2 => $qte) {
						$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
						$element->bindParam(":id", $p2);
						$element->execute();
						$e = $element->fetch(PDO::FETCH_OBJ);
						
						$contenu .= "
									<tr style='text-align: center;width: 100%;vertical-align: top;'>
									<td style='vertical-align: middle;'>
						";
						if($e->marque == 2) {
							$contenu .= "<span style='color: red;text-transform: uppercase;'>Précommande</span><br class='no-margin'/>";
						}								
						$contenu .= $e->code_produit." - ".$e->nom."<br style='margin: 0';/>".$e->nom_sup."</td>
										<td>".Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance)."</td>
										<td>".number_format($e->degre, 1, ',', ' ')."°</td>
										<td>".number_format($e->prix_ht, 2, ',', ' ')."€</td>
										<td>".number_format($e->droits, 2, ',', ' ')."€</td>
										<td>".number_format($e->prix_ht+$e->droits, 2, ',', ' ')."€</td>
						";
						if($consigne) { $contenu .= "<td>".number_format($e->consigne_caisse, 2, ',', ' ')."€</td>"; }
						$contenu .= "
										<td>".$qte."</td>
									</tr>
						";
					}
				}
				$contenu .= "
								</tbody>
							</table>
							<table style='width: 100%;'>
								<td style='vertical-align: top;border: none;'>
									<div style='font-weight: bold;'>Prix HT HD : ".PrixPanier('ht')."€</div>
									<div style='font-weight: bold;'>Prix HT DC : ".PrixPanier('droits')."€</div>
									<div style='font-weight: bold;'>Livraison HT : ".LivraisonPrix(CodePostal())."€</div>
									<div style='font-weight: bold;'>Consigne : ".Consigne()."€</div>
									<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>TOTAL TTC : ".number_format(LivraisonPrix(CodePostal(),"ttc")+PrixPanier("ttc",TRUE)+Consigne(TRUE), 2, ',', ' ')."€</div>
								</td>
							</table>
							<table style='width: 100%;text-align: center;'>
								<td style='border: none;position: relative;font-size: 0.8em;text-align: center;margin-top: 20px;color: #404040;width: 100%;'>L'abus d'alcool est dangereux pour la sant&eacute;, &agrave; consommer avec mod&eacute;ration. Copyright 2020 &copy; Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu</td>
							</table>
							<hr>
							<table style='width: 100%;'>
								<td style='vertical-align: top;border: none;'>
									<div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>Occitanie Boissons</div>
									<div style='font-weight: bold;'>05 61 82 50 78</div>
									<div style='font-weight: bold;'>06 20 23 58 60</div>
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
  				$sujet = "Commande (occitanieboissons.com) - ".$commande_id." - ".mPaiement($u->paiement_default);
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
			//===== AJOUT DU MESSAGE AU FORMAT HTML
	  			$affichage .= "Content-Type: text/html; charset=ISO-8859-1'".$passage_ligne;
	  			$affichage .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
	  			$affichage .= $passage_ligne.$contenu.$passage_ligne;
			//==========

	  		//=====On ferme la boundary alternative.
				$affichage.= $passage_ligne."--".$boundary_alt."--".$passage_ligne;
			//==========
	  			$affichage.= $passage_ligne."--".$boundary.$passage_ligne;
				$affichage.= $passage_ligne."--".$boundary."--".$passage_ligne; 

			//========== ENVOIE DE L'EMAIL
		  		if(mail($mail_newsletter, mb_encode_mimeheader($sujet), $affichage, $en_tete)) {
	  				// ON VIDE LE PANIER
					$bdd->query("UPDATE ob_users SET panier = '' WHERE id = '".$u->id."'");
				
					$redirect = $url."/commande-visualisation/";
				} else {
					// SUPPRESSION DE LA COMMANDE
					$bdd->query("DELETE FROM ob_users_commande WHERE id = '".$commande_id."'");
					$bdd->query("DELETE FROM ob_users_commande_element WHERE commandeid = '".$commande_id."'");

					$message = "Une erreur est survenue, veuillez réessayer."; 
	 				$couleur = "rouge";
				}
			} else {
				// MODIFICATION DU PANIER
				$modificationPanier = $bdd->query("UPDATE ob_users SET panier = '".json_encode($newpanier)."' WHERE id = '".$u->id."'");
				setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
				// PAGE ERREUR STOCK
				$redirect = $url."/panier/erreur/stock";
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