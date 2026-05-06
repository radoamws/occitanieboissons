<?php
	require("../../includes/configuration.php");

	$data['file'] = $_FILES;
	$data['text'] = $_POST;

	if(isset($_FILES['image']['name']) && isset($_POST['nom']) && isset($_POST['categorie']) && isset($_POST['element'])) {
		if(empty($_FILES['image']['name']) || empty($_POST['nom']) || empty($_POST['categorie']) || empty($_POST['element'])) {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		} else {
			if($_FILES['image']['error'] > 0) {
				$message = "Erreur lors du transfert de l'image";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["image"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises = array('png','jpeg','jpg');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format de l'image n'est pas supporté. Formats autorisés : png, jpeg, jpg.";
					$couleur = "rouge";
				} else {
			  	// IMPORTATION DE L'IMAGE
			  	// GENERATION NOM IMAGE
					$nom_img = "produit-".$_POST['nom'].".".$extentions;
					$nom_img = str_replace(" ", "", $nom_img);
					$nom_img = str_replace(":", "", $nom_img);
					$nom_img = str_replace("/", "", $nom_img);
					$nom_img = str_replace("\'", "", $nom_img);
					$nom_img = str_replace("'", "", $nom_img);
					$nom_img = str_replace("é", "e", $nom_img);
					$nom_img = str_replace("è", "e", $nom_img);
					$nom_img = str_replace("ë", "e", $nom_img);
					$nom_img = str_replace("à", "a", $nom_img);
					$nom_img = str_replace("…", "", $nom_img);
					$nom_img = str_replace("?", "", $nom_img);
					$nom_img = str_replace("!", "", $nom_img);
					$nom_img = str_replace("ô", "o", $nom_img);
					$nom_img = str_replace("î", "i", $nom_img);
					$nom_img = str_replace("’", "", $nom_img);
					$nom_img = str_replace("ç", "c", $nom_img);
				// GENERATION LIEN IMAGE
					$upload = '../../../gallery/images/upload';
					$image_lien = str_replace("../../../gallery", $url."/gallery", $upload);
					$image_lien = $image_lien."/".$nom_img;
					$tmp_name = $_FILES["image"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$nom_img")) {
						$message = "Erreur lors de l'importation de l'image, veuillez réessayer.";
						$couleur = "rouge";
					} else {

					// HIDE
						if(@$_POST['hide'] == "on") {
							$hide = 1;
						} else {
							$hide = 0;
						}

			  			// CREATION DE L'ACTUALITE
						$create_produit = $bdd->prepare("INSERT INTO ob_boutique_produit (nom, description, image_short, image, time, categorie, auteur, hide, element) VALUES (:nom, :descr, :image_short, :image, :time, :categorie, :auteur, :hide, :element)");
						$create_produit->bindParam(":nom", $_POST['nom']);
						$create_produit->bindParam(":descr", @$_POST['descr']);
						$create_produit->bindParam(":image_short", $nom_img);
						$create_produit->bindParam(":image", $image_lien);
						$create_produit->bindParam(":time", time());
						$create_produit->bindParam(":categorie", $_POST['categorie']);
						$create_produit->bindParam(":auteur", $_SESSION['username']);
						$create_produit->bindParam(":hide", $hide);
						$create_produit->bindParam(":element", $_POST['element']);
						$create_produit->execute();

						$message = "Le produit a bien été ajouté à la boutique.";
						$couleur = "vert";
						$redirect = $admurl."/boutique.php";
					}
				}
			} 
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