<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id'])) {
		$verifCommande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id");
		$verifCommande->bindParam(':id', $_POST['id']);
		$verifCommande->execute();
		if($verifCommande->rowCount() > 0) {
			$v = $verifCommande->fetch(PDO::FETCH_OBJ);
			if($v->paiment == 0 || $v->paiement == 2) {
				// ON CACHE LA COMMANDE
				$hideCommande = $bdd->prepare("UPDATE ob_users_commande SET hide = '1' WHERE id = :id");
				$hideCommande->bindParam(':id', $c->id);
				$hideCommande->execute();
				$pagename = "Commandes";
			}
		}
	}
?>