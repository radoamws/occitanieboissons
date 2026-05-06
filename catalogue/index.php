<?php
 	require("./includes/configuration.php");
 	require("./includes/functions.php");

 	$droit_catalogue = TRUE;
 	if(!isset($_SESSION['site'])) {
		header("Location: ".$url."/connexion/");
		exit();
	} elseif($u->catalogue == 0) {
		$droit_catalogue = FALSE;
	}

 	$pagename = "Catalogue";

	// Univers (menu PDF) : bieres / vins / spiritueux / softs / promotions
	$univers = isset($_GET['univers']) ? (string) $_GET['univers'] : 'bieres';
	$univers_allowed = ['bieres', 'vins', 'spiritueux', 'softs', 'promotions'];
	if(!in_array($univers, $univers_allowed, true)) {
		$univers = 'bieres';
	}

	// Buckets "type V and B" pour le filtre degré d'alcool (sans inventer de taxonomy)
	$degre_buckets_definitions = [
		['key' => '0-3', 'min' => 0.0, 'max' => 3.0, 'label' => "0° à 2,9°"],
		['key' => '3-5', 'min' => 3.0, 'max' => 5.0, 'label' => "3° à 4,9°"],
		['key' => '5-7', 'min' => 5.0, 'max' => 7.0, 'label' => "5° à 6,9°"],
		['key' => '7-9', 'min' => 7.0, 'max' => 9.0, 'label' => "7° à 8,9°"],
		['key' => '9-plus', 'min' => 9.0, 'max' => null, 'label' => "9° et +"],
	];
	$degre_bucket_by_key = [];
	foreach($degre_buckets_definitions as $b) {
		$degre_bucket_by_key[$b['key']] = $b;
	}

	$numeric_slug = function($value) {
		$value = (float) $value;
		return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
	};
	$numeric_label = function($value) {
		$value = (float) $value;
		if(abs($value - round($value)) < 0.00001) {
			return (string) ((int) round($value));
		}
		return str_replace('.', ',', rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'));
	};
	$univers_definitions = [
		'bieres' => [
			'label' => 'Bières',
			'categorie_ids' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,28,29,32],
		],
		'vins' => [
			'label' => 'Vins',
			'categorie_ids' => [20,21,22,23,24,25,26],
		],
		'spiritueux' => [
			'label' => 'Spiritueux',
			'categorie_ids' => [30,31],
		],
		'softs' => [
			'label' => 'Softs',
			'categorie_ids' => [],
		],
		'promotions' => [
			'label' => 'Promotions',
			'categorie_ids' => [],
		],
	];

	$categorie_labels = [
		0 => 'Articles divers',
		1 => 'IPA',
		2 => 'Sour',
		3 => 'Blanche',
		4 => 'Ambrée / Rousse / Red Ale',
		5 => 'Stout / Porter',
		6 => 'Barrel Aged',
		7 => 'Lager / Pils / Ale / Blonde',
		8 => 'Pale Ale',
		9 => 'Gose',
		10 => 'Triple',
		11 => 'Saison',
		12 => 'Brune',
		13 => 'Lambic',
		14 => 'Fruité',
		15 => 'Strong Ale',
		16 => 'Ginger Beer',
		18 => 'Hiver / Noël',
		20 => 'Vin rouge',
		21 => 'Vin rosé',
		22 => 'Vin blanc sec',
		23 => 'Vin blanc demi-sec',
		24 => 'Vin blanc moelleux',
		25 => 'Vin blanc liquoreux',
		26 => 'Vin pétillant',
		28 => 'Cidre / Cider',
		29 => 'Dubble / Double grains',
		30 => 'Tourbé',
		31 => 'Légèrement tourbé',
		32 => 'Sans alcool',
	];
	$categorie_label = function($id) use ($categorie_labels) {
		$id = (int) $id;
		if(array_key_exists($id, $categorie_labels)) {
			return $categorie_labels[$id];
		}
		return "Catégorie ".$id;
	};

	$base_catalogue_url = $url."/univers/".$univers;

	// Pré-calcul des données de menu (catégories, fabricants, pays) par univers
	$univers_menu = [];
	foreach($univers_definitions as $key => $def) {
		$univers_menu[$key] = [
			'categories' => [],
			'fabricants' => [],
			'pays' => [],
			'degres' => [],
			'contenances' => [],
			'fabriquant_ids' => [],
		];
		if(empty($def['categorie_ids'])) {
			continue;
		}
		$inCats = implode(',', array_map('intval', $def['categorie_ids']));
		$catStmt = $bdd->query("SELECT DISTINCT categorie FROM ob_catalogue_produits WHERE categorie IN ($inCats) ORDER BY categorie");
		while($c = $catStmt->fetch(PDO::FETCH_OBJ)) {
			$univers_menu[$key]['categories'][] = [
				'id' => (int) $c->categorie,
				'label' => $categorie_label($c->categorie),
			];
		}
		$fabIds = [];
		$fabStmt = $bdd->query("SELECT DISTINCT brasserie FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND brasserie <> 0");
		while($f = $fabStmt->fetch(PDO::FETCH_OBJ)) {
			$fabIds[] = (int) $f->brasserie;
		}
		$fabIds = array_values(array_unique(array_filter($fabIds)));
		$univers_menu[$key]['fabriquant_ids'] = $fabIds;
		if(!empty($fabIds)) {
			$inFab = implode(',', array_map('intval', $fabIds));
			$brasseriesStmt = $bdd->query("SELECT id, name, country, id_fabriquant FROM ob_brasseries WHERE hiden = '1' AND id_fabriquant IN ($inFab) ORDER BY name");
			while($bMenu = $brasseriesStmt->fetch(PDO::FETCH_OBJ)) {
				$univers_menu[$key]['fabricants'][] = $bMenu;
				if(!empty($bMenu->country)) {
					$univers_menu[$key]['pays'][$bMenu->country] = true;
				}
			}
			$univers_menu[$key]['pays'] = array_keys($univers_menu[$key]['pays']);
			sort($univers_menu[$key]['pays'], SORT_NATURAL | SORT_FLAG_CASE);
		} else {
			$univers_menu[$key]['pays'] = [];
		}

		// Degrés / contenances pour le méga-menu
		$degrees = [];
		$degStmt = $bdd->query("SELECT DISTINCT degre FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre IS NOT NULL AND degre > 0 ORDER BY degre");
		while($d = $degStmt->fetch(PDO::FETCH_OBJ)) {
			$degrees[] = (float) $d->degre;
		}
		foreach($degre_buckets_definitions as $bucket) {
			$min = (float) $bucket['min'];
			$max = $bucket['max'] === null ? null : (float) $bucket['max'];
			$has = false;
			foreach($degrees as $dv) {
				if($dv >= $min && ($max === null || $dv < $max)) {
					$has = true;
					break;
				}
			}
			if($has) {
				$univers_menu[$key]['degres'][] = $bucket;
			}
		}
		$contStmt = $bdd->query("SELECT DISTINCT contenance FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND contenance IS NOT NULL AND contenance > 0 ORDER BY contenance");
		$contenances = [];
		while($c = $contStmt->fetch(PDO::FETCH_OBJ)) {
			$contenances[] = (float) $c->contenance;
		}
		$contenances = array_values(array_unique($contenances));
		sort($contenances, SORT_NUMERIC);
		foreach($contenances as $cv) {
			$univers_menu[$key]['contenances'][] = [
				'value' => $cv,
				'slug' => $numeric_slug($cv),
				'label' => $numeric_label($cv),
			];
		}
	}
	 $brasseries_select = FALSE;
	if(isset($_GET['id']) && isset($_GET['nom'])) { 
		$brasseries = $bdd->prepare("SELECT * FROM ob_brasseries WHERE id = :id AND hiden = '1' LIMIT 1");
		$brasseries->bindParam(':id', $_GET['id']);
		$brasseries->execute();
		if($brasseries->rowCount() < 1) {
			header("Location: ".$base_catalogue_url."");
			exit();
		} else {
		    $b = $brasseries->fetch(PDO::FETCH_OBJ);
		    $brasseries_select = TRUE;
		    $pagename = $b->name." - Catalogue";
		}
	}

	if(isset($_GET['id']) && isset($_GET['produit'])) {
		$brasseries = $bdd->prepare("SELECT * FROM ob_brasseries WHERE id = :id AND hiden = '1' LIMIT 1");
		$brasseries->bindParam(':id', $_GET['id']);
		$brasseries->execute();
		if($brasseries->rowCount() < 1) {
			header("Location: ".$base_catalogue_url."");
			exit();
		} else {
		    $b = $brasseries->fetch(PDO::FETCH_OBJ);
		    $brasseries_select = TRUE;
		    $pagename = $b->name." - Catalogue";
		}
	}

	if(isset($_GET['pays'])) {
		if(!array_key_exists($_GET['pays'], $pays_brasseries)) {
			header("Location: ".$base_catalogue_url."");
		}
	}

	$select_categorie = FALSE;
	if(isset($_GET['categorie'])) {
		$select_categorie = TRUE;
	}

	$select_degre = FALSE;
	$degre_bucket = null;
	if(isset($_GET['degre'])) {
		$key = (string) $_GET['degre'];
		if(isset($degre_bucket_by_key[$key])) {
			$select_degre = TRUE;
			$degre_bucket = $degre_bucket_by_key[$key];
		}
	}
	$select_contenance = FALSE;
	$contenance_value = null;
	$contenance_slug = null;
	if(isset($_GET['contenance'])) {
		$slug = (string) $_GET['contenance'];
		if(preg_match('/^\d+(?:\.\d+)?$/', $slug)) {
			$select_contenance = TRUE;
			$contenance_slug = $slug;
			$contenance_value = (float) $slug;
		}
	}

	function ObPanierMap() {
		$panier_map = [];
		$panier = [];
		if(isset($_SESSION['site']) && isset($GLOBALS['u']) && isset($GLOBALS['u']->panier)) {
			$panier = json_decode($GLOBALS['u']->panier, true);
		} elseif(isset($_COOKIE['panier'])) {
			$panier = json_decode($_COOKIE['panier'], true);
		}
		if(!is_array($panier)) {
			return $panier_map;
		}
		foreach($panier as $p) {
			if(!is_array($p)) {
				continue;
			}
			foreach($p as $id => $qte) {
				$panier_map[(int) $id] = (int) $qte;
			}
		}
		return $panier_map;
	}

	function ObRenderProduitsGrid($elements, $consigne) {
		$panier_map = ObPanierMap();
		$u = isset($GLOBALS['u']) ? $GLOBALS['u'] : null;
		$is_admin = ($u && isset($u->admin) && (int) $u->admin === 1);

		echo '<div class="produits-grid">';
		while($e = $elements->fetch(PDO::FETCH_OBJ)) {
			if(!(floor($e->stock/$e->uv_caisse) > 0 || $e->marque == 2)) {
				continue;
			}
			$cart_qte = isset($panier_map[(int) $e->id]) ? (int) $panier_map[(int) $e->id] : 0;
			$max_qte = null;
			if($e->marque == 1) {
				$max_qte = (int) floor($e->stock/$e->uv_caisse);
			}

			echo '<div class="produit-card">';
			echo '<article class="produit-card-inner" data-id="'.(int) $e->id.'" data-cart-qte="'.$cart_qte.'">';
			echo '<div class="produit-body">';
			echo '<div class="produit-title">';
			if($e->marque == 2) {
				echo '<div class="produit-badge">Précommande</div>';
			}
			if($is_admin) {
				echo '<div class="btn-content">';
				echo '<input type="text" readonly data-id="'.(int) $e->id.'" data-type="nom" class="libelle" value="'.htmlspecialchars($e->nom, ENT_QUOTES, 'UTF-8').'"/>';
				echo '<input type="text" readonly data-id="'.(int) $e->id.'" data-type="sup" class="libelle" value="'.htmlspecialchars($e->nom_sup, ENT_QUOTES, 'UTF-8').'"/>';
				echo '</div>';
			} else {
				echo htmlspecialchars($e->nom, ENT_QUOTES, 'UTF-8');
				if(!empty($e->nom_sup)) {
					echo '<div class="produit-subtitle">'.htmlspecialchars($e->nom_sup, ENT_QUOTES, 'UTF-8').'</div>';
				}
			}
			echo '</div>';
			echo '<div class="produit-meta">';
			echo '<div class="produit-meta-row"><span>Conditionnement</span><span>'.htmlspecialchars(Conditionnement($e->condition_vente, $e->uv_caisse, $e->contenance), ENT_QUOTES, 'UTF-8').'</span></div>';
			echo '<div class="produit-meta-row"><span>% Alcool</span><span>'.number_format($e->degre, 1, ',', ' ').'°</span></div>';
			echo '<div class="produit-meta-row"><span>Prix HT HD</span><span>'.number_format($e->prix_ht, 2, ',', ' ').'€</span></div>';
			echo '<div class="produit-meta-row"><span>Droits accise</span><span>'.number_format($e->droits, 2, ',', ' ').'€</span></div>';
			echo '<div class="produit-meta-row produit-meta-total"><span>Prix HT DC</span><span>'.number_format($e->prix_ht + $e->droits, 2, ',', ' ').'€</span></div>';
			if($consigne && (float) $e->consigne_caisse != 0.0) {
				echo '<div class="produit-meta-row"><span>Consigne</span><span>'.number_format($e->consigne_caisse, 2, ',', ' ').'€</span></div>';
			}
			echo '</div>';
			echo '<div class="produit-actions">';
			echo '<div class="produit-qty">';
			echo '<button type="button" class="produit-qty-btn" data-step="-1">-</button>';
			echo '<input type="number" class="produit-qty-input" value="1" min="1" '.(!is_null($max_qte) ? 'max="'.$max_qte.'"' : '').' step="1" />';
			echo '<button type="button" class="produit-qty-btn" data-step="1">+</button>';
			echo '</div>';
			echo '<button type="button" class="btn produit-add-to-cart" data-id="'.(int) $e->id.'">Ajouter au panier</button>';
			echo '</div>';
			echo '</div>';
			echo '</article>';
			echo '</div>';
		}
		echo '</div>';
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $pagename; ?> - Occitanie Boissons</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/catalogue.css?v=<?php echo @filemtime(__DIR__."/gallery/css/catalogue.css"); ?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body data-catalogue-url="<?php echo $url; ?>" data-catalogue-base="<?php echo $base_catalogue_url; ?>" data-catalogue-univers="<?php echo $univers; ?>">
		<!-- HEADER -->
		<?php require("./includes/catalogue-header.php"); ?>

		<!-- PAGE -->
		<div class="page catalogue">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>

			<div class="container">
				<?php if($droit_catalogue) { ?>
					<!-- PANIER -->
					<?php if(@$brasseries_select) { ?>
						<div class="button">
							<a href="<?php echo $base_catalogue_url; ?>"><button class="btn" type="button"><i class="icon-fleche-gauche"></i> Retour</button></a>
							<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-cart"></i> Panier <span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€ HT HD</button></a>
						</div>
					<?php } else { ?>
						<div class="button">
							<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-cart"></i> Panier <span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€ HT HD</button></a>
							<a href="<?php echo $url; ?>/Conditions de livraison OB.pdf" download><button class="btn" type="button"><i class="icon-pdf"></i> Conditions de livraison</button></a>
						</div>
					<?php } ?>
					<!-- MENU (survol) -->
					<div class="rechercher" data-base="<?php echo $base_catalogue_url; ?>">
						<div class="catalogue-tabs" role="menubar" aria-label="Menu catalogue">
							<?php foreach($univers_definitions as $ukey => $udef) { ?>
								<button type="button" class="catalogue-tab <?php echo ($ukey === $univers) ? 'is-active' : ''; ?>" data-univers="<?php echo $ukey; ?>">
									<?php echo $udef['label']; ?>
								</button>
							<?php } ?>
						</div>
						<?php if($brasseries_select || $select_categorie || $select_degre || $select_contenance) { ?>
							<div class="catalogue-sort">
								<?php if($brasseries_select) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-id="<?php echo $b->id; ?>" data-titre="<?php echo $b->name; ?>" id="tri-prix">
								<?php } elseif($select_categorie) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-categorie="<?php echo $_GET['categorie']; ?>" id="tri-prix-categorie">
								<?php } elseif($select_degre) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-degre="<?php echo htmlspecialchars($_GET['degre'], ENT_QUOTES, 'UTF-8'); ?>" id="tri-prix-degre">
								<?php } else { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-contenance="<?php echo htmlspecialchars($contenance_slug, ENT_QUOTES, 'UTF-8'); ?>" id="tri-prix-contenance">
								<?php } ?>
									<option disabled <?php if(@$_GET['trier_prix'] != "croissant" && @$_GET['trier_prix'] != "decroissant") {echo "selected";} ?>>Trier par prix</option>
									<option value="croissant" <?php if(@$_GET['trier_prix'] == "croissant") {echo "selected";} ?>>Croissant</option>
									<option value="decroissant" <?php if(@$_GET['trier_prix'] == "decroissant") {echo "selected";} ?>>Décroissant</option>
									<option value="aucun">Aucun</option>
								</select>
							</div>
						<?php } ?>

						<div class="mot-cle catalogue-megamenu" data-active="<?php echo $univers; ?>">
						<?php foreach($univers_definitions as $ukey => $udef) { $menu = $univers_menu[$ukey]; ?>
							<div class="catalogue-panel <?php echo ($ukey === $univers) ? 'is-active' : ''; ?>" data-panel="<?php echo $ukey; ?>" data-active-dim="categories">
								<div class="menu-sidenav" role="tablist" aria-label="Filtres">
									<button type="button" class="menu-dim-btn is-active" data-dim="categories">Catégories</button>
									<button type="button" class="menu-dim-btn" data-dim="fabricants">Fabricants</button>
									<button type="button" class="menu-dim-btn" data-dim="pays">Pays</button>
									<button type="button" class="menu-dim-btn" data-dim="degres">Degrés d'alcool</button>
									<button type="button" class="menu-dim-btn" data-dim="contenances">Contenances</button>
								</div>
								<div class="menu-content">
									<div class="menu-dim-panel is-active" data-dim="categories">
										<div class="menu-title">Catégories</div>
										<?php if(!empty($menu['categories'])) { ?>
											<?php foreach($menu['categories'] as $cat) { ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/categorie/<?php echo $cat['id']; ?>"><?php echo $cat['label']; ?></a>
										<?php } ?>
									<?php } else { ?>
										<span class="menu-empty">Aucune catégorie</span>
									<?php } ?>
									<a class="menu-more" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
									</div>

									<div class="menu-dim-panel" data-dim="fabricants">
										<div class="menu-title">Fabricants</div>
										<?php if(!empty($menu['fabricants'])) { ?>
											<?php $limit = 24; $i = 0; foreach($menu['fabricants'] as $fab) { if($i >= $limit) break; $i++; ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/<?php echo filterNom($fab->name)."-".$fab->id; ?>"><?php echo $fab->name; ?></a>
										<?php } ?>
									<?php } else { ?>
										<span class="menu-empty">Aucun fabricant</span>
									<?php } ?>
									<a class="menu-more" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
									</div>

									<div class="menu-dim-panel" data-dim="pays">
										<div class="menu-title">Pays</div>
										<?php if(!empty($menu['pays'])) { ?>
											<?php foreach($menu['pays'] as $pays) { ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/pays/<?php echo rawurlencode($pays); ?>"><?php echo $pays; ?></a>
										<?php } ?>
									<?php } else { ?>
										<span class="menu-empty">Aucun pays</span>
									<?php } ?>
									<a class="menu-more" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
									</div>

									<div class="menu-dim-panel" data-dim="degres">
										<div class="menu-title">Degrés d'alcool</div>
										<?php if(!empty($menu['degres'])) { ?>
											<?php foreach($menu['degres'] as $bucket) { ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/degre/<?php echo $bucket['key']; ?>"><?php echo $bucket['label']; ?></a>
										<?php } ?>
									<?php } else { ?>
										<span class="menu-empty">Aucun degré</span>
									<?php } ?>
									<a class="menu-more" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
									</div>

									<div class="menu-dim-panel" data-dim="contenances">
										<div class="menu-title">Contenances</div>
										<?php if(!empty($menu['contenances'])) { ?>
											<?php foreach($menu['contenances'] as $c) { ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/contenance/<?php echo $c['slug']; ?>"><?php echo $c['label']; ?> cl</a>
										<?php } ?>
									<?php } else { ?>
										<span class="menu-empty">Aucune contenance</span>
									<?php } ?>
									<a class="menu-more" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					</div>
					<?php if($brasseries_select) { ?>
						<?php
							switch(@$_GET['trier_prix']) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE brasserie = '".$b->id_fabriquant."' AND marque = '1' OR brasserie = '".$b->id_fabriquant."' AND marque = '2' ORDER by prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE brasserie = '".$b->id_fabriquant."' AND marque = '1' OR brasserie = '".$b->id_fabriquant."' AND marque = '2' ORDER by prix_ht+droits DESC, marque DESC";
								break;
								default: $sql = "SELECT * FROM ob_catalogue_produits WHERE brasserie = '".$b->id_fabriquant."' AND marque = '1' OR brasserie = '".$b->id_fabriquant."' AND marque = '2' ORDER by contenance DESC, marque DESC";
							}
							$elements = $bdd->query($sql);
							/* CHECK CONSIGNE */
							$consigne = FALSE;
							while($e = $elements->fetch(PDO::FETCH_OBJ)) {
								if($e->consigne_caisse != 0) {$consigne = TRUE;}
							}

							$elements = $bdd->query($sql);
							if($elements->rowCount() > 0) {					
						?>
							<?php ObRenderProduitsGrid($elements, $consigne); ?>
						<?php } ?>
					<?php } elseif($select_categorie) { ?>
						<?php
							$categorie = htmlentities($_GET['categorie']);
							$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
							switch($trier_prix) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie = '".$categorie."' AND marque = '1' OR categorie = '".$categorie."' AND marque = '2' ORDER by prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie = '".$categorie."' AND marque = '1' OR categorie = '".$categorie."' AND marque = '2' ORDER by prix_ht+droits DESC, marque DESC";
								break;
								default: $sql = "SELECT * FROM ob_catalogue_produits WHERE categorie = '".$categorie."' AND marque = '1' OR categorie = '".$categorie."' AND marque = '2' ORDER by contenance DESC, marque DESC";
							}
							$elements = $bdd->query($sql);
							/* CHECK CONSIGNE */
							$consigne = FALSE;
							while($e = $elements->fetch(PDO::FETCH_OBJ)) {
								if($e->consigne_caisse != 0) {$consigne = TRUE;}
							}

							$elements = $bdd->query($sql);
							if($elements->rowCount() > 0) {					
						?>
							<?php ObRenderProduitsGrid($elements, $consigne); ?>
						<?php } ?>
					<?php } elseif($select_degre && $degre_bucket && !empty($univers_definitions[$univers]['categorie_ids'])) { ?>
						<?php
							$inCats = implode(',', array_map('intval', $univers_definitions[$univers]['categorie_ids']));
							$min = (float) $degre_bucket['min'];
							$max = $degre_bucket['max'] === null ? null : (float) $degre_bucket['max'];
							$labelDegre = $degre_bucket['label'];
							switch(@$_GET['trier_prix']) {
								case "croissant":
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND marque = '2' ORDER by prix_ht+droits, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '2' ORDER by prix_ht+droits, marque DESC";
									}
								break;
								case "decroissant":
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND marque = '2' ORDER by prix_ht+droits DESC, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '2' ORDER by prix_ht+droits DESC, marque DESC";
									}
								break;
								default:
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND marque = '2' ORDER by contenance DESC, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '1' OR categorie IN ($inCats) AND degre >= $min AND degre < $max AND marque = '2' ORDER by contenance DESC, marque DESC";
									}
							}
							$elements = $bdd->query($sql);
							$consigne = FALSE;
							while($e = $elements->fetch(PDO::FETCH_OBJ)) {
								if($e->consigne_caisse != 0) {$consigne = TRUE;}
							}
							$elements = $bdd->query($sql);
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements, $consigne); ?>
						<?php } ?>
					<?php } elseif($select_contenance && $contenance_value !== null && !empty($univers_definitions[$univers]['categorie_ids'])) { ?>
						<?php
							$inCats = implode(',', array_map('intval', $univers_definitions[$univers]['categorie_ids']));
							$contenance = (float) $contenance_value;
							switch(@$_GET['trier_prix']) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND contenance = $contenance AND marque = '1' OR categorie IN ($inCats) AND contenance = $contenance AND marque = '2' ORDER by prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND contenance = $contenance AND marque = '1' OR categorie IN ($inCats) AND contenance = $contenance AND marque = '2' ORDER by prix_ht+droits DESC, marque DESC";
								break;
								default:
									$sql = "SELECT * FROM ob_catalogue_produits WHERE categorie IN ($inCats) AND contenance = $contenance AND marque = '1' OR categorie IN ($inCats) AND contenance = $contenance AND marque = '2' ORDER by contenance DESC, marque DESC";
							}
							$elements = $bdd->query($sql);
							$consigne = FALSE;
							while($e = $elements->fetch(PDO::FETCH_OBJ)) {
								if($e->consigne_caisse != 0) {$consigne = TRUE;}
							}
							$elements = $bdd->query($sql);
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements, $consigne); ?>
						<?php } ?>
					<?php } else { ?>
						<!-- PANNEL FABRICANTS -->
						<section id="brasseries-pannel">
							<?php
								$fabFilter = $univers_menu[$univers]['fabriquant_ids'];
								if(empty($univers_definitions[$univers]['categorie_ids'])) {
									$brasseries = $bdd->query("SELECT * FROM ob_brasseries WHERE 1=0");
								} elseif(!empty($fabFilter)) {
									$inFab = implode(',', array_map('intval', $fabFilter));
									if(isset($_GET['pays'])) {
										$brasseries = $bdd->prepare("SELECT * FROM ob_brasseries WHERE country = :pays AND hiden = '1' AND id_fabriquant IN ($inFab) ORDER BY name");
										$brasseries->bindParam(":pays", $_GET['pays']);
										$brasseries->execute();
									} else {
										$brasseries = $bdd->query("SELECT * FROM ob_brasseries WHERE hiden = '1' AND id_fabriquant IN ($inFab) ORDER BY name");
									}
								} else {
									$brasseries = $bdd->query("SELECT * FROM ob_brasseries WHERE 1=0");
								}
								while($b = $brasseries->fetch(PDO::FETCH_OBJ)) {
							?>
								<div data-id="<?php echo $b->id; ?>" data-titre="<?php echo $b->name; ?>" class="brasseriesc-boxe">
									<a href="<?php echo $url; ?>/univers/<?php echo $univers; ?>/<?php echo filterNom($b->name)."-".$b->id; ?>">
										<div class="image">
											<img alt="<?php echo $b->image_short; ?>" src="<?php echo $b->image_url; ?>"/>
										</div>
									</a>
									<div class="informations-content">
										<h4>
											<?php
												echo $b->name;
												foreach($pays_brasseries as $nom => $d) {
													if($nom == $b->country) {
											?>
												<img alt="<?php echo $nom; ?>" src="<?php echo $d; ?>"/>
											<?php } } ?>
										</h4>
										<a href="<?php echo $url; ?>/univers/<?php echo $univers; ?>/<?php echo filterNom($b->name)."-".$b->id; ?>"><button class="button-vide" type="button"><i class="icon-plus"></i> Découvrir la gamme</button></a>
									</div>
								</div>			
							<?php } ?>
						</section>
					<?php } ?>
				<?php } else { ?>
					<p style="color: red;">Vous n'avez pas les droits nécessaires pour accéder au catalogue. Veuillez nous contacter à cette adresse email : commercial.ob@free.fr</p>
				<?php } ?>

				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>
		<!-- JAVASCRIPT -->
		<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script src="<?php echo $gallery; ?>/js/general.js?v=<?php echo @filemtime(__DIR__."/gallery/js/general.js"); ?>"></script>
	</body>
</html>
