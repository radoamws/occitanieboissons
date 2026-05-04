<?php 
	require("includes/configuration.php");
	require("includes/functions.php");
	/*UpdateStats("visiteur");*/
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Conditions Générales de Vente - Occitanie Boissons</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">
		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/print.css" type="text/css" media="print">

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
		<div class="page mentions-legales">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<div class="titre">Conditions Générales de Vente</div><br/>
				<h3>Clause n° 1 : Objet</h3>
				<p>Les conditions générales de vente décrites ci-après détaillent les droits et obligations de la société "La Cave d'Occitanie" et de son client dans le cadre de la vente des marchandises
				suivantes : bières, boissons sans alcools, vins et spiritueux.<br/>
				Toute prestation accomplie par la société "La Cave d'Occitanie" implique donc l'adhésion sans réserve de l'acheteur aux présentes conditions générales de vente.
				<h3>Clause n° 2 : Prix</h3>
				<p>Les prix des marchandises vendues sont ceux en vigueur au jour de la prise de commande. Ils sont libellés en euros et calculés automtiquement avec TVA comprise. Par voie de conséquence, ils seront majorés du des frais de transport applicables au jour de la commande.<br/>
				La société "La Cave d'Occitanie" s'accorde le droit de modifier ses tarifs à tout moment.<br/>
				Toutefois, elle s'engage à facturer les marchandises commandées aux prix indiqués lors de l'enregistrement de la commande.<br/>
				<h3>Clause n° 3 : Rabais et ristournes</h3>
				<p>Les tarifs proposés comprennent les rabais et ristournes que la société "La Cave d'Occitanie" serait amenée à octroyer compte tenu de ses résultats ou de la prise en charge par l'acheteur de certaines prestations.
				<h3>Clause n° 4 : Escompte</h3>
				<p>Aucun escompte ne sera consenti en cas de paiement anticipé.</p>
				<h3>Clause n° 5 : Modalités de paiement</h3>
				<p>Le règlement des commandes s'effectue en totalité :<br/>
				• par carte bancaire ;<br/>		
				<h3>Clause n° 6 : Clause de réserve de propriété</h3>
				<p>La société "La Cave d'Occitanie" conserve la propriété des biens vendus jusqu'au paiement intégral du prix, en principal et en accessoires. À ce titre, si l'acheteur fait l'objet d'un redressement ou d'une liquidation judiciaire, la société "La Cave d'Occitanie" se réserve le droit de revendiquer, dans le cadre de la procédure collective, les marchandises vendues et restées impayées.
				<h3>Clause n° 7 : Livraison</h3>
				<p>La livraison est effectuée :<br/>
				• soit par la remise directe de la marchandise à l'acheteur ;<br/>
				• soit par l'envoi d'un avis de mise à disposition en magasin à l'attention de l'acheteur ;<br/>
				• soit au lieu indiqué par l'acheteur sur le bon de commande.<br/>
				Le délai de livraison indiqué lors de l'enregistrement de la commande n'est donné qu'à titre indicatif et n'est aucunement garanti.<br/>
				Par voie de conséquence, tout retard raisonnable dans la livraison des produits ne pourra pas donner lieu au profit de l'acheteur à :<br/>
				• l'allocation de dommages et intérêts ;<br/>
				• l'annulation de la commande.<br/>
				Le risque du transport est supporté en totalité par l'acheteur. En cas de marchandises manquantes ou détériorées lors du transport, l'acheteur devra formuler toutes les réserves nécessaires sur le bon de commande à réception des dites marchandises.<br/>
				Ces réserves devront être, en outre, confirmées par écrit dans les cinq jours suivant la livraison, par courrier recommandé AR.
				<h3>Clause n° 8 : Force majeure</h3>
				<p>La responsabilité de la société "La Cave d'Occitanie" ne pourra pas être mise en oeuvre si la non-exécution ou le retard dans l'exécution de l'une de ses obligations décrites dans les présentes conditions générales de vente découle d'un cas de force majeure. À ce titre, la force majeure s'entend de tout événement extérieur, imprévisible et irrésistible au sens de l'article 1148 du Code civil.
				<h3>Clause n° 9 : Tribunal compétent</h3>
				<p>Tout litige relatif à l'interprétation et à l'exécution des présentes conditions générales de vente est soumis au droit français.<br/>
				À défaut de résolution amiable, le litige sera porté devant le Tribunal de commerce de Toulouse.<br/>
				Fait à Pechbonnieu, le 12/04/2020
				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>

		<!-- AVERTISSEMENT -->
		<?php if(!isset($_SESSION['majeur'])) { require("./includes/avertissement.php"); } ?>
		<div id="backdrop" <?php if(isset($_SESSION['majeur'])) { ?>style="display: none;"<?php } ?>></div>

		<!-- JAVASCRIPT -->
		<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>



