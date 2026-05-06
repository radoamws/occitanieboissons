<?php
	require("../../includes/configuration.php");

	if(isset($_GET['action']) && isset($_POST['id']) && isset($_POST['modif'])) {
		$verifElement = $bdd->prepare("SELECT * FROM ob_boutique_produit_element WHERE id = :id");
		$verifElement->bindParam(":id", $_POST['id']);
		$verifElement->execute();
		if($verifElement->rowCount() > 0) {
			switch($_GET['action']) {
				case "nom":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET nom = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", $_POST['modif']);
					$elementModif->execute();
				break;
				case "stock":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET stock = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", $_POST['modif']);
					$elementModif->execute();
				break;
				case "prixht":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET prixht = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", str_replace(',', '.', $_POST['modif']));
					$elementModif->execute();
				break;
				case "alcool":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET alcool = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", str_replace(',', '.', $_POST['modif']));
					$elementModif->execute();
				break;
				case "contenance":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET contenance = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", $_POST['modif']);
					$elementModif->execute();
				break;
				case "tva":
					$elementModif = $bdd->prepare("UPDATE ob_boutique_produit_element SET tva = :modification WHERE id = :id");
					$elementModif->bindParam(":id", $_POST['id']);
					$elementModif->bindParam(":modification", $_POST['modif']);
					$elementModif->execute();
				break;
			}
			$couleur = "vert";
		}  else {
			$message = "Une erreur est survenue, veuillez réessayer plus tard.";
			$couleur = "rouge";
		}
	} else {
		$message = "Une erreur est survenue, veuillez réessayer plus tard.";
		$couleur = "rouge";
	}

	echo json_encode(array(
		'message' => $message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>