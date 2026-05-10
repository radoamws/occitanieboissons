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
	$mail = "commande@occitanieboissons.com"; 
	$mail_newsletter = "commercial@occitanieboissons.com";
	$mail_contact = "contact@occitanieboissons.com"; 

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
	// Source: ART_PRIX_STO.CSV à la racine du projet (occitanieboissons/)
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
		if(!isset($prev['tarif']) || !isset($prev['art'])) {
			return true;
		}
		return !($prev['tarif'] == $nextState['tarif'] && $prev['art'] == $nextState['art']);
	}

	// Import CSV → tables (ne doit pas tourner à chaque page)
	$projectRoot = @realpath(__DIR__ . '/../../..');
	$artFile = $projectRoot ? ($projectRoot . '/ART_PRIX_STO.CSV') : null;
	if(!$artFile || !file_exists($artFile)) {
		$artFile = __DIR__ . '/../transfert/produits/ART_PRIX_STO.CSV';
	}
	$tarifFile = __DIR__ . "/../transfert/produits/TARIFINTERNET_COMPLET.CSV";
	$importStateFile = __DIR__ . "/../transfert/produits/.ob_import_state.json";
	$importLockFile = __DIR__ . "/../transfert/produits/.ob_import_lock";
	$forceImport = (isset($_GET['ob_import']) && $_GET['ob_import'] == '1') || (getenv('OB_FORCE_IMPORT') === '1');
	$nextState = array(
		'version' => 1,
		'tarif' => ob_file_signature($tarifFile),
		'art' => ob_file_signature($artFile),
	);
	$shouldImport = $nextState['tarif'] !== null && ob_should_run_import($importStateFile, $nextState, $forceImport);
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
			$shouldImport = $nextState['tarif'] !== null && ob_should_run_import($importStateFile, $nextState, $forceImport);
			if($shouldImport) {
				$taxonomyByCodeProduit = array();
				$familleIdCache = array();
				$sousFamilleIdCache = array();

				// ART_PRIX_STO.CSV (taxonomie)
				if($artFile && file_exists($artFile) && is_readable($artFile)) {
					$headerArt = NULL;
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
									$tryDelimiter = ($delimiterArt === ';') ? "\t" : ';';
									$rowTry = str_getcsv($lineArt, $tryDelimiter);
									if(is_array($rowTry) && count($rowTry) === count($headerArt)) {
										$rowArt = $rowTry;
										$delimiterArt = $tryDelimiter;
									} else {
										continue;
									}
								}
								$assocArt = array_combine($headerArt, $rowArt);
								if(!is_array($assocArt) || !isset($assocArt['Article'])) {
									continue;
								}
								$code = (int) preg_replace('/[^\d]/', '', (string) $assocArt['Article']);
								if($code <= 0) {
									continue;
								}
								$fam = isset($assocArt['Désignation famille']) ? trim((string) $assocArt['Désignation famille']) : '';
								$sf = isset($assocArt['Désignation sous famille']) ? trim((string) $assocArt['Désignation sous famille']) : '';
								$taxonomyByCodeProduit[$code] = array('famille' => $fam, 'sous_famille' => $sf);
							}
						}
						fclose($handleArt);
					}
				}

				try {
					$bdd->beginTransaction();

					### BRASSERIES AUTORISEES
					$brasseriesCheck = $bdd->query("SELECT id_fabriquant FROM ob_brasseries WHERE hiden = '1'");
					$brassieres_active = array();
					while($c = $brasseriesCheck->fetch(PDO::FETCH_OBJ)) {
						$brassieres_active[] = (int) $c->id_fabriquant;
					}

					$checkExist = $bdd->prepare("SELECT id FROM ob_catalogue_produits WHERE code_produit = :code_produit LIMIT 1");
					$modifProduit = $bdd->prepare("UPDATE ob_catalogue_produits SET prix_ht = :prix_ht, droits = :droits, stock = :stock, code_tva = :code_tva, uv_caisse = :uv_caisse, brasserie = :brasserie, contenance = :contenance, degre = :degre, condition_vente = :condition_vente, consigne_caisse = :consigne_caisse, marque = :marque, categorie = :categorie, famille_id = :famille_id, sous_famille_id = :sous_famille_id WHERE code_produit = :code_produit");
					$taxOnly = $bdd->prepare("UPDATE ob_catalogue_produits SET famille_id = :famille_id, sous_famille_id = :sous_famille_id WHERE code_produit = :code_produit");
					$createProduit = $bdd->prepare("INSERT INTO ob_catalogue_produits (code_produit, prix_ht, stock, code_tva, nom, nom_sup, brasserie, contenance, degre, condition_vente, consigne_caisse, uv_caisse, droits, marque, categorie, famille_id, sous_famille_id) VALUES (:code_produit, :prix_ht, :stock, :code_tva, :nom, :nom_sup, :brasserie, :contenance, :degre, :condition_vente, :consigne_caisse, :uv_caisse, :droits, :marque, :categorie, :famille_id, :sous_famille_id)");

					if(($handle = fopen($tarifFile, 'r')) !== FALSE) {
						$header = fgetcsv($handle, 0, ";");
						if(is_array($header) && count($header) > 1) {
							while(($row = fgetcsv($handle, 0, ";")) !== FALSE) {
								if(!is_array($row) || count($row) !== count($header)) {
									continue;
								}
								try {
									$assoc = array_combine($header, $row);
									} catch(ValueError $e) {
										continue;
									}
									if(!is_array($assoc) || !isset($assoc['CODE_PRODUIT'])) {
										continue;
									}

									$code_produit = (int) preg_replace('/[^\d]/', '', (string) $assoc['CODE_PRODUIT']);
									if($code_produit <= 0) {
										continue;
									}
									$fabriquant = (int) trim((string) $assoc['FABRIQUANT']);
									$categorie = (int) trim((string) $assoc['CATEGORIE']);
									$is_active = in_array($fabriquant, $brassieres_active, true);
									$requiresActiveBrasserie = ($categorie >= 1 && $categorie <= 19) || in_array($categorie, array(28,29,32,33), true);
									$canImport = $is_active || !$requiresActiveBrasserie;
									$brasserieToStore = $is_active ? $fabriquant : 0;

									$famille_id = null;
									$sous_famille_id = null;
									if(isset($taxonomyByCodeProduit[$code_produit])) {
										$famille_id = ob_get_or_create_famille_id($bdd, $taxonomyByCodeProduit[$code_produit]['famille'], $familleIdCache);
										$sous_famille_id = ob_get_or_create_sous_famille_id($bdd, $famille_id, $taxonomyByCodeProduit[$code_produit]['sous_famille'], $sousFamilleIdCache);
									}

									$checkExist->execute(array(':code_produit' => $code_produit));
									$existingId = $checkExist->fetchColumn();

									if($existingId !== false && $existingId !== null) {
										if($canImport) {
											$prix_ht          = (float) trim((string) $assoc['PRIX']);
											$droits           = (float) trim((string) $assoc['ACCISE']);
											$stock            = (int) trim((string) $assoc['STOCK_UV']);
											$code_tva         = (int) trim((string) $assoc['CODE_TVA']);
											$uv_caisse        = (int) trim((string) $assoc['UV_CAISSE']);
											$contenance       = (float) trim((string) $assoc['CONTENANCE']);
											$degre            = (float) trim((string) $assoc['DEGRE']);
											$condition_vente  = (int) trim((string) $assoc['CONDITION_VENTE']);
											$consigne_caisse  = (float) trim((string) $assoc['CONSIGNE_PAR_CONDITION_VENTE']);
											$marque           = trim((string) $assoc['MARQUE']);

											$modifProduit->execute(array(
												':prix_ht' => (string) $prix_ht,
												':droits' => (string) $droits,
												':stock' => $stock,
												':code_tva' => $code_tva,
												':uv_caisse' => $uv_caisse,
												':brasserie' => (int) $brasserieToStore,
												':contenance' => (string) $contenance,
												':degre' => (string) $degre,
												':condition_vente' => $condition_vente,
												':consigne_caisse' => (string) $consigne_caisse,
												':marque' => $marque,
												':categorie' => $categorie,
												':famille_id' => $famille_id,
												':sous_famille_id' => $sous_famille_id,
												':code_produit' => $code_produit,
											));
										} else {
											if(!is_null($famille_id) || !is_null($sous_famille_id)) {
												$taxOnly->execute(array(
													':famille_id' => $famille_id,
													':sous_famille_id' => $sous_famille_id,
													':code_produit' => $code_produit,
												));
											}
										}
									} else {
										if($canImport) {
											$createProduit->execute(array(
												':code_produit' => $code_produit,
												':prix_ht' => $assoc['PRIX'],
												':stock' => $assoc['STOCK_UV'],
												':code_tva' => $assoc['CODE_TVA'],
												':nom' => $assoc['LIBELLE'],
												':nom_sup' => $assoc['LIBELLE COMP.'],
												':brasserie' => (int) $brasserieToStore,
												':contenance' => $assoc['CONTENANCE'],
												':degre' => $assoc['DEGRE'],
												':condition_vente' => $assoc['CONDITION_VENTE'],
												':consigne_caisse' => $assoc['CONSIGNE_PAR_CONDITION_VENTE'],
												':uv_caisse' => $assoc['UV_CAISSE'],
												':droits' => $assoc['ACCISE'],
												':marque' => $assoc['MARQUE'],
												':categorie' => $categorie,
												':famille_id' => $famille_id,
												':sous_famille_id' => $sous_famille_id,
											));
										}
									}
							}
						}
						fclose($handle);
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