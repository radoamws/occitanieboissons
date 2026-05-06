<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

	if(isset($_POST['id'])) {
		$data['file'] = $_FILES;
		$data['text'] = $_POST;

		if(isset($_FILES['image']['name']) && isset($_POST['titre'])) {
			if($_FILES['image']['error'] > 0) {
				$message = "Erreur lors du transfert de l'image";
				$couleur = "rouge";
			} else {
				// FORMAT IMAGE
				$info = pathinfo($_FILES["image"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises=array('png','jpeg','jpg','webp');
				if(!in_array($extentions,$extentionsAutorises)) {
					$message = "Le format de l'image n'est pas supporté. Format autorisés : png, jpeg, jpg, webp.";
					$couleur = "rouge";
				} else {	
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
						$modif_img_news = $bdd->prepare("UPDATE ob_actualites SET image_short = :imageshort, image = :image WHERE id = :id");
						$modif_img_news->bindParam(":imageshort", $nom_img);
						$modif_img_news->bindParam(":image", $image_lien);
						$modif_img_news->bindParam(":id", $_POST['id']);
						$modif_img_news->execute();
					}
				}
			}
		}

		if(isset($_POST['titre']) && isset($_POST['contenu']) && isset($_POST['tags'])) {
			if(empty($_POST['titre']) || empty($_POST['contenu']) || empty($_POST['tags'])) {
				$message = "Veuillez remplir les champs vides.";
				$couleur = "rouge";
			} else {
				// GESTION DES TAGS
				$taglist = array();
				$tags = explode(",", $_POST['tags']);
				foreach($tags as $t) {
					if(!intval($t)) {
						$exist_t = $bdd->prepare("SELECT null FROM ob_motsclefs WHERE motclef = :motclef");
						$exist_t->bindParam(":motclef", $t);
						$exist_t->execute();
						if($exist_t->rowCount() < 1) {
							$insert_t = $bdd->prepare("INSERT INTO ob_motsclefs (motclef) VALUES (:motclef)");
							$insert_t->bindParam(":motclef", $t);
							$insert_t->execute();
							$taglist[] = $bdd->lastInsertId();
						}
					} else {
						$taglist[] = $t;
					}
				}

				// HIDE
				if(@$_POST['hide'] == "on") {
					$hide = 1;
				} else {
					$hide = 0;
				}

				// MODIFICATION DE L'ACTUALITE
				$modif_news = $bdd->prepare("UPDATE ob_actualites SET titre = :titre, contenu = :contenu, taglist = :taglist, hide = '".$hide."' WHERE id = :id");
				$modif_news->bindParam(":titre", $_POST['titre']);
				$modif_news->bindParam(":taglist", implode(", ", $taglist));
				$modif_news->bindParam(":contenu", $_POST['contenu']);
				$modif_news->bindParam(":id", $_POST['id']);
				$modif_news->execute();

				$message = "L'actualité a bien été modifiée.";
				$couleur = "vert";
				$redirect = $admurl."/actualites.php";
			}
		} 
	}

	echo json_encode(array(
		'message' => $message, 
		'couleur' => $couleur,
		'redirect' => @$redirect
	));
?>