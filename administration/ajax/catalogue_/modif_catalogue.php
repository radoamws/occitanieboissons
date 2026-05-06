<?php
	require("../../includes/configuration.php");

	$data['file'] = $_FILES;
	$data['text'] = $_POST;

	if(isset($_FILES['catalogue']['name'])) {
		if(empty($_FILES['catalogue']['name'])) {
			$message = "Veuillez remplir les champs vides.";
			$couleur = "rouge";
		} else {
			$info = pathinfo($_FILES["catalogue"]["name"]);
			$extentions = $info["extension"];
			$extentionsAutorises = array('pdf,xlsx');
			if(!in_array($extentions,$extentionsAutorises)) {
				$message = "Le format du catalogue n'est pas supporté. Format autorisé : pdf,xlsx.";
				$couleur = "rouge";
			} else {
			  // IMPORTATION DU CATALOGUE
			  	// GENERATION NOM CATALOGUE
					$nom_catalogue = "Catalogue_OCCITANIE_BOISSONS.".$info["extension"];
				// GENERATION LIEN CATALOGUE
					$upload = '../../../gallery/catalogue/';
					$pdf_lien = str_replace("../../../gallery", $image_url."/gallery", $upload);
					$pdf_lien = $pdf_lien."/".$nom_catalogue;
					$tmp_name = $_FILES["catalogue"]["tmp_name"];
				if(!move_uploaded_file($tmp_name, "$upload/$nom_catalogue")) {
					$message = "Erreur lors de l'importation du catalogue, veuillez réessayer.";
					$couleur = "rouge";
				} else {
					$catalogue = $bdd->prepare("UPDATE ob_catalogue SET cookie = cookie + 1, lien = :lien WHERE type = 'national'");
					$catalogue->bindParam(":lien", $pdf_lien);
					$catalogue->execute();

					$c = $catalogue->fetch(PDO::FETCH_OBJ);

					$message = "Le catalogue a bien été modifié.".$c->cookie;
					$couleur = "vert";
					$redirect = $admurl."/catalogue.php";
				}
			} 
		}	  
	} else {
		$message = "Une erreur est survenue, veuillez réessayer plus tard.";
		$couleur = "rouge";
	}

	echo json_encode(array(
		'message' => $message, 
		'couleur' => $couleur
	));
?>