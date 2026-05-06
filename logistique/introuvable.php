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
			$texte = "La page que vous recherchez est introuvable.<br/> Elle a peut-être été déplacée ou supprimée.";
		break;
		default:
			$erreur = "404";
			$texte = "La page que vous recherchez est introuvable.<br/> Elle a peut-être été déplacée ou supprimée.";
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title><?php echo $erreur; ?> - <?php echo $sitename; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">
		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body>
		<div class="boxe petite">
			<div class="content-boxe">
				<!-- HEADER -->
				<?php require("./includes/header.php"); ?>

				<h2>Erreur <?php echo $erreur; ?></h2>
				<p><?php echo $texte; ?></p>
			</div>
		</div>

		<!-- FOOTER -->
		<?php require("./includes/footer.php"); ?>

		<!-- JAVASCRIPT -->
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery-3.2.1.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>