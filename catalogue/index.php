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
	 $brasseries_select = FALSE;
	if(isset($_GET['id']) && isset($_GET['nom'])) { 
		$brasseries = $bdd->prepare("SELECT * FROM ob_brasseries WHERE id = :id AND hiden = '1' LIMIT 1");
		$brasseries->bindParam(':id', $_GET['id']);
		$brasseries->execute();
		if($brasseries->rowCount() < 1) {
			header("Location: ".$url."");
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
			header("Location: ".$url."");
			exit();
		} else {
		    $b = $brasseries->fetch(PDO::FETCH_OBJ);
		    $brasseries_select = TRUE;
		    $pagename = $b->name." - Catalogue";
		}
	}

	if(isset($_GET['pays'])) {
		if(!array_key_exists($_GET['pays'], $pays_brasseries)) {
			header("Location: ".$url."");
		}
	}

	$select_categorie = FALSE;
	if(isset($_GET['categorie'])) {
		$select_categorie = TRUE;
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
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/catalogue.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body>
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
							<a href="<?php echo $url; ?>"><button class="btn" type="button"><i class="icon-fleche-gauche"></i> Retour</button></a>
							<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-cart"></i> Panier <span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€ HT HD</button></a>
						</div>
					<?php } else { ?>
						<div class="button">
							<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-cart"></i> Panier <span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€ HT HD</button></a>
							<a href="<?php echo $url; ?>/Conditions de livraison OB.pdf" download><button class="btn" type="button"><i class="icon-pdf"></i> Conditions de livraison</button></a>
						</div>
					<?php } ?>
					<!-- RECHERCHER -->
					<div class="rechercher">
						<?php if(!$select_categorie) { ?>
							<select id="select_paysc">
								<option disabled>Rechercher par pays</option>
								<?php
									echo "<option value='toutes' ".((isset($_GET['pays'])) ? "" : "selected").">Tous les pays</option>";
									foreach($pays_brasseries as $nom => $d) {
										$checkPays = $bdd->query("SELECT * FROM ob_brasseries WHERE country = '".$nom."' AND hiden = '1' LIMIT 1");
										if($checkPays->rowCount() > 0) {
											echo "<option value='".$nom."' ".(($nom == $_GET['pays']) ? "selected" : "").">".$nom."</option>";
										}
									}
								?>
							</select>
						<?php } ?>
						<select id="select_categorie">
							<option disabled>Rechercher par catégories</option>
							<?php
								echo "<option value='toutes' ".((isset($_GET['categorie'])) ? "" : "selected").">Toutes les catégories</option>";
								$i = 1;
								while($i < 13) {
									echo "<option value='".$i."' ".(($i == $_GET['categorie']) ? "selected" : "").">".Categorie($i)."</option>";
									$i++;
								}
							?>
						</select>
						<div class="recherche-content">
							<form id="recherche_bc">
								<input type="text" name="term" placeholder="Rechercher une brasserie..." value="<?php echo @$b->name; ?>" class="ui-autocomplete-input" autocomplete="off">
								<button type="submit" class="btn"><i class="icon-loupe"></i></button>
							</form>
							<ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front" style="display: none;"></ul>
						</div>
						<?php if($brasseries_select || $select_categorie) { ?>
							<?php if($brasseries_select) { ?>
								<select data-id="<?php echo $b->id; ?>" data-titre="<?php echo $b->name; ?>" id="tri-prix">
							<?php } else { ?>
								<select data-categorie="<?php echo $_GET['categorie']; ?>" id="tri-prix-categorie">
							<?php } ?>
								<option disabled <?php if(@$_GET['trier_prix'] != "croissant" && @$_GET['trier_prix'] != "decroissant") {echo "selected";} ?>>Trier par prix</option>
								<option value="croissant" <?php if(@$_GET['trier_prix'] == "croissant") {echo "selected";} ?>>Croissant</option>
								<option value="decroissant" <?php if(@$_GET['trier_prix'] == "decroissant") {echo "selected";} ?>>Décroissant</option>
								<option value="aucun">Aucun</option>
							</select>
						<?php } ?>
					</div>
					<!-- BRASSERIES -->
					<div class="mot-cle">
						<?php
							$brasseries = $bdd->query("SELECT * FROM ob_brasseries WHERE hiden = '1' ORDER BY name");
							while($m = $brasseries->fetch(PDO::FETCH_OBJ)) {
						?>
							<a href="<?php echo $url."/".filterNom($m->name)."-".$m->id; ?>">
								<div <?php if(@$_GET['id'] == $m->id) {echo "class='selected'";}; ?>><?php echo $m->name; ?></div>
							</a>
						<?php } ?>
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
							<table class="boutique-table" style="width: 100%;margin-top: 10px;">
								<thead>
									<?php if($consigne) { ?>
										<tr>
											<th width="25%">Produit</th>
											<th width="15%">Contenance</th>
											<th width="8%">% Alcool</th>
											<th width="10.5%">Prix HT HD</th>
											<th width="10%">Droits accise</th>
											<th width="10.5%">Prix HT DC</th>
											<th width="10%">Consigne</th>
											<th width="11%">Quantité</th>
										</tr>
									<?php } else { ?>
										<tr>
											<th width="25%">Produit</th>
											<th width="15%">Contenance</th>
											<th width="10%">% Alcool</th>
											<th width="12.5%">Prix HT HD</th>
											<th width="10%">Droits accise</th>
											<th width="12.5%">Prix HT DC</th>
											<th width="15%">Quantité</th>
										</tr>
									<?php } ?>
								</thead>
								<tbody>
									<?php
										if(isset($_SESSION['site'])) {
											$panier = json_decode($u->panier, true);
										} else {
											if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
										}
										while($e = $elements->fetch(PDO::FETCH_OBJ)) {
											if(floor($e->stock/$e->uv_caisse) > 0 || $e->marque == 2) {
												$quantite = 0;
												foreach($panier as $p) {
													foreach($p as $p2 => $qte) {
														if($p2 == $e->id) {$quantite = $qte;}
													}
												}
									?>
										<tr>
											<td class="nom"><?php if($e->marque == 2) { ?><span style="color: red;text-transform: uppercase;">Précommande</span><br class='no-margin'/><?php } ?>
												<?php if($u->admin == 1) { ?>
													<div class="btn-content">
														<input type="text" data-id="<?php echo $e->id; ?>" data-type="nom" class="libelle" value="<?php echo $e->nom; ?>"/>
														<input type="text" data-id="<?php echo $e->id; ?>" data-type="sup" class="libelle" value="<?php echo $e->nom_sup; ?>"/>
													</div>
												<?php } else { ?>
													<?php echo $e->nom."<br class='no-margin'/>".$e->nom_sup; ?>
												<?php } ?>
											</td>
											<td data-label="Contenance"><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
											<td data-label="% Alcool"><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
											<td data-label="Prix UV HT HD"><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
											<td data-label="Droits accise UV"><?php echo number_format($e->droits, 2, ',', ' '); ?>€</td>
											<td data-label="Prix UV HT DC"><?php echo number_format($e->prix_ht+$e->droits, 2, ',', ' '); ?>€</td>
											<?php if($consigne) { ?><td data-label="Consigne"><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
											<td data-label="Quantité"><input type="number" value="<?php echo $quantite; ?>" <?php if($e->marque == 1) { ?>max="<?php echo floor($e->stock/$e->uv_caisse); ?>"<?php } ?> class="ajouter-panier" data-id="<?php echo $e->id; ?>" min="0"></td>
										</tr>
									<?php } } ?>
								</tbody>
							</table>
						<?php } ?>
					<?php } elseif($select_categorie) { ?>
						<?php
							$categorie = htmlentities($_GET['categorie']);
							switch($_GET['trier_prix']) {
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
							<table class="boutique-table" style="width: 100%;margin-top: 10px;">
								<thead>
									<?php if($consigne) { ?>
										<tr>
											<th width="25%">Produit</th>
											<th width="15%">Contenance</th>
											<th width="8%">% Alcool</th>
											<th width="10.5%">Prix HT HD</th>
											<th width="10%">Droits accise</th>
											<th width="10.5%">Prix HT DC</th>
											<th width="10%">Consigne</th>
											<th width="11%">Quantité</th>
										</tr>
									<?php } else { ?>
										<tr>
											<th width="25%">Produit</th>
											<th width="15%">Contenance</th>
											<th width="10%">% Alcool</th>
											<th width="12.5%">Prix HT HD</th>
											<th width="10%">Droits accise</th>
											<th width="12.5%">Prix HT DC</th>
											<th width="15%">Quantité</th>
										</tr>
									<?php } ?>
								</thead>
								<tbody>
									<?php
										if(isset($_SESSION['site'])) {
											$panier = json_decode($u->panier, true);
										} else {
											if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
										}
										while($e = $elements->fetch(PDO::FETCH_OBJ)) {
											if(floor($e->stock/$e->uv_caisse) > 0 || $e->marque == 2) {
												$quantite = 0;
												foreach($panier as $p) {
													foreach($p as $p2 => $qte) {
														if($p2 == $e->id) {$quantite = $qte;}
													}
												}
									?>
										<tr>
											<td class="nom"><?php if($e->marque == 2) { ?><span style="color: red;text-transform: uppercase;">Précommande</span><br class='no-margin'/><?php } ?>
												<?php if($u->admin == 1) { ?>
													<div class="btn-content">
														<input type="text" data-id="<?php echo $e->id; ?>" data-type="nom" class="libelle" value="<?php echo $e->nom; ?>"/>
														<input type="text" data-id="<?php echo $e->id; ?>" data-type="sup" class="libelle" value="<?php echo $e->nom_sup; ?>"/>
													</div>
												<?php } else { ?>
													<?php echo $e->nom."<br class='no-margin'/>".$e->nom_sup; ?>
												<?php } ?>
											</td>
											<td data-label="Contenance"><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
											<td data-label="% Alcool"><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
											<td data-label="Prix UV HT HD"><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
											<td data-label="Droits accise UV"><?php echo number_format($e->droits, 2, ',', ' '); ?>€</td>
											<td data-label="Prix UV HT DC"><?php echo number_format($e->prix_ht+$e->droits, 2, ',', ' '); ?>€</td>
											<?php if($consigne) { ?><td data-label="Consigne"><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
											<td data-label="Quantité"><input type="number" value="<?php echo $quantite; ?>" <?php if($e->marque == 1) { ?>max="<?php echo floor($e->stock/$e->uv_caisse); ?>"<?php } ?> class="ajouter-panier" data-id="<?php echo $e->id; ?>" min="0"></td>
										</tr>
									<?php } } ?>
								</tbody>
							</table>
						<?php } ?>
					<?php } else { ?>
						<!-- PANNEL BRASERIES -->
						<section id="brasseries-pannel">
							<?php
								if(isset($_GET['pays'])) {
									$brasseries = $bdd->prepare("SELECT * FROM ob_brasseries WHERE country = :pays AND hiden = '1' ORDER BY name");
									$brasseries->bindParam(":pays", $_GET['pays']);
									$brasseries->execute();
								} else {
									$brasseries = $bdd->query("SELECT * FROM ob_brasseries WHERE hiden = '1' ORDER BY name");
								}
								while($b = $brasseries->fetch(PDO::FETCH_OBJ)) {
							?>
								<div data-id="<?php echo $b->id; ?>" data-titre="<?php echo $b->name; ?>" class="brasseriesc-boxe">
									<a href="<?php echo $url."/".filterNom($b->name)."-".$b->id; ?>">
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
										<a href="<?php echo $url."/".filterNom($b->name)."-".$b->id; ?>"><button class="button-vide" type="button"><i class="icon-plus"></i> Découvrir la gamme</button></a>
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
		<script src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>
