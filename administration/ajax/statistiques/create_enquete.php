<?php
  require("../../includes/configuration.php");

  if(isset($_POST['type'])) {
	if(empty($_POST['type'])) {
		$message = "Veuillez remplir les champs vides.";
		$couleur = "rouge";
	} else {
		$enquete = $bdd->prepare("SELECT * FROM ob_enquete_type WHERE type = :type");
		$enquete->bindParam(":type", $_POST['type']);
		$enquete->execute();
		if($enquete->rowCount() > 0) {
			$message = "Cette enquête est déjà existante.";
			$couleur = "rouge";
		} else {
	  		// CREATION DE L'ENQUETE
		  	$create_news = $bdd->prepare("INSERT INTO ob_enquete_type (type) VALUES (:type)");
		  	$create_news->bindParam(":type", $_POST['type']);
		  	$create_news->execute();

		  	$message = "L'enquête a bien été créée.";
		  	$couleur = "vert";
		  	$redirect = $admurl."/index.php";
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