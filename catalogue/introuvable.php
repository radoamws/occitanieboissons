<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	switch(@$_GET['erreur']) {
		case 403:
			$erreur = "403";
			$texte = "Interdit ! <br/>Vous ne pouvez pas accéder à ce répertoire ou à ce fichier.";
		break;
		case 404:
			$erreur = "404";
			$texte = "La page que vous recherchez est introuvable.<br/> Elle a peut-être été déplacé ou supprimé.";
		break;
		default:
			$erreur = "404";
			$texte = "La page que vous recherchez est introuvable.<br/> Elle a peut-être été déplacé ou supprimé.";
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $erreur; ?> - Occitanie Boissons</title>
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
		<div class="page introuvable">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<h2>Erreur <?php echo $erreur; ?></h2>
				<p><?php echo $texte; ?></p>
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