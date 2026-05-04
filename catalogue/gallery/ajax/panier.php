<?php 
 	require("../../includes/configuration.php");

	if(isset($_POST['id']) && isset($_POST['quantite'])) {
		$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
		$element->bindParam(":id", $_POST['id']);
		$element->execute();
		$e = $element->fetch(PDO::FETCH_OBJ);
		// EXISTENCE DU PRODUIT
		if($element->rowCount() < 1) {
			$message = "Oops ! Le produit que vous avez séléctionné n'est plus disponible."; 
 			$couleur = "rouge";
		} else {
			// VERIFICATION STOCK
			if($_POST['quantite'] > floor($e->stock/$e->uv_caisse) && $e->marque != 2) {$_POST['quantite'] = floor($e->stock/$e->uv_caisse);}
			
			// AJOUT AU PANIER
			if(isset($_SESSION['site'])) {
				$panier = json_decode($u->panier, true);
				// MODIFICATION QTE PANIER
				$UpdateQte = FALSE;
				$newpanier = array();
				foreach($panier as $p) {
					foreach($p as $p2 => $qte) {
						if($p2 == $_POST['id']) {
							$qte = $_POST['quantite'];
							// VERIFICATION QUANTITE
							if($qte > floor($e->stock/$e->uv_caisse) && $e->marque != 2) {$qte=floor($e->stock/$e->uv_caisse);}
							$UpdateQte = TRUE;
						}
						if($qte > 0) {$newpanier[] = array($p2 => $qte);}
					}
				}

				if(!$UpdateQte) {
					$panier[] = array($_POST['id'] => $_POST['quantite']);
					setcookie("panier",json_encode($panier),time()+60*60*24*30, '/');
					$modificationPanier = $bdd->query("UPDATE ob_users SET panier = '".json_encode($panier)."' WHERE id = '".$u->id."'");
				} else {
					$modificationPanier = $bdd->query("UPDATE ob_users SET panier = '".json_encode($newpanier)."' WHERE id = '".$u->id."'");
					setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
				}
			} else {
				if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
				// MODIFICATION QTE PANIER
				$UpdateQte = FALSE;
				$newpanier = array();
				foreach($panier as $p) {
					foreach($p as $p2 => $qte) {
						if($p2 == $_POST['id']) {
							$qte = $_POST['quantite'];
							// VERIFICATION QUANTITE
							if($qte > floor($e->stock/$e->uv_caisse) && $e->marque != 2) {$qte=floor($e->stock/$e->uv_caisse);}
							$UpdateQte = TRUE;
						}
						if($qte > 0) {$newpanier[] = array($p2 => $qte);}
					}
				}

				if(!$UpdateQte) {
					$panier[] = array($_POST['id'] => $_POST['quantite']);
					setcookie("panier",json_encode($panier),time()+60*60*24*30, '/');
				} else {
					setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
				}
			}
			$couleur = "vert";
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
 			'qte' => $_POST['quantite']
 		)
 	);
?>