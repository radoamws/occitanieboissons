<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	if(!empty($_POST)) {
		if(isset($_POST['vads_hash'])) {
			$verifCommande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id");
			$verifCommande->bindParam(":id", $_POST['vads_order_id']);
			$verifCommande->execute();
			if($verifCommande->rowCount() > 0) {
				if($_POST['signature'] == getSignature($_POST,"8YdNzzNccUfAlYZ5")) {	
					if($_POST['vads_trans_status'] == "ACCEPTED" || $_POST['vads_trans_status'] == "AUTHORISED") {
						$updateCommande = $bdd->prepare("UPDATE ob_users_commande SET paiement = '1' WHERE id = :id");
						$updateCommande->bindParam(":id",$_POST['vads_order_id']);
						$updateCommande->execute();

						// MODIFICATION DES INFORMATIONS
						$updateCommande = $bdd->prepare("UPDATE ob_users_commande SET option_livraison = :vads_order_info2, message_livraison = :vads_order_info, adresse_l = :vads_ship_to_street, adressec_l = :vads_ship_to_street2, codepostal_l = :vads_ship_to_zip, ville_l = :vads_ship_to_city, pays_l = 'France', nom_l = :vads_ship_to_last_name, prenom_l = :vads_ship_to_first_name, phone_l = :vads_ship_to_phone_num, entreprise_l = :vads_ship_to_legal_name, adresse_f = :vads_cust_address, codepostal_f = :vads_cust_zip, ville_f = :vads_cust_city, pays_f = 'France', nom_f = :vads_cust_last_name, prenom_f = :vads_cust_first_name, phone_f = :vads_cust_cell_phone, entreprise_f = :vads_cust_legal_name, transaction = :vads_trans_id, transaction_date = :vads_trans_date, distance = :vads_order_info3, paiement = '1', prix_paiement = :vads_amount WHERE id = :id");
						$updateCommande->bindParam(":id",$_POST['vads_order_id']);
						$updateCommande->bindParam(":vads_order_info2",$_POST['vads_order_info2']);
						$updateCommande->bindParam(":vads_order_info",$_POST['vads_order_info']);
						$updateCommande->bindParam(":vads_ship_to_street",$_POST['vads_ship_to_street']);
						$updateCommande->bindParam(":vads_ship_to_street2",$_POST['vads_ship_to_street2']);
						$updateCommande->bindParam(":vads_ship_to_zip",$_POST['vads_ship_to_zip']);
						$updateCommande->bindParam(":vads_ship_to_city",$_POST['vads_ship_to_city']);
						$updateCommande->bindParam(":vads_ship_to_last_name",$_POST['vads_ship_to_last_name']);
						$updateCommande->bindParam(":vads_ship_to_first_name",$_POST['vads_ship_to_first_name']);
						$updateCommande->bindParam(":vads_ship_to_phone_num",$_POST['vads_ship_to_phone_num']);
						$updateCommande->bindParam(":vads_ship_to_legal_name",$_POST['vads_ship_to_legal_name']);
						$updateCommande->bindParam(":vads_cust_address",$_POST['vads_cust_address']);
						$updateCommande->bindParam(":vads_cust_zip",$_POST['vads_cust_zip']);
						$updateCommande->bindParam(":vads_cust_city",$_POST['vads_cust_city']);
						$updateCommande->bindParam(":vads_cust_last_name",$_POST['vads_cust_last_name']);
						$updateCommande->bindParam(":vads_cust_first_name",$_POST['vads_cust_first_name']);
						$updateCommande->bindParam(":vads_cust_cell_phone",$_POST['vads_cust_cell_phone']);
						$updateCommande->bindParam(":vads_cust_legal_name",$_POST['vads_cust_legal_name']);
						$updateCommande->bindParam(":vads_trans_id",$_POST['vads_trans_id']);
						$updateCommande->bindParam(":vads_order_info3",$_POST['vads_order_info3']);
						$updateCommande->bindParam(":vads_trans_date",$_POST['vads_trans_date']);
						$updateCommande->bindParam(":vads_amount",$_POST['vads_amount']);
						$updateCommande->execute();
						echo "PAIEMENT OK";
					} elseif($_POST['vads_trans_status'] == "REFUSED") {	
						$updateCommande = $bdd->prepare("UPDATE ob_users_commande SET paiement = '2' WHERE id = :id");
						$updateCommande->bindParam(":id",$_POST['vads_order_id']);
						$updateCommande->execute();
						echo "PAIEMENT REFUSE";
					} elseif($_POST['vads_trans_status'] == "ABANDONED") {
						// REMISE EN STOCK DES PRODUITS
						$elements = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
						$elements->bindParam(':commandeid', $_POST['vads_order_id']);
						$elements->execute();
						if($elements->rowCount() > 0) {
							while($e = $elements->fetch(PDO::FETCH_OBJ)) {
								$bdd->query("UPDATE ob_boutique_produit_element SET stock = stock + '".$e->qte."' WHERE id = '".$e->elementid."'");
								$bdd->query("DELETE FROM ob_users_commande_element WHERE id = '".$e->id."'");
							}
						}

						$updateCommande = $bdd->prepare("DELETE FROM ob_users_commande WHERE id = :id");
						$updateCommande->bindParam(":id",$_POST['vads_order_id']);
						$updateCommande->execute();
						echo "PAIEMENT ABANDONNE -> SUPPRESSION";
					} else {
						throw new Exception('Statut de la commande indéfini : '.$_POST['vads_trans_status']);
					}
				} else {
					throw new Exception('Problème de signature.');
				}
			} else {
				throw new Exception('La commande '.$_POST["vads_order_id"].'n\'existe pas.');
			}
	 	} else {
	 		throw new Exception('Erreur de la clé de hashage.');
	 	}
	} else {
		throw new Exception('Aucune donnée reçue.');
	}
?>