<?php
	require("includes/configuration.php");
	require("includes/functions.php");

	if(isset($_SESSION['logistique'])) {
		header("Location: ".$url."/accueil/");
		exit();
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Connexion - <?php echo $sitename; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		
		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/select2.min.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body>
		<!-- MESSAGE -->
		<div id="message-content">
			<p></p>
			<button type="button" class="close" data-message="close">×</button>
		</div>
		
		<div class="boxe petite">
			<div class="content-boxe">
				<!-- HEADER -->
				<?php require("./includes/header.php"); ?>

				<p style="margin-top: 20px;text-align: center;">LogistiqueOB</p>
		
				<form id="connexion">
					<div class="input-container">		
						<input type="mail" placeholder="Adresse email" name="email" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="oubli-mdp">
						<div class="input-container">		
							<input type="password" placeholder="Mot de passe" name="mdp" class="input"/>
							<span class="focus-input"></span>
						</div>
						<!-- MOT DE PASSE OUBLIE -->
						<a href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">Mot de passe oublié ?</a>
					</div>
					<?php if(isset($_GET['redirect'])) { ?>
						<input name="redirect" type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>"/>
					<?php } ?>
					<div class="content-btn">
						<button class="btn" type="submit">Connexion</button>
						<!--<a href="<?php echo $url; ?>/inscription/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">M'inscrire à LogistiqueOB</button></a>-->
					</div>
				</form>
			</div>
		</div>
		<div id="dropDownSelect1"></div>

		<!-- FOOTER -->
		<?php require("./includes/footer.php"); ?>
		
		<!-- JAVASCRIPT -->
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery-3.2.1.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
	</body>
</html>