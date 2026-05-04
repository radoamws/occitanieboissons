<?php 
	require("includes/configuration.php");
	require("includes/functions.php");
	/*UpdateStats("visiteur");*/

	if(isset($_SESSION['site'])) {
		header("Location: ".$url);
		exit();
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Connexion - Occitanie Boissons</title>
		<meta charset="utf-8">
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
		<div class="page contact">
			<!-- CONTAINER -->
			<div class="container">
				<!-- FLEX -->
				<div id="flex-contact">
					<!-- FORMULAIRE CONTACT -->
					<section id="formulaire-contact" class="abonnement">
						<div id="content-form">
							<div class="titre">Connexion</div>
							<h3 id="titre-bleu">Connexion à votre compte</h3>
							<p>Connectez-vous dès maintenant à votre compte pour récupérer votre panier ou passer commande à partir de notre espace professionnel !<br/>
								Si vous ne possédez pas encore de compte, nous vous invitons à en créer un en cliquant <a href="<?php echo $url; ?>/inscription/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">juste ici</a>.</p>
							<form class="formulaire" id="connexion" method="post">
								<div><label>Email</label><input name="email" type="email" /></div>
								<div><label>Mot de passe</label><input name="mdp" type="password" /></div>
								<label class="catalogue-checkbox">
								  <div>Je souhaite maintenir ma session active.</div>
								  <input type="checkbox" checked="checked" name="session">
								  <span class="checkmark"></span>
								</label>
								<!-- MOT DE PASSE OUBLIE -->
								<a class="forget-pwd" href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">Mot de passe oublié ?</a>
								<?php if(isset($_GET['redirect'])) { ?>
									<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect">
								<?php } ?>
								<button class="btn" type="submit">Connexion &nbsp; <i class="icon-user-circle"></i></button>
								<a href="<?php echo $url; ?>/inscription/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Inscription &nbsp; <i class="icon-fleche-droite"></i></button></a>
							</form>
						</div>
					</section>
					<!-- CARTE FRANCE -->
					<aside id="carte-france">
						<img src="<?php echo $gallery; ?>/images/carte-france.webp"/>
					</aside>
				</div>
				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>

		<!-- JAVASCRIPT -->
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery.mask.js"></script>
	</body>
</html>