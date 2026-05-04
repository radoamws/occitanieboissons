<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");

 	if(isset($_POST['code_postal'])) {
 		if(empty($_POST['code_postal'])) {
 			$message = "Veuillez remplir les champs vides.";
 			$couleur = "rouge";
 		} else {
 			if(!preg_match('#^([0-9]{5})$#', $_POST['code_postal'])) {
 				$message = "Veuillez entrer un code postal correct.";
 				$couleur = "rouge";
 			} else {
 				$code_postal = substr($_POST['code_postal'], 0, 2);
 				setcookie("CodePostal", $code_postal, time()+7200, '/');
 			}
		}
 	} else {
 		$message = "Une erreur est survenue, veuillez réessayer plus tard."; 
 		$couleur = "rouge";
 	}
 	echo json_encode(array('message' => @$message, 'couleur' => @$couleur, 'redirect' => @$redirect));
?>