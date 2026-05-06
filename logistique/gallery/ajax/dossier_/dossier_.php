<?php
	require("../../../includes/configuration.php");
	require("../../../includes/functions.php");

	if(!isset($_SESSION['site'])) {
		echo json_encode(array('redirect' => $url.'/connexion/'));
		exit();
	}
	
 	$data['file'] = $_FILES;
	$data['text'] = $_POST;

	$importation_certificat = TRUE;
	if(isset($_FILES['certificat']['name']) && !empty($_FILES['certificat']['name'])) {
		if($_FILES['certificat']['error'] > 0) {
			$importation_certificat = FALSE;
			$message = "Erreur lors du transfert du certificat médical.";
			$couleur = "rouge";
		} else {
			$info = pathinfo($_FILES["certificat"]["name"]);
			$extentions = $info["extension"];
			$extentionsAutorises = array('png','jpeg','jpg','pdf');
			if(!in_array($extentions,$extentionsAutorises)) {
				$importation_certificat = FALSE;
				$message = "Le format de l'image n'est pas supporté. Formats autorisés : png, jpeg, jpg, pdf.";
				$couleur = "rouge";
			} else {
		  	// IMPORTATION DE L'IMAGE
		  	// GENERATION NOM IMAGE
				$nom_certificat = FichierNom($_POST['nom'].$_POST['prenom'].'-certificatmedical',$extentions);
			// GENERATION LIEN IMAGE
				$upload = '../../images/upload';
				$certificat_lien = str_replace("../..", $url."/gallery", $upload);
				$certificat_lien = $certificat_lien."/".$nom_certificat;
				$tmp_name = $_FILES["certificat"]["tmp_name"];
				if(!move_uploaded_file($tmp_name, "$upload/$nom_certificat")) {
					$importation_certificat = FALSE;
					$message = "Il y a eu une erreur lors de l'importation du certificat médical, veuillez réessayer.";
					$couleur = "rouge";
				}
			}
		}
	}

	if($importation_certificat) {
		$importation_mineur = TRUE;
		if(isset($_FILES['mineur']['name']) && !empty($_FILES['mineur']['name'])) {	
			if($_FILES['mineur']['error'] > 0) {
				$importation_mineur = FALSE;
				$message = "Erreur lors du transfert du justificat pour mineur.";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["mineur"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises = array('png','jpeg','jpg','pdf');
				if(!in_array($extentions,$extentionsAutorises)) {
					$importation_mineur = FALSE;
					$message = "Le format du justificat pour mineur n'est pas supporté. Formats autorisés : png, jpeg, jpg, pdf.";
					$couleur = "rouge";
				} else {
			  	// IMPORTATION DE L'IMAGE
			  	// GENERATION NOM IMAGE
					$nom_mineur = FichierNom($_POST['nom'].$_POST['prenom'].'-mineur',$extentions);
				// GENERATION LIEN IMAGE
					$upload = '../../images/upload';
					$mineur_lien = str_replace("../..", $url."/gallery", $upload);
					$mineur_lien = $mineur_lien."/".$nom_mineur;
					$tmp_name = $_FILES["mineur"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$nom_mineur")) {
						$importation_mineur = FALSE;
						$message = "Il y a eu une erreur lors de l'importation du justificatif pour mineur, veuillez réessayer.";
						$couleur = "rouge";
					}
				}
			}
		}
	}

	if($importation_certificat && $importation_mineur) {
		$importation_assurance = TRUE;
		if(isset($_FILES['assurance']['name']) && !empty($_FILES['assurance']['name'])) {	
			if($_FILES['assurance']['error'] > 0) {
				$importation_assurance = FALSE;
				$message = "Erreur lors du transfert du justificat d'assurance.";
				$couleur = "rouge";
			} else {
				$info = pathinfo($_FILES["assurance"]["name"]);
				$extentions = $info["extension"];
				$extentionsAutorises = array('png','jpeg','jpg','pdf');
				if(!in_array($extentions,$extentionsAutorises)) {
					$importation_assurance = FALSE;
					$message = "Le format du justificat d'assurance n'est pas supporté. Formats autorisés : png, jpeg, jpg, pdf.";
					$couleur = "rouge";
				} else {
			  	// IMPORTATION DE L'IMAGE
			  	// GENERATION NOM IMAGE
					$assurance_img = FichierNom($_POST['nom'].$_POST['prenom'].'-assurance',$extentions);
				// GENERATION LIEN IMAGE
					$upload = '../../images/upload';
					$assurance_lien = str_replace("../..", $url."/gallery", $upload);
					$assurance_lien = $assurance_lien."/".$assurance_img;
					$tmp_name = $_FILES["assurance"]["tmp_name"];
					if(!move_uploaded_file($tmp_name, "$upload/$assurance_img")) {
						$importation_assurance = FALSE;
						$message = "Il y a eu une erreur lors de l'importation du justificatif d'assurance, veuillez réessayer.";
						$couleur = "rouge";
					}
				}
			}
		}
	}

	if($importation_certificat && $importation_mineur && $importation_assurance) {
	 	if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['phone']) && isset($_POST['naissance']) && isset($_POST['site'])  && isset($_POST['etudiant'])) {
	 		if(empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['naissance']) || empty($_POST['site']) || empty($_POST['etudiant'])) {
	 			$message = "Veuillez remplir les informations importants.";
	 			$couleur = "rouge";
	 		} else {
				$filter = preg_replace("/[^a-z \s -à]/i", "", $_POST['nom']);
				if($filter !== $_POST['nom']) {
					$message = "Veuillez entrer un nom valide.";
					$couleur = "rouge";
				} else {
	 				$filter = preg_replace("/[^a-z \s -à]/i", "", $_POST['prenom']);
		 			if($filter !== $_POST['prenom']) {
		 				$message = "Veuillez entrer un prénom valide.";
		 				$couleur = "rouge";
		 			} else {
		 				if(!preg_match("#\+[0-9]{2}[0-9 \s]#", $_POST['phone'])) {
	 						$message = "Veuillez entrer un numéro de téléphone valide.";
	 						$couleur = "rouge";
	 					} else {
	 						$value = array("Bordeaux","Valence","Toulouse","Nancy","Grenoble");
	 						if(!in_array($_POST['site'], $value)) {
	 							$message = "Le site de Prépa INP sélectionné n'est pas valide.";
	 							$couleur = "rouge";
	 						} else {
	 							if($_POST['etudiant'] == 1 || $_POST['etudiant'] == 2 || $_POST['etudiant'] == 3) {
	 								$etudiant = $_POST['etudiant'];
	 							} else {
	 								$etudiant = 0;
	 							}

					 			### MODIFICATION DU DOSSIER
					 			$modifDossier = $bdd->prepare("UPDATE cppiades_users SET nom = :nom, prenom = :prenom, ip_lastconnexion = '".$_SERVER['REMOTE_ADDR']."', phone = :phone, naissance = :naissance, site = :site, contre_indication = :contre_indication, allergies = :allergies, alimentation = :alimentation, handicap = :handicap, information = :information, certificat = :certificat, mineur = :mineur, assurance = :assurance, nombre_validation = nombre_validation + 1, etudiant = '".$etudiant."', depart = :depart WHERE id = '".$u->id."'");
					 			$modifDossier->bindParam(":nom", $_POST['nom']);
								$modifDossier->bindParam(":prenom", $_POST['prenom']);					
								$modifDossier->bindParam(":phone", $_POST['phone']);
								$modifDossier->bindParam(":naissance", $_POST['naissance']);
								$modifDossier->bindParam(":site", $_POST['site']);
								$modifDossier->bindParam(":contre_indication", $_POST['contre_indication']);
								$modifDossier->bindParam(":allergies", $_POST['allergies']);
								$modifDossier->bindParam(":alimentation", $_POST['alimentation']);
								$modifDossier->bindParam(":handicap", $_POST['handicap']);
								$modifDossier->bindParam(":information", $_POST['information']);
								$modifDossier->bindParam(":certificat", $certificat_lien);
								$modifDossier->bindParam(":mineur", $mineur_lien);
								$modifDossier->bindParam(":assurance", $assurance_lien);
								$modifDossier->bindParam(":depart", $_POST['depart']);
								$modifDossier->execute();

								$message = "Ton dossier a bien été mis à jour."; 
								$couleur = "vert";
			 				}
		 				}
	 				}
	 			}
	 		}  
	 	} else {
	 		$message = "Une erreur est survenue, veuillez réessayer."; 
	 		$couleur = "rouge";
	 	}
 	} 

 	echo json_encode(array('message' => $message, 'couleur' => $couleur, 'redirect' => @$redirect));
?>