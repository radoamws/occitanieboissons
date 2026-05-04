<?php 
	require("includes/configuration.php");
	require("includes/functions.php");
	$menu = "boutique";
	/*UpdateStats("visiteur");*/
	$c_national = $bdd->query("SELECT lien FROM ob_catalogue WHERE type = 'national'")->fetch(PDO::FETCH_OBJ);

	$pagename = "Boutique";
	if(isset($_GET['id']) && intval($_GET['id']) && isset($_GET['nom'])) { 
		$produit = $bdd->prepare("SELECT * FROM ob_boutique_produit WHERE id = :id LIMIT 1");
		$produit->bindParam(':id', $_GET['id']);
		$produit->execute();
		if($produit->rowCount() < 1) {
			header("Location: ".$url);
			exit();
		} else {
			$produit_select = true;
		    $p = $produit->fetch(PDO::FETCH_OBJ);
		    $pagename = $p->nom;
		}
    }
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $pagename; ?> - Occitanie Boissons</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<meta name="description" content="La Cave d’Occitanie propose la livraison à domicile de boissons sans alcool, bières, vins et spiritueux aux particuliers disponibles sur sa boutique en ligne. Nombreuses bières artisanales locales et étrangères crafts..."/>
        <meta name="keywords" content="cave bière pechbonnieu, cave vin pechbonnieu, location tireuse pechbonnieu, perfect draft pechbonnieu, philips HD3620 pechbonnieu, occitanie boissons, cave bière toulouse, cave vin toulouse, location tireuse toulouse, perfect draft toulouse, philips HD3620 toulouse, boutique, la cave d'occitanie, cave occitanie, achat"/>

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css?<?php echo time(); ?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">

		<!--[if lt IE 9]>
			<script src="<?php echo $gallery; ?>/js/html5shiv.js"></script>
			<script src="<?php echo $gallery; ?>/js/html5-ie.js"></script>
		<![endif]-->

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
		<div class="page boutique">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<div class="button">
					<a href="<?php echo $c_national->lien; ?>" download=""><button class="btn print-active" type="button"><i class="icon-download"></i> Télécharger le catalogue</button></a>
					<a href="<?php echo $url; ?>/la-cave-occitanie/panier/"><button class="btn print-active" type="button"><i class="icon-cart"></i> Panier - TOTAL <span id="panier-prix"><?php echo PrixPanier(); ?></span>€</button></a>
				</div>
				<div class="boutique-header">
					<div class="informations">
						<p>
							Devenu un acteur de la boisson sur la région, <strong>Occitanie Boissons</strong> n’en reste pas moins une entreprise proche de ses clients.<br/> En plus de nos activités réservées aux professionnels, nous possédons un point de vente directe à Pechbonnieu, <strong>« La Cave d'Occitanie »</strong> créée dès 2001 dans le prolongement de l'activité de distribution, ainsi que deux caves à bière <strong>« Autour d’une Bière »</strong> avec espace dégustation à Toulouse (Avenue de Muret et Impasse Honoré Daumier) afin que les particuliers puissent acheter ou déguster nos différents produits.</p>
						</p>
					</div>
				</div>
				<div class="boutique-header">
					<div class="informations">
						<p>
							Face à la pandémie, <strong>« La Cave d'Occitanie »</strong> et les deux <strong>« Autour d’une Bière »</strong> s'adaptent et proposent de venir à la rencontre de leurs clients en mettant en place un service de livraison à domicile.<br/>
							<span style="color: red;">ATTENTION !</span> Les livraisons de vos commandes seront assurées à partir d'un montant de <span style="color: red;">30€</span> et dans un rayon de <span style="color: red;">20km</span> de notre entreprise <a href="<?php echo $gallery; ?>/images/zone-de-livraison-20km-occitanieboissons.png" target="_blank">(voir zone de livraison)</a>.<br/>
							<p>Si vous êtes <span style="color: red;">hors de la zone couverte</span> ou que vous souhaitez profiter de <span style="color: red;">produits plus spécifiques</span>, un catalogue est à votre disposition ci-dessus. Transmettez-nous votre commande par email à <A HREF="mailto:commande@occitanieboissons.com">commande@occitanieboissons.com</a>, par la rubrique "Contact" ou encore par <a href="https://www.facebook.com/Occitanie-Boissons-1619195328335124/">Facebook</a>. Nous vous recontacterons dans les plus brefs délais pour régler les modalités de paiement et de livraison.
						</p>
					</div>
				</div>
				<?php if(CommandeEnCours()) { ?>
					<div class="boutique-header">
						<div class="informations">
							<div class="titre">Commandes en cours</div>
							<p class="panier-informations"><span style='color: red;'>ATTENTION !</span> Vous avez actuellement des commandes en cours <span style='color: red;'>non réglées</span>.<br/>Les produits des commandes en cours sont <span style='color: red;'>déduits du stock de la boutique</span>. Veuillez supprimer vos commandes abandonnées <a href="<?php echo $url; ?>/la-cave-occitanie/commande-visualisation/">en cliquant ici</a>.</p>
							<a href="<?php echo $url; ?>/la-cave-occitanie/commande-visualisation/"><button type="button" class="btn"><i class="icon-loupe"></i> Visualiser mes commandes</button></a>
						</div>
					</div>
				<?php } ?>
				<?php if(!@$produit_select) { ?>
					<!-- PAGE PRINCIPALE -->
					<?php
						$catagorie_b = $bdd->query("SELECT * FROM ob_boutique_categorie ORDER by rang");
						while($c = $catagorie_b->fetch(PDO::FETCH_OBJ)) {
					?>
						<div class="titre"><?php echo $c->nom; ?></div>
						<section class="boutique-pannel">
							<?php
								$produit = $bdd->query("SELECT * FROM ob_boutique_produit WHERE categorie = '".$c->id."' AND hide = '0'");
								while($p = $produit->fetch(PDO::FETCH_OBJ)) {
									$elements = explode(",", $p->element);
									$e = $bdd->query("SELECT * FROM ob_boutique_produit_element WHERE id = '".$elements[0]."'")->fetch(PDO::FETCH_OBJ);
									if(count($elements) == 1 && $e->stock > 0) {
							?>
								<div class="boutique-boxe">
									<div class="informations">
										<h4><?php echo $p->nom; ?></h4>
									</div>						
									<div class="image">
										<img src="<?php echo $p->image; ?>">
									</div>
									<div class="actions">
										<p><?php echo $e->description; ?></p>
								  		<strong>Prix : <?php echo number_format(($e->prixht)*(1+($e->tva)/100), 2, ',', ' '); ?>€</strong>
								  		<form class="add-panier">
											<input type="number" name="quantite" min="1" max="<?php echo $e->stock ?>" placeholder="Quantité">
											<input type="hidden" name="id" value="<?php echo $e->id ?>">
											<input type="hidden" name="idproduit" value="<?php echo $p->id ?>">
											<button class="btn" type="submit">» Ajouter au panier</button>
										</form>
									</div>
								</div>
							<?php } else { ?>
								<div class="boutique-boxe">
									<div class="informations">
										<h4><?php echo $p->nom; ?></h4>
									</div>						
									<div class="image">
										<img class="information-boutique" data-id="<?php echo $p->id; ?>" data-nom="<?php echo $p->nom; ?>" src="<?php echo $p->image; ?>">
									</div>
									<div class="actions">
										<p><?php echo @$p->description; ?></p>
								  		<form><button class="btn information-boutique" type="button" data-id="<?php echo $p->id; ?>" data-nom="<?php echo $p->nom; ?>"><i class="icon-plus"></i> Voir les produits</button></form>
									</div>
								</div>
							<?php } } ?>
						</section>	
					<?php } ?>
				<?php } else { ?>
					<!-- PAGE INFORMATIONS PRODUIT -->
					<article>
						<div id="image">
							<img src="<?php echo $p->image; ?>"/>
						</div>
						<div id="text">
							<h3><?php echo $p->nom; ?></h3>
							<p><?php echo $p->description; ?></p>
							<table class="boutique-table">
								<thead>
									<tr>
										<th width="30%">Nom</th>
										<th width="10%">Contenance</th>
										<th width="10%">Alcool</th>
										<th width="10%">Prix TTC</th>
										<th width="10%">Quantité</th>
										<th width="10%">#</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$elements = explode(",", $p->element);
										foreach($elements as $ide) {
											$e = $bdd->query("SELECT * FROM ob_boutique_produit_element WHERE id = '".$ide."'")->fetch(PDO::FETCH_OBJ);
											if($e->stock > 0) {	
								  	?>
										<tr>
											<td class="nom"><?php echo $e->nom; ?></td>
											<td><?php echo $e->contenance; ?></td>
											<td><?php echo number_format($e->alcool, 1, ',', ' '); ?>°</td>
											<td><?php echo number_format(($e->prixht)*(1+($e->tva)/100), 2, ',', ' '); ?>€</td>
											<td><input type="number" value="0" min="1" max="<?php echo $e->stock ?>" id="quantite-<?php echo $e->id; ?>" min="0"></td>
											<td><button class="btn add-panier" type="submit" data-id="<?php echo $e->id; ?>" data-idproduit="<?php echo $p->id; ?>"><i class="icon-cart"></i></button></td>
										</tr>
									<?php } } ?>
								</tbody>
							</table>
						</div>
					</article>
				<?php } ?>


				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>

		<!-- AVERTISSEMENT -->
		<?php if(!isset($_SESSION['majeur'])) { require("./includes/avertissement.php"); } ?>
		<div id="backdrop" <?php if(isset($_SESSION['majeur'])) { ?>style="display: none;"<?php } ?>></div>
		
		<!-- AJOUTER PANIER -->
		<div id="boutique-panier" style="display: none;">
			<div id="content-boutique-panier">
				<div class="padding enquete">
					<div class="titre">Produit ajouté au panier avec succès !</div>
					<article>
						<div id="image-boutique"><img src="" id="image"/></div>
						<div id="text">
							<div class="titre"><span id="nom"></span></div>
							<div class="infos"><span>Prix (TTC)</span><span class="right"><span id="prix"></span>€</span></div>
							<div class="infos"><span>Alcool</span> <span class="right"><span id="alcool"></span>°</div>
							<div class="infos"><span>Contenance</span> <span class="right"><span id="contenance"></span></div>
							<div class="infos"><span>Quantite</span> <span class="right"><span id="quantite"></span></div>
						</div>
					</article>
					<div id="content-btn">
						<button type="button" class="btn close">Continuer les achats</button>
						<a href="<?php echo $url; ?>/la-cave-occitanie/panier/"><button type="button" class="btn supprimer">Commander</button></a>
					</div>
				</div>
			</div>
		</div>

		<!-- JAVASCRIPT -->
		<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>