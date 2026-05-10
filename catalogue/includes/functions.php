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

	function ob_get_panier_data() {
		global $u;
		if(isset($_SESSION['site'])) {
			$panier = json_decode($u->panier, true);
		} else {
			if(isset($_COOKIE['panier'])) {
				$panier = json_decode($_COOKIE['panier'], true);
			} else {
				$panier = array();
			}
		}
		return is_array($panier) ? $panier : array();
	}
	function ob_flatten_panier($panier) {
		$items = array();
		if(!is_array($panier)) {
			return $items;
		}
		foreach($panier as $p) {
			if(!is_array($p)) {
				continue;
			}
			foreach($p as $id => $qte) {
				$id = (int) $id;
				$qte = (int) $qte;
				$items[] = array('id' => $id, 'qte' => $qte);
			}
		}
		return $items;
	}
	function ob_fetch_produits_by_ids($ids) {
		global $bdd;
		static $cache = array();
		$ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
		if(empty($ids)) {
			return array();
		}
		$missing = array();
		foreach($ids as $id) {
			if(!array_key_exists($id, $cache)) {
				$missing[] = $id;
			}
		}
		if(!empty($missing)) {
			$placeholders = implode(',', array_fill(0, count($missing), '?'));
			$sql = "SELECT id, stock, marque, uv_caisse, prix_ht, droits, code_tva, consigne_caisse FROM ob_catalogue_produits WHERE id IN ($placeholders)";
			$stmt = $bdd->prepare($sql);
			$stmt->execute($missing);
			$foundIds = array();
			while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
				$rid = (int) $row->id;
				$cache[$rid] = $row;
				$foundIds[$rid] = true;
			}
			// Cache negative lookups too to avoid requerying
			foreach($missing as $mid) {
				if(!isset($foundIds[$mid])) {
					$cache[$mid] = null;
				}
			}
		}
		$result = array();
		foreach($ids as $id) {
			$result[$id] = $cache[$id];
		}
		return $result;
	}
	function ob_update_panier_storage($newpanier, $changed) {
		global $bdd, $u;
		// Toujours rafraîchir le cookie (prolonge l'expiration)
		setcookie("panier", json_encode($newpanier), time()+60*60*24*30, '/');
		if(isset($_SESSION['site']) && $changed) {
			$upd = $bdd->prepare("UPDATE ob_users SET panier = :panier WHERE id = :id");
			$upd->execute(array(':panier' => json_encode($newpanier), ':id' => (int) $u->id));
		}
	}

	function VerifPanier() {
		global $bdd, $u;
		$panier = ob_get_panier_data();
		$items = ob_flatten_panier($panier);
		if(empty($items)) {
			ob_update_panier_storage(array(), $panier != array());
			return;
		}
		$ids = array();
		foreach($items as $it) {
			$ids[] = (int) $it['id'];
		}
		$produits = ob_fetch_produits_by_ids($ids);

		$newpanier = array();
		$doublon = array();
		foreach($items as $it) {
			$p2 = (int) $it['id'];
			$qte = (int) $it['qte'];
			if($qte <= 0) {
				continue;
			}
			if(in_array($p2, $doublon, true)) {
				continue;
			}
			$e = isset($produits[$p2]) ? $produits[$p2] : null;
			if(!$e) {
				continue;
			}
			if(!($e->stock > 0 || $e->marque == 2)) {
				continue;
			}
			$doublon[] = $p2;
			if($e->marque != 2) {
				$uv = (int) $e->uv_caisse;
				if($uv > 0) {
					$maxQte = (int) floor(((int) $e->stock) / $uv);
					if($qte > $maxQte) {
						$qte = $maxQte;
					}
				}
			}
			if($qte > 0) {
				$newpanier[] = array($p2 => $qte);
			}
		}

		$changed = !($panier == $newpanier);
		ob_update_panier_storage($newpanier, $changed);
	}
	VerifPanier();

	function PrixPanier($type="ttc", $decimal=FALSE) {
		global $bdd, $u;
		$prix = 0;
		$panier = ob_get_panier_data();
		$items = ob_flatten_panier($panier);
		if(empty($items)) {
			return $decimal ? 0 : number_format(0, 2, ',', ' ');
		}
		$ids = array();
		foreach($items as $it) {
			$ids[] = (int) $it['id'];
		}
		$produits = ob_fetch_produits_by_ids($ids);
		foreach($items as $it) {
			$p2 = (int) $it['id'];
			$qte = (int) $it['qte'];
			$e = isset($produits[$p2]) ? $produits[$p2] : null;
			if(!$e) {
				continue;
			}
			$tva = 0;
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
		$panierHt = PrixPanier("ht", TRUE);
		if(in_array($code_postal, $groupe1)) {
			if($panierHt < 1000) {$r = "90.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe2)) {
			if($panierHt < 1000) {$r = "100.00";} elseif($panierHt >= 1000 && $panierHt < 1200) {$r = "25.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe3)) {
			if($panierHt < 1000) {$r = "120.00";} elseif($panierHt >= 1000 && $panierHt < 1200) {$r = "35.00";} elseif($panierHt >= 1200 && $panierHt < 1300) {$r = "20.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe4)) {
			if($panierHt < 1000) {$r = "130.00";} elseif($panierHt >= 1000 && $panierHt < 1200) {$r = "55.00";} elseif($panierHt >= 1200 && $panierHt < 1300) {$r = "40.00";} elseif($panierHt >= 1300 && $panierHt < 1400) {$r = "30.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe5)) {
			if($panierHt < 1000) {$r = "140.00";} elseif($panierHt >= 1000 && $panierHt < 1200) {$r = "65.00";} elseif($panierHt >= 1200 && $panierHt < 1300) {$r = "50.00";} elseif($panierHt >= 1300 && $panierHt < 1400) {$r = "40.00";} elseif($panierHt >= 1400 && $panierHt < 1500) {$r = "30.00";} else {$r = "0.00";}
		} elseif(in_array($code_postal, $groupe6)) {
			if($panierHt < 1000) {$r = "150.00";} elseif($panierHt >= 1000 && $panierHt < 1200) {$r = "75.00";} elseif($panierHt >= 1200 && $panierHt < 1300) {$r = "60.00";} elseif($panierHt >= 1300 && $panierHt < 1400) {$r = "50.00";} elseif($panierHt >= 1400 && $panierHt < 1500) {$r = "40.00";} elseif($panierHt >= 1500 && $panierHt < 1600) {$r = "20.00";} else {$r = "0.00";}
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
		$panier = ob_get_panier_data();
		$items = ob_flatten_panier($panier);
		if(!empty($items)) {
			$ids = array();
			foreach($items as $it) {
				$ids[] = (int) $it['id'];
			}
			$produits = ob_fetch_produits_by_ids($ids);
			foreach($items as $it) {
				$p2 = (int) $it['id'];
				$qte = (int) $it['qte'];
				$e = isset($produits[$p2]) ? $produits[$p2] : null;
				if(!$e) {
					continue;
				}
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