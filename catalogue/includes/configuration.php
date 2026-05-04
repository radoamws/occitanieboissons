<?php
	@session_start();

	# Connexion à la base de donnée
	$host = "occitanila010301.mysql.db";
	$port = "3306";
	$user = "occitanila010301";
	$pass = "Lilian0103";
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
	$sitename = "Occitanie Boissons";
	$url = "//".$_SERVER["HTTP_HOST"];
	$image_url = "http://catalogue.occitanieboissons.com";
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
	$file = "./transfert/produits/TARIFINTERNET_COMPLET.CSV";
	if(file_exists($file) && is_readable($file)) {
		$header = NULL;
		$data = array();
		if(($handle = fopen($file, 'r')) !== FALSE) {
	        while (($row = fgetcsv($handle, 0, ";")) !== FALSE) {
	            if(!$header) {
	            	$header = $row;
	            } else {
	                $data[] = array_combine($header, $row);
	            }
	        }
	        fclose($handle);

	        ### BRASSERIES AUTORISEES
	        $brasseriesCheck = $bdd->query("SELECT id_fabriquant FROM ob_brasseries WHERE hiden = '1'");
	        $brassieres_active = array();
	        while($c = $brasseriesCheck->fetch(PDO::FETCH_OBJ)) {
	        	$brassieres_active[] = $c->id_fabriquant;
	        }

	        ### ON SELECTIONNE LES ARTICLES
	        foreach($data as $p1) {
	        	if(in_array($p1["FABRIQUANT"], $brassieres_active)) {
		      		$checkExist = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE code_produit = :code_produit");
		      		$checkExist->bindParam(":code_produit", $p1["CODE_PRODUIT"]);
		      		$checkExist->execute(); 
					
		        	if($checkExist->rowCount() > 0) {
		        		### MODIFIER LE PRODUIT
						$prix_ht          = (float) trim($p1['PRIX']);
						$droits           = (float) trim($p1['ACCISE']);
						$stock            = (int) trim($p1['STOCK_UV']);
						$code_tva         = (int) trim($p1['CODE_TVA']);
						$uv_caisse        = (int) trim($p1['UV_CAISSE']);
						$brasserie        = (int) trim($p1['FABRIQUANT']);
						$contenance       = (float) trim($p1['CONTENANCE']);
						$degre            = (float) trim($p1['DEGRE']);
						$condition_vente  = (int) trim($p1['CONDITION_VENTE']);
						$consigne_caisse  = (float) trim($p1['CONSIGNE_PAR_CONDITION_VENTE']);
						$marque           = trim($p1['MARQUE']); // enum('0','1','2') → laisser en string
						$categorie        = (int) trim($p1['CATEGORIE']);
						$code_produit     = preg_replace('/[^\d]/', '', $p1['CODE_PRODUIT']); // int, nettoyé


		        		$modifProduit = $bdd->prepare("UPDATE ob_catalogue_produits SET prix_ht = :prix_ht, droits = :droits, stock = :stock, code_tva = :code_tva, uv_caisse = :uv_caisse, brasserie = :brasserie, contenance = :contenance, degre = :degre, condition_vente = :condition_vente, consigne_caisse = :consigne_caisse, marque = :marque, categorie = :categorie WHERE code_produit = :code_produit");
		        		$modifProduit->bindParam(":prix_ht", $prix_ht, PDO::PARAM_STR);        // FLOAT → PDO::PARAM_STR
						$modifProduit->bindParam(":droits", $droits, PDO::PARAM_STR);          // FLOAT
						$modifProduit->bindParam(":stock", $stock, PDO::PARAM_INT);            // INT
						$modifProduit->bindParam(":code_tva", $code_tva, PDO::PARAM_INT);      // INT
						$modifProduit->bindParam(":uv_caisse", $uv_caisse, PDO::PARAM_INT);    // INT
						$modifProduit->bindParam(":brasserie", $brasserie, PDO::PARAM_INT);    // INT
						$modifProduit->bindParam(":contenance", $contenance, PDO::PARAM_STR);   // FLOAT
						$modifProduit->bindParam(":degre", $degre, PDO::PARAM_STR);            // FLOAT
						$modifProduit->bindParam(":condition_vente", $condition_vente, PDO::PARAM_INT); // INT
						$modifProduit->bindParam(":consigne_caisse", $consigne_caisse, PDO::PARAM_STR);  // FLOAT
						$modifProduit->bindParam(":marque", $marque, PDO::PARAM_STR);          // ENUM → string
						$modifProduit->bindParam(":categorie", $categorie, PDO::PARAM_INT);    // INT
						$modifProduit->bindParam(":code_produit", $code_produit, PDO::PARAM_INT); // INT
		        		$modifProduit->execute();

						if ($code_produit == "2403") {
							//echo $modifProduit->rowCount();
							//$modifProduit->debugDumpParams();
						}
		        	} else {
		        		### CREER LE PRODUIT
		        		$createProduit = $bdd->prepare("INSERT INTO ob_catalogue_produits (code_produit, prix_ht, stock, code_tva, nom, nom_sup, brasserie, contenance, degre, condition_vente, consigne_caisse, uv_caisse, droits, marque, categorie) VALUES (:code_produit, :prix_ht, :stock, :code_tva, :nom, :nom_sup, :brasserie, :contenance, :degre, :condition_vente, :consigne_caisse, :uv_caisse, :droits, :marque, :categorie)");
		        		$createProduit->bindParam(":code_produit", $p1['CODE_PRODUIT']);
		        		$createProduit->bindParam(":prix_ht", $p1['PRIX']);
		        		$createProduit->bindParam(":stock", $p1['STOCK_UV']);
		        		$createProduit->bindParam(":code_tva", $p1['CODE_TVA']);
		        		$createProduit->bindParam(":nom", $p1['LIBELLE']);
		        		$createProduit->bindParam(":brasserie", $p1['FABRIQUANT']);
		        		$createProduit->bindParam(":nom_sup", $p1['LIBELLE COMP.']);
		        		$createProduit->bindParam(":contenance", $p1['CONTENANCE']);
		        		$createProduit->bindParam(":degre", $p1['DEGRE']);
		        		$createProduit->bindParam(":condition_vente", $p1['CONDITION_VENTE']);
		        		$createProduit->bindParam(":consigne_caisse", $p1['CONSIGNE_PAR_CONDITION_VENTE']);
		        		$createProduit->bindParam(":uv_caisse", $p1['UV_CAISSE']);
		        		$createProduit->bindParam(":droits", $p1['ACCISE']);
		        		$createProduit->bindParam(":marque", $p1['MARQUE']);
		        		$createProduit->bindParam(":categorie", $p1['CATEGORIE']);
		        		$createProduit->execute();
		        	}
		        }
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