<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	if(!isset($_SESSION['logistique'])) {
		header("Location: ".$url."/connexion/");
		exit();
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Accueil - <?php echo $sitename; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		
        <!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body>
		<div class="boxe">
			<div class="content-boxe">
				<!-- HEADER -->
				<?php require("./includes/header.php"); ?>
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