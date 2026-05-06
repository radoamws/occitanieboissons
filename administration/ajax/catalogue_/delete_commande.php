<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id'])) {
		$verifCommande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id AND paiement = '0'");
		$verifCommande->bindParam(':id', $_POST['id']);
		$verifCommande->execute();
		if($verifCommande->rowCount() > 0) {
			$elements = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
			$elements->bindParam(':commandeid', $_POST['id']);
			$elements->execute();
			if($elements->rowCount() > 0) {
				while($e = $elements->fetch(PDO::FETCH_OBJ)) {
					$bdd->query("UPDATE ob_boutique_produit_element SET stock = stock + '".$e->qte."' WHERE id = '".$e->elementid."'");
					$bdd->query("DELETE FROM ob_users_commande_element WHERE id = '".$e->id."'");
				}
			}
			// SUPRESSION DE LA COMMANDE
			$delete = $bdd->prepare("DELETE FROM ob_users_commande WHERE id = :id");
			$delete->bindParam(':id', $_POST['id']);
			$delete->execute();
		}
	}
?>