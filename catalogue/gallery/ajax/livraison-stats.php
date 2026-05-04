<?php 
 	require("../../includes/configuration.php");
 	require("../../includes/functions.php");

 	if(isset($_COOKIE['CodePostal'])) {
 		echo number_format(LivraisonPrix($_COOKIE['CodePostal']), 2, ',', ' ')."€";
 	}
?>