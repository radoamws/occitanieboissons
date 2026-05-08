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
	$menu_norm = function($value) {
		$value = trim((string) $value);
		$value = preg_replace('/\s+/', ' ', $value);
		$value = mb_strtoupper($value, 'UTF-8');
		return $value;
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
	// Filtrage des familles/sous-familles affichées par univers (menu PDF)
	$univers_famille_filter_slugs = [
		'bieres' => ['bieres', 'cidres', 'ciders'],
		'vins' => ['vins', 'vins-permanents', 'vins-au-comptoir', 'sangria'],
		'spiritueux' => ['alcool'],
		'softs' => ['softs', 'soft-drinks-bib', 'eaux', 'jus-de-fruit', 'gaz', 'sirop', 'puree-de-fruits'],
		'promotions' => ['remises'],
	];
	// (PDF) Menus: libellés et ordre figés sur les captures.
	$pdf_menu = [
		'bieres' => [
			'famille_label' => 'Famille > 20',
			'famille_all' => 'Toutes les bières',
			'sous_famille_1_label' => 'Sous-famille > 8 à 19',
			'sous_famille_1_btn' => 'Bouteilles et canettes',
			'sous_famille_2_label' => 'Sous-famille > 1 à 7',
			'sous_famille_2_btn' => 'Fûts',
			'col_2_head' => 'Catégorie > 1 à 15 + 33',
			'col_2_title' => 'Couleur',
			'col_2_items' => [
				['label' => 'Blanche', 'href' => '/univers/bieres/categorie/3'],
				['label' => 'Blonde', 'href' => '/univers/bieres/categorie/7'],
				['label' => 'Ambrée', 'href' => '/univers/bieres/categorie/4'],
			],
			'col_3_head' => 'Catégorie > 1 à 15 + 33',
			'col_3_title' => 'Style',
			'col_3_items' => [
				['label' => 'IPA', 'href' => '/univers/bieres/categorie/1'],
				['label' => 'Lager', 'href' => '/univers/bieres/categorie/7'],
				['label' => 'Stout', 'href' => '/univers/bieres/categorie/5'],
			],
			'col_4_head' => 'Fabricant (à trier)',
			'col_4_title' => 'Brasserie',
			'col_5_head' => 'Pays',
			'col_5_title' => 'Pays',
		],
		'vins' => [
			'famille_label' => 'Famille > 10',
			'famille_all' => 'Tous les vins',
			'sous_famille_1_label' => 'Tout sauf Conditionnement > 4',
			'sous_famille_1_btn' => 'Bouteilles',
			'sous_famille_2_label' => 'Conditionnement > 4',
			'sous_famille_2_btn' => 'BIB',
			'col_2_head' => 'Catégorie > 20 à 26',
			'col_2_title' => 'Type',
			'col_2_items' => [
				['label' => 'Rouge', 'href' => '/univers/vins/categorie/20'],
				['label' => 'Blanc', 'href' => '/univers/vins/categorie/22'],
				['label' => 'Rosé', 'href' => '/univers/vins/categorie/21'],
			],
			'col_3_head' => 'Sous-famille > 4 à 99',
			'col_3_title' => 'Appellation',
			'col_3_items' => [
				['label' => "Pays d’Oc", 'href' => '/univers/vins/sous-famille/igp-oc'],
				['label' => 'Bordeaux', 'href' => '/univers/vins/sous-famille/bordeaux'],
				['label' => 'Côtes-du-Rhône', 'href' => '/univers/vins/sous-famille/cotes-du-rhone'],
			],
			'col_4_head' => 'Fabricant (à trier)',
			'col_4_title' => 'Domaine',
			'col_4_text' => ['Plaisance', 'Le Clos du Gravillas', 'Anne de Joyeuse'],
			'col_5_head' => 'Sous-famille > 4 à 99 (à trier)',
			'col_5_title' => 'Région',
			'col_5_items' => [
				['label' => 'Alsace', 'href' => '/univers/vins/sous-famille/alsace'],
				['label' => 'Languedoc', 'href' => '/univers/vins/sous-famille/aoc-languedoc'],
				['label' => 'Sud-Ouest', 'href' => '/univers/vins'],
			],
		],
		'spiritueux' => [
			'famille_label' => 'Famille > 1',
			'famille_all' => 'Tous les spiritueux',
			'sous_famille_1_label' => 'Sous-famille > ?',
			'sous_famille_1_btn' => 'Bouteilles',
			'sous_famille_2_label' => 'Tout sauf la sous-famille à créer',
			'sous_famille_2_btn' => 'Fûts',
			'sous_famille_3_label' => 'Sous-famille à créer',
			'col_2_head' => 'Sous-famille > 1 à 84',
			'col_2_title' => 'Type',
			'col_2_items' => [
				['label' => 'Whisky', 'href' => '/univers/spiritueux/sous-famille/whisky'],
				['label' => 'Rhum', 'href' => '/univers/spiritueux/sous-famille/rhums-tiers'],
				['label' => 'Armagnac', 'href' => '/univers/spiritueux/sous-famille/armagnac'],
			],
			'col_3_head' => 'Fabricant (à trier)',
			'col_3_title' => 'Distillerie',
			'col_3_text' => ['Saint-James', 'Springbank', 'Clairin'],
			'col_4_head' => 'Pays',
			'col_4_title' => 'Pays',
			'col_4_text' => ['France', 'Japon', 'Écosse'],
		],
		'softs' => [
			'famille_label' => 'Famille > 40 à 75',
			'famille_all' => 'Tous les softs',
			'sous_famille_1_label' => 'Famille > 40 à 75',
			'sous_famille_1_btn' => 'Bouteilles',
			'sous_famille_2_btn' => 'Fûts',
			'col_2_head' => 'Famille 40 > 75',
			'col_2_title' => 'Type',
			'col_2_items' => [
				['label' => 'Boissons sucrées', 'href' => '/univers/softs/famille/softs'],
				['label' => 'Jus de fruit', 'href' => '/univers/softs/famille/jus-de-fruit'],
				['label' => 'Eaux', 'href' => '/univers/softs/famille/eaux'],
			],
			'col_3_head' => 'Fabricant (à trier)',
			'col_3_title' => 'Marque',
			'col_3_text' => ['Giffard', 'Rauch', 'Fever Tree'],
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
			'familles' => [],
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
		$universWhere = "p.categorie IN ($inCats)";
		if(in_array($key, ['vins','spiritueux'], true)) {
			$slugs = isset($univers_famille_filter_slugs[$key]) ? $univers_famille_filter_slugs[$key] : [];
			if(!empty($slugs)) {
				$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
				$universWhere = "(p.categorie IN ($inCats) OR p.famille_id IN (SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)))";
			}
		}
		$taxStmt = $bdd->query("SELECT f.id AS famille_id, f.nom AS famille_nom, f.slug AS famille_slug, sf.id AS sous_famille_id, sf.nom AS sous_famille_nom, sf.slug AS sous_famille_slug\n\t\t\tFROM ob_catalogue_produits p\n\t\t\tINNER JOIN ob_catalogue_familles f ON p.famille_id = f.id\n\t\t\tLEFT JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id\n\t\t\tWHERE $universWhere AND p.famille_id IS NOT NULL\n\t\t\tORDER BY f.nom, sf.nom");
		$familles = [];
		while($t = $taxStmt->fetch(PDO::FETCH_OBJ)) {
			$fid = (int) $t->famille_id;
			if(!isset($familles[$fid])) {
				$familles[$fid] = [
					'id' => $fid,
					'nom' => (string) $t->famille_nom,
					'slug' => (string) $t->famille_slug,
					'sous_familles' => [],
				];
			}
			if(!empty($t->sous_famille_id)) {
				$sfid = (int) $t->sous_famille_id;
				$familles[$fid]['sous_familles'][$sfid] = [
					'id' => $sfid,
					'nom' => (string) $t->sous_famille_nom,
					'slug' => (string) $t->sous_famille_slug,
				];
			}
		}
		foreach($familles as $fid => $f) {
			$familles[$fid]['sous_familles'] = array_values($f['sous_familles']);
		}
		$familiesList = array_values($familles);
		if(isset($univers_famille_filter_slugs[$key])) {
			$allowed = array_flip($univers_famille_filter_slugs[$key]);
			$filtered = [];
			foreach($familiesList as $f) {
				if(isset($allowed[$f['slug']])) {
					$filtered[] = $f;
				}
			}
			$familiesList = $filtered;
		}
		$univers_menu[$key]['familles'] = $familiesList;
		$fabIds = [];
		$fabStmt = $bdd->query("SELECT DISTINCT brasserie FROM ob_catalogue_produits p WHERE $universWhere AND brasserie <> 0");
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
		$degStmt = $bdd->query("SELECT DISTINCT degre FROM ob_catalogue_produits p WHERE $universWhere AND degre IS NOT NULL AND degre > 0 ORDER BY degre");
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
		$contStmt = $bdd->query("SELECT DISTINCT contenance FROM ob_catalogue_produits p WHERE $universWhere AND contenance IS NOT NULL AND contenance > 0 ORDER BY contenance");
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
	$select_famille = FALSE;
	$famille_id = null;
	$famille_slug = null;
	if(isset($_GET['famille'])) {
		$famille_slug = preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['famille']);
		if($famille_slug !== '') {
			$famStmt = $bdd->prepare("SELECT id, nom FROM ob_catalogue_familles WHERE slug = :slug LIMIT 1");
			$famStmt->bindParam(':slug', $famille_slug);
			$famStmt->execute();
			if($famStmt->rowCount() > 0) {
				$fam = $famStmt->fetch(PDO::FETCH_OBJ);
				$famille_id = (int) $fam->id;
				$select_famille = TRUE;
				$pagename = (string) $fam->nom . " - Catalogue";
			}
		}
		if(!$select_famille) {
			header("Location: ".$base_catalogue_url."");
			exit();
		}
	}
	$select_sous_famille = FALSE;
	$sous_famille_id = null;
	$sous_famille_slug = null;
	if(isset($_GET['sous-famille'])) {
		$sous_famille_slug = preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['sous-famille']);
		if($sous_famille_slug !== '') {
			$sfStmt = $bdd->prepare("SELECT id, nom, famille_id FROM ob_catalogue_sous_familles WHERE slug = :slug LIMIT 1");
			$sfStmt->bindParam(':slug', $sous_famille_slug);
			$sfStmt->execute();
			if($sfStmt->rowCount() > 0) {
				$sf = $sfStmt->fetch(PDO::FETCH_OBJ);
				$sous_famille_id = (int) $sf->id;
				$famille_id = (int) $sf->famille_id;
				$select_sous_famille = TRUE;
				$pagename = (string) $sf->nom . " - Catalogue";
			}
		}
		if(!$select_sous_famille) {
			header("Location: ".$base_catalogue_url."");
			exit();
		}
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

	$select_pack = FALSE;
	$pack_slug = null;
	$allowed_pack_by_univers = [
		'bieres' => ['bouteilles-canettes', 'futs'],
		'vins' => ['bouteilles', 'bib'],
		'spiritueux' => ['bouteilles', 'futs'],
		'softs' => ['bouteilles', 'futs'],
		'promotions' => [],
	];
	if(isset($_GET['pack'])) {
		$pack_slug = preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['pack']);
		if($pack_slug !== '' && isset($allowed_pack_by_univers[$univers]) && in_array($pack_slug, $allowed_pack_by_univers[$univers], true)) {
			$select_pack = TRUE;
			switch($pack_slug) {
				case 'bouteilles-canettes': $pagename = "Bouteilles et canettes - Catalogue"; break;
				case 'futs': $pagename = "Fûts - Catalogue"; break;
				case 'bouteilles': $pagename = "Bouteilles - Catalogue"; break;
				case 'bib': $pagename = "BIB - Catalogue"; break;
			}
		} else {
			header("Location: ".$base_catalogue_url."");
			exit();
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
						<?php if($brasseries_select || $select_pack || $select_categorie || $select_famille || $select_sous_famille || $select_degre || $select_contenance) { ?>
							<div class="catalogue-sort">
								<?php if($brasseries_select) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-id="<?php echo $b->id; ?>" data-titre="<?php echo $b->name; ?>" id="tri-prix">
								<?php } elseif($select_pack) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-pack="<?php echo htmlspecialchars($pack_slug, ENT_QUOTES, 'UTF-8'); ?>" id="tri-prix-pack">
								<?php } elseif($select_sous_famille) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-sous-famille="<?php echo htmlspecialchars($sous_famille_slug, ENT_QUOTES, 'UTF-8'); ?>" id="tri-prix-sous-famille">
								<?php } elseif($select_famille) { ?>
									<select data-base="<?php echo $base_catalogue_url; ?>" data-famille="<?php echo htmlspecialchars($famille_slug, ENT_QUOTES, 'UTF-8'); ?>" id="tri-prix-famille">
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
							<div class="catalogue-panel <?php echo ($ukey === $univers) ? 'is-active' : ''; ?>" data-panel="<?php echo $ukey; ?>">
								<div class="menu-grid menu-grid--<?php echo $ukey; ?>">
									<?php if($ukey === 'bieres') { ?>
										<div class="menu-col">
											<div class="menu-block-head">Famille > 20</div>
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/bieres">Toutes les bières</a>
											<div class="menu-block-head">Sous-famille > 8 à 19</div>
												<a class="menu-pill" href="<?php echo $url; ?>/univers/bieres/pack/bouteilles-canettes">Bouteilles et canettes</a>
											<div class="menu-block-head">Sous-famille > 1 à 7</div>
												<a class="menu-pill" href="<?php echo $url; ?>/univers/bieres/pack/futs">Fûts</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Catégorie > 1 à 15 + 33</div>
											<div class="menu-section-title">Couleur</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/3">Blanche</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/7">Blonde</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/4">Ambrée</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/bieres">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Catégorie > 1 à 15 + 33</div>
											<div class="menu-section-title">Style</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/1">IPA</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/7">Lager</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/categorie/5">Stout</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/bieres">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Fabricant (à trier)</div>
											<div class="menu-section-title">Brasserie</div>
											<?php if(!empty($menu['fabricants'])) { $limit = 3; $i = 0; foreach($menu['fabricants'] as $fab) { if($i >= $limit) break; $i++; ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/<?php echo filterNom($fab->name)."-".$fab->id; ?>"><?php echo htmlspecialchars($fab->name, ENT_QUOTES, 'UTF-8'); ?></a>
											<?php } } ?>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/bieres">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Pays</div>
											<div class="menu-section-title">Pays</div>
											<?php if(!empty($menu['pays'])) { $limit = 3; $i = 0; foreach($menu['pays'] as $pays) { if($i >= $limit) break; $i++; ?>
												<a class="menu-link" href="<?php echo $url; ?>/univers/bieres/pays/<?php echo rawurlencode($pays); ?>"><?php echo htmlspecialchars($pays, ENT_QUOTES, 'UTF-8'); ?></a>
											<?php } } ?>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/bieres">Voir tout</a>
										</div>
									<?php } elseif($ukey === 'vins') { ?>
										<div class="menu-col">
											<div class="menu-block-head">Famille > 10</div>
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/vins">Tous les vins</a>
											<div class="menu-block-head">Tout sauf Conditionnement > 4</div>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/vins/pack/bouteilles">Bouteilles</a>
											<div class="menu-block-head">Conditionnement > 4</div>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/vins/pack/bib">BIB</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Catégorie > 20 à 26</div>
											<div class="menu-section-title">Type</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/categorie/20">Rouge</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/categorie/22">Blanc</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/categorie/21">Rosé</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/vins">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Sous-famille > 4 à 99</div>
											<div class="menu-section-title">Appellation</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/sous-famille/igp-oc">Pays d’Oc</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/sous-famille/bordeaux">Bordeaux</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/sous-famille/cotes-du-rhone">Côtes-du-Rhône</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/vins">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Fabricant (à trier)</div>
											<div class="menu-section-title">Domaine</div>
											<span class="menu-text">Plaisance</span>
											<span class="menu-text">Le Clos du Gravillas</span>
											<span class="menu-text">Anne de Joyeuse</span>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/vins">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Sous-famille > 4 à 99 (à trier)</div>
											<div class="menu-section-title">Région</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/sous-famille/alsace">Alsace</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins/sous-famille/aoc-languedoc">Languedoc</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/vins">Sud-Ouest</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/vins">Voir tout</a>
										</div>
									<?php } elseif($ukey === 'spiritueux') { ?>
										<div class="menu-col">
											<div class="menu-block-head">Famille > 1</div>
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/spiritueux">Tous les spiritueux</a>
											<div class="menu-block-head">Sous-famille > ?</div>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/spiritueux/pack/bouteilles">Bouteilles</a>
											<div class="menu-block-head">Tout sauf la sous-famille à créer</div>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/spiritueux/pack/futs">Fûts</a>
											<div class="menu-block-head">Sous-famille à créer</div>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Sous-famille > 1 à 84</div>
											<div class="menu-section-title">Type</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/spiritueux/sous-famille/whisky">Whisky</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/spiritueux/sous-famille/rhums-tiers">Rhum</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/spiritueux/sous-famille/armagnac">Armagnac</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/spiritueux">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Fabricant (à trier)</div>
											<div class="menu-section-title">Distillerie</div>
											<span class="menu-text">Saint-James</span>
											<span class="menu-text">Springbank</span>
											<span class="menu-text">Clairin</span>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/spiritueux">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Pays</div>
											<div class="menu-section-title">Pays</div>
											<span class="menu-text">France</span>
											<span class="menu-text">Japon</span>
											<span class="menu-text">Écosse</span>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/spiritueux">Voir tout</a>
										</div>
									<?php } elseif($ukey === 'softs') { ?>
										<div class="menu-col">
											<div class="menu-block-head">Famille > 40 à 75</div>
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/softs">Tous les softs</a>
											<div class="menu-block-head">Famille > 40 à 75</div>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/softs/pack/bouteilles">Bouteilles</a>
											<a class="menu-pill" href="<?php echo $url; ?>/univers/softs/pack/futs">Fûts</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Famille 40 > 75</div>
											<div class="menu-section-title">Type</div>
											<a class="menu-link" href="<?php echo $url; ?>/univers/softs/famille/softs">Boissons sucrées</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/softs/famille/jus-de-fruit">Jus de fruit</a>
											<a class="menu-link" href="<?php echo $url; ?>/univers/softs/famille/eaux">Eaux</a>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/softs">Voir tout</a>
										</div>
										<div class="menu-col">
											<div class="menu-block-head">Fabricant (à trier)</div>
											<div class="menu-section-title">Marque</div>
											<span class="menu-text">Giffard</span>
											<span class="menu-text">Rauch</span>
											<span class="menu-text">Fever Tree</span>
											<span class="menu-etc">etc.</span>
											<a class="menu-more" href="<?php echo $url; ?>/univers/softs">Voir tout</a>
										</div>
									<?php } else { ?>
										<div class="menu-col">
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
										</div>
									<?php } ?>
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
					<?php } elseif($select_sous_famille && $sous_famille_id) { ?>
						<?php
							$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
							switch($trier_prix) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE sous_famille_id = ".(int) $sous_famille_id." AND marque IN ('1','2') ORDER BY prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE sous_famille_id = ".(int) $sous_famille_id." AND marque IN ('1','2') ORDER BY prix_ht+droits DESC, marque DESC";
								break;
								default:
									$sql = "SELECT * FROM ob_catalogue_produits WHERE sous_famille_id = ".(int) $sous_famille_id." AND marque IN ('1','2') ORDER BY contenance DESC, marque DESC";
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
					<?php } elseif($select_famille && $famille_id) { ?>
						<?php
							$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
							switch($trier_prix) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE famille_id = ".(int) $famille_id." AND marque IN ('1','2') ORDER BY prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE famille_id = ".(int) $famille_id." AND marque IN ('1','2') ORDER BY prix_ht+droits DESC, marque DESC";
								break;
								default:
									$sql = "SELECT * FROM ob_catalogue_produits WHERE famille_id = ".(int) $famille_id." AND marque IN ('1','2') ORDER BY contenance DESC, marque DESC";
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
							$universeWhere = "categorie IN ($inCats)";
							if(in_array($univers, ['vins','spiritueux'], true)) {
								$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
								if(!empty($slugs)) {
									$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
									$universeWhere = "(categorie IN ($inCats) OR famille_id IN (SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)))";
								}
							}
							$min = (float) $degre_bucket['min'];
							$max = $degre_bucket['max'] === null ? null : (float) $degre_bucket['max'];
							$labelDegre = $degre_bucket['label'];
							switch(@$_GET['trier_prix']) {
								case "croissant":
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND marque IN ('1','2') ORDER by prix_ht+droits, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND degre < $max AND marque IN ('1','2') ORDER by prix_ht+droits, marque DESC";
									}
								break;
								case "decroissant":
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND marque IN ('1','2') ORDER by prix_ht+droits DESC, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND degre < $max AND marque IN ('1','2') ORDER by prix_ht+droits DESC, marque DESC";
									}
								break;
								default:
									if($max === null) {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND marque IN ('1','2') ORDER by contenance DESC, marque DESC";
									} else {
										$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND degre >= $min AND degre < $max AND marque IN ('1','2') ORDER by contenance DESC, marque DESC";
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
							$universeWhere = "categorie IN ($inCats)";
							if(in_array($univers, ['vins','spiritueux'], true)) {
								$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
								if(!empty($slugs)) {
									$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
									$universeWhere = "(categorie IN ($inCats) OR famille_id IN (SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)))";
								}
							}
							$contenance = (float) $contenance_value;
							switch(@$_GET['trier_prix']) {
								case "croissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND contenance = $contenance AND marque IN ('1','2') ORDER by prix_ht+droits, marque DESC";
								break;
								case "decroissant":
									$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND contenance = $contenance AND marque IN ('1','2') ORDER by prix_ht+droits DESC, marque DESC";
								break;
								default:
									$sql = "SELECT * FROM ob_catalogue_produits WHERE $universeWhere AND contenance = $contenance AND marque IN ('1','2') ORDER by contenance DESC, marque DESC";
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
										<?php if($select_pack) { ?>
											<?php
												$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
												$whereParts = [];
												$joins = '';
												if(!empty($univers_definitions[$univers]['categorie_ids'])) {
													$inCats = implode(',', array_map('intval', $univers_definitions[$univers]['categorie_ids']));
															$catWhere = "p.categorie IN ($inCats)";
															if(in_array($univers, ['vins','spiritueux'], true)) {
																// TARIFINTERNET_COMPLET contient beaucoup d'articles vins/spiritueux avec categorie=0.
																// Pour garder les packs cohérents, on inclut aussi via la taxonomie (familles).
																$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
																$ids = [];
																if(!empty($slugs)) {
																	$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
																	$idStmt = $bdd->query("SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)");
																	while($r = $idStmt->fetch(PDO::FETCH_OBJ)) { $ids[] = (int) $r->id; }
																}
																$ids = array_values(array_unique(array_filter($ids)));
																if(!empty($ids)) {
																	$whereParts[] = "($catWhere OR p.famille_id IN (".implode(',', array_map('intval', $ids))."))";
																} else {
																	$whereParts[] = $catWhere;
																}
															} else {
																$whereParts[] = $catWhere;
															}
												} else {
													// Univers sans catégories: on limite par familles (taxonomie importée)
													$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
													$ids = [];
													if(!empty($slugs)) {
														$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
														$idStmt = $bdd->query("SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)");
														while($r = $idStmt->fetch(PDO::FETCH_OBJ)) { $ids[] = (int) $r->id; }
													}
													$ids = array_values(array_unique(array_filter($ids)));
													if(!empty($ids)) {
														$whereParts[] = "p.famille_id IN (".implode(',', array_map('intval', $ids)).")";
													} else {
														$whereParts[] = "1=0";
													}
												}
												$whereParts[] = "p.marque IN ('1','2')";
												$pack = $pack_slug;
												$packCondition = '1=1';
												if($univers === 'vins') {
													// Données réelles: les BIB ne sont pas fiables via condition_vente.
													// On s'appuie sur la sous-famille, le libellé et la contenance.
													$joins = " LEFT JOIN ob_catalogue_sous_familles sf ON sf.id = p.sous_famille_id ";
													$isFut = "(UPPER(COALESCE(sf.nom,'')) LIKE '%FUT%' OR UPPER(p.nom) LIKE '%FUT%' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
													$isBib = "(UPPER(COALESCE(sf.nom,'')) LIKE '%BIB%' OR UPPER(p.nom) LIKE '%BIB%' OR (p.contenance IN (300,500,1000) AND UPPER(p.nom) NOT LIKE '%MAGNUM%'))";
													if($pack === 'bib') {
														$packCondition = $isBib;
													} elseif($pack === 'bouteilles') {
														// Menu vins: seulement "Bouteilles" vs "BIB" → tout le non-BIB va en "Bouteilles".
														$packCondition = "NOT $isBib";
													}
												} else {
													// Heuristique "Fût": on se base sur la sous-famille (si dispo) et le libellé produit
													$joins = " LEFT JOIN ob_catalogue_sous_familles sf ON sf.id = p.sous_famille_id ";
													$isFut = "(UPPER(COALESCE(sf.nom,'')) LIKE '%FUT%' OR UPPER(p.nom) LIKE '%FUT%' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
													if($pack === 'futs') {
														$packCondition = $isFut;
													} else {
														$packCondition = "NOT $isFut";
													}
												}
												$whereParts[] = $packCondition;
												$where = implode(' AND ', $whereParts);
												switch($trier_prix) {
													case 'croissant':
														$order = 'ORDER BY p.prix_ht+p.droits, p.marque DESC';
													break;
													case 'decroissant':
														$order = 'ORDER BY p.prix_ht+p.droits DESC, p.marque DESC';
													break;
													default:
														$order = 'ORDER BY p.contenance DESC, p.marque DESC';
												}
												$sql = "SELECT p.* FROM ob_catalogue_produits p $joins WHERE $where $order";
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
											<?php if($univers === 'bieres') { ?>
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
											<?php } else { ?>
												<?php
													$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
													$whereParts = [];
													if(!empty($univers_definitions[$univers]['categorie_ids'])) {
														$inCats = implode(',', array_map('intval', $univers_definitions[$univers]['categorie_ids']));
																$catWhere = "p.categorie IN ($inCats)";
																if(in_array($univers, ['vins','spiritueux'], true)) {
																	$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
																	$ids = [];
																	if(!empty($slugs)) {
																		$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
																		$idStmt = $bdd->query("SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)");
																		while($r = $idStmt->fetch(PDO::FETCH_OBJ)) { $ids[] = (int) $r->id; }
																	}
																	$ids = array_values(array_unique(array_filter($ids)));
																	if(!empty($ids)) {
																		$whereParts[] = "($catWhere OR p.famille_id IN (".implode(',', array_map('intval', $ids))."))";
																	} else {
																		$whereParts[] = $catWhere;
																	}
																} else {
																	$whereParts[] = $catWhere;
																}
													} else {
														$slugs = isset($univers_famille_filter_slugs[$univers]) ? $univers_famille_filter_slugs[$univers] : [];
														$ids = [];
														if(!empty($slugs)) {
															$inSlugs = implode(',', array_map(function($s){ return "'".addslashes($s)."'"; }, $slugs));
															$idStmt = $bdd->query("SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs)");
															while($r = $idStmt->fetch(PDO::FETCH_OBJ)) { $ids[] = (int) $r->id; }
														}
														$ids = array_values(array_unique(array_filter($ids)));
														if(!empty($ids)) {
															$whereParts[] = "p.famille_id IN (".implode(',', array_map('intval', $ids)).")";
														} else {
															$whereParts[] = "1=0";
														}
													}
													$whereParts[] = "p.marque IN ('1','2')";
													$where = implode(' AND ', $whereParts);
													switch($trier_prix) {
														case 'croissant':
															$order = 'ORDER BY p.prix_ht+p.droits, p.marque DESC';
														break;
														case 'decroissant':
															$order = 'ORDER BY p.prix_ht+p.droits DESC, p.marque DESC';
														break;
														default:
															$order = 'ORDER BY p.contenance DESC, p.marque DESC';
													}
													$sql = "SELECT p.* FROM ob_catalogue_produits p WHERE $where $order";
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
											<?php } ?>
										<?php } ?>
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
