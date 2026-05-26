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
			'famille_codes' => [20],
		],
		'vins' => [
			'label' => 'Vins',
			'categorie_ids' => [20,21,22,23,24,25,26],
			'famille_codes' => [10],
		],
		'spiritueux' => [
			'label' => 'Spiritueux',
			'categorie_ids' => [30,31],
			'famille_codes' => [1],
		],
		'softs' => [
			'label' => 'Softs',
			'categorie_ids' => [],
			'famille_code_min' => 40,
			'famille_code_max' => 75,
		],
		'promotions' => [
			'label' => 'Promotions',
			'categorie_ids' => [],
		],
	];
	// Filtrage des familles/sous-familles affichées par univers (menu PDF)
	$univers_famille_filter_slugs = [
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
	$show_univers_products = (isset($_GET['listing']) && $_GET['listing'] === 'produits');
	$menu_scope = isset($_GET['menu_scope']) ? preg_replace('/[^a-z0-9\-_]/i', '', (string) $_GET['menu_scope']) : '';
	$show_scope_products = ($menu_scope !== '');
	$build_universe_where = function($universKey, $alias = 'p') use ($univers_definitions, $univers_famille_filter_slugs) {
		if(!isset($univers_definitions[$universKey])) {
			return '1=0';
		}
		$def = $univers_definitions[$universKey];
		$prefix = $alias ? ($alias.'.') : '';
		$parts = [];
		if(!empty($def['categorie_ids'])) {
			$inCats = implode(',', array_map('intval', $def['categorie_ids']));
			$parts[] = $prefix."categorie IN ($inCats)";
		}
		if(isset($def['famille_codes']) && !empty($def['famille_codes'])) {
			$inFamCodes = implode(',', array_map('intval', $def['famille_codes']));
			$parts[] = $prefix."famille_id IN (SELECT id FROM ob_catalogue_familles WHERE code IN ($inFamCodes))";
		}
		if(isset($def['famille_code_min']) && isset($def['famille_code_max'])) {
			$minCode = (int) $def['famille_code_min'];
			$maxCode = (int) $def['famille_code_max'];
			if($minCode > 0 && $maxCode >= $minCode) {
				$parts[] = $prefix."famille_id IN (SELECT id FROM ob_catalogue_familles WHERE code BETWEEN $minCode AND $maxCode)";
			}
		}
		if(isset($univers_famille_filter_slugs[$universKey]) && !empty($univers_famille_filter_slugs[$universKey])) {
			$inSlugs = implode(',', array_map(function($slug) {
				return "'".addslashes($slug)."'";
			}, $univers_famille_filter_slugs[$universKey]));
			$parts[] = $prefix."famille_id IN (SELECT id FROM ob_catalogue_familles WHERE slug IN ($inSlugs))";
		}
		if(empty($parts)) {
			return '1=0';
		}
		return '('.implode(' OR ', $parts).')';
	};

	// Pré-calcul des données de menu (catégories, fabricants, pays) par univers
	$univers_menu = [];
	foreach($univers_definitions as $key => $def) {
		$univers_menu[$key] = [
			'familles' => [],
			'familles_top' => [],
			'sous_familles_top' => [],
			'sous_familles_all' => [],
			'fabricants' => [],
			'fabricants_all' => [],
			'pays' => [],
			'pays_all' => [],
			'categories' => [],
			'degres' => [],
			'contenances' => [],
			'fabriquant_ids' => [],
		];
		$universWhere = $build_universe_where($key, 'p');
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
		if(isset($univers_famille_filter_slugs[$key]) && !empty($univers_famille_filter_slugs[$key])) {
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
		foreach($familiesList as $familyItem) {
			if(empty($familyItem['sous_familles'])) {
				continue;
			}
			foreach($familyItem['sous_familles'] as $subFamilyItem) {
				$univers_menu[$key]['sous_familles_all'][$subFamilyItem['slug']] = $subFamilyItem;
			}
		}
		$univers_menu[$key]['sous_familles_all'] = array_values($univers_menu[$key]['sous_familles_all']);
		$famillesTopStmt = $bdd->query("SELECT f.slug, f.nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			INNER JOIN ob_catalogue_familles f ON p.famille_id = f.id
			WHERE $universWhere AND p.famille_id IS NOT NULL
			GROUP BY f.id
			ORDER BY total DESC, f.nom");
		while($famTop = $famillesTopStmt->fetch(PDO::FETCH_OBJ)) {
			$univers_menu[$key]['familles_top'][] = [
				'slug' => (string) $famTop->slug,
				'nom' => (string) $famTop->nom,
				'total' => (int) $famTop->total,
			];
		}
		$sousFamillesTopStmt = $bdd->query("SELECT sf.slug, sf.nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			INNER JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
			WHERE $universWhere AND p.sous_famille_id IS NOT NULL
			GROUP BY sf.id
			ORDER BY total DESC, sf.nom");
		while($sfTop = $sousFamillesTopStmt->fetch(PDO::FETCH_OBJ)) {
			$univers_menu[$key]['sous_familles_top'][] = [
				'slug' => (string) $sfTop->slug,
				'nom' => (string) $sfTop->nom,
				'total' => (int) $sfTop->total,
			];
		}
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
		$fabricantsAllStmt = $bdd->query("SELECT p.brasserie AS code, COALESCE(f.nom, b.name) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			LEFT JOIN ob_catalogue_fabriquants f ON f.code = p.brasserie
			LEFT JOIN ob_brasseries b ON b.id_fabriquant = p.brasserie AND b.hiden = '1'
			WHERE $universWhere AND p.brasserie <> 0
			GROUP BY p.brasserie, COALESCE(f.nom, b.name)
			ORDER BY total DESC, nom");
		while($fabAll = $fabricantsAllStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($fabAll->code) || empty($fabAll->nom)) {
				continue;
			}
			$univers_menu[$key]['fabricants_all'][] = [
				'code' => (int) $fabAll->code,
				'nom' => (string) $fabAll->nom,
				'total' => (int) $fabAll->total,
			];
		}
		$paysAllStmt = $bdd->query("SELECT p.pays_code AS code, COALESCE(cp.nom, p.pays_code) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			LEFT JOIN ob_catalogue_pays cp ON cp.code = p.pays_code
			WHERE $universWhere AND p.pays_code IS NOT NULL AND p.pays_code <> ''
			GROUP BY p.pays_code, COALESCE(cp.nom, p.pays_code)
			ORDER BY total DESC, nom");
		while($paysAll = $paysAllStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($paysAll->code)) {
				continue;
			}
			$univers_menu[$key]['pays_all'][] = [
				'code' => (string) $paysAll->code,
				'nom' => (string) $paysAll->nom,
				'total' => (int) $paysAll->total,
			];
		}
		$categoriesStmt = $bdd->query("SELECT p.categorie AS code, COALESCE(c.nom, CONCAT('Catégorie ', p.categorie)) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			LEFT JOIN ob_catalogue_categories c ON c.code = p.categorie
			WHERE $universWhere AND p.categorie <> 0
			GROUP BY p.categorie, COALESCE(c.nom, CONCAT('Catégorie ', p.categorie))
			ORDER BY total DESC, nom");
		while($cat = $categoriesStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($cat->code)) {
				continue;
			}
			$univers_menu[$key]['categories'][] = [
				'code' => (int) $cat->code,
				'nom' => (string) $cat->nom,
				'total' => (int) $cat->total,
			];
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
	$current_univers_menu = isset($univers_menu[$univers]) ? $univers_menu[$univers] : [
		'familles' => [],
		'familles_top' => [],
		'sous_familles_top' => [],
		'fabricants_all' => [],
		'pays_all' => [],
		'categories' => [],
	];
	$menu_filter_href = function($universKey, $filters = array()) use ($url) {
		$clean = array();
		foreach($filters as $key => $value) {
			if($value === null || $value === '') {
				continue;
			}
			$clean[$key] = $value;
		}
		$query = http_build_query($clean);
		return $url.'/univers/'.$universKey.'/produits'.($query !== '' ? '?'.$query : '');
	};
	$submenu_titles_by_univers = array(
		'bieres' => 'Toutes les brasseries',
		'vins' => 'Tous les domaines',
		'spiritueux' => 'Toutes les distilleries',
		'softs' => 'Toutes les marques',
	);
	$beer_color_labels = array('blanche', 'blonde', 'ambre', 'ambree', 'rousse', 'brune', 'red ale', 'fruit');
	$beer_color_items = array();
	$beer_style_items = array();
	foreach($univers_menu['bieres']['categories'] as $categoryItem) {
		$label = mb_strtolower((string) $categoryItem['nom'], 'UTF-8');
		$isColor = false;
		foreach($beer_color_labels as $needle) {
			if(strpos($label, $needle) !== false) {
				$isColor = true;
				break;
			}
		}
		if($isColor) {
			$beer_color_items[] = $categoryItem;
		} else {
			$beer_style_items[] = $categoryItem;
		}
	}
	$submenu_config_by_scope = array(
		'bieres-couleur' => array(
			'title' => 'Toutes les couleurs',
			'items' => $beer_color_items,
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('bieres', array('menu_scope' => 'bieres-couleur', 'filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-style' => array(
			'title' => 'Tous les styles',
			'items' => $beer_style_items,
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('bieres', array('menu_scope' => 'bieres-style', 'filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-brasserie' => array(
			'title' => 'Toutes les brasseries',
			'items' => $univers_menu['bieres']['fabricants_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('bieres', array('menu_scope' => 'bieres-brasserie', 'filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['bieres']['pays_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('bieres', array('menu_scope' => 'bieres-pays', 'filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['vins']['categories'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('vins', array('menu_scope' => 'vins-type', 'filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-appellation' => array(
			'title' => 'Toutes les appellations',
			'items' => $univers_menu['vins']['sous_familles_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('vins', array('menu_scope' => 'vins-appellation', 'filtre_sous_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-domaine' => array(
			'title' => 'Tous les domaines',
			'items' => $univers_menu['vins']['fabricants_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('vins', array('menu_scope' => 'vins-domaine', 'filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['vins']['pays_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('vins', array('menu_scope' => 'vins-pays', 'filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['spiritueux']['sous_familles_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('spiritueux', array('menu_scope' => 'spiritueux-type', 'filtre_sous_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-distillerie' => array(
			'title' => 'Toutes les distilleries',
			'items' => $univers_menu['spiritueux']['fabricants_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('spiritueux', array('menu_scope' => 'spiritueux-distillerie', 'filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['spiritueux']['pays_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('spiritueux', array('menu_scope' => 'spiritueux-pays', 'filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'softs-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['softs']['familles'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('softs', array('menu_scope' => 'softs-type', 'filtre_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'softs-marque' => array(
			'title' => 'Toutes les marques',
			'items' => $univers_menu['softs']['fabricants_all'],
			'build_href' => function($item) use ($menu_filter_href) {
				return $menu_filter_href('softs', array('menu_scope' => 'softs-marque', 'filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
	);
	$current_scope_submenu = (isset($submenu_config_by_scope[$menu_scope]) && !empty($submenu_config_by_scope[$menu_scope]['items'])) ? $submenu_config_by_scope[$menu_scope] : null;
	$filter_query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
	$filter_famille_slug = isset($_GET['filtre_famille']) ? preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['filtre_famille']) : '';
	$filter_sous_famille_slug = isset($_GET['filtre_sous_famille']) ? preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['filtre_sous_famille']) : '';
	$filter_categorie_code = isset($_GET['filtre_categorie']) ? (int) $_GET['filtre_categorie'] : 0;
	$filter_fabriquant_code = isset($_GET['filtre_fabriquant']) ? (int) $_GET['filtre_fabriquant'] : 0;
	$filter_pays_code = isset($_GET['filtre_pays']) ? trim((string) $_GET['filtre_pays']) : '';
	$filter_pack_slug = isset($_GET['filtre_pack']) ? preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['filtre_pack']) : '';
	$has_query_filters = ($filter_query !== '' || $filter_famille_slug !== '' || $filter_sous_famille_slug !== '' || $filter_categorie_code > 0 || $filter_fabriquant_code > 0 || $filter_pays_code !== '' || $filter_pack_slug !== '');
	$filter_famille_id = null;
	if($filter_famille_slug !== '') {
		$familleFilterStmt = $bdd->prepare("SELECT id FROM ob_catalogue_familles WHERE slug = :slug LIMIT 1");
		$familleFilterStmt->bindParam(':slug', $filter_famille_slug);
		$familleFilterStmt->execute();
		$filterFamille = $familleFilterStmt->fetch(PDO::FETCH_OBJ);
		if($filterFamille && isset($filterFamille->id)) {
			$filter_famille_id = (int) $filterFamille->id;
		}
	}
	$filter_sous_famille_id = null;
	if($filter_sous_famille_slug !== '') {
		$sousFamilleFilterStmt = $bdd->prepare("SELECT id, famille_id FROM ob_catalogue_sous_familles WHERE slug = :slug LIMIT 1");
		$sousFamilleFilterStmt->bindParam(':slug', $filter_sous_famille_slug);
		$sousFamilleFilterStmt->execute();
		$filterSousFamille = $sousFamilleFilterStmt->fetch(PDO::FETCH_OBJ);
		if($filterSousFamille && isset($filterSousFamille->id)) {
			$filter_sous_famille_id = (int) $filterSousFamille->id;
			if(!$filter_famille_id) {
				$filter_famille_id = (int) $filterSousFamille->famille_id;
			}
		}
	}
	$available_sub_familles = [];
	foreach($current_univers_menu['familles'] as $familleMenu) {
		if($filter_famille_slug !== '' && $familleMenu['slug'] !== $filter_famille_slug) {
			continue;
		}
		foreach($familleMenu['sous_familles'] as $sousFamilleMenu) {
			$available_sub_familles[] = $sousFamilleMenu;
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
	$effective_pack_slug = null;
	if($select_pack) {
		$effective_pack_slug = $pack_slug;
	} elseif($filter_pack_slug !== '' && isset($allowed_pack_by_univers[$univers]) && in_array($filter_pack_slug, $allowed_pack_by_univers[$univers], true)) {
		$effective_pack_slug = $filter_pack_slug;
	}
	$build_pack_scope = function($universKey, $packSlug, $alias = 'p') {
		$packSlug = trim((string) $packSlug);
		if($packSlug === '' || $packSlug === 'all') {
			return array('joins' => '', 'where' => '1=1');
		}
		$prefix = $alias ? ($alias.'.') : '';
		$packSousFamilleAlias = $alias ? ('sf_pack_'.$alias) : 'sf_pack';
		$joins = " LEFT JOIN ob_catalogue_sous_familles ".$packSousFamilleAlias." ON ".$packSousFamilleAlias.".id = ".$prefix."sous_famille_id ";
		$upperNom = "UPPER(".$prefix."nom)";
		$isFut = "(UPPER(COALESCE(".$packSousFamilleAlias.".nom,'')) LIKE '%FUT%' OR $upperNom LIKE '%FUT%' OR $upperNom REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
		if($universKey === 'vins') {
			$isBib = "(UPPER(COALESCE(".$packSousFamilleAlias.".nom,'')) LIKE '%BIB%' OR $upperNom LIKE '%BIB%' OR (".$prefix."contenance IN (300,500,1000) AND $upperNom NOT LIKE '%MAGNUM%'))";
			if($packSlug === 'bib') {
				return array('joins' => $joins, 'where' => $isBib);
			}
			if($packSlug === 'bouteilles') {
				return array('joins' => $joins, 'where' => 'NOT '.$isBib);
			}
		}
		if($packSlug === 'futs') {
			return array('joins' => $joins, 'where' => $isFut);
		}
		if(in_array($packSlug, array('bouteilles-canettes', 'bouteilles'), true)) {
			return array('joins' => $joins, 'where' => 'NOT '.$isFut);
		}
		return array('joins' => '', 'where' => '1=1');
	};
	$build_menu_dataset = function($universKey, $packSlug = 'all') use ($bdd, $build_universe_where, $build_pack_scope, $univers_famille_filter_slugs, $degre_buckets_definitions, $numeric_slug, $numeric_label) {
		$menuData = array(
			'familles' => array(),
			'familles_top' => array(),
			'sous_familles_top' => array(),
			'sous_familles_all' => array(),
			'fabricants_all' => array(),
			'pays_all' => array(),
			'categories' => array(),
			'degres' => array(),
			'contenances' => array(),
			'fabriquant_ids' => array(),
		);
		$scope = $build_pack_scope($universKey, $packSlug, 'p');
		$joins = $scope['joins'];
		$whereParts = array($build_universe_where($universKey, 'p'), "p.marque IN ('1','2')");
		if($scope['where'] !== '1=1') {
			$whereParts[] = $scope['where'];
		}
		$where = implode(' AND ', $whereParts);

		$taxStmt = $bdd->query("SELECT f.id AS famille_id, f.nom AS famille_nom, f.slug AS famille_slug, sf.id AS sous_famille_id, sf.nom AS sous_famille_nom, sf.slug AS sous_famille_slug
			FROM ob_catalogue_produits p
			INNER JOIN ob_catalogue_familles f ON p.famille_id = f.id
			LEFT JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
			".$joins."
			WHERE $where AND p.famille_id IS NOT NULL
			ORDER BY f.nom, sf.nom");
		$familles = array();
		while($t = $taxStmt->fetch(PDO::FETCH_OBJ)) {
			$fid = (int) $t->famille_id;
			if(!isset($familles[$fid])) {
				$familles[$fid] = array(
					'id' => $fid,
					'nom' => (string) $t->famille_nom,
					'slug' => (string) $t->famille_slug,
					'sous_familles' => array(),
				);
			}
			if(!empty($t->sous_famille_id)) {
				$sfid = (int) $t->sous_famille_id;
				$familles[$fid]['sous_familles'][$sfid] = array(
					'id' => $sfid,
					'nom' => (string) $t->sous_famille_nom,
					'slug' => (string) $t->sous_famille_slug,
				);
			}
		}
		foreach($familles as $fid => $familleItem) {
			$familles[$fid]['sous_familles'] = array_values($familleItem['sous_familles']);
		}
		$familiesList = array_values($familles);
		if(isset($univers_famille_filter_slugs[$universKey]) && !empty($univers_famille_filter_slugs[$universKey])) {
			$allowed = array_flip($univers_famille_filter_slugs[$universKey]);
			$filtered = array();
			foreach($familiesList as $familleItem) {
				if(isset($allowed[$familleItem['slug']])) {
					$filtered[] = $familleItem;
				}
			}
			$familiesList = $filtered;
		}
		$menuData['familles'] = $familiesList;
		foreach($familiesList as $familyItem) {
			foreach($familyItem['sous_familles'] as $subFamilyItem) {
				$menuData['sous_familles_all'][$subFamilyItem['slug']] = $subFamilyItem;
			}
		}
		$menuData['sous_familles_all'] = array_values($menuData['sous_familles_all']);

		$famillesTopStmt = $bdd->query("SELECT f.slug, f.nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			INNER JOIN ob_catalogue_familles f ON p.famille_id = f.id
			".$joins."
			WHERE $where AND p.famille_id IS NOT NULL
			GROUP BY f.id
			ORDER BY total DESC, f.nom");
		while($famTop = $famillesTopStmt->fetch(PDO::FETCH_OBJ)) {
			$menuData['familles_top'][] = array(
				'slug' => (string) $famTop->slug,
				'nom' => (string) $famTop->nom,
				'total' => (int) $famTop->total,
			);
		}

		$sousFamillesTopStmt = $bdd->query("SELECT sf.slug, sf.nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			INNER JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id
			".$joins."
			WHERE $where AND p.sous_famille_id IS NOT NULL
			GROUP BY sf.id
			ORDER BY total DESC, sf.nom");
		while($sfTop = $sousFamillesTopStmt->fetch(PDO::FETCH_OBJ)) {
			$menuData['sous_familles_top'][] = array(
				'slug' => (string) $sfTop->slug,
				'nom' => (string) $sfTop->nom,
				'total' => (int) $sfTop->total,
			);
		}

		$fabricantsStmt = $bdd->query("SELECT p.brasserie AS code, COALESCE(f.nom, b.name, CONCAT('Fabriquant ', p.brasserie)) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			".$joins."
			LEFT JOIN ob_catalogue_fabriquants f ON f.code = p.brasserie
			LEFT JOIN ob_brasseries b ON b.id_fabriquant = p.brasserie AND b.hiden = '1'
			WHERE $where AND p.brasserie <> 0
			GROUP BY p.brasserie, COALESCE(f.nom, b.name, CONCAT('Fabriquant ', p.brasserie))
			ORDER BY total DESC, nom");
		while($fab = $fabricantsStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($fab->code) || empty($fab->nom)) {
				continue;
			}
			$menuData['fabricants_all'][] = array(
				'code' => (int) $fab->code,
				'nom' => (string) $fab->nom,
				'total' => (int) $fab->total,
			);
			$menuData['fabriquant_ids'][] = (int) $fab->code;
		}
		$menuData['fabriquant_ids'] = array_values(array_unique(array_filter($menuData['fabriquant_ids'])));

		$paysStmt = $bdd->query("SELECT p.pays_code AS code, COALESCE(cp.nom, p.pays_code) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			".$joins."
			LEFT JOIN ob_catalogue_pays cp ON cp.code = p.pays_code
			WHERE $where AND p.pays_code IS NOT NULL AND p.pays_code <> ''
			GROUP BY p.pays_code, COALESCE(cp.nom, p.pays_code)
			ORDER BY total DESC, nom");
		while($pays = $paysStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($pays->code)) {
				continue;
			}
			$menuData['pays_all'][] = array(
				'code' => (string) $pays->code,
				'nom' => (string) $pays->nom,
				'total' => (int) $pays->total,
			);
		}

		$categoriesStmt = $bdd->query("SELECT p.categorie AS code, COALESCE(c.nom, CONCAT('Catégorie ', p.categorie)) AS nom, COUNT(*) AS total
			FROM ob_catalogue_produits p
			".$joins."
			LEFT JOIN ob_catalogue_categories c ON c.code = p.categorie
			WHERE $where AND p.categorie <> 0
			GROUP BY p.categorie, COALESCE(c.nom, CONCAT('Catégorie ', p.categorie))
			ORDER BY total DESC, nom");
		while($cat = $categoriesStmt->fetch(PDO::FETCH_OBJ)) {
			if(empty($cat->code)) {
				continue;
			}
			$menuData['categories'][] = array(
				'code' => (int) $cat->code,
				'nom' => (string) $cat->nom,
				'total' => (int) $cat->total,
			);
		}

		$degrees = array();
		$degStmt = $bdd->query("SELECT DISTINCT p.degre FROM ob_catalogue_produits p ".$joins." WHERE $where AND p.degre IS NOT NULL AND p.degre > 0 ORDER BY p.degre");
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
				$menuData['degres'][] = $bucket;
			}
		}

		$contenances = array();
		$contStmt = $bdd->query("SELECT DISTINCT p.contenance FROM ob_catalogue_produits p ".$joins." WHERE $where AND p.contenance IS NOT NULL AND p.contenance > 0 ORDER BY p.contenance");
		while($c = $contStmt->fetch(PDO::FETCH_OBJ)) {
			$contenances[] = (float) $c->contenance;
		}
		$contenances = array_values(array_unique($contenances));
		sort($contenances, SORT_NUMERIC);
		foreach($contenances as $cv) {
			$menuData['contenances'][] = array(
				'value' => $cv,
				'slug' => $numeric_slug($cv),
				'label' => $numeric_label($cv),
			);
		}
		return $menuData;
	};
	$menu_pack_labels = array(
		'bouteilles-canettes' => 'Bouteilles et canettes',
		'futs' => 'Fûts',
		'bouteilles' => 'Bouteilles',
		'bib' => 'BIB',
	);
	$univers_menu_scoped = array();
	foreach($univers_definitions as $menuUniversKey => $menuUniversDef) {
		$univers_menu_scoped[$menuUniversKey] = array('all' => $univers_menu[$menuUniversKey]);
		foreach($allowed_pack_by_univers[$menuUniversKey] as $menuPackKey) {
			$univers_menu_scoped[$menuUniversKey][$menuPackKey] = $build_menu_dataset($menuUniversKey, $menuPackKey);
		}
	}
	$get_active_menu_pack = function($panelUnivers) use ($univers, $effective_pack_slug) {
		if($panelUnivers === $univers && !empty($effective_pack_slug)) {
			return $effective_pack_slug;
		}
		return 'all';
	};
	$with_menu_pack_filter = function($packKey, $filters = array()) {
		if($packKey !== 'all' && $packKey !== '') {
			$filters['filtre_pack'] = $packKey;
		}
		return $filters;
	};
	$split_beer_categories = function($menuData) use ($beer_color_labels) {
		$colors = array();
		$styles = array();
		foreach($menuData['categories'] as $categoryItem) {
			$label = mb_strtolower((string) $categoryItem['nom'], 'UTF-8');
			$isColor = false;
			foreach($beer_color_labels as $needle) {
				if(strpos($label, $needle) !== false) {
					$isColor = true;
					break;
				}
			}
			if($isColor) {
				$colors[] = $categoryItem;
			} else {
				$styles[] = $categoryItem;
			}
		}
		return array('colors' => $colors, 'styles' => $styles);
	};

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

	function ObRenderProduitsGrid($elements, $consigne = true) {
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
						<?php foreach($univers_definitions as $ukey => $udef) { $menu = $univers_menu[$ukey]; $panelActivePack = $get_active_menu_pack($ukey); $panelPackKeys = array_merge(array('all'), $allowed_pack_by_univers[$ukey]); ?>
							<div class="catalogue-panel <?php echo ($ukey === $univers) ? 'is-active' : ''; ?>" data-panel="<?php echo $ukey; ?>" data-default-pack="<?php echo htmlspecialchars($panelActivePack, ENT_QUOTES, 'UTF-8'); ?>" data-active-pack="<?php echo htmlspecialchars($panelActivePack, ENT_QUOTES, 'UTF-8'); ?>">
								<div class="menu-grid menu-grid--<?php echo $ukey; ?>">
									<?php if($ukey === 'bieres' || $ukey === 'vins' || $ukey === 'spiritueux' || $ukey === 'softs') { ?>
										<div class="menu-col menu-col--primary">
											<div class="menu-primary-stack">
												<?php if($ukey === 'bieres') { ?>
													<a class="menu-link menu-link-primary menu-pack-btn <?php echo ($panelActivePack === 'all') ? 'is-active' : ''; ?>" data-pack="all" href="<?php echo $url; ?>/univers/bieres/produits">Toutes les bières</a>
												<?php } else { ?>
													<a class="menu-link menu-link-primary menu-pack-btn <?php echo ($panelActivePack === 'all') ? 'is-active' : ''; ?>" data-pack="all" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>"><?php echo ($ukey === 'vins') ? 'Tous les vins' : (($ukey === 'spiritueux') ? 'Tous les spiritueux' : 'Tous les softs'); ?></a>
												<?php } ?>
												<?php foreach($allowed_pack_by_univers[$ukey] as $menuPackKey) { ?>
													<a class="menu-pill menu-pack-btn <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>/pack/<?php echo $menuPackKey; ?>"><?php echo htmlspecialchars($menu_pack_labels[$menuPackKey], ENT_QUOTES, 'UTF-8'); ?></a>
												<?php } ?>
											</div>
										</div>
									<?php } ?>

									<?php if($ukey === 'bieres') { ?>
										<div class="menu-col">
											<div class="menu-section-title">Couleur</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; $beerGroups = $split_beer_categories($packMenu); ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($beerGroups['colors'], 0, 3) as $catItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $catItem['code']))); ?>"><?php echo htmlspecialchars($catItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($beerGroups['colors']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'bieres-couleur'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Style</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; $beerGroups = $split_beer_categories($packMenu); ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($beerGroups['styles'], 0, 3) as $catItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $catItem['code']))); ?>"><?php echo htmlspecialchars($catItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($beerGroups['styles']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'bieres-style'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Brasserie</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['fabricants_all'], 0, 3) as $fabItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('filtre_fabriquant' => $fabItem['code']))); ?>"><?php echo htmlspecialchars($fabItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['fabricants_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'bieres-brasserie'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Pays</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['pays_all'], 0, 3) as $paysItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('filtre_pays' => $paysItem['code']))); ?>"><?php echo htmlspecialchars($paysItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['pays_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'bieres-pays'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
									<?php } elseif($ukey === 'vins') { ?>
										<div class="menu-col">
											<div class="menu-section-title">Type</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['categories'], 0, 3) as $catItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $catItem['code']))); ?>"><?php echo htmlspecialchars($catItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['categories']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'vins-type'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Appellation</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['sous_familles_top'], 0, 3) as $sfItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_sous_famille' => $sfItem['slug']))); ?>"><?php echo htmlspecialchars($sfItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['sous_familles_top']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'vins-appellation'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Domaine</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['fabricants_all'], 0, 3) as $fabItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_fabriquant' => $fabItem['code']))); ?>"><?php echo htmlspecialchars($fabItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['fabricants_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'vins-domaine'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Pays</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['pays_all'], 0, 3) as $paysItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_pays' => $paysItem['code']))); ?>"><?php echo htmlspecialchars($paysItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['pays_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'vins-pays'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
									<?php } elseif($ukey === 'spiritueux') { ?>
										<div class="menu-col">
											<div class="menu-section-title">Type</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['sous_familles_top'], 0, 3) as $sfItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('filtre_sous_famille' => $sfItem['slug']))); ?>"><?php echo htmlspecialchars($sfItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['sous_familles_top']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'spiritueux-type'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Distillerie</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['fabricants_all'], 0, 3) as $fabItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('filtre_fabriquant' => $fabItem['code']))); ?>"><?php echo htmlspecialchars($fabItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['fabricants_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'spiritueux-distillerie'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Pays</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['pays_all'], 0, 3) as $paysItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('filtre_pays' => $paysItem['code']))); ?>"><?php echo htmlspecialchars($paysItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['pays_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('spiritueux', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'spiritueux-pays'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
									<?php } elseif($ukey === 'softs') { ?>
										<div class="menu-col">
											<div class="menu-section-title">Type</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['familles_top'], 0, 3) as $famItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('softs', $with_menu_pack_filter($menuPackKey, array('filtre_famille' => $famItem['slug']))); ?>"><?php echo htmlspecialchars($famItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['familles_top']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('softs', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'softs-type'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
										<div class="menu-col">
											<div class="menu-section-title">Marque</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['fabricants_all'], 0, 3) as $fabItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('softs', $with_menu_pack_filter($menuPackKey, array('filtre_fabriquant' => $fabItem['code']))); ?>"><?php echo htmlspecialchars($fabItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['fabricants_all']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<a class="menu-more" href="<?php echo $menu_filter_href('softs', $with_menu_pack_filter($menuPackKey, array('menu_scope' => 'softs-marque'))); ?>">Voir tout</a>
												</div>
											<?php } ?>
										</div>
									<?php } else { ?>
										<div class="menu-col">
											<a class="menu-link menu-link-primary" href="<?php echo $url; ?>/univers/<?php echo $ukey; ?>">Voir tout</a>
										</div>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
						</div><!-- /.mot-cle.catalogue-megamenu -->
					<?php if(($show_univers_products || $show_scope_products) && !$brasseries_select && $current_scope_submenu !== null) { ?>
						<div class="catalogue-submenu catalogue-submenu-filters">
							<div class="catalogue-submenu-title"><?php echo $current_scope_submenu['title']; ?></div>
							<div class="catalogue-submenu-links">
								<?php foreach($current_scope_submenu['items'] as $submenuItem) { ?>
									<a class="catalogue-submenu-link" href="<?php echo htmlspecialchars($current_scope_submenu['build_href']($submenuItem), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($current_scope_submenu['get_label']($submenuItem), ENT_QUOTES, 'UTF-8'); ?></a>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
					</div><!-- /.rechercher -->
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
							if($elements->rowCount() > 0) {					
						?>
							<?php ObRenderProduitsGrid($elements); ?>
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
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements); ?>
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
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements); ?>
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
							if($elements->rowCount() > 0) {					
						?>
							<?php ObRenderProduitsGrid($elements); ?>
						<?php } ?>
					<?php } elseif($select_degre && $degre_bucket) { ?>
						<?php
							$universeWhere = $build_universe_where($univers, '');
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
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements); ?>
						<?php } ?>
					<?php } elseif($select_contenance && $contenance_value !== null) { ?>
						<?php
							$universeWhere = $build_universe_where($univers, '');
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
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements); ?>
						<?php } ?>
					<?php } elseif(($show_univers_products || $show_scope_products || $has_query_filters) && !$brasseries_select) { ?>
						<?php
							$whereParts = array();
							$params = array();
							$joins = '';
							$whereParts[] = $build_universe_where($univers, 'p');
							$whereParts[] = "p.marque IN ('1','2')";
							if($filter_famille_id) {
								$whereParts[] = 'p.famille_id = :filtre_famille_id';
								$params[':filtre_famille_id'] = (int) $filter_famille_id;
							}
							if($filter_sous_famille_id) {
								$whereParts[] = 'p.sous_famille_id = :filtre_sous_famille_id';
								$params[':filtre_sous_famille_id'] = (int) $filter_sous_famille_id;
							}
							if($filter_categorie_code > 0) {
								$whereParts[] = 'p.categorie = :filtre_categorie';
								$params[':filtre_categorie'] = $filter_categorie_code;
							}
							if($filter_fabriquant_code > 0) {
								$whereParts[] = 'p.brasserie = :filtre_fabriquant';
								$params[':filtre_fabriquant'] = $filter_fabriquant_code;
							}
							if($filter_pays_code !== '') {
								$whereParts[] = 'p.pays_code = :filtre_pays';
								$params[':filtre_pays'] = $filter_pays_code;
							}
							if($filter_query !== '') {
								$whereParts[] = '(p.nom LIKE :filtre_recherche OR p.nom_sup LIKE :filtre_recherche)';
								$params[':filtre_recherche'] = '%'.$filter_query.'%';
							}
							if($effective_pack_slug !== null) {
								$joins = ' LEFT JOIN ob_catalogue_sous_familles sf ON sf.id = p.sous_famille_id ';
								$isFut = "(UPPER(COALESCE(sf.nom,'')) LIKE '%FUT%' OR UPPER(p.nom) LIKE '%FUT%' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
								if($univers === 'vins') {
									$isBib = "(UPPER(COALESCE(sf.nom,'')) LIKE '%BIB%' OR UPPER(p.nom) LIKE '%BIB%' OR (p.contenance IN (300,500,1000) AND UPPER(p.nom) NOT LIKE '%MAGNUM%'))";
									if($effective_pack_slug === 'bib') {
										$whereParts[] = $isBib;
									} elseif($effective_pack_slug === 'bouteilles') {
										$whereParts[] = 'NOT '.$isBib;
									}
								} else {
									if($effective_pack_slug === 'futs') {
										$whereParts[] = $isFut;
									} else {
										$whereParts[] = 'NOT '.$isFut;
									}
								}
							}
							switch(@$_GET['trier_prix']) {
								case 'croissant':
									$order = 'ORDER BY p.prix_ht+p.droits, p.marque DESC';
								break;
								case 'decroissant':
									$order = 'ORDER BY p.prix_ht+p.droits DESC, p.marque DESC';
								break;
								default:
									$order = 'ORDER BY p.contenance DESC, p.marque DESC';
							}
							$sql = 'SELECT DISTINCT p.* FROM ob_catalogue_produits p'.$joins.' WHERE '.implode(' AND ', $whereParts).' '.$order;
							$elements = $bdd->prepare($sql);
							$elements->execute($params);
							if($elements->rowCount() > 0) {
						?>
							<?php ObRenderProduitsGrid($elements); ?>
						<?php } else { ?>
							<p>Aucun produit ne correspond aux filtres sélectionnés.</p>
						<?php } ?>
					<?php } else { ?>
										<?php if($select_pack) { ?>
											<?php
												$trier_prix = isset($_GET['trier_prix']) ? $_GET['trier_prix'] : null;
												$whereParts = [];
												$joins = '';
												$whereParts[] = $build_universe_where($univers, 'p');
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
												if($elements->rowCount() > 0) {
											?>
												<?php ObRenderProduitsGrid($elements); ?>
											<?php } ?>
										<?php } else { ?>
											<?php if($univers === 'bieres' && !$show_univers_products) { ?>
							<!-- PANNEL FABRICANTS -->
							<section id="brasseries-pannel">
								<?php
									$fabFilter = $univers_menu[$univers]['fabriquant_ids'];
									if(!empty($fabFilter)) {
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
													$whereParts[] = $build_universe_where($univers, 'p');
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
													if($elements->rowCount() > 0) {
												?>
													<?php ObRenderProduitsGrid($elements); ?>
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
