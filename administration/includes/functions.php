<?php
	function ImageNom($titre,$extentions) {
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
		return $nom_img;
	}

	function UpdateStats($type=false) {
		global $bdd;
		$d = date('Y-m-d');
		$stats = $bdd->query('SELECT date FROM ob_statistiques WHERE date = "'.$d.'"');
		if($stats->rowCount() == 0) $bdd->query('INSERT INTO ob_statistiques (date) VALUES ("'.$d.'")');
		switch($type) {
			case "catalogue":
				$bdd->query('UPDATE ob_statistiques SET catalogue = catalogue+1 WHERE date = "'.$d.'"');
			break;
			case "inscription":
				$bdd->query('UPDATE ob_statistiques SET inscription = inscription+1 WHERE date = "'.$d.'"');
			break;
			case "visiteur":
				if(!isset($_COOKIE['visiteurs'])) {
					setcookie('visiteurs', 'true', time() + 7200);
					$bdd->query('UPDATE ob_statistiques SET visites = visites+1 WHERE date = "'.$d.'"');
				}
				$bdd->query('UPDATE ob_statistiques SET pagesvues = pagesvues+1 WHERE date = "'.$d.'"');
			break;
		}
	}
	UpdateStats();
	
	function CategorieClient($id) {
		switch($id) {
			case "0":
				$r = 6;
			break;
			case "1":
				$r = "Professionnel"; 
			break;
			case "2":
				$r = "Prospection";
			break;
			case "3":
				$r = "Particulier";
			break;
			case "4":
				$r = "Client Toulouse Revendeur"; 
			break;
			case "5":
				$r = "Client Toulouse CHR"; 
			break;
		}
		return $r;
	}

	function mPaiement($type) {
		switch($type) {
			case 0:
				$r = "Virement comptant";
			break;
			case 1:
				$r = "Traite 30 jours";
			break;
			case 2:
				$r = "Virement 30 jours";
			break;
		}
		return $r;
	}

	function Conditionnement($id, $uv_caisse, $contenance) {
		switch($id) {
			case "1":
				$r = "Fût ".$uv_caisse." L"; 
			break;
			case "2":
				$r = $uv_caisse." x ".$contenance." cl";
			break;
			case "3":
				$r = $uv_caisse." x ".$contenance." cl";
			break;
			case "4":
				$r = "Bib ".$uv_caisse." L"; 
			break;
			case "5":
				$r = "Keykeg ".$uv_caisse." L"; 
			break;
			case "6":
				$r = "Dolium ".$uv_caisse." L"; 
			break;
			case "7":
				$r = "Polykeg ".$uv_caisse." L"; 
			break;
			case "8":
				$r = "Sodakeg ".$uv_caisse." L"; 
			break;
			case "9":
				$r = "Petainer ".$uv_caisse." L"; 
			break;
			default: $r = $uv_caisse." x ".$contenance." cl";
		}
		return $r;
	}

	
	function Categorie($id) {
		switch($id) {
			case "1":
				$r = "Ipa"; 
			break;
			case "2":
				$r = "Sour/Berliner Weisse/Gueuze";
			break;
			case "3":
				$r = "Blanche";
			break;
			case "4":
				$r = "Ambrée"; 
			break;
			case "5":
				$r = "Stout/Porter"; 
			break;
			case "6":
				$r = "Barrel Aged"; 
			break;
			case "7":
				$r = "Lager / Pils"; 
			break;
			case "8":
				$r = "Pale Ale"; 
			break;
			case "9":
				$r = "Gose"; 
			break;
			case "10":
				$r = "Triple"; 
			break;
			case "11":
				$r = "Saison"; 
			break;
			case "12":
				$r = "Brune"; 
			break;
		}
		return $r;
	}
?>