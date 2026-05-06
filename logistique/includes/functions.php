<?php
	function FichierNom($titre,$extentions) {
		$nom_img = $titre.".".$extentions;
		$nom_img = str_replace(" ", "", $nom_img);
		$nom_img = str_replace(":", "", $nom_img);
		$nom_img = str_replace("/", "", $nom_img);
		$nom_img = str_replace("\'", "", $nom_img);
		$nom_img = str_replace("'", "", $nom_img);
		$nom_img = str_replace("é", "e", $nom_img);
		$nom_img = str_replace("è", "e", $nom_img);
		$nom_img = str_replace("ë", "e", $nom_img);
		$nom_img = str_replace("à", "a", $nom_img);
		$nom_img = str_replace("…", "", $nom_img);
		$nom_img = str_replace("?", "", $nom_img);
		$nom_img = str_replace("!", "", $nom_img);
		$nom_img = str_replace("ô", "o", $nom_img);
		$nom_img = str_replace("î", "i", $nom_img);
		$nom_img = str_replace("’", "", $nom_img);
		$nom_img = str_replace("ç", "c", $nom_img);
		$nom_img = str_replace("🍻", "", $nom_img);
		return $nom_img;
	}
?>