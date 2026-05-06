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
		<title>Informations personnelles - <?php echo $sitename; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		
		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">

		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
	</head>
	<body>
		<!-- MESSAGE -->
		<div id="message-content">
			<p></p>
			<button type="button" class="close" data-message="close">×</button>
		</div>

		<div class="boxe">
			<div class="content-boxe">
				<!-- HEADER -->
				<?php require("./includes/header.php"); ?>

				<h3 class="titre-bleu">Modification des informations personnelles</h3><br/>
				<h4 class="titre">Adresse email</h4><br/>
				<h5>Adresse email actuelle : <?php echo $u->email; ?></h5><br/>
				<form class="informations-personnelles" data-action="identifiants_mail">
					<div class="input-container">		
						<input type="email" placeholder="Adresse email" name="email" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">		
						<input type="email" placeholder="Confirme-la" name="reemail" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="content-btn">
						<button class="btn" type="submit">Modifier</button>
					</div>
				</form><br/>
				<h4 class="titre">Mot de passe</h4><br/>
				<form class="informations-personnelles" data-action="identifiants_pwd">
					<div class="input-container">		
						<input type="password" placeholder="Mot de passe actuel" name="actualpwd" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">		
						<input type="password" placeholder="Nouveau mot de passe" name="pwd" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">		
						<input type="password" placeholder="Confirme-le" name="repwd" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="content-btn">
						<button class="btn" type="submit">Modifier</button>
					</div>
				</form>
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