<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	if(isset($_POST['id'])) {
		$data['file'] = $_FILES;
		$data['text'] = $_POST;

		if(isset($_FILES['image']['name']) && $_FILES['image']['name'] && isset($_POST['id'])) {
			if($_FILES['image']['error'] > 0) {
				$message = "Erreur lors du transfert de l'image";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["image"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises = array('png','jpeg','jpg','webp');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format de l'image n'est pas supporté. Format autorisés : png, jpeg, jpg, webp.";
					$couleur = "rouge";
				} else {
					// GENERATION NOM IMAGE
						$nom_img = ImageNom($_FILES['image']['name'],$extentions);
					// GENERATION LIEN IMAGE
						$upload = '../../../upload/brasseries';
						$image_lien = str_replace("../../../upload/brasseries", $img_brasseries_url, $upload);
						$image_lien = $image_lien."/".$nom_img;
						$tmp_name = $_FILES["image"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$nom_img")) {
						$message = "Erreur lors de l'importation de l'image, veuillez réessayer.";
						$couleur = "rouge";
					} else {	
						$modif_img_news = $bdd->prepare("UPDATE ob_brasseries SET image_short = :imageshort, image_url = :image WHERE id = :id");
						$modif_img_news->bindParam(":imageshort", $nom_img);
						$modif_img_news->bindParam(":image", $image_lien);
						$modif_img_news->bindParam(":id", $_POST['id']);
						$modif_img_news->execute();
					}
				}
			}
		}

		if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'] && isset($_POST['id'])) {
			if($_FILES['logo']['error'] > 0) {
				$message = "Erreur lors du transfert du logo.";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["logo"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises=array('png','jpeg','jpg');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format du logo n'est pas supporté. Format autorisés : png, jpeg, jpg.";
					$couleur = "rouge";
				} else {	
				// GENERATION NOM IMAGE
					$nom_logo = ImageNom($_FILES['logo']['name'],$extentions);
				// GENERATION LIEN IMAGE
					$upload = '../../../upload/brasseries';
					$logo_lien = str_replace("../../../upload/brasseries", $img_brasseries_url, $upload);
					$logo_lien = $logo_lien."/".$nom_logo;
					$tmp_name = $_FILES["logo"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$nom_logo")) {
						$message = "Erreur lors de l'importation de l'image, veuillez réessayer.";
						$couleur = "rouge";
					} else {	
						$modif_img_news = $bdd->prepare("UPDATE ob_brasseries SET logo_short = :logoshort, logo_url = :logo WHERE id = :id");
						$modif_img_news->bindParam(":logoshort", $nom_logo);
						$modif_img_news->bindParam(":logo", $logo_lien);
						$modif_img_news->bindParam(":id", $_POST['id']);
						$modif_img_news->execute();
					}
				}
			}
		}

		if(isset($_POST['nom']) && isset($_POST['pays']) && isset($_POST['contenu'])) {
			if(empty($_POST['nom']) || empty($_POST['pays']) || empty($_POST['contenu'])) {
				$message = "Veuillez remplir les champs vides.";
				$couleur = "rouge";
			} else {
				// CATALOGUE ACTIVE
				if(@$_POST['catalogue_active'] == "on") {
					$catalogue_active = 1;
				} else {
					$catalogue_active = 0;
				}

				// MODIFICATION DE L'ACTUALITE
				$modif_news = $bdd->prepare("UPDATE ob_brasseries SET name = :nom, country = :country, content = :contenu, hiden = '".$catalogue_active."', id_fabriquant = :id_fabriquant, logo_alt = :logo_alt, image_alt = :image_alt, meta_title = :meta_title, meta_description = :meta_description, anchor = :ancre, title = :title WHERE id = :id");
				$modif_news->bindParam(":nom", $_POST['nom']);
				$modif_news->bindParam(":country", $_POST['pays']);
				$modif_news->bindParam(":contenu", $_POST['contenu']);
				$modif_news->bindParam(":logo_alt", $_POST['logo_alt']);
				$modif_news->bindParam(":image_alt", $_POST['image_alt']);
				$modif_news->bindParam(":meta_title", $_POST['meta_title']);
				$modif_news->bindParam(":meta_description", $_POST['meta_description']);
				$modif_news->bindParam(":ancre", $_POST['ancre']);
				$modif_news->bindParam(":title", $_POST['title']);
				$modif_news->bindParam(":id_fabriquant", $_POST['id_fabriquant']);
				$modif_news->bindParam(":id", $_POST['id']);
				$modif_news->execute();

				$message = "La brasserie a bien été modifiée.";
				$couleur = "vert";
				$redirect = $admurl."/brasseries.php";
			}
		} else {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		}
	} else {
		$message = "Brasserie non identifiée !!!";
		$couleur = "rouge";
	}

	echo json_encode(array(
		'message' => $message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>