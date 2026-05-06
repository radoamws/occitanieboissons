<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id'])) {
		$data['file'] = $_FILES;
		$data['text'] = $_POST;

		if(isset($_FILES['image']['name']) && isset($_POST['nom'])) {
			if($_FILES['image']['error'] > 0) {
				$message = "Erreur lors du transfert de l'image";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["image"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises=array('png','jpeg','jpg');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format de l'image n'est pas supporté. Format autorisés : png, jpeg, jpg.";
					$couleur = "rouge";
				} else {	
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
					$image_lien = str_replace("../../../gallery", $gallery, $upload);
					$image_lien = $image_lien."/".$nom_img;
					$tmp_name = $_FILES["image"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$nom_img")) {
						$message = "Erreur lors de l'importation de l'image, veuillez réessayer.";
						$couleur = "rouge";
					} else {	
						$modif_img_produit = $bdd->prepare("UPDATE ob_boutique_produit SET image_short = :imageshort, image = :image WHERE id = :id");
						$modif_img_produit->bindParam(":imageshort", $nom_img);
						$modif_img_produit->bindParam(":image", $image_lien);
						$modif_img_produit->bindParam(":id", $_POST['id']);
						$modif_img_produit->execute();
					}
				}
			}
		}

		if(isset($_POST['nom']) && isset($_POST['categorie']) && isset($_POST['element'])) {
			if(empty($_POST['nom']) || empty($_POST['categorie']) || empty($_POST['element'])) {
				$message = "Veuillez remplir les champs vides.";
				$couleur = "rouge";
			} else {
				// HIDE
				if(@$_POST['hide'] == "on") {
					$hide = 1;
				} else {
					$hide = 0;
				}
				
				// MODIFICATION DU PRODUIT
				$modif_produit = $bdd->prepare("UPDATE ob_boutique_produit SET nom = :nom, description = :descr, categorie = :categorie, element = :element, hide = '".$hide."' WHERE id = :id");
				$modif_produit->bindParam(":nom", $_POST['nom']);
				$modif_produit->bindParam(":descr", @$_POST['descr']);
				$modif_produit->bindParam(":categorie", $_POST['categorie']);
				$modif_produit->bindParam(":element", $_POST['element']);
				$modif_produit->bindParam(":id", $_POST['id']);
				$modif_produit->execute();

				$message = "Le produit a bien été modifié.";
				$couleur = "vert";
				$redirect = $admurl."/boutique.php";
			}
		} 
	} else {
		$message = "Une erreur est survenue, veuillez réessayer plus tard.";
		$couleur = "rouge";
	}

	echo json_encode(array(
		'message' => @$message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>