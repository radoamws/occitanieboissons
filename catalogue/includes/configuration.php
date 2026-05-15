<?php
	@session_start();

	# Connexion à la base de donnée
	/*$host = "occitanila010301.mysql.db";
	$port = "3306";
	$user = "occitanila010301";
	$pass = "Lilian0103";
	$dbname = "occitanila010301";*/
	$host = "localhost";
	$port = "3306";
	$user = "root";
	$pass = "";
	$dbname = "occitanila010301";
	try {
		$bdd = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname.'', $user, $pass);
		$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
		echo "Impossible de se connecter &agrave; la base de donn&eacute;es <b>".$host."</b>.<br />Veuillez v&eacute;rifier le contenu du fichier de configuration.";
	}

	################################
 	#     CONFIGURATION DU CMS     # 
 	################################
	// Environnement: basculer sur "production" lors de la mise en prod
	// Valeurs possibles: "development" | "production"
	$env = "development";
	$envFromServer = getenv('OB_ENV');
	if($envFromServer) {
		$env = $envFromServer;
	}
	$baseUrls = array(
		"development" => "http://localhost/occitanieboissons/catalogue",
		"production"  => "https://catalogue.occitanieboissons.com"
	);
	$baseUrl = isset($baseUrls[$env]) ? $baseUrls[$env] : $baseUrls["development"];

	$sitename = "Occitanie Boissons";
	//$url = "//".$_SERVER["HTTP_HOST"];
	$url = $baseUrl;
	$image_url = $baseUrl;
	$gallery = $url."/gallery";
	$galleryimage = str_replace("catalogue.", "", $gallery);
	$urlupload = $gallery."/upload/";
	$drapeaux = $gallery."/images/drapeaux/";

	$mails = array(
		"production" => array(
			"mail" => "commande@occitanieboissons.com",
			"newsletter" => "commercial@occitanieboissons.com",
			"contact" => "contact@occitanieboissons.com"
		),
		"development"  => array(
			"mail" => "rado.rakotoarivelo@amws.space",
			"newsletter" => "rado.rakotoarivelo@amws.space",
			"contact" => "rado.rakotoarivelo@amws.space"
		)
	);

	$mail = $mails[$env]['mail'];
	$mail_newsletter = $mails[$env]['newsletter'];
	$mail_contact = $mails[$env]['contact'];

	#### GESTION UTILISATEUR
	if(isset($_SESSION['site'])) {
		$utilisateur = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
		$utilisateur->bindParam(":email", $_SESSION['site']);
		$utilisateur->execute();
		if($utilisateur->rowCount() < 1) {
			unset($_SESSION['site']);
			header("Refresh:0");
		} else {
			$u = $utilisateur->fetch(PDO::FETCH_OBJ);
		}
	}
	
	#### MODIFICATION DU CATALOGUE AUTOMATIQUE
	// Complément de données pour le menu (famille / sous-famille)
	// Source: catalogue/transfert/produits/ART_PRIX_STO.CSV
	function ob_slugify($value) {
		$value = trim((string) $value);
		if($value === '') {
			return '';
		}
		if(function_exists('iconv')) {
			$converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
			if($converted !== false) {
				$value = $converted;
			}
		}
		$value = strtolower($value);
		$value = preg_replace('/[^a-z0-9]+/', '-', $value);
		$value = trim($value, '-');
		return $value;
	}
	function ob_get_or_create_famille_id($bdd, $nom, &$cacheBySlug) {
		$nom = trim((string) $nom);
		if($nom === '') {
			return null;
		}
		$slug = ob_slugify($nom);
		if($slug === '') {
			return null;
		}
		if(isset($cacheBySlug[$slug])) {
			return (int) $cacheBySlug[$slug];
		}
		$sel = $bdd->prepare("SELECT id FROM ob_catalogue_familles WHERE slug = :slug LIMIT 1");
		$sel->bindParam(':slug', $slug);
		$sel->execute();
		$found = $sel->fetch(PDO::FETCH_OBJ);
		if($found && isset($found->id)) {
			$cacheBySlug[$slug] = (int) $found->id;
			return (int) $found->id;
		}
		$ins = $bdd->prepare("INSERT INTO ob_catalogue_familles (nom, slug) VALUES (:nom, :slug)");
		$ins->bindParam(':nom', $nom);
		$ins->bindParam(':slug', $slug);
		$ins->execute();
		$id = (int) $bdd->lastInsertId();
		$cacheBySlug[$slug] = $id;
		return $id;
	}
	function ob_get_or_create_sous_famille_id($bdd, $familleId, $nom, &$cacheByFamilleAndSlug) {
		$nom = trim((string) $nom);
		if($nom === '' || !$familleId) {
			return null;
		}
		$slug = ob_slugify($nom);
		if($slug === '') {
			return null;
		}
		$key = (int) $familleId . ':' . $slug;
		if(isset($cacheByFamilleAndSlug[$key])) {
			return (int) $cacheByFamilleAndSlug[$key];
		}
		$sel = $bdd->prepare("SELECT id FROM ob_catalogue_sous_familles WHERE famille_id = :fid AND slug = :slug LIMIT 1");
		$sel->bindParam(':fid', $familleId, PDO::PARAM_INT);
		$sel->bindParam(':slug', $slug);
		$sel->execute();
		$found = $sel->fetch(PDO::FETCH_OBJ);
		if($found && isset($found->id)) {
			$cacheByFamilleAndSlug[$key] = (int) $found->id;
			return (int) $found->id;
		}
		$ins = $bdd->prepare("INSERT INTO ob_catalogue_sous_familles (famille_id, nom, slug) VALUES (:fid, :nom, :slug)");
		$ins->bindParam(':fid', $familleId, PDO::PARAM_INT);
		$ins->bindParam(':nom', $nom);
		$ins->bindParam(':slug', $slug);
		$ins->execute();
		$id = (int) $bdd->lastInsertId();
		$cacheByFamilleAndSlug[$key] = $id;
		return $id;
	}
	function ob_column_exists($bdd, $table, $column) {
		$statement = $bdd->prepare("SHOW COLUMNS FROM `".$table."` LIKE :column");
		$statement->bindParam(':column', $column);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC) !== false;
	}
	function ob_index_exists($bdd, $table, $indexName) {
		$statement = $bdd->prepare("SHOW INDEX FROM `".$table."` WHERE Key_name = :index_name");
		$statement->bindParam(':index_name', $indexName);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC) !== false;
	}
	function ob_add_column_if_missing($bdd, $table, $column, $definitionSql) {
		if(!ob_column_exists($bdd, $table, $column)) {
			$bdd->exec("ALTER TABLE `".$table."` ADD COLUMN `".$column."` ".$definitionSql);
		}
	}
	function ob_add_index_if_missing($bdd, $table, $indexName, $definitionSql) {
		if(!ob_index_exists($bdd, $table, $indexName)) {
			$bdd->exec("ALTER TABLE `".$table."` ADD ".$definitionSql);
		}
	}
	function ob_ensure_catalogue_schema($bdd) {
		$bdd->exec("CREATE TABLE IF NOT EXISTS ob_catalogue_categories (
			id INT(11) NOT NULL AUTO_INCREMENT,
			code INT(11) NOT NULL,
			nom VARCHAR(200) NOT NULL,
			slug VARCHAR(200) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY uq_ob_catalogue_categories_code (code),
			KEY idx_ob_catalogue_categories_slug (slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$bdd->exec("CREATE TABLE IF NOT EXISTS ob_catalogue_fabriquants (
			id INT(11) NOT NULL AUTO_INCREMENT,
			code INT(11) NOT NULL,
			nom VARCHAR(200) NOT NULL,
			rue VARCHAR(200) DEFAULT NULL,
			quartier VARCHAR(200) DEFAULT NULL,
			code_postal VARCHAR(50) DEFAULT NULL,
			ville VARCHAR(150) DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY uq_ob_catalogue_fabriquants_code (code),
			KEY idx_ob_catalogue_fabriquants_nom (nom)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$bdd->exec("CREATE TABLE IF NOT EXISTS ob_catalogue_pays (
			id INT(11) NOT NULL AUTO_INCREMENT,
			code VARCHAR(50) NOT NULL,
			nom VARCHAR(200) NOT NULL,
			slug VARCHAR(200) NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY uq_ob_catalogue_pays_code (code),
			KEY idx_ob_catalogue_pays_slug (slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		ob_add_column_if_missing($bdd, 'ob_catalogue_familles', 'code', 'INT(11) DEFAULT NULL AFTER id');
		ob_add_column_if_missing($bdd, 'ob_catalogue_sous_familles', 'code', 'INT(11) DEFAULT NULL AFTER famille_id');
		ob_add_column_if_missing($bdd, 'ob_catalogue_produits', 'pays_code', 'VARCHAR(50) DEFAULT NULL AFTER sous_famille_id');

		ob_add_index_if_missing($bdd, 'ob_catalogue_familles', 'uq_ob_catalogue_familles_code', 'UNIQUE KEY uq_ob_catalogue_familles_code (code)');
		ob_add_index_if_missing($bdd, 'ob_catalogue_sous_familles', 'uq_ob_catalogue_sous_familles_famille_code', 'UNIQUE KEY uq_ob_catalogue_sous_familles_famille_code (famille_id, code)');
		ob_add_index_if_missing($bdd, 'ob_catalogue_produits', 'idx_ob_catalogue_produits_pays_code', 'KEY idx_ob_catalogue_produits_pays_code (pays_code)');
	}
	function ob_excel_column_to_index($columnRef) {
		$columnRef = strtoupper((string) preg_replace('/[^A-Z]/i', '', (string) $columnRef));
		$length = strlen($columnRef);
		$index = 0;
		for($i = 0; $i < $length; $i++) {
			$index = ($index * 26) + (ord($columnRef[$i]) - 64);
		}
		return max(0, $index - 1);
	}
	function ob_xlsx_shared_strings($zip) {
		$sharedStrings = array();
		$xml = $zip->getFromName('xl/sharedStrings.xml');
		if($xml === false || trim((string) $xml) === '') {
			return $sharedStrings;
		}
		$shared = @simplexml_load_string($xml);
		if($shared === false || !isset($shared->si)) {
			return $sharedStrings;
		}
		foreach($shared->si as $item) {
			if(isset($item->t)) {
				$sharedStrings[] = trim((string) $item->t);
				continue;
			}
			$text = '';
			if(isset($item->r)) {
				foreach($item->r as $run) {
					$text .= (string) $run->t;
				}
			}
			$sharedStrings[] = trim($text);
		}
		return $sharedStrings;
	}
	function ob_xlsx_read_rows($path) {
		$rows = array();
		if(!$path || !file_exists($path) || !is_readable($path) || !class_exists('ZipArchive')) {
			return $rows;
		}
		$zip = new ZipArchive();
		if($zip->open($path) !== true) {
			return $rows;
		}
		$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
		if($sheetXml === false || trim((string) $sheetXml) === '') {
			$zip->close();
			return $rows;
		}
		$sharedStrings = ob_xlsx_shared_strings($zip);
		$zip->close();
		$sheet = @simplexml_load_string($sheetXml);
		if($sheet === false || !isset($sheet->sheetData->row)) {
			return $rows;
		}
		foreach($sheet->sheetData->row as $row) {
			$rowValues = array();
			$maxIndex = -1;
			foreach($row->c as $cell) {
				$cellRef = isset($cell['r']) ? (string) $cell['r'] : '';
				$index = ob_excel_column_to_index($cellRef);
				if($index > $maxIndex) {
					$maxIndex = $index;
				}
				$value = '';
				$type = isset($cell['t']) ? (string) $cell['t'] : '';
				if($type === 's') {
					$sharedIndex = (int) $cell->v;
					$value = isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
				} elseif($type === 'inlineStr') {
					$value = isset($cell->is->t) ? (string) $cell->is->t : '';
				} else {
					$value = isset($cell->v) ? (string) $cell->v : '';
				}
				$rowValues[$index] = trim((string) $value);
			}
			if($maxIndex >= 0) {
				$normalized = array();
				for($i = 0; $i <= $maxIndex; $i++) {
					$normalized[] = array_key_exists($i, $rowValues) ? $rowValues[$i] : '';
				}
				$rows[] = $normalized;
			}
		}
		return $rows;
	}
	function ob_xlsx_assoc_rows($path) {
		$rows = ob_xlsx_read_rows($path);
		if(empty($rows)) {
			return array();
		}
		$headers = array_shift($rows);
		$headers = array_map(function($value) {
			return trim((string) $value);
		}, $headers);
		$data = array();
		foreach($rows as $row) {
			if(count($row) < count($headers)) {
				$row = array_pad($row, count($headers), '');
			}
			$assoc = array_combine($headers, array_slice($row, 0, count($headers)));
			if(!is_array($assoc)) {
				continue;
			}
			$hasValue = false;
			foreach($assoc as $value) {
				if(trim((string) $value) !== '') {
					$hasValue = true;
					break;
				}
			}
			if($hasValue) {
				$data[] = $assoc;
			}
		}
		return $data;
	}
	function ob_upsert_lookup_entry($bdd, $table, $code, $values) {
		$fields = array_keys($values);
		$columns = array_merge(array('code'), $fields);
		$insertColumns = array();
		$insertParams = array();
		$updates = array();
		$params = array(':code' => $code);
		foreach($columns as $column) {
			$insertColumns[] = '`'.$column.'`';
			$insertParams[] = ':'.$column;
			if($column !== 'code') {
				$updates[] = '`'.$column.'` = VALUES(`'.$column.'`)';
				$params[':'.$column] = $values[$column];
			}
		}
		$sql = 'INSERT INTO `'.$table.'` ('.implode(', ', $insertColumns).') VALUES ('.implode(', ', $insertParams).') ON DUPLICATE KEY UPDATE '.implode(', ', $updates);
		$statement = $bdd->prepare($sql);
		$statement->execute($params);
	}
	function ob_get_or_create_famille_by_code($bdd, $code, $nom, &$cacheByCode) {
		$code = (int) $code;
		$nom = trim((string) $nom);
		if($code <= 0 || $nom === '') {
			return null;
		}
		if(isset($cacheByCode[$code])) {
			return (int) $cacheByCode[$code];
		}
		$slug = ob_slugify($nom);
		$select = $bdd->prepare('SELECT id FROM ob_catalogue_familles WHERE code = :code LIMIT 1');
		$select->bindParam(':code', $code, PDO::PARAM_INT);
		$select->execute();
		$found = $select->fetch(PDO::FETCH_OBJ);
		if($found && isset($found->id)) {
			$update = $bdd->prepare('UPDATE ob_catalogue_familles SET nom = :nom, slug = :slug WHERE id = :id');
			$update->execute(array(':nom' => $nom, ':slug' => $slug, ':id' => (int) $found->id));
			$cacheByCode[$code] = (int) $found->id;
			return (int) $found->id;
		}
		$selectBySlug = $bdd->prepare('SELECT id FROM ob_catalogue_familles WHERE slug = :slug LIMIT 1');
		$selectBySlug->bindParam(':slug', $slug);
		$selectBySlug->execute();
		$foundBySlug = $selectBySlug->fetch(PDO::FETCH_OBJ);
		if($foundBySlug && isset($foundBySlug->id)) {
			$update = $bdd->prepare('UPDATE ob_catalogue_familles SET code = :code, nom = :nom WHERE id = :id');
			$update->execute(array(':code' => $code, ':nom' => $nom, ':id' => (int) $foundBySlug->id));
			$cacheByCode[$code] = (int) $foundBySlug->id;
			return (int) $foundBySlug->id;
		}
		$insert = $bdd->prepare('INSERT INTO ob_catalogue_familles (code, nom, slug) VALUES (:code, :nom, :slug)');
		$insert->execute(array(':code' => $code, ':nom' => $nom, ':slug' => $slug));
		$cacheByCode[$code] = (int) $bdd->lastInsertId();
		return (int) $cacheByCode[$code];
	}
	function ob_get_or_create_sous_famille_by_code($bdd, $familleId, $code, $nom, &$cacheByFamilleAndCode) {
		$familleId = (int) $familleId;
		$code = (int) $code;
		$nom = trim((string) $nom);
		if($familleId <= 0 || $code <= 0 || $nom === '') {
			return null;
		}
		$key = $familleId.':'.$code;
		if(isset($cacheByFamilleAndCode[$key])) {
			return (int) $cacheByFamilleAndCode[$key];
		}
		$slug = ob_slugify($nom);
		$select = $bdd->prepare('SELECT id FROM ob_catalogue_sous_familles WHERE famille_id = :famille_id AND code = :code LIMIT 1');
		$select->execute(array(':famille_id' => $familleId, ':code' => $code));
		$found = $select->fetch(PDO::FETCH_OBJ);
		if($found && isset($found->id)) {
			$update = $bdd->prepare('UPDATE ob_catalogue_sous_familles SET nom = :nom, slug = :slug WHERE id = :id');
			$update->execute(array(':nom' => $nom, ':slug' => $slug, ':id' => (int) $found->id));
			$cacheByFamilleAndCode[$key] = (int) $found->id;
			return (int) $found->id;
		}
		$selectBySlug = $bdd->prepare('SELECT id FROM ob_catalogue_sous_familles WHERE famille_id = :famille_id AND slug = :slug LIMIT 1');
		$selectBySlug->execute(array(':famille_id' => $familleId, ':slug' => $slug));
		$foundBySlug = $selectBySlug->fetch(PDO::FETCH_OBJ);
		if($foundBySlug && isset($foundBySlug->id)) {
			$update = $bdd->prepare('UPDATE ob_catalogue_sous_familles SET code = :code, nom = :nom WHERE id = :id');
			$update->execute(array(':code' => $code, ':nom' => $nom, ':id' => (int) $foundBySlug->id));
			$cacheByFamilleAndCode[$key] = (int) $foundBySlug->id;
			return (int) $foundBySlug->id;
		}
		$insert = $bdd->prepare('INSERT INTO ob_catalogue_sous_familles (famille_id, code, nom, slug) VALUES (:famille_id, :code, :nom, :slug)');
		$insert->execute(array(':famille_id' => $familleId, ':code' => $code, ':nom' => $nom, ':slug' => $slug));
		$cacheByFamilleAndCode[$key] = (int) $bdd->lastInsertId();
		return (int) $cacheByFamilleAndCode[$key];
	}
	function ob_country_label_from_context($countryCode, $fabriquantCode, $countryLabelsByFabriquant) {
		$countryCode = trim((string) $countryCode);
		$fabriquantCode = (int) $fabriquantCode;
		if($fabriquantCode > 0 && isset($countryLabelsByFabriquant[$fabriquantCode]) && trim((string) $countryLabelsByFabriquant[$fabriquantCode]) !== '') {
			return trim((string) $countryLabelsByFabriquant[$fabriquantCode]);
		}
		return $countryCode;
	}
	function ob_file_signature($path) {
		if(!$path || !file_exists($path) || !is_readable($path)) {
			return null;
		}
		$mtime = @filemtime($path);
		$size = @filesize($path);
		return array(
			'path' => (string) $path,
			'mtime' => ($mtime === false) ? null : (int) $mtime,
			'size' => ($size === false) ? null : (int) $size,
		);
	}
	function ob_should_run_import($stateFile, $nextState, $force = false) {
		if($force) {
			return true;
		}
		$prevRaw = @file_get_contents($stateFile);
		if($prevRaw === false || $prevRaw === '') {
			return true;
		}
		$prev = json_decode($prevRaw, true);
		if(!is_array($prev)) {
			return true;
		}
		if(!isset($prev['version']) || (int) $prev['version'] !== (int) $nextState['version']) {
			return true;
		}
		foreach($nextState as $key => $value) {
			if($key === 'generated_at' || $key === 'version') {
				continue;
			}
			if(!array_key_exists($key, $prev) || $prev[$key] != $value) {
				return true;
			}
		}
		return false;
	}
	function ob_delete_missing_catalogue_products($bdd, $retainedCodes) {
		$retainedMap = array();
		foreach($retainedCodes as $code) {
			$code = (int) $code;
			if($code > 0) {
				$retainedMap[$code] = true;
			}
		}
		$deleteProduit = $bdd->prepare("DELETE FROM ob_catalogue_produits WHERE code_produit = :code_produit");
		$existingCodes = $bdd->query("SELECT code_produit FROM ob_catalogue_produits");
		while($existing = $existingCodes->fetch(PDO::FETCH_OBJ)) {
			$code = isset($existing->code_produit) ? (int) $existing->code_produit : 0;
			if($code > 0 && !isset($retainedMap[$code])) {
				$deleteProduit->execute(array(':code_produit' => $code));
			}
		}
	}

	// Import ERP -> tables (ne doit pas tourner à chaque page)
	$artFile = __DIR__ . '/../transfert/produits/ART_PRIX_STO.CSV';
	$tarifFile = __DIR__ . "/../transfert/produits/TARIFINTERNET_COMPLET.CSV";
	$allArtFile = __DIR__ . '/../transfert/produits/all_art.xlsx';
	$familleSsFamilleFile = __DIR__ . '/../transfert/produits/famille_ssfamille.xlsx';
	$categoriesFile = __DIR__ . '/../transfert/produits/categories.xlsx';
	$fabriquantFile = __DIR__ . '/../transfert/produits/fabriquant.xlsx';
	$importStateFile = __DIR__ . "/../transfert/produits/.ob_import_state.json";
	$importLockFile = __DIR__ . "/../transfert/produits/.ob_import_lock";
	$forceImport = (isset($_GET['ob_import']) && $_GET['ob_import'] == '1') || (getenv('OB_FORCE_IMPORT') === '1');
	$nextState = array(
		'version' => 3,
		'tarif' => ob_file_signature($tarifFile),
		'art_csv' => ob_file_signature($artFile),
		'all_art' => ob_file_signature($allArtFile),
		'famille_ssfamille' => ob_file_signature($familleSsFamilleFile),
		'categories' => ob_file_signature($categoriesFile),
		'fabriquant' => ob_file_signature($fabriquantFile),
	);
	$shouldImport = ($nextState['all_art'] !== null || $nextState['tarif'] !== null) && ob_should_run_import($importStateFile, $nextState, $forceImport);
	if($shouldImport) {
		$lockHandle = @fopen($importLockFile, 'c');
		$locked = false;
		if($lockHandle) {
			$locked = @flock($lockHandle, LOCK_EX | LOCK_NB);
		}
		// Si un autre process importe déjà, on ne bloque pas la navigation.
		if($lockHandle && !$locked) {
			@fclose($lockHandle);
		} else {
			// Double-check sous lock (un autre process a pu finir pendant qu'on attendait)
			$shouldImport = ($nextState['all_art'] !== null || $nextState['tarif'] !== null) && ob_should_run_import($importStateFile, $nextState, $forceImport);
			if($shouldImport) {
				$familleIdCache = array();
				$sousFamilleIdCache = array();
				$retainedCodeProduits = array();
				$processedProductRows = 0;

				ob_ensure_catalogue_schema($bdd);
				try {
					$bdd->beginTransaction();

					$countryLabelsByFabriquant = array();
					$brasseriesCheck = $bdd->query("SELECT id_fabriquant, country FROM ob_brasseries WHERE hiden = '1' AND id_fabriquant <> 0");
					while($c = $brasseriesCheck->fetch(PDO::FETCH_OBJ)) {
						$fabriquantCode = isset($c->id_fabriquant) ? (int) $c->id_fabriquant : 0;
						if($fabriquantCode > 0 && !empty($c->country)) {
							$countryLabelsByFabriquant[$fabriquantCode] = trim((string) $c->country);
						}
					}

					$familleLookup = array();
					$sousFamilleLookup = array();
					foreach(ob_xlsx_assoc_rows($familleSsFamilleFile) as $row) {
						$familleCode = (int) trim((string) (isset($row['Famille']) ? $row['Famille'] : '0'));
						$sousFamilleCode = trim((string) (isset($row['Sous famille']) ? $row['Sous famille'] : ''));
						$libelle = trim((string) (isset($row['Libellé famille']) ? $row['Libellé famille'] : ''));
						if($familleCode <= 0 || $libelle === '') {
							continue;
						}
						if($sousFamilleCode === '') {
							$familleLookup[$familleCode] = $libelle;
							continue;
						}
						$sousFamilleLookup[$familleCode.':'.(int) $sousFamilleCode] = $libelle;
					}
					foreach($familleLookup as $familleCode => $familleNom) {
						ob_get_or_create_famille_by_code($bdd, $familleCode, $familleNom, $familleIdCache);
					}

					foreach(ob_xlsx_assoc_rows($categoriesFile) as $row) {
						$code = (int) trim((string) (isset($row['Catégorie']) ? $row['Catégorie'] : '0'));
						$nom = trim((string) (isset($row['Nom']) ? $row['Nom'] : ''));
						if($code > 0 && $nom !== '') {
							ob_upsert_lookup_entry($bdd, 'ob_catalogue_categories', $code, array(
								'nom' => $nom,
								'slug' => ob_slugify($nom),
							));
						}
					}

					foreach(ob_xlsx_assoc_rows($fabriquantFile) as $row) {
						$code = (int) trim((string) (isset($row['Code']) ? $row['Code'] : '0'));
						$nom = trim((string) (isset($row['Nom']) ? $row['Nom'] : ''));
						if($code > 0 && $nom !== '') {
							ob_upsert_lookup_entry($bdd, 'ob_catalogue_fabriquants', $code, array(
								'nom' => $nom,
								'rue' => isset($row['Rue']) ? trim((string) $row['Rue']) : null,
								'quartier' => isset($row['Quartier']) ? trim((string) $row['Quartier']) : null,
								'code_postal' => isset($row['Code postal']) ? trim((string) $row['Code postal']) : null,
								'ville' => isset($row['Ville']) ? trim((string) $row['Ville']) : null,
							));
						}
					}

					$tarifByCodeProduit = array();
					if($tarifFile && file_exists($tarifFile) && is_readable($tarifFile) && ($handle = fopen($tarifFile, 'r')) !== FALSE) {
						$header = fgetcsv($handle, 0, ';');
						if(is_array($header) && count($header) > 1) {
							while(($row = fgetcsv($handle, 0, ';')) !== FALSE) {
								if(!is_array($row) || count($row) !== count($header)) {
									continue;
								}
								$assoc = array_combine($header, $row);
								if(!is_array($assoc) || !isset($assoc['CODE_PRODUIT'])) {
									continue;
								}
								$code = (int) preg_replace('/[^\d]/', '', (string) $assoc['CODE_PRODUIT']);
								if($code > 0) {
									$tarifByCodeProduit[$code] = $assoc;
								}
							}
						}
						fclose($handle);
					}

					$checkExist = $bdd->prepare("SELECT id FROM ob_catalogue_produits WHERE code_produit = :code_produit LIMIT 1");
					$modifProduit = $bdd->prepare("UPDATE ob_catalogue_produits SET prix_ht = :prix_ht, droits = :droits, stock = :stock, code_tva = :code_tva, nom = :nom, nom_sup = :nom_sup, uv_caisse = :uv_caisse, brasserie = :brasserie, contenance = :contenance, degre = :degre, condition_vente = :condition_vente, consigne_caisse = :consigne_caisse, marque = :marque, categorie = :categorie, famille_id = :famille_id, sous_famille_id = :sous_famille_id, pays_code = :pays_code WHERE code_produit = :code_produit");
					$createProduit = $bdd->prepare("INSERT INTO ob_catalogue_produits (code_produit, prix_ht, stock, code_tva, nom, nom_sup, brasserie, contenance, degre, condition_vente, consigne_caisse, uv_caisse, droits, marque, categorie, famille_id, sous_famille_id, pays_code) VALUES (:code_produit, :prix_ht, :stock, :code_tva, :nom, :nom_sup, :brasserie, :contenance, :degre, :condition_vente, :consigne_caisse, :uv_caisse, :droits, :marque, :categorie, :famille_id, :sous_famille_id, :pays_code)");

					$allArtRows = ob_xlsx_assoc_rows($allArtFile);
					if(empty($allArtRows) && $artFile && file_exists($artFile) && is_readable($artFile)) {
						$headerArt = null;
						$delimiterArt = ';';
						if(($handleArt = fopen($artFile, 'r')) !== FALSE) {
							$headerLineArt = fgets($handleArt);
							if($headerLineArt !== false) {
								$headerArt = str_getcsv($headerLineArt, ';');
								if(is_array($headerArt) && count($headerArt) === 1 && strpos($headerLineArt, "\t") !== false) {
									$headerArt = str_getcsv($headerLineArt, "\t");
									$delimiterArt = "\t";
								}
							}
							if(is_array($headerArt) && count($headerArt) > 1) {
								while(($lineArt = fgets($handleArt)) !== FALSE) {
									$rowArt = str_getcsv($lineArt, $delimiterArt);
									if(!is_array($rowArt) || count($rowArt) !== count($headerArt)) {
										continue;
									}
									$assocArt = array_combine($headerArt, $rowArt);
									if(is_array($assocArt)) {
										$allArtRows[] = array(
											'Code article' => isset($assocArt['Article']) ? $assocArt['Article'] : '',
											'Libellé article' => isset($assocArt['Désignation article']) ? $assocArt['Désignation article'] : '',
											'Lib complementaire' => '',
											'Famille' => '',
											'Ssfamille' => '',
											'Code taxe' => isset($assocArt['Code TVA']) ? $assocArt['Code TVA'] : '',
											'Contenance' => '',
											'Degre alcool' => '',
											'Code Suppression' => '',
											'Categorie article' => '',
											'Code' => '',
											'Code pays' => '',
											'Pré commande' => '',
											'Unite caisse' => '',
											'Vente en caisse ou fût' => '',
										);
									}
								}
							}
							fclose($handleArt);
						}
					}

					foreach($allArtRows as $row) {
						$codeProduit = (int) preg_replace('/[^\d]/', '', (string) (isset($row['Code article']) ? $row['Code article'] : '0'));
						if($codeProduit <= 0) {
							continue;
						}
						$codeSuppression = strtoupper(trim((string) (isset($row['Code Suppression']) ? $row['Code Suppression'] : '')));
						if($codeSuppression === 'S') {
							continue;
						}
						$processedProductRows++;

						$familleCode = (int) trim((string) (isset($row['Famille']) ? $row['Famille'] : '0'));
						$sousFamilleCode = (int) trim((string) (isset($row['Ssfamille']) ? $row['Ssfamille'] : '0'));
						$familleId = null;
						$sousFamilleId = null;
						if($familleCode > 0 && isset($familleLookup[$familleCode])) {
							$familleId = ob_get_or_create_famille_by_code($bdd, $familleCode, $familleLookup[$familleCode], $familleIdCache);
						}
						if($familleId && $familleCode > 0 && $sousFamilleCode > 0) {
							$sousFamilleKey = $familleCode.':'.$sousFamilleCode;
							if(isset($sousFamilleLookup[$sousFamilleKey])) {
								$sousFamilleId = ob_get_or_create_sous_famille_by_code($bdd, $familleId, $sousFamilleCode, $sousFamilleLookup[$sousFamilleKey], $sousFamilleIdCache);
							}
						}

						$tarif = isset($tarifByCodeProduit[$codeProduit]) ? $tarifByCodeProduit[$codeProduit] : array();
						$nom = trim((string) (isset($row['Libellé article']) ? $row['Libellé article'] : (isset($tarif['LIBELLE']) ? $tarif['LIBELLE'] : '')));
						$nomSup = trim((string) (isset($row['Lib complementaire']) ? $row['Lib complementaire'] : (isset($tarif['LIBELLE COMP.']) ? $tarif['LIBELLE COMP.'] : '')));
						$fabriquantCode = (int) trim((string) (isset($row['Code']) ? $row['Code'] : (isset($tarif['FABRIQUANT']) ? $tarif['FABRIQUANT'] : '0')));
						$categorie = (int) trim((string) (isset($row['Categorie article']) ? $row['Categorie article'] : (isset($tarif['CATEGORIE']) ? $tarif['CATEGORIE'] : '0')));
						$paysCode = trim((string) (isset($row['Code pays']) ? $row['Code pays'] : ''));
						if($paysCode !== '') {
							$paysNom = ob_country_label_from_context($paysCode, $fabriquantCode, $countryLabelsByFabriquant);
							ob_upsert_lookup_entry($bdd, 'ob_catalogue_pays', $paysCode, array(
								'nom' => $paysNom,
								'slug' => ob_slugify($paysNom),
							));
						}

						$contenance = isset($tarif['CONTENANCE']) ? (float) trim((string) $tarif['CONTENANCE']) : (float) trim((string) (isset($row['Contenance']) ? str_replace(',', '.', $row['Contenance']) : '0'));
						if($contenance > 0 && $contenance < 10) {
							$contenance *= 100;
						}
						$degre = isset($tarif['DEGRE']) ? (float) trim((string) $tarif['DEGRE']) : (float) trim((string) (isset($row['Degre alcool']) ? str_replace(',', '.', $row['Degre alcool']) : '0'));
						$uvCaisse = isset($tarif['UV_CAISSE']) ? (int) trim((string) $tarif['UV_CAISSE']) : (int) trim((string) (isset($row['Unite caisse']) ? $row['Unite caisse'] : '0'));
						$conditionVente = isset($tarif['CONDITION_VENTE']) ? (int) trim((string) $tarif['CONDITION_VENTE']) : (int) trim((string) (isset($row['Vente en caisse ou fût']) ? $row['Vente en caisse ou fût'] : '0'));
						$marque = isset($tarif['MARQUE']) ? trim((string) $tarif['MARQUE']) : ((trim((string) (isset($row['Pré commande']) ? $row['Pré commande'] : '0')) === '1') ? '2' : '1');
						if(!in_array($marque, array('0', '1', '2'), true)) {
							$marque = '1';
						}
						$dataProduit = array(
							':code_produit' => $codeProduit,
							':prix_ht' => isset($tarif['PRIX']) ? (string) ((float) trim((string) $tarif['PRIX'])) : '0',
							':stock' => isset($tarif['STOCK_UV']) ? (int) trim((string) $tarif['STOCK_UV']) : 0,
							':code_tva' => isset($tarif['CODE_TVA']) ? (int) trim((string) $tarif['CODE_TVA']) : (int) trim((string) (isset($row['Code taxe']) ? $row['Code taxe'] : '0')),
							':nom' => $nom,
							':nom_sup' => $nomSup,
							':brasserie' => $fabriquantCode,
							':contenance' => (string) $contenance,
							':degre' => (string) $degre,
							':condition_vente' => $conditionVente,
							':consigne_caisse' => isset($tarif['CONSIGNE_PAR_CONDITION_VENTE']) ? (string) ((float) trim((string) $tarif['CONSIGNE_PAR_CONDITION_VENTE'])) : '0',
							':uv_caisse' => $uvCaisse,
							':droits' => isset($tarif['ACCISE']) ? (string) ((float) trim((string) $tarif['ACCISE'])) : '0',
							':marque' => $marque,
							':categorie' => $categorie,
							':famille_id' => $familleId,
							':sous_famille_id' => $sousFamilleId,
							':pays_code' => ($paysCode !== '') ? $paysCode : null,
						);

						$checkExist->execute(array(':code_produit' => $codeProduit));
						$existingId = $checkExist->fetchColumn();
						if($existingId !== false && $existingId !== null) {
							$modifProduit->execute($dataProduit);
						} else {
							$createProduit->execute($dataProduit);
						}
						$retainedCodeProduits[$codeProduit] = true;
					}

					if($processedProductRows > 0) {
						ob_delete_missing_catalogue_products($bdd, array_keys($retainedCodeProduits));
					}

					$bdd->commit();
					$nextState['generated_at'] = date('c');
					@file_put_contents($importStateFile, json_encode($nextState));
				} catch(Exception $e) {
					if($bdd->inTransaction()) {
						$bdd->rollBack();
					}
					throw $e;
				}
			}
			if($lockHandle) {
				@flock($lockHandle, LOCK_UN);
				@fclose($lockHandle);
			}
		}
	}

    #### GENERATION DU FICHIER BON DE COMMANDE

    // NOTIFICATION DES COMMANDES ENVOYEES
    $file_commande = "./transfert/commande/COMMANDES.txt";
    if(file_exists($file_commande) && is_readable($file_commande)) {
	    $commandeCheck = explode(";", file_get_contents($file_commande, true));
	    foreach($commandeCheck as $numero_commande) {
	    	$checkCommande = $bdd->prepare("UPDATE ob_users_commande SET envoye = '1' WHERE id = :numero_commande");
	    	$checkCommande->bindParam(":numero_commande", $numero_commande);
	    	$checkCommande->execute();
	    }
	}

    $file = "./transfert/commande/COMMANDES.CSV";
    if(file_exists($file) && is_readable($file)) {
	    $commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE envoye = '0' ORDER BY id");
	    $data = array();

	    while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
	    	$elements = $bdd->query("SELECT * FROM ob_users_commande_element WHERE commandeid = '".$c->id."'");
	    	$i = 1;
	    	while($e = $elements->fetch(PDO::FETCH_OBJ)) {
		    	$data[] = array(
		    		'NUMERO_LIGNE' => $i,
			        'CLIENT' => $c->numero_client,
			        'DATE' => date("d/m/Y", $c->time),
			        'DATE_LIVRAISON' => date("d/m/Y", $c->time),
			        'ARTICLE' => $e->code_produit,
			        'QUANTITE_CAISSE' => $e->qte,
			        'NUMERO_COMMANDE' => $c->id
			    );
			    $i++;
			}
	    }

		$fp = fopen($file,'w');
		fputcsv($fp, array('NUMERO_LIGNE','CLIENT','DATE','DATE_LIVRAISON','ARTICLE','QUANTITE_CAISSE','NUMERO_COMMANDE'), ';');
		foreach($data as $fields) {
		    fputcsv($fp, $fields, ';');
		}
		fclose($fp);
	}
    

	// Génération du mois
	$mois = array("Janvier" => "01", "Février" => "02", "Mars" => "03", "Avril" => "04", "Mai" => "05", "Juin" => "06", "Juillet" => "07", "Août" => "08", "Septembre" => "09", "Octobre" => "10", "Novembre" => "11", "Décembre" => "12");
	// ASSOCIATION D'UN DRAPEAU A UN PAYS
	$pays_brasseries = array(
		"Angleterre" => $drapeaux."Angleterre.png",
		"Autriche" => $drapeaux."Autriche.png",
		"Belgique" => $drapeaux."Belgique.png",
		"Canada" => $drapeaux."Canada.png",
		"Danemark" => $drapeaux."Danemark.png",
		"Espagne" => $drapeaux."Espagne.png",
		"Etats-Unis" => $drapeaux."USA.png",
		"Finlande" => $drapeaux."Finlande.png",
		"France" => $drapeaux."France.png",
		"Hongrie" => $drapeaux."Hongrie.png",
		"Irlande" => $drapeaux."Irlande.png",
		"Italie" => $drapeaux."Italie.png",
		"Norvège" => $drapeaux."Norvege.png",
		/*"Pays Basque" => $drapeaux."Pays basque.png",*/
		"Pays de Galles" => $drapeaux."Pays de Galles.png",
		"Pologne" => $drapeaux."Pologne.png",
		"Portugal" => $drapeaux."Portugal.png",
		/*"Royaume-Uni" => $drapeaux."UK.png",*/
		"Suède" => $drapeaux."Suede.png",
		"Suisse" => $drapeaux."Suisse.png",
	);
?>