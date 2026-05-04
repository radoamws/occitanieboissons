<?php
	function ImageNom($titre,$extentions,$type="image") {
		$nom_img = $type."-".$titre.".".$extentions;
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

	function VerifPanier() {
		global $bdd, $u;
		if(isset($_SESSION['site'])) {
			$panier = json_decode($u->panier, true);
		} else {
			if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
		}

		if(!empty($panier)) {
			$newpanier = array();
			$doublon = array();
			foreach($panier as $p) {
				foreach($p as $p2 => $qte) {
					if($qte > 0) {
						// VERIFICATION QUANTITE
						$stockElement = $bdd->prepare("SELECT stock,marque,uv_caisse FROM ob_catalogue_produits WHERE id = :id");
						$stockElement->bindParam(":id", $p2);
						$stockElement->execute();
						if($stockElement->rowCount() > 0) {
							$e = $stockElement->fetch(PDO::FETCH_OBJ);
							if(($e->stock > 0 || $e->marque == 2) && !in_array($p2, $doublon)) {
								$doublon[] = $p2;
								if($qte > floor($e->stock/$e->uv_caisse) && $e->marque != 2) {$qte=floor($e->stock/$e->uv_caisse);}
								$newpanier[] = array($p2 => $qte);
							}
						}
					}
				}
			}
		} else {
			$newpanier = array();
		}

		if(isset($_SESSION['site'])) {
			$modificationPanier = $bdd->query("UPDATE ob_users SET panier = '".json_encode($newpanier)."' WHERE id = '".$u->id."'");
			setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
		} else {
			setcookie("panier",json_encode($newpanier),time()+60*60*24*30, '/');
		}
	}
	VerifPanier();

	function PrixPanier($type="ttc", $decimal=FALSE) {
		global $bdd, $u;
		$prix = 0;
		if(isset($_SESSION['site'])) {
			$panier = json_decode($u->panier, true);
		} else {
			if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
		}
		foreach($panier as $p) {
			foreach($p as $p2 => $qte) {
				$prixElement = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
				$prixElement->bindParam(":id", $p2);
				$prixElement->execute();
				$e = $prixElement->fetch(PDO::FETCH_OBJ);
				// TVA
				switch($e->code_tva) {
					case 2:
						$tva = 20;
					break;
					case 3:
						$tva = 5.5;
					break;
					case 4:
						$tva = 10;
					break;
					case 5:
						$tva = 0;
					break;
				}
				if($type == "ttc") {
					$prix += ($e->prix_ht*$e->uv_caisse+$e->droits*$e->uv_caisse)*(1+$tva/100)*$qte;
				} elseif($type == "droits") {
					$prix += ($e->prix_ht*$e->uv_caisse+$e->droits*$e->uv_caisse)*$qte;
				} elseif($type == "ht") {
					$prix += $e->prix_ht*$e->uv_caisse*$qte;
				}
			}
		}
		if($decimal) {
			return $prix;
		} else {
			return number_format($prix, 2, ',', ' ');
		}
	}

	function ArticlePanier() {
		global $bdd, $u;
		$nombre = 0;
		if(isset($_SESSION['site'])) {
			$panier = json_decode($u->panier, true);
		} else {
			if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
		}

		foreach($panier as $p) {
			foreach($p as $p2 => $qte) {
				$nombre += $qte;
			}
		}

		return $nombre;
	}

	function CommandeEnCours() {
		global $bdd,$u;
		if(isset($_SESSION['site'])) {
			$commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE userid = '".$u->id."' AND paiement = '0'");
			if($commandes->rowCount() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	function filterNom($text) {
		$text = str_replace(" ", "-", $text);
		$text = str_replace("&", "-", $text);
		return $text;
	}

	function LivraisonPrix($code_postal, $type="ht") {
		$groupe1 = ["09", "11", "12", "24", "32", "33", "34", "40", "46", "47", "48", "64", "65", "66"];
		$groupe2 = ["07", "13", "15", "16", "17", "19", "23", "30", "63", "83", "84", "87"];
		$groupe3 = ["03", "26", "28", "36", "37", "38", "41", "42", "43", "44", "45", "49", "56", "71", "79", "85", "86"];
		$groupe4 = ["01", "02", "06", "08", "14", "22", "27", "35", "39", "51", "53", "60", "61", "69", "72", "80"];
		$groupe5 = ["04", "05", "10", "18", "50", "55", "58", "73", "74", "76", "89"];
		$groupe6 = ["20", "21", "25", "29", "52", "54", "57", "59", "62", "67", "68", "70", "75", "77", "78", "88", "90", "91", "92", "93", "94", "95"];	
		if(in_array($code_postal, $groupe1)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "90.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe2)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "100.00";} elseif(PrixPanier("ht", TRUE) >= 1000 && PrixPanier("ht", TRUE) < 1200) {$r = "25.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe3)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "120.00";} elseif(PrixPanier("ht", TRUE) >= 1000 && PrixPanier("ht", TRUE) < 1200) {$r = "35.00";} elseif(PrixPanier("ht", TRUE) >= 1200 && PrixPanier("ht", TRUE) < 1300) {$r = "20.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe4)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "130.00";} elseif(PrixPanier("ht", TRUE) >= 1000 && PrixPanier("ht", TRUE) < 1200) {$r = "55.00";} elseif(PrixPanier("ht", TRUE) >= 1200 && PrixPanier("ht", TRUE) < 1300) {$r = "40.00";} elseif(PrixPanier("ht", TRUE) >= 1300 && PrixPanier("ht", TRUE) < 1400) {$r = "30.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe5)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "140.00";} elseif(PrixPanier("ht", TRUE) >= 1000 && PrixPanier("ht", TRUE) < 1200) {$r = "65.00";} elseif(PrixPanier("ht", TRUE) >= 1200 && PrixPanier("ht", TRUE) < 1300) {$r = "50.00";} elseif(PrixPanier("ht", TRUE) >= 1300 && PrixPanier("ht", TRUE) < 1400) {$r = "40.00";} elseif(PrixPanier("ht", TRUE) >= 1400 && PrixPanier("ht", TRUE) < 1500) {$r = "30.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe6)) {
			if(PrixPanier("ht", TRUE) < 1000) {$r = "150.00";} elseif(PrixPanier("ht", TRUE) >= 1000 && PrixPanier("ht", TRUE) < 1200) {$r = "75.00";} elseif(PrixPanier("ht", TRUE) >= 1200 && PrixPanier("ht", TRUE) < 1300) {$r = "60.00";} elseif(PrixPanier("ht", TRUE) >= 1300 && PrixPanier("ht", TRUE) < 1400) {$r = "50.00";} elseif(PrixPanier("ht", TRUE) >= 1400 && PrixPanier("ht", TRUE) < 1500) {$r = "40.00";} elseif(PrixPanier("ht", TRUE) >= 1500 && PrixPanier("ht", TRUE) < 1600) {$r = "20.00";} else {$r = "0.00";}
		} else {
			$r = "0";
		}
		if($type == "ttc") {
			return $r*1.2;
		} else {
			return $r;
		}
	}

	function Consigne($decimal=FALSE) {
		global $bdd, $u;
		$consigne = 0;
		if(isset($_SESSION['site'])) {
			$panier = json_decode($u->panier, true);
		} else {
			if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
		}
		foreach($panier as $p) {
			foreach($p as $p2 => $qte) {
				$prixElement = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
				$prixElement->bindParam(":id", $p2);
				$prixElement->execute();
				$e = $prixElement->fetch(PDO::FETCH_OBJ);
				$consigne += $e->consigne_caisse*$qte;
			}
		}

		if($decimal) {
			return $consigne;
		} else {
			return number_format($consigne, 2, ',', ' ');
		}
	}

	function CodePostal() {
		global $bdd,$u;
		$a = $bdd->query("SELECT codepostal FROM ob_users_adresses WHERE id = '".$u->adresse_livraison."'")->fetch(PDO::FETCH_OBJ);
		return substr($a->codepostal, 0, 2);
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

	function is_siret($siret) {
		if (strlen($siret) != 14) return 1; // le SIRET doit contenir 14 caractères
		if (!is_numeric($siret)) return 2; // le SIRET ne doit contenir que des chiffres

		// on prend chaque chiffre un par un
		// si son index (position dans la chaîne en commence à 0 au premier caractère) est pair
		// on double sa valeur et si cette dernière est supérieure à 9, on lui retranche 9
		// on ajoute cette valeur à la somme totale

		for ($index = 0; $index < 14; $index ++)
		{
			$number = (int) $siret[$index];
			if (($index % 2) == 0) { if (($number *= 2) > 9) $number -= 9; }
			$sum += $number;
		}

		// le numéro est valide si la somme des chiffres est multiple de 10
		if (($sum % 10) != 0) return 3; else return 0;		
	}
?>