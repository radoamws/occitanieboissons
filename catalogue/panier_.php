<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	$menu = "boutique";

	if(!isset($_SESSION['site'])) {
		header("Location: ".$url."/connexion/redirection/panier/");
		exit();
	} elseif($u->catalogue == 0) {
		header("Location: ".$url);
		exit();
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Panier - Occitanie Boissons</title>
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
				<div class="button">
					<a href="<?php echo $url; ?>"><button class="btn" type="button"><i class="icon-fleche-gauche"></i> Continuer mes achats</button></a>
					<a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2021.pdf" download><button class="btn" type="button"><i class="icon-pdf"></i> Conditions de livraison</button></a>
				</div>
				<!-- FLEX -->
				<div id="flex-panier">
					<!-- FORMULAIRE CONTACT -->
					<section id="panier-contenu">
						<div id="content-form">
							<div class="titre">Panier (<span class="panier-articles"><?php echo ArticlePanier(); ?></span>)</div>
							<?php if(isset($_GET['erreur'])) { ?>
								<?php if($_GET['erreur'] == "stock") { ?>
									<p style="color: red;">Certains produits demandés ne sont plus disponibles. Votre panier a été modifié avec les produits toujours disponibles.</p>
								<?php } ?>
							<?php } ?>
							<?php
								if(isset($_SESSION['site'])) {
									$panier = json_decode($u->panier, true);
								} else {
									if(isset($_COOKIE['panier'])) {$panier = json_decode($_COOKIE['panier'], true);} else {$panier = array();}
								}

								/* CHECK CONSIGNE */
								$consigne = FALSE;
								foreach($panier as $p) {
									foreach($p as $p2 => $qte) {
										$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
										$element->bindParam(":id", $p2);
										$element->execute();
										$e = $element->fetch(PDO::FETCH_OBJ);
										if($e->consigne_caisse != 0) {$consigne = TRUE;}
									}
								}

								if(empty($panier)) {
									echo "<center><p>Le panier est actuellement vide, choissez un article dans le <a href='".$url."'>catalogue.</a></p></center>";
								} else {
							?>
								<table class="boutique-table" style="width: 100%;margin-top: 10px;">
									<thead>
										<tr>
											<?php if($consigne) { ?>
												<th width="30%">Produit</th>
												<th width="12.5%">Contenance</th>
												<th width="12.5%">% Alcool</th>
												<th width="12.5%">Prix HT HD</th>
												<th width="12.5%">Consigne</th>
												<th width="10%">Quantité</th>
												<th width="10%">#</th>
											<?php } else { ?>
												<th width="35%">Produit</th>
												<th width="15%">Contenance</th>
												<th width="15%">% Alcool</th>
												<th width="15%">Prix HT HD</th>
												<th width="10%">Quantité</th>
												<th width="10%">#</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php
											foreach($panier as $p) {
												foreach($p as $p2 => $qte) {
													$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
													$element->bindParam(":id", $p2);
													$element->execute();
													$e = $element->fetch(PDO::FETCH_OBJ);
									  	?>
											<tr class="boutique-element table-produit-<?php echo $e->id; ?>">
												<td class="nom"><?php if($e->marque == 2) { ?><span style="color: red;text-transform: uppercase;">Précommande</span><br class='no-margin'/><?php } ?><?php echo $e->nom."<br class='no-margin'/>".$e->nom_sup; ?></td>
												<td data-label="Contenance"><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
												<td data-label="% Alcool"><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
												<td data-label="Prix HT HD"><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
												<?php if($consigne) { ?><td data-label="Consigne"><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
												<td data-label="Quantité"><input type="number" value="<?php echo $qte; ?>" <?php if($e->marque == 1) { ?>max="<?php echo floor($e->stock/$e->uv_caisse); ?>"<?php } ?> class="ajouter-panier" data-id="<?php echo $e->id; ?>" min="1"></td>
												<td class="actions"><button class="btn remove-panier" type="button" data-id="<?php echo $e->id; ?>"><i class="icon-poubelle"></i></button></td>
											</tr>
										<?php } } ?>
									</tbody>
								</table>
							<?php } ?>
							<center style="display: none;" id="panier-vide-msg"><p>Le panier est actuellement vide, choisissez un article dans le <a href='<?php echo $url; ?>'>catalogue.</a></p></center>
						</div>
					</section>
					<section id="panier-total">
						<div id="content-form">
							<div class="infos">Article<?php if(ArticlePanier() > 1) { ?>s<?php } ?> au total <span class="right"><span class="panier-articles"><?php echo ArticlePanier(); ?></span></span></div>
							<div class="infos">Prix HT HD <span class="right"><span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€</span></div>
							<div class="infos">Prix HT DC <span class="right"><span class="panier-prix-droits"><?php echo PrixPanier("droits"); ?></span>€</span></div>
							<div class="infos">Prix TTC <span class="right"><span class="panier-prix"><?php echo PrixPanier("ttc"); ?></span>€</span></div>
							<hr/>
							<div class="infos">Livraison <span class="right"><a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2021.pdf" download>Voir les conditions</a></span></div>
							<div class="infos">Consigne <span class="right"><span class="consigne-prix"><?php echo Consigne(); ?></span>€</span></div>
							<hr/>
							<?php
								switch($u->paiement_default) {
									case 0:
										$nom = "Virement comptant";
									break;
									case 1:
										$nom = "Traite 30 jours";
									break;
									case 2:
										$nom = "Virement 30 jours";
									break;
								}
							?>
							<div class="infos">Méthode de paiement <span class="right"><?php echo $nom; ?></span></div>
							<?php if(PrixPanier() <= 0) { ?><p class="panier-informations" style='color: red;'>Vous ne pouvez pas passer commande avec un prix négatif ou nul, veuillez compléter votre commande puis actualiser la page.</p><?php } ?>
							<?php if(ArticlePanier() > 0 && PrixPanier() > 0) { ?>
								<div class="commander"><a href="<?php echo $url; ?>/commande/"><button type="button" class="btn"><i class="icon-cart"></i> Commander</button></a></div>
							<?php } ?>
						</div>
					</section>
				</div>
				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>
		
		<!-- JAVASCRIPT -->
		<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery.mask.js"></script>
		<script type="text/javascript">
			$("[data-mask='phone-int']").mask("+33 999 999 999");
		</script>	
	</body>
</html>