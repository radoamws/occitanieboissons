<?php 
 	require("../../includes/configuration.php");
 	require("../../includes/functions.php");

 	switch($_GET['type']) {
 		case "prix_ht":
 			echo PrixPanier("ht");
 		break;
 		case "prix_ttc":
 			echo PrixPanier("ttc");
 		break;
 		case "prix_droits":
 			echo PrixPanier("droits");
 		break;
 		case "articles":
 			echo ArticlePanier();
 		break;
 	}
?>