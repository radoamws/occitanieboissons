<?php 
 	require("../../includes/configuration.php");
 	require("../../includes/functions.php");

 	if(isset($_POST['quantite']) && isset($_POST['id'])) {
 		$element = $bdd->prepare("SELECT * FROM ob_boutique_produit_element WHERE id = :id");
		$element->bindParam(":id", $_POST['id']);
		$element->execute();
		$e = $element->fetch(PDO::FETCH_OBJ);
		if($element->rowCount() > 0) {
			$quantite = $_POST['quantite'];
			if($quantite < 1) {
				$quantite = 1;
				$message = "La quantité saisie ne peut pas être inférieure à 1."; 
	 			$couleur = "rouge";
			} else {
				if($quantite > $e->stock) {$quantite = $e->stock;$couleur = "rouge";$message = "La quantité saisie est supérieure au stock disponible.";}

				if(isset($_SESSION['site'])) {
					$panier = json_decode($u->panier, true);
				} else {
					if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
				}
					
				$newpanier = array();
				foreach($panier as $p) {
					foreach($p as $p2 => $qte) {
						if($p2 == $_POST['id']) {$qte = $quantite;}
						$newpanier[] = array($p2 => $qte);
					}
				}

				if(isset($_SESSION['site'])) {
					$modificationPanier = $bdd->query("UPDATE ob_users SET panier = '".json_encode($newpanier)."' WHERE id = '".$u->id."'");
				} else {
					setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
				}
			}
		} else {
	  		$message = "Oops ! L'article n'est plus disponible."; 
	 		$couleur = "rouge";
	  	}
 	} else {
  		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 		$couleur = "rouge";
  	}

 	echo json_encode(
 		array(
 			'message' => @$message,
 			'couleur' => @$couleur,
 			'redirect' => @$redirect,
 			'quantite' => @$quantite,
 		)
 	);
?>