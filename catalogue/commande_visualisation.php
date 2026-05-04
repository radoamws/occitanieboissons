<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	if(isset($_SESSION['site'])) {
		if(isset($_GET['type'])) {
			if(isset($_GET['id'])) {
				$verifCommande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id AND userid = '".$u->id."'");
				$verifCommande->bindParam(":id", htmlentities($_GET['id']));
				$verifCommande->execute();
				if($verifCommande->rowCount() > 0) {
					if($_GET['type'] == "visualisation") {
						$c = $verifCommande->fetch(PDO::FETCH_OBJ);
						$pagename = "Commande n°".$c->id;
						$visualisation = TRUE;
					} elseif($_GET['type'] == "supprimer") {
						$c = $verifCommande->fetch(PDO::FETCH_OBJ);
						if($c->paiement == 0 || $c->paiement == 2) {
							// ON CACHE LA COMMANDE
							$hideCommande = $bdd->prepare("UPDATE ob_users_commande SET hide = '1' WHERE id = :id");
							$hideCommande->bindParam(':id', $c->id);
							$hideCommande->execute();
							$pagename = "Commandes";
						} else {
							header("Location: ".$url."/commande-visualisation/");
							exit();
						}
					}
				} else {
					if($_GET['type'] == "visualisation") {
						header("Location: ".$url."/commande-visualisation/");
						exit();
					} else {
						header("Location: ".$url."/commande-visualisation/");
						exit();
					}
				}
			} else {
				if($_GET['type'] == "visualisation") {
					header("Location: ".$url."/commande-visualisation/");
					exit();
				} else {
					header("Location: ".$url."/commande-visualisation/");
					exit();
				}
			}
		} else {
			$pagename = "Commandes";
		}
	} else {
		header("Location: ".$url."/connexion/redirection/commande-visualisation/");
		exit();
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

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-111970466-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-111970466-1');
		</script>
	</head>
	<body>
		<!-- HEADER -->
		<?php require("./includes/header.php"); ?>

		<!-- PAGE -->
		<div class="page panier">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<?php if(@$visualisation) { ?>
					<div class="button">
						<a href="<?php echo $url; ?>/commande-visualisation/"><button class="btn" type="button"><i class="icon-fleche-gauche"></i> Retour</button></a>
						<?php if($c->paiement == 1) { ?>
							<a href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" download>
								<button class="btn print-active" type="button"><i class="icon-pdf"></i> Télécharger une facture</button>
							</a>
						<?php } ?>
					</div>
					<div class="content">
						<h3>Commande n°<?php echo $c->id; ?> - <?php echo mPaiement($u->paiement_default); ?></h3><br/>
						<h4>Paiement : <?php if($c->paiement == 0){echo "<span style='color:red;'>Non réglée</span>";}elseif($c->paiement == 1){echo "<span style='color:green;'>Payée</span>";}elseif($c->paiement == 2){echo"<span style='color:red;'>Paiement refusé</span>";}?></h4><br/>
						<p class="book">
							Prix HT HD : <?php echo number_format($c->prixht, 2, ',', ' '); ?>€<br/>
							Prix HT DC : <?php echo number_format($c->prixdroits, 2, ',', ' '); ?>€<br/>
							Livraison HT : <?php echo number_format($c->livraison, 2, ',', ' '); ?>€<br/>
							Consigne : <?php echo number_format($c->consigne, 2, ',', ' '); ?>€<br/>
							Total TTC : <strong><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</strong><br/>
						</p>
						<h4>Date - <?php echo date('d/m/Y', $c->time); ?></h4><br/>
						<h4>Statut -
						<?php
							if($c->paiement == 1) {
								switch($c->statut) { 
									case 0:
										echo "Non traitée";
									break;
									case 1:
										echo "Expédiée";
									break;
									case 2:
										echo "Reçue";
									break;
								} 
							} else {
								echo "En attente de paiement";
							}
						?>
						</h4><br/>
						<h4>Informations personnelles</h4>
						<div class="address-selector">
							<div class="address-content">
									<article class="livraison address-item">
							    	<div class="top">
							        	<label class="radio-block">
							          		<h4 class="address-alias"><?php echo $c->prenom." ".$c->nom; ?></h4>
							          		<div class="address"><?php echo $c->email; ?><br/><?php echo $c->phone; ?><br/><?php echo $c->entreprise; ?></div>
							        	</label>
							      	</div>
								</article>
							</div>
						</div>
						<h4>Adresses</h4>
						<div class="address-selector">
							<div class="address-content">
									<article class="livraison address-item">
							    	<div class="top">
							        	<label class="radio-block">
							          		<h4 class="address-alias">Votre adresse de livraison</h4>
							          		<div class="address"><?php echo $c->prenom_l; ?> <?php echo mb_strtoupper($c->nom,'UTF-8'); ?><br><?php echo $c->adresse_l; ?><br><?php echo $c->adressec_l; ?><br><?php echo $c->codepostal_l; ?> <?php echo mb_strtoupper($c->ville_l, 'UTF-8'); ?><br><?php echo $c->pays_l; ?><br><?php echo $c->phone_l; ?></div>
							        	</label>
							      	</div>
								</article>
							</div>
							<div class="address-content">
									<article class="livraison address-item <?php if($a->id == $u->adresse_livraison) {echo "selected";} ?>">
							    	<div class="top">
							        	<label class="radio-block">
							          		<h4 class="address-alias">Votre adresse de facturation</h4>
							          		<div class="address"><?php echo $c->prenom_f; ?> <?php echo mb_strtoupper($c->nom_f, 'UTF-8'); ?><br><?php echo $c->adresse_f; ?><br><?php echo $c->adressec_f; ?><br><?php echo $c->codepostal_f; ?> <?php echo mb_strtoupper($c->ville_f, 'UTF-8'); ?><br><?php echo $c->pays_f; ?><br><?php echo $c->phone_f; ?></div>
							        	</label>
							      	</div>
								</article>
							</div>
						</div>
						<h4>Produits (<?php echo $c->articles; ?>)</h4>
						<?php
							$element = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
							$element->bindParam(":commandeid", $c->id);
							$element->execute();
							/* CHECK CONSIGNE */
							$consigne = FALSE;
							while($e = $element->fetch(PDO::FETCH_OBJ)) {
								if($e->consigne_caisse != 0) {$consigne = TRUE;}
							}
						?>
						<table class="boutique-table" style="width: 100%;margin-top: 10px;">
							<thead>
								<tr>
									<?php if($consigne) { ?>
										<th width='25%'>Produit</th>
										<th width='12.5%'>Contenance</th>
										<th width='10%'>% Alcool</th>
										<th width='10%'>Prix HT HD</th>
										<th width='10%'>Droits accise</th>
										<th width='10%'>Prix HT DC</th>
										<th width='10%'>Consigne</th>
										<th width='12.5%'>Quantité</th>
									<?php } else { ?>
										<th width='25%'>Produit</th>
										<th width='15%'>Contenance</th>
										<th width='10%'>% Alcool</th>
										<th width='12.5%'>Prix HT HD</th>
										<th width='10%'>Droits accise</th>
										<th width='12.5%'>Prix HT DC</th>
										<th width='15%'>Quantité</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php
									$element = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
									$element->bindParam(":commandeid", $c->id);
									$element->execute();
									while($e = $element->fetch(PDO::FETCH_OBJ)) {
							  	?>
									<tr class="table-produit-<?php echo $e->id; ?>">
										<td class="nom"><?php echo $e->nom; ?><br style='margin: 0';/><?php echo $e->nom_sup; ?></td>
										<td data-label="Contenance"><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
										<td data-label="% Alcool"><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
										<td data-label="Prix HT HD"><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
										<td data-label="Droits accise"><?php echo number_format($e->droits, 2, ',', ' '); ?>€</td>
										<?php if($consigne) { ?><td data-label="Consigne"><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
										<td data-label="Quantité"><?php echo number_format($e->prix_ht+$e->droits, 2, ',', ' '); ?>€</td>
										<td><?php echo $e->qte; ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="content">
						<h2>Visualisation des commandes</h2>
						<?php
							$commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE userid = '".$u->id."' AND hide = '0' ORDER BY id DESC");
							if($commandes->rowCount() < 1) {
								echo "<center><p style='margin-top: 20px;'>Vous n'avez actuellement passé aucune commande, passez commande dans le <a href='".$url."'>catalogue.</a></p></center>";
							} else {
						?>
						<table class="boutique-table" style="margin: 20px 0;width: 100%;">
							<thead>
								<tr>
									<th width="15%">N° commande</th>
									<th width="20%">Date</th>
									<th width="15%" class="nbre-articles">Nombre d'articles</th>
									<th width="20%">Prix TTC</th>
									<th width="20%">Paiement</th>
									<th width="20%">Statut</th>
									<th width="10%">#</th>
								</tr>
							</thead>
							<tbody>
								<?php
									while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
							  	?>
									<tr class="table-produit-<?php echo $c->id; ?>">
										<td data-label="N° commande"><?php echo $c->id; ?></td>
										<td data-label="Date"><?php echo date('d/m/Y', $c->time); ?></td>
										<td data-label="Nombre d'articles" class="nbre-articles"><?php echo $c->articles; ?></td>
										<td data-label="Prix TTC"><strong><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</strong></td>
										<td data-label="Paiement">
											<?php if($c->paiement == 0){echo "<span style='color:red;'>Non réglée</span>";}elseif($c->paiement == 1){echo "<span style='color:green;'>Payée</span>";}elseif($c->paiement == 2){echo"<span style='color:red;'>Paiement refusé</span>";}?>
										</td>
										<td data-label="Statut"><?php
												if($c->paiement == 1) {
													switch($c->statut) { 
														case 0:
															echo "Non traitée";
														break;
														case 1:
															if($c->option_livraison == 0) {
																echo "Disponible";
															} elseif($c->option_livraison == 1) {
																echo "Expédiée";
															}
														break;
														case 2:
															echo "Reçue";
														break;
													} 
												} else {
													echo "En attente de paiement";
												}
											?>
										</td>
										<td class="actions">
											<a href="<?php echo $url; ?>/commande-visualisation/visualisation/<?php echo $c->id; ?>"><button class="btn" type="button"><i class="icon-loupe"></i></button></a>
											<?php if($c->paiement == 0 || $c->paiement == 2) { ?>
												<a href="<?php echo $url; ?>/commande-visualisation/supprimer/<?php echo $c->id; ?>"><button class="btn" type="button"><i class="icon-poubelle"></i></button></a>
											<?php } else { ?>
												<a href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" download><button class="btn" type="button"><i class="icon-pdf"></i></button></a>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						<?php } ?>
					</div>
				<?php } ?>
				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>

		<!-- JAVASCRIPT -->
		<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>