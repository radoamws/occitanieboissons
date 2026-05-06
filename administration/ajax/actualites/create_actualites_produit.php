<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");
	
	$data['file'] = $_FILES;
	$data['text'] = $_POST;

	if(isset($_FILES['image']['name']) && isset($_POST['titre']) && isset($_POST['brasseries']) && isset($_POST['contenu']) && isset($_POST['tags'])) {
		if(empty($_FILES['image']['name']) || empty($_POST['titre']) || empty($_POST['brasseries']) || empty($_POST['contenu']) || empty($_POST['tags'])) {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		} else {
			if($_FILES['image']['error'] > 0) {
				$message = "Erreur lors du transfert de l'image";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["image"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises = array('png','jpeg','jpg','webp');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format de l'image n'est pas supporté. Formats autorisés : png, jpeg, jpg, webp.";
					$couleur = "rouge";
				} else {
			  	// IMPORTATION DE L'IMAGE
			  	// GENERATION NOM IMAGE
					$nom_img = ImageNom($_POST['titre'],$extentions);
				// GENERATION LIEN IMAGE
					$upload = '../../../www/gallery/images/upload';
					$image_lien = str_replace("../../../www/gallery", $gallery, $upload);
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

				  	// GESTION DES TAGS
						$tags = explode(",", $_POST['tags']);
						
						$taglist = array();
						foreach($tags as $t) {
							if(!intval($t)) {
								$id = $bdd->prepare("SELECT null FROM ob_motsclefs WHERE motclef = :motclef");
								$id->bindParam(":motclef", $t);
								$id->execute();
								if($id->rowCount() < 1) {
									$insert_tags = $bdd->prepare("INSERT INTO ob_motsclefs (motclef) VALUES (:motclef)");
									$insert_tags->bindParam(":motclef", $t);
									$insert_tags->execute();
									$taglist[] = $bdd->lastInsertId();
								}
							} else {
								$taglist[] = $t;
							}
						}

						$brasseries = explode(",", $_POST['brasseries']);

						$brasseries_contenu = array();
						foreach($brasseries as $b) {
							if((intval($b)) && (!array_key_exists($b, $taglist))) $taglist[] = $b;
						}

			  			// CREATION DE L'ACTUALITE
						$create_actualites = $bdd->prepare("INSERT INTO ob_actualites (titre, image_short, image, time, contenu, auteur, taglist, brasseries, hide, element) VALUES (:titre, :image_short, :image, :time, :contenu, :auteur, :taglist, :brasseries, :hide, :element)");
						$create_actualites->bindParam(":titre", $_POST['titre']);
						$create_actualites->bindParam(":image_short", $nom_img);
						$create_actualites->bindParam(":image", $image_lien);
						$create_actualites->bindParam(":time", time());
						$create_actualites->bindParam(":contenu", $_POST['contenu']);
						$create_actualites->bindParam(":auteur", $_SESSION['username']);
						$create_actualites->bindParam(":taglist", implode(", ", $taglist));
						$create_actualites->bindParam(":brasseries", $_POST['brasseries']);
						$create_actualites->bindParam(":element", $_POST['element']);
						$create_actualites->bindParam(":hide", $hide);
						$create_actualites->execute();

						$message = "L'actualité a bien été créée.";
						$couleur = "vert";
						$redirect = $admurl."/actualites.php";
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