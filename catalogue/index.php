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
			'categorie_ids' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,28,29,32,33],
			'famille_code_min' => 20,
			'famille_code_max' => 30,
		],
		'vins' => [
			'label' => 'Vins',
			'categorie_ids' => [20,21,22,23,24,25,26,34,35,36,37],
			'famille_codes' => [10,11],
		],
		'spiritueux' => [
			'label' => 'Spiritueux',
			'categorie_ids' => [30,31],
			'famille_codes' => [1],
		],
		'softs' => [
			'label' => 'Softs',
			'categorie_ids' => [],
			'famille_codes' => [40,50,60,70,75,80,85],
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
	// Sous-familles à masquer par univers et par pack
	// '*' = tous les packs; 'futs', 'bouteilles-canettes'... = pack spécifique
	$univers_excluded_sous_famille_slugs = [
		'bieres' => [
			// Tous packs : softs mal affectés dans famille bières par l'ERP
			'*' => ['biere-pression-comptoir', 'eaux-boite-aromatisees', 'boissons-sucrees-vp'],
		],
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
			'col_2_title' => 'Style',
			'col_2_items' => [
				['label' => 'Blanche', 'href' => '/univers/bieres/categorie/3'],
				['label' => 'Blonde', 'href' => '/univers/bieres/categorie/7'],
				['label' => 'IPA', 'href' => '/univers/bieres/categorie/1'],
			],
			'col_3_head' => 'Fabricant (à trier)',
			'col_3_title' => 'Brasserie',
			'col_4_head' => 'Pays',
			'col_4_title' => 'Pays',
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

	$champagne_cat_ids = [34, 35, 36, 37];
	$champagne_appellation_items = [
		['code' => 34, 'nom' => 'BLANC DE NOIRS'],
		['code' => 35, 'nom' => 'BLANC DE BLANCS'],
		['code' => 36, 'nom' => 'DEMI SEC'],
		['code' => 37, 'nom' => 'BRUT'],
	];

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
		// Exclure les sous-familles masquées du menu global (pack 'all')
		$_uMenuSfExcl = isset($univers_excluded_sous_famille_slugs[$key]['*']) ? array_flip($univers_excluded_sous_famille_slugs[$key]['*']) : [];
		if(!empty($_uMenuSfExcl)) {
			$univers_menu[$key]['sous_familles_all'] = array_values(array_filter($univers_menu[$key]['sous_familles_all'], function($sf) use ($_uMenuSfExcl) {
				return !isset($_uMenuSfExcl[$sf['slug']]);
			}));
		}
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
		// Déduplique par slug + exclut les masquées (Point N°2 : Cidre fût double, etc.)
		$_uMenuSfSeen = [];
		$univers_menu[$key]['sous_familles_top'] = array_values(array_filter($univers_menu[$key]['sous_familles_top'], function($sf) use (&$_uMenuSfSeen, $_uMenuSfExcl) {
			if(isset($_uMenuSfSeen[$sf['slug']]) || isset($_uMenuSfExcl[$sf['slug']])) return false;
			$_uMenuSfSeen[$sf['slug']] = true;
			return true;
		}));
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
	$ob_get_filter_values = function($key, $sanitizeCb = null) {
		if(!isset($_GET[$key])) {
			return array();
		}
		$raw = $_GET[$key];
		$values = is_array($raw) ? $raw : array($raw);
		$result = array();
		foreach($values as $value) {
			if($sanitizeCb !== null) {
				$value = $sanitizeCb($value);
			}
			if($value === null || $value === '') {
				continue;
			}
			$result[(string) $value] = (string) $value;
		}
		return array_values($result);
	};
	$filter_query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
	$filter_famille_slugs = $ob_get_filter_values('filtre_famille', function($value) {
		return preg_replace('/[^a-z0-9\-]/i', '', (string) $value);
	});
	$filter_sous_famille_slugs = $ob_get_filter_values('filtre_sous_famille', function($value) {
		return preg_replace('/[^a-z0-9\-]/i', '', (string) $value);
	});
	$filter_categorie_codes = array_map('intval', $ob_get_filter_values('filtre_categorie', function($value) {
		$value = (int) $value;
		return ($value > 0) ? (string) $value : '';
	}));
	$filter_fabriquant_codes = array_map('intval', $ob_get_filter_values('filtre_fabriquant', function($value) {
		$value = (int) $value;
		return ($value > 0) ? (string) $value : '';
	}));
	$filter_pays_codes = $ob_get_filter_values('filtre_pays', function($value) {
		return trim((string) $value);
	});
	$filter_pack_slugs = $ob_get_filter_values('filtre_pack', function($value) {
		return preg_replace('/[^a-z0-9\-]/i', '', (string) $value);
	});

	$filter_famille_slug = !empty($filter_famille_slugs) ? (string) $filter_famille_slugs[0] : '';
	$filter_sous_famille_slug = !empty($filter_sous_famille_slugs) ? (string) $filter_sous_famille_slugs[0] : '';
	$filter_categorie_code = !empty($filter_categorie_codes) ? (int) $filter_categorie_codes[0] : 0;
	$filter_fabriquant_code = !empty($filter_fabriquant_codes) ? (int) $filter_fabriquant_codes[0] : 0;
	$filter_pays_code = !empty($filter_pays_codes) ? (string) $filter_pays_codes[0] : '';
	$filter_pack_slug = !empty($filter_pack_slugs) ? (string) $filter_pack_slugs[0] : '';

	$has_query_filters = (
		$filter_query !== ''
		|| !empty($filter_famille_slugs)
		|| !empty($filter_sous_famille_slugs)
		|| !empty($filter_categorie_codes)
		|| !empty($filter_fabriquant_codes)
		|| !empty($filter_pays_codes)
		|| !empty($filter_pack_slugs)
	);
	$menu_scope_href = function($universKey, $menuScope, $filters = array()) use ($menu_filter_href, $filter_query, $filter_famille_slug, $filter_sous_famille_slug, $filter_categorie_code, $filter_fabriquant_code, $filter_pays_code, $filter_pack_slug) {
		$baseFilters = array('menu_scope' => $menuScope);
		if($filter_query !== '') {
			$baseFilters['q'] = $filter_query;
		}
		if($filter_famille_slug !== '') {
			$baseFilters['filtre_famille'] = $filter_famille_slug;
		}
		if($filter_sous_famille_slug !== '') {
			$baseFilters['filtre_sous_famille'] = $filter_sous_famille_slug;
		}
		if($filter_categorie_code > 0) {
			$baseFilters['filtre_categorie'] = $filter_categorie_code;
		}
		if($filter_fabriquant_code > 0) {
			$baseFilters['filtre_fabriquant'] = $filter_fabriquant_code;
		}
		if($filter_pays_code !== '') {
			$baseFilters['filtre_pays'] = $filter_pays_code;
		}
		if($filter_pack_slug !== '') {
			$baseFilters['filtre_pack'] = $filter_pack_slug;
		}
		foreach($filters as $key => $value) {
			$baseFilters[$key] = $value;
		}
		return $menu_filter_href($universKey, $baseFilters);
	};
	$submenu_titles_by_univers = array(
		'bieres' => 'Toutes les brasseries',
		'vins' => 'Tous les domaines',
		'spiritueux' => 'Toutes les distilleries',
		'softs' => 'Toutes les marques',
	);
	$beer_all_items = $univers_menu['bieres']['categories'];
	$submenu_config_by_scope = array(
		'bieres-style' => array(
			'title' => 'Tous les styles',
			'items' => $beer_all_items,
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('bieres', 'bieres-style', array('filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-couleur' => array(
			'title' => 'Tous les styles',
			'items' => $beer_all_items,
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('bieres', 'bieres-style', array('filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-brasserie' => array(
			'title' => 'Toutes les brasseries',
			'items' => $univers_menu['bieres']['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('bieres', 'bieres-brasserie', array('filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'bieres-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['bieres']['pays_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('bieres', 'bieres-pays', array('filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['vins']['categories'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('vins', 'vins-type', array('filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-appellation' => array(
			'title' => 'Toutes les appellations',
			'items' => $univers_menu['vins']['sous_familles_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('vins', 'vins-appellation', array('filtre_sous_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-domaine' => array(
			'title' => 'Tous les domaines',
			'items' => $univers_menu['vins']['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('vins', 'vins-domaine', array('filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['vins']['pays_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('vins', 'vins-pays', array('filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'vins-champagne' => array(
			'title' => 'Champagne — Types',
			'items' => [
				['code' => 34, 'nom' => 'BLANC DE NOIRS'],
				['code' => 35, 'nom' => 'BLANC DE BLANCS'],
				['code' => 36, 'nom' => 'DEMI SEC'],
				['code' => 37, 'nom' => 'BRUT'],
			],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('vins', 'vins-champagne', array('filtre_categorie' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['spiritueux']['sous_familles_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('spiritueux', 'spiritueux-type', array('filtre_sous_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-distillerie' => array(
			'title' => 'Toutes les distilleries',
			'items' => $univers_menu['spiritueux']['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('spiritueux', 'spiritueux-distillerie', array('filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'spiritueux-pays' => array(
			'title' => 'Tous les pays',
			'items' => $univers_menu['spiritueux']['pays_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('spiritueux', 'spiritueux-pays', array('filtre_pays' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'softs-type' => array(
			'title' => 'Tous les types',
			'items' => $univers_menu['softs']['familles'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('softs', 'softs-type', array('filtre_famille' => $item['slug']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
		'softs-marque' => array(
			'title' => 'Toutes les marques',
			'items' => $univers_menu['softs']['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href) {
				return $menu_scope_href('softs', 'softs-marque', array('filtre_fabriquant' => $item['code']));
			},
			'get_label' => function($item) {
				return $item['nom'];
			},
		),
	);
	$current_scope_submenu = isset($submenu_config_by_scope[$menu_scope]) ? $submenu_config_by_scope[$menu_scope] : null;
	$filter_famille_ids = array();
	if(!empty($filter_famille_slugs)) {
		$familleFilterStmt = $bdd->prepare("SELECT id FROM ob_catalogue_familles WHERE slug = :slug LIMIT 1");
		foreach($filter_famille_slugs as $familleSlugItem) {
			$familleFilterStmt->bindParam(':slug', $familleSlugItem);
			$familleFilterStmt->execute();
			$filterFamille = $familleFilterStmt->fetch(PDO::FETCH_OBJ);
			if($filterFamille && isset($filterFamille->id)) {
				$filter_famille_ids[(int) $filterFamille->id] = (int) $filterFamille->id;
			}
		}
		$filter_famille_ids = array_values($filter_famille_ids);
	}
	$filter_sous_famille_ids = array();
	if(!empty($filter_sous_famille_slugs)) {
		$sousFamilleFilterStmt = $bdd->prepare("SELECT id, famille_id FROM ob_catalogue_sous_familles WHERE slug = :slug LIMIT 1");
		foreach($filter_sous_famille_slugs as $sousFamilleSlugItem) {
			$sousFamilleFilterStmt->bindParam(':slug', $sousFamilleSlugItem);
			$sousFamilleFilterStmt->execute();
			$filterSousFamille = $sousFamilleFilterStmt->fetch(PDO::FETCH_OBJ);
			if($filterSousFamille && isset($filterSousFamille->id)) {
				$sfid = (int) $filterSousFamille->id;
				$ffid = (int) $filterSousFamille->famille_id;
				$filter_sous_famille_ids[$sfid] = $sfid;
				if($ffid > 0) {
					$filter_famille_ids[$ffid] = $ffid;
				}
			}
		}
		$filter_sous_famille_ids = array_values($filter_sous_famille_ids);
		$filter_famille_ids = array_values($filter_famille_ids);
	}
	$filter_famille_id = !empty($filter_famille_ids) ? (int) $filter_famille_ids[0] : null;
	$filter_sous_famille_id = !empty($filter_sous_famille_ids) ? (int) $filter_sous_famille_ids[0] : null;
	$available_sub_familles = [];
	foreach($current_univers_menu['familles'] as $familleMenu) {
		if(!empty($filter_famille_slugs) && !in_array($familleMenu['slug'], $filter_famille_slugs, true)) {
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
	$effective_pack_slugs = array();
	if($select_pack) {
		$effective_pack_slug = $pack_slug;
		$effective_pack_slugs = array($pack_slug);
	} elseif($filter_pack_slug !== '' && isset($allowed_pack_by_univers[$univers]) && in_array($filter_pack_slug, $allowed_pack_by_univers[$univers], true)) {
		$effective_pack_slug = $filter_pack_slug;
	}
	if(!$select_pack && !empty($filter_pack_slugs) && isset($allowed_pack_by_univers[$univers])) {
		$validPackSlugs = array();
		foreach($filter_pack_slugs as $filterPackSlugItem) {
			if(in_array($filterPackSlugItem, $allowed_pack_by_univers[$univers], true)) {
				$validPackSlugs[$filterPackSlugItem] = $filterPackSlugItem;
			}
		}
		if(!empty($validPackSlugs)) {
			$effective_pack_slugs = array_values($validPackSlugs);
			$effective_pack_slug = $effective_pack_slugs[0];
		}
	}
	if(empty($effective_pack_slugs) && $effective_pack_slug !== null) {
		$effective_pack_slugs = array($effective_pack_slug);
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
		// Fût : sous-famille ERP contient FUT (mot entier) OU nom produit contient capacité litre (ex: 20L)
		// On n'utilise PAS le nom produit pour FUT : évite les faux positifs (FUTUR, CONFUTER, etc.)
		$isFut = "(UPPER(COALESCE(".$packSousFamilleAlias.".nom,'')) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)' OR $upperNom REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
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
	$build_menu_dataset = function($universKey, $packSlug = 'all') use ($bdd, $build_universe_where, $build_pack_scope, $univers_famille_filter_slugs, $univers_excluded_sous_famille_slugs, $degre_buckets_definitions, $numeric_slug, $numeric_label) {
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

		// Déterminer les sous-familles à exclure pour cet univers+pack (Point N°1 et N°2)
		$_sfExcl = [];
		if(isset($univers_excluded_sous_famille_slugs[$universKey])) {
			if(!empty($univers_excluded_sous_famille_slugs[$universKey]['*'])) {
				$_sfExcl = array_merge($_sfExcl, $univers_excluded_sous_famille_slugs[$universKey]['*']);
			}
			if($packSlug !== 'all' && !empty($univers_excluded_sous_famille_slugs[$universKey][$packSlug])) {
				$_sfExcl = array_merge($_sfExcl, $univers_excluded_sous_famille_slugs[$universKey][$packSlug]);
			}
		}
		$_sfExclFlip = !empty($_sfExcl) ? array_flip($_sfExcl) : [];
		if(!empty($_sfExclFlip)) {
			$menuData['sous_familles_all'] = array_values(array_filter($menuData['sous_familles_all'], function($sf) use ($_sfExclFlip) {
				return !isset($_sfExclFlip[$sf['slug']]);
			}));
			foreach($menuData['familles'] as &$_bfam) {
				$_bfam['sous_familles'] = array_values(array_filter($_bfam['sous_familles'], function($sf) use ($_sfExclFlip) {
					return !isset($_sfExclFlip[$sf['slug']]);
				}));
			}
			unset($_bfam);
		}

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
		// Déduplique par slug (deux sf dans des familles différentes peuvent avoir même slug) + exclut les masquées
		$_sfTopSeen = [];
		$menuData['sous_familles_top'] = array_values(array_filter($menuData['sous_familles_top'], function($sf) use (&$_sfTopSeen, $_sfExclFlip) {
			if(isset($_sfTopSeen[$sf['slug']]) || isset($_sfExclFlip[$sf['slug']])) return false;
			$_sfTopSeen[$sf['slug']] = true;
			return true;
		}));

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
	$resolve_scope_pack = function($universKey) use ($filter_pack_slug, $effective_pack_slug, $allowed_pack_by_univers) {
		if(isset($allowed_pack_by_univers[$universKey]) && in_array($filter_pack_slug, $allowed_pack_by_univers[$universKey], true)) {
			return $filter_pack_slug;
		}
		if(isset($allowed_pack_by_univers[$universKey]) && in_array((string) $effective_pack_slug, $allowed_pack_by_univers[$universKey], true)) {
			return (string) $effective_pack_slug;
		}
		return 'all';
	};
	$scope_pack = array(
		'bieres' => $resolve_scope_pack('bieres'),
		'vins' => $resolve_scope_pack('vins'),
		'spiritueux' => $resolve_scope_pack('spiritueux'),
		'softs' => $resolve_scope_pack('softs'),
	);
	$submenu_bieres_menu = $univers_menu_scoped['bieres'][$scope_pack['bieres']];
	$submenu_vins_menu = $univers_menu_scoped['vins'][$scope_pack['vins']];
	$submenu_spiritueux_menu = $univers_menu_scoped['spiritueux'][$scope_pack['spiritueux']];
	$submenu_softs_menu = $univers_menu_scoped['softs'][$scope_pack['softs']];
	$submenu_config_by_scope = array(
		'bieres-style' => array(
			'title' => 'Tous les styles',
			'items' => $submenu_bieres_menu['categories'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_categorie' => $item['code']);
				if($scope_pack['bieres'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['bieres'];
				}
				return $menu_scope_href('bieres', 'bieres-style', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_categorie_code) { return ((int) $filter_categorie_code === (int) $item['code']); },
		),
		'bieres-couleur' => array(
			'title' => 'Tous les styles',
			'items' => $submenu_bieres_menu['categories'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_categorie' => $item['code']);
				if($scope_pack['bieres'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['bieres'];
				}
				return $menu_scope_href('bieres', 'bieres-style', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_categorie_code) { return ((int) $filter_categorie_code === (int) $item['code']); },
		),
		'bieres-brasserie' => array(
			'title' => 'Toutes les brasseries',
			'items' => $submenu_bieres_menu['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_fabriquant' => $item['code']);
				if($scope_pack['bieres'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['bieres'];
				}
				return $menu_scope_href('bieres', 'bieres-brasserie', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_fabriquant_code) { return ((int) $filter_fabriquant_code === (int) $item['code']); },
		),
		'bieres-pays' => array(
			'title' => 'Tous les pays',
			'items' => $submenu_bieres_menu['pays_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_pays' => $item['code']);
				if($scope_pack['bieres'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['bieres'];
				}
				return $menu_scope_href('bieres', 'bieres-pays', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_pays_code) { return ((string) $filter_pays_code === (string) $item['code']); },
		),
		'vins-type' => array(
			'title' => 'Tous les types',
			'items' => $submenu_vins_menu['categories'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_categorie' => $item['code']);
				if($scope_pack['vins'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['vins'];
				}
				return $menu_scope_href('vins', 'vins-type', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_categorie_code) { return ((int) $filter_categorie_code === (int) $item['code']); },
		),
		'vins-appellation' => array(
			'title' => 'Toutes les appellations',
			'items' => $submenu_vins_menu['sous_familles_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_sous_famille' => $item['slug']);
				if($scope_pack['vins'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['vins'];
				}
				return $menu_scope_href('vins', 'vins-appellation', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_sous_famille_slug) { return ((string) $filter_sous_famille_slug === (string) $item['slug']); },
		),
		'vins-domaine' => array(
			'title' => 'Tous les domaines',
			'items' => $submenu_vins_menu['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_fabriquant' => $item['code']);
				if($scope_pack['vins'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['vins'];
				}
				return $menu_scope_href('vins', 'vins-domaine', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_fabriquant_code) { return ((int) $filter_fabriquant_code === (int) $item['code']); },
		),
		'vins-pays' => array(
			'title' => 'Tous les pays',
			'items' => $submenu_vins_menu['pays_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_pays' => $item['code']);
				if($scope_pack['vins'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['vins'];
				}
				return $menu_scope_href('vins', 'vins-pays', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_pays_code) { return ((string) $filter_pays_code === (string) $item['code']); },
		),
		'vins-champagne' => array(
			'title' => 'Champagne — Types',
			'items' => [
				['code' => 34, 'nom' => 'BLANC DE NOIRS'],
				['code' => 35, 'nom' => 'BLANC DE BLANCS'],
				['code' => 36, 'nom' => 'DEMI SEC'],
				['code' => 37, 'nom' => 'BRUT'],
			],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_categorie' => $item['code']);
				if($scope_pack['vins'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['vins'];
				}
				return $menu_scope_href('vins', 'vins-champagne', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_categorie_code) { return ((int) $filter_categorie_code === (int) $item['code']); },
		),
		'spiritueux-type' => array(
			'title' => 'Tous les types',
			'items' => $submenu_spiritueux_menu['sous_familles_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_sous_famille' => $item['slug']);
				if($scope_pack['spiritueux'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['spiritueux'];
				}
				return $menu_scope_href('spiritueux', 'spiritueux-type', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_sous_famille_slug) { return ((string) $filter_sous_famille_slug === (string) $item['slug']); },
		),
		'spiritueux-distillerie' => array(
			'title' => 'Toutes les distilleries',
			'items' => $submenu_spiritueux_menu['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_fabriquant' => $item['code']);
				if($scope_pack['spiritueux'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['spiritueux'];
				}
				return $menu_scope_href('spiritueux', 'spiritueux-distillerie', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_fabriquant_code) { return ((int) $filter_fabriquant_code === (int) $item['code']); },
		),
		'spiritueux-pays' => array(
			'title' => 'Tous les pays',
			'items' => $submenu_spiritueux_menu['pays_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_pays' => $item['code']);
				if($scope_pack['spiritueux'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['spiritueux'];
				}
				return $menu_scope_href('spiritueux', 'spiritueux-pays', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_pays_code) { return ((string) $filter_pays_code === (string) $item['code']); },
		),
		'softs-type' => array(
			'title' => 'Tous les types',
			'items' => $submenu_softs_menu['familles'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_famille' => $item['slug']);
				if($scope_pack['softs'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['softs'];
				}
				return $menu_scope_href('softs', 'softs-type', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_famille_slug) { return ((string) $filter_famille_slug === (string) $item['slug']); },
		),
		'softs-marque' => array(
			'title' => 'Toutes les marques',
			'items' => $submenu_softs_menu['fabricants_all'],
			'build_href' => function($item) use ($menu_scope_href, $scope_pack) {
				$filters = array('filtre_fabriquant' => $item['code']);
				if($scope_pack['softs'] !== 'all') {
					$filters['filtre_pack'] = $scope_pack['softs'];
				}
				return $menu_scope_href('softs', 'softs-marque', $filters);
			},
			'get_label' => function($item) { return $item['nom']; },
			'is_active' => function($item) use ($filter_fabriquant_code) { return ((int) $filter_fabriquant_code === (int) $item['code']); },
		),
	);
	$current_scope_submenu = isset($submenu_config_by_scope[$menu_scope]) ? $submenu_config_by_scope[$menu_scope] : null;

	$sidebar_active_values = array(
		'filtre_pack' => !empty($effective_pack_slugs) ? array_map('strval', $effective_pack_slugs) : array(),
		'filtre_famille' => !empty($filter_famille_slugs) ? array_map('strval', $filter_famille_slugs) : (($select_famille && !empty($famille_slug)) ? array((string) $famille_slug) : array()),
		'filtre_sous_famille' => !empty($filter_sous_famille_slugs) ? array_map('strval', $filter_sous_famille_slugs) : (($select_sous_famille && !empty($sous_famille_slug)) ? array((string) $sous_famille_slug) : array()),
		'filtre_categorie' => !empty($filter_categorie_codes) ? array_map('strval', $filter_categorie_codes) : (($select_categorie && isset($_GET['categorie'])) ? array((string) ((int) $_GET['categorie'])) : array()),
		'filtre_fabriquant' => !empty($filter_fabriquant_codes) ? array_map('strval', $filter_fabriquant_codes) : array(),
		'filtre_pays' => !empty($filter_pays_codes) ? array_map('strval', $filter_pays_codes) : array(),
	);

	$sidebar_pack_key = 'all';
	if($effective_pack_slug !== null && isset($univers_menu_scoped[$univers][$effective_pack_slug])) {
		$sidebar_pack_key = (string) $effective_pack_slug;
	}
	$sidebar_menu_data = isset($univers_menu_scoped[$univers][$sidebar_pack_key]) ? $univers_menu_scoped[$univers][$sidebar_pack_key] : $univers_menu[$univers];

	if(empty($sidebar_active_values['filtre_famille']) && !empty($sidebar_active_values['filtre_sous_famille']) && !empty($sidebar_menu_data['familles'])) {
		foreach($sidebar_menu_data['familles'] as $sidebarFamilleItem) {
			if(empty($sidebarFamilleItem['sous_familles'])) {
				continue;
			}
			foreach($sidebarFamilleItem['sous_familles'] as $sidebarSousFamilleItem) {
				if(in_array((string) $sidebarSousFamilleItem['slug'], $sidebar_active_values['filtre_sous_famille'], true)) {
					$sidebar_active_values['filtre_famille'][] = (string) $sidebarFamilleItem['slug'];
				}
			}
		}
		$sidebar_active_values['filtre_famille'] = array_values(array_unique($sidebar_active_values['filtre_famille']));
	}

	$sidebar_filter_sections = array();
	if(isset($allowed_pack_by_univers[$univers]) && !empty($allowed_pack_by_univers[$univers])) {
		$packItems = array();
		foreach($allowed_pack_by_univers[$univers] as $sidebarPackSlug) {
			if(!isset($menu_pack_labels[$sidebarPackSlug])) {
				continue;
			}
			$packItems[] = array(
				'slug' => (string) $sidebarPackSlug,
				'nom' => (string) $menu_pack_labels[$sidebarPackSlug],
			);
		}
		if(!empty($packItems)) {
			$sidebar_filter_sections[] = array(
				'title' => 'Conditionnement',
				'field' => 'filtre_pack',
				'items' => $packItems,
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
	}

	if($univers === 'bieres') {
		if($effective_pack_slug !== null && !empty($sidebar_menu_data['sous_familles_top'])) {
			$sousFamilleTitle = ($effective_pack_slug === 'futs') ? 'Contenance' : 'Format';
			$sidebar_filter_sections[] = array(
				'title' => $sousFamilleTitle,
				'field' => 'filtre_sous_famille',
				'items' => $sidebar_menu_data['sous_familles_top'],
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['categories'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Style',
				'field' => 'filtre_categorie',
				'items' => $sidebar_menu_data['categories'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['fabricants_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Brasserie',
				'field' => 'filtre_fabriquant',
				'items' => $sidebar_menu_data['fabricants_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['pays_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Pays',
				'field' => 'filtre_pays',
				'items' => $sidebar_menu_data['pays_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
	} elseif($univers === 'vins') {
		$_champagne_type_active = !empty(array_intersect($filter_categorie_codes, $champagne_cat_ids));
		$_vins_type_items = array_values(array_filter(!empty($sidebar_menu_data['categories']) ? $sidebar_menu_data['categories'] : [], function($cat) use ($champagne_cat_ids) {
			return !in_array((int) $cat['code'], $champagne_cat_ids);
		}));
		$_vins_type_items[] = ['code' => 'champagne-group', 'nom' => 'Champagne', '_is_champagne_group' => true, '_is_active' => $_champagne_type_active];
		$sidebar_filter_sections[] = array(
			'title' => 'Type',
			'field' => 'filtre_categorie',
			'items' => $_vins_type_items,
			'value_key' => 'code',
			'label_key' => 'nom',
		);
		$_non_champagne_type_cats = array_values(array_diff($filter_categorie_codes, $champagne_cat_ids));
		$_champagne_type_selected = !empty(array_intersect($filter_categorie_codes, $champagne_cat_ids));
		if(!empty($filter_categorie_codes)) {
			if(!empty($_non_champagne_type_cats)) {
				$_inNonChampCats = implode(',', array_map('intval', $_non_champagne_type_cats));
				$_univWhere = $build_universe_where('vins', 'p');
				$_sfByTypeStmt = $bdd->query("SELECT DISTINCT sf.id, sf.nom, sf.slug FROM ob_catalogue_produits p INNER JOIN ob_catalogue_sous_familles sf ON p.sous_famille_id = sf.id WHERE $_univWhere AND p.categorie IN ($_inNonChampCats) ORDER BY sf.nom");
				$_vins_appellation_items = [];
				while($_sfRow = $_sfByTypeStmt->fetch(PDO::FETCH_OBJ)) {
					$_vins_appellation_items[] = ['id' => (int) $_sfRow->id, 'nom' => (string) $_sfRow->nom, 'slug' => (string) $_sfRow->slug];
				}
			} else {
				$_vins_appellation_items = [];
			}
			$_vins_champagne_cat_items = $_champagne_type_selected ? $champagne_appellation_items : [];
		} else {
			$_vins_appellation_items = !empty($sidebar_menu_data['sous_familles_all']) ? $sidebar_menu_data['sous_familles_all'] : [];
			$_vins_champagne_cat_items = $champagne_appellation_items;
		}
		$sidebar_filter_sections[] = array(
			'title' => 'Appellation',
			'field' => 'filtre_sous_famille',
			'items' => $_vins_appellation_items,
			'value_key' => 'slug',
			'label_key' => 'nom',
			'_champagne_cat_items' => $_vins_champagne_cat_items,
		);
		if(!empty($sidebar_menu_data['fabricants_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Domaine',
				'field' => 'filtre_fabriquant',
				'items' => $sidebar_menu_data['fabricants_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['pays_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Pays',
				'field' => 'filtre_pays',
				'items' => $sidebar_menu_data['pays_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
	} elseif($univers === 'spiritueux') {
		if(!empty($sidebar_menu_data['sous_familles_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Type',
				'field' => 'filtre_sous_famille',
				'items' => $sidebar_menu_data['sous_familles_all'],
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['fabricants_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Distillerie',
				'field' => 'filtre_fabriquant',
				'items' => $sidebar_menu_data['fabricants_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['pays_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Pays',
				'field' => 'filtre_pays',
				'items' => $sidebar_menu_data['pays_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
	} elseif($univers === 'softs') {
		if(!empty($sidebar_menu_data['sous_familles_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Gamme',
				'field' => 'filtre_sous_famille',
				'items' => $sidebar_menu_data['sous_familles_all'],
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['familles'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Type',
				'field' => 'filtre_famille',
				'items' => $sidebar_menu_data['familles'],
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['fabricants_all'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Marque',
				'field' => 'filtre_fabriquant',
				'items' => $sidebar_menu_data['fabricants_all'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
	} else {
		if(!empty($sidebar_menu_data['familles'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Famille',
				'field' => 'filtre_famille',
				'items' => $sidebar_menu_data['familles'],
				'value_key' => 'slug',
				'label_key' => 'nom',
			);
		}
		if(!empty($sidebar_menu_data['categories'])) {
			$sidebar_filter_sections[] = array(
				'title' => 'Type',
				'field' => 'filtre_categorie',
				'items' => $sidebar_menu_data['categories'],
				'value_key' => 'code',
				'label_key' => 'nom',
			);
		}
	}

	$show_listing_sidebar = !$brasseries_select && (
		$show_univers_products
		|| $show_scope_products
		|| $has_query_filters
		|| $select_pack
		|| $select_categorie
		|| $select_famille
		|| $select_sous_famille
		|| $select_degre
		|| $select_contenance
		|| $univers !== 'bieres'
	);

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
								<button type="button" class="catalogue-tab <?php echo ($ukey === $univers) ? 'is-active' : ''; ?>" data-univers="<?php echo $ukey; ?>"<?php if($ukey === 'promotions') { ?> style="display:none"<?php } ?>>
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
											<div class="menu-section-title">Style</div>
											<?php foreach($panelPackKeys as $menuPackKey) { $packMenu = $univers_menu_scoped[$ukey][$menuPackKey]; ?>
												<div class="menu-pack-view <?php echo ($panelActivePack === $menuPackKey) ? 'is-active' : ''; ?>" data-pack="<?php echo htmlspecialchars($menuPackKey, ENT_QUOTES, 'UTF-8'); ?>">
													<?php foreach(array_slice($packMenu['categories'], 0, 3) as $catItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('bieres', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $catItem['code']))); ?>"><?php echo htmlspecialchars($catItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($packMenu['categories']) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
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
													<?php $vins_menu_non_champagne = array_values(array_filter($packMenu['categories'], function($cat) use ($champagne_cat_ids) { return !in_array((int)$cat['code'], $champagne_cat_ids); })); ?>
													<?php foreach(array_slice($vins_menu_non_champagne, 0, 3) as $catItem) { ?>
														<a class="menu-link" href="<?php echo $menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $catItem['code']))); ?>"><?php echo htmlspecialchars($catItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
													<?php } ?>
													<?php if(count($vins_menu_non_champagne) > 3) { ?><span class="menu-etc">etc.</span><?php } ?>
													<div class="menu-link-with-sub">
														<a class="menu-link menu-link--has-sub" href="<?php echo htmlspecialchars($menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $champagne_cat_ids))), ENT_QUOTES, 'UTF-8'); ?>">CHAMPAGNE</a>
														<div class="menu-sub-dropdown">
															<?php foreach($champagne_appellation_items as $champItem) { ?>
																<a class="menu-link" href="<?php echo htmlspecialchars($menu_filter_href('vins', $with_menu_pack_filter($menuPackKey, array('filtre_categorie' => $champItem['code']))), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($champItem['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
															<?php } ?>
														</div>
													</div>
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
					</div><!-- /.rechercher -->
					<?php if($menu_scope !== '' && !$brasseries_select) { ?>
						<div class="catalogue-submenu catalogue-submenu-filters">
							<div class="catalogue-submenu-title"><?php echo ($current_scope_submenu !== null && isset($current_scope_submenu['title'])) ? $current_scope_submenu['title'] : 'Filtres'; ?></div>
							<div class="catalogue-submenu-links">
								<?php if($current_scope_submenu !== null && !empty($current_scope_submenu['items'])) { ?>
									<?php foreach($current_scope_submenu['items'] as $submenuItem) { ?>
										<?php $submenuItemIsActive = isset($current_scope_submenu['is_active']) ? (bool) $current_scope_submenu['is_active']($submenuItem) : false; ?>
										<a class="catalogue-submenu-link catalogue-submenu-btn <?php echo $submenuItemIsActive ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars($current_scope_submenu['build_href']($submenuItem), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($current_scope_submenu['get_label']($submenuItem), ENT_QUOTES, 'UTF-8'); ?></a>
									<?php } ?>
								<?php } else { ?>
									<span class="catalogue-submenu-empty">Aucun filtre disponible pour ce contexte (<?php echo htmlspecialchars($menu_scope, ENT_QUOTES, 'UTF-8'); ?>).</span>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
					<?php if($show_listing_sidebar) { ?>
						<div class="catalogue-listing-layout">
							<aside class="catalogue-filters-sidebar">
								<div class="catalogue-filters-box">
									<h3 class="catalogue-filters-title">Filtrer les produits</h3>
									<form class="catalogue-filters-form" method="get" action="<?php echo $url; ?>/univers/<?php echo $univers; ?>/produits">
										<?php if($menu_scope !== '') { ?><input type="hidden" name="menu_scope" value="<?php echo htmlspecialchars($menu_scope, ENT_QUOTES, 'UTF-8'); ?>" /><?php } ?>
										<?php if($filter_query !== '') { ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($filter_query, ENT_QUOTES, 'UTF-8'); ?>" /><?php } ?>
										<?php if(isset($_GET['trier_prix']) && ($_GET['trier_prix'] === 'croissant' || $_GET['trier_prix'] === 'decroissant')) { ?><input type="hidden" name="trier_prix" value="<?php echo htmlspecialchars($_GET['trier_prix'], ENT_QUOTES, 'UTF-8'); ?>" /><?php } ?>
										<?php foreach($sidebar_filter_sections as $sidebarSection) { ?>
											<?php $sidebarField = (string) $sidebarSection['field']; ?>
											<?php $sidebarValueKey = (string) $sidebarSection['value_key']; ?>
											<?php $sidebarLabelKey = (string) $sidebarSection['label_key']; ?>
											<?php $sidebarActive = isset($sidebar_active_values[$sidebarField]) && is_array($sidebar_active_values[$sidebarField]) ? $sidebar_active_values[$sidebarField] : array(); ?>
											<fieldset class="catalogue-filter-group" data-field="<?php echo htmlspecialchars($sidebarField, ENT_QUOTES, 'UTF-8'); ?>[]">
												<legend>
													<button type="button" class="catalogue-filter-toggle" aria-expanded="true"><?php echo htmlspecialchars($sidebarSection['title'], ENT_QUOTES, 'UTF-8'); ?></button>
												</legend>
												<div class="catalogue-filter-options">
													<button type="button" class="catalogue-filter-clear" data-clear-field="<?php echo htmlspecialchars($sidebarField, ENT_QUOTES, 'UTF-8'); ?>[]">Tous</button>
												<?php foreach($sidebarSection['items'] as $sidebarOption) { ?>
													<?php if(!empty($sidebarOption['_is_champagne_group'])) { ?>
														<label class="catalogue-filter-option catalogue-filter-option--champagne-group <?php echo !empty($sidebarOption['_is_active']) ? 'is-active' : ''; ?>">
															<input type="checkbox" class="champagne-group-checkbox" data-champagne-cats="34,35,36,37" <?php echo !empty($sidebarOption['_is_active']) ? 'checked' : ''; ?> />
															<span>CHAMPAGNE</span>
														</label>
													<?php } else { ?>
														<?php if(!isset($sidebarOption[$sidebarValueKey]) || !isset($sidebarOption[$sidebarLabelKey])) { continue; } ?>
														<?php $optionValue = (string) $sidebarOption[$sidebarValueKey]; ?>
														<?php $optionLabel = (string) $sidebarOption[$sidebarLabelKey]; ?>
														<label class="catalogue-filter-option <?php echo in_array($optionValue, $sidebarActive, true) ? 'is-active' : ''; ?>">
															<input type="checkbox" name="<?php echo htmlspecialchars($sidebarField, ENT_QUOTES, 'UTF-8'); ?>[]" value="<?php echo htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($optionValue, $sidebarActive, true) ? 'checked' : ''; ?> />
															<span><?php echo htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?></span>
														</label>
													<?php } ?>
												<?php } ?>
												<?php if(!empty($sidebarSection['_champagne_cat_items'])) { ?>
													<?php $sidebarChampagneCatActive = isset($sidebar_active_values['filtre_categorie']) && is_array($sidebar_active_values['filtre_categorie']) ? $sidebar_active_values['filtre_categorie'] : array(); ?>
													<?php foreach($sidebarSection['_champagne_cat_items'] as $champCat) { ?>
														<?php $champCatValue = (string) $champCat['code']; ?>
														<label class="catalogue-filter-option catalogue-filter-option--champagne-appellation <?php echo in_array($champCatValue, $sidebarChampagneCatActive, true) ? 'is-active' : ''; ?>">
															<input type="checkbox" name="filtre_categorie[]" value="<?php echo htmlspecialchars($champCatValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($champCatValue, $sidebarChampagneCatActive, true) ? 'checked' : ''; ?> />
															<span><?php echo htmlspecialchars($champCat['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
														</label>
													<?php } ?>
												<?php } ?>
												</div>
											</fieldset>
										<?php } ?>
										<div class="catalogue-filter-actions">
											<a class="btn btn-light" href="<?php echo $url; ?>/univers/<?php echo $univers; ?>/produits">Réinitialiser</a>
										</div>
									</form>
								</div>
							</aside>
							<div class="catalogue-listing-results">
					<?php } ?>
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
							$bindIn = function($prefix, $values, &$params) {
								$placeholders = array();
								foreach(array_values($values) as $idx => $value) {
									$key = ':'.$prefix.$idx;
									$placeholders[] = $key;
									$params[$key] = $value;
								}
								return $placeholders;
							};
							$whereParts[] = $build_universe_where($univers, 'p');
							$whereParts[] = "p.marque IN ('1','2')";
							if(!empty($filter_famille_ids)) {
								$inFamille = $bindIn('filtre_famille_id_', array_map('intval', $filter_famille_ids), $params);
								$whereParts[] = 'p.famille_id IN ('.implode(',', $inFamille).')';
							}
							if(!empty($filter_sous_famille_ids)) {
								$inSousFamille = $bindIn('filtre_sous_famille_id_', array_map('intval', $filter_sous_famille_ids), $params);
								$whereParts[] = 'p.sous_famille_id IN ('.implode(',', $inSousFamille).')';
							}
							if(!empty($filter_categorie_codes)) {
								$inCategorie = $bindIn('filtre_categorie_', array_map('intval', $filter_categorie_codes), $params);
								$whereParts[] = 'p.categorie IN ('.implode(',', $inCategorie).')';
							}
							if(!empty($filter_fabriquant_codes)) {
								$inFabriquant = $bindIn('filtre_fabriquant_', array_map('intval', $filter_fabriquant_codes), $params);
								$whereParts[] = 'p.brasserie IN ('.implode(',', $inFabriquant).')';
							}
							if(!empty($filter_pays_codes)) {
								$inPays = $bindIn('filtre_pays_', array_values($filter_pays_codes), $params);
								$whereParts[] = 'p.pays_code IN ('.implode(',', $inPays).')';
							}
							if($filter_query !== '') {
								$whereParts[] = '(p.nom LIKE :filtre_recherche OR p.nom_sup LIKE :filtre_recherche)';
								$params[':filtre_recherche'] = '%'.$filter_query.'%';
							}
							if(!empty($effective_pack_slugs)) {
								$joins = ' LEFT JOIN ob_catalogue_sous_familles sf ON sf.id = p.sous_famille_id ';
								$isFut = "(UPPER(COALESCE(sf.nom,'')) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
								$packWhereParts = array();
								if($univers === 'vins') {
									$isBib = "(UPPER(COALESCE(sf.nom,'')) LIKE '%BIB%' OR UPPER(p.nom) LIKE '%BIB%' OR (p.contenance IN (300,500,1000) AND UPPER(p.nom) NOT LIKE '%MAGNUM%'))";
									foreach($effective_pack_slugs as $effectivePackSlugItem) {
										if($effectivePackSlugItem === 'bib') {
											$packWhereParts[] = $isBib;
										} elseif($effectivePackSlugItem === 'bouteilles') {
											$packWhereParts[] = 'NOT '.$isBib;
										}
									}
								} else {
									foreach($effective_pack_slugs as $effectivePackSlugItem) {
										if($effectivePackSlugItem === 'futs') {
											$packWhereParts[] = $isFut;
										} else {
											$packWhereParts[] = 'NOT '.$isFut;
										}
									}
								}
								$packWhereParts = array_values(array_unique($packWhereParts));
								if(!empty($packWhereParts)) {
									$whereParts[] = '('.implode(' OR ', $packWhereParts).')';
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
													$isFut = "(UPPER(COALESCE(sf.nom,'')) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
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
													$isFut = "(UPPER(COALESCE(sf.nom,'')) REGEXP '(^|[^A-Z])FUT([^A-Z]|$)' OR UPPER(p.nom) REGEXP '(^|[^0-9])([0-9]{1,2})L([^A-Z]|$)')";
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
						<?php if($show_listing_sidebar) { ?>
								</div>
							</div>
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
		<script>
			(function($) {
				$(function() {
					var $forms = $('.catalogue-filters-form');
					if($forms.length < 1) {
						return;
					}

					$(document).on('click', '.catalogue-filter-toggle', function() {
						var $btn = $(this);
						var $group = $btn.closest('.catalogue-filter-group');
						var $options = $group.find('.catalogue-filter-options').first();
						var expanded = $btn.attr('aria-expanded') === 'true';
						$btn.attr('aria-expanded', expanded ? 'false' : 'true');
						$group.toggleClass('is-collapsed', expanded);
						$options.stop(true, true)[expanded ? 'slideUp' : 'slideDown'](140);
					});

					$(document).on('click', '.catalogue-filter-clear', function() {
						var $btn = $(this);
						var fieldName = $btn.attr('data-clear-field');
						var $form = $btn.closest('form');
						if(!fieldName || $form.length < 1) {
							return;
						}
						if(fieldName === 'filtre_categorie[]') {
							$form.find('.champagne-group-checkbox').prop('checked', false).closest('.catalogue-filter-option').removeClass('is-active');
							$form.find('.champagne-hidden-input').remove();
						}
						var escapedName = fieldName.replace(/([\[\]])/g, '\\$1');
						$form.find('input[name="'+escapedName+'"]:checked').each(function() {
							var $input = $(this);
							$input.prop('checked', false);
							$input.closest('.catalogue-filter-option').removeClass('is-active');
						});
						$form.trigger('submit');
					});

					$(document).on('change', '.catalogue-filters-form input[type="checkbox"]', function() {
						var $input = $(this);
						if($input.hasClass('champagne-group-checkbox')) {
							$input.closest('.catalogue-filter-option').toggleClass('is-active', $input.is(':checked'));
							var $form = $input.closest('form');
							var champCats = ($input.attr('data-champagne-cats') || '').split(',').map(function(c) { return $.trim(c); }).filter(Boolean);
							$form.find('.champagne-hidden-input').remove();
							if($input.is(':checked')) {
								champCats.forEach(function(code) {
									$('<input>').attr({type:'hidden',name:'filtre_categorie[]',value:code,'class':'champagne-hidden-input'}).appendTo($form);
								});
							} else {
								// Décocher aussi les appellations champagne individuelles (34,35,36,37) encore cochées
								champCats.forEach(function(code) {
									$form.find('input[name="filtre_categorie[]"][value="'+code+'"]:checked').each(function() {
										$(this).prop('checked', false).closest('.catalogue-filter-option').removeClass('is-active');
									});
								});
							}
							$form.trigger('submit');
							return;
						}
						$input.closest('.catalogue-filter-option').toggleClass('is-active', $input.is(':checked'));
						$input.closest('form').trigger('submit');
					});
				});
			})(jQuery);
		</script>
	</body>
</html>
