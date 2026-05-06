<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	$data['file'] = $_FILES;
	$data['text'] = $_POST;

	if(isset($_FILES['image']['name']) && isset($_FILES['logo']['name']) && isset($_POST['nom']) && isset($_POST['country']) && isset($_POST['contenu'])) {
		if(empty($_FILES['image']['name']) || empty($_FILES['logo']['name']) || empty($_POST['nom']) || empty($_POST['country']) || empty($_POST['contenu'])) {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		} else {
	  		// FORMAT IMAGE
			$info = pathinfo($_FILES["image"]["name"]);
			$extentions = $info["extension"];
			$extentionsAutorises = array('png','jpeg','jpg','webp');
			if(!in_array($extentions,$extentionsAutorises)) {
				$message = "Le format de l'image n'est pas supporté. Format autorisés : png, jpeg, jpg, webp.";
				$couleur = "rouge";
			} else {
	  			// GENERATION NOM IMAGE
					$nom_img = ImageNom($_FILES["image"]["name"],$extentions);
					// GENERATION LIEN IMAGE
						$upload = '../../../upload/brasseries';
						$image_lien = str_replace("../../../upload/brasseries", $img_brasseries_url, $upload);
						$image_lien = $image_lien."/".$nom_img;
						$tmp_name = $_FILES["image"]["tmp_name"];
				if(!move_uploaded_file($tmp_name, "$upload/$nom_img")) {
					$message = "Erreur lors de l'importation de l'image, veuillez réessayer.";
					$couleur = "rouge";
				} else {
					// FORMAT LOGO
					$info = pathinfo($_FILES["logo"]["name"]);
					$extentions = $info["extension"];
					$extentionsAutorises = array('png','jpeg','jpg','webp');
					if(!in_array($extentions,$extentionsAutorises)) {
						$message = "Le format du logo n'est pas supporté. Format autorisés : png, jpeg, jpg, webp.";
						$couleur = "rouge";
					} else {
						// IMPORTATION DU LOGO
						  	// GENERATION NOM LOGO
							$nom_logo = ImageNom($_FILES["logo"]["name"],$extentions);
							// GENERATION LIEN IMAGE
							$upload = '../../../upload/brasseries';
							$logo_lien = str_replace("../../../upload/brasseries", $img_brasseries_url, $upload);
							$logo_lien = $logo_lien."/".$nom_logo;
							$tmp_logo = $_FILES["logo"]["tmp_name"];
						if(!move_uploaded_file($tmp_logo, "$upload/$nom_logo")) {
							$message = "Erreur lors de l'importation du logo, veuillez réessayer.";
							$couleur = "rouge";
						} else {
							// CATALOGUE ACTIVE
							if(@$_POST['catalogue_active'] == "on") {
								$catalogue_active = 1;
							} else {
								$catalogue_active = 0;
							}

			  				// CREATION DE LA BRASSERIE
							$create_brasserie = $bdd->prepare("INSERT INTO ob_brasseries (name, country, image_short, image_url, logo_short, logo_url, date_time, content, author, hiden, id_fabriquant, logo_alt, image_alt, meta_title, meta_description, anchor, title) VALUES (:name, :country, :image_short, :image, :logo_short, :logo, NOW(), :contenu, :auteur, :catalogue_active, :id_fabriquant, :logo_alt, :image_alt, :meta_title, :meta_description, :ancre, :title)");
							$create_brasserie->bindParam(":name", $_POST['nom']);
							$create_brasserie->bindParam(":country", $_POST['country']);
							$create_brasserie->bindParam(":image_short", $nom_img);
							$create_brasserie->bindParam(":image", $image_lien);
							$create_brasserie->bindParam(":logo_short", $nom_logo);
							$create_brasserie->bindParam(":logo", $logo_lien);
							$create_brasserie->bindParam(":contenu", $_POST['contenu']);
							$create_brasserie->bindParam(":auteur", $_SESSION['username']);
							$create_brasserie->bindParam(":logo_alt", $_POST['logo_alt']);
							$create_brasserie->bindParam(":image_alt", $_POST['image_alt']);
							$create_brasserie->bindParam(":meta_title", $_POST['meta_title']);
							$create_brasserie->bindParam(":meta_description", $_POST['meta_description']);
							$create_brasserie->bindParam(":ancre", $_POST['ancre']);
							$create_brasserie->bindParam(":title", $_POST['title']);
							$create_brasserie->bindParam(":catalogue_active", $catalogue_active);
							$create_brasserie->bindParam(":id_fabriquant", $_POST['id_fabriquant']);
							$create_brasserie->execute();

							$message = "La brasserie a bien été créée.";
							$couleur = "vert";
							$redirect = $admurl."/brasseries.php";
						}
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