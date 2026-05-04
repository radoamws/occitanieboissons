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
		<title>Inscription - Occitanie Boissons</title>
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
							<div class="titre">Inscription</div>
							<h3 id="titre-bleu">Création d'un nouveau compte</h3>
							<p>Inscrivez-vous sur notre espace professionnel Occitanie Boissons pour pouvoir passer commande sur le catalogue et être livré le plus rapidement possible. L'inscription est réservée exclusivement aux professionnels.<br/>
								Si vous possédez déjà un compte, connectez-vous en suivant <a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">ce lien</a>.</p>
							<form class="formulaire" id="inscription" method="post">
								<div><label>Nom</label><input name="nom" type="text" /></div>
								<div><label>Prénom</label><input name="prenom" type="text" /></div>
								<div><label>Email</label><input name="email" type="email" /></div>
								<div><label>Mot de passe</label><input name="mdp" type="password" /></div>
								<div><label>Confirmez-le</label><input name="remdp" type="password" /></div>
								<div><label>Téléphone</label><input data-mask="phone-int" name="phone" type="text" /></div>
								<div><label>Entreprise</label><input name="entreprise" type="text" /></div>
								<div><label>N° SIRET</label><input name="siret" type="text" /></div>
								<label class="catalogue-checkbox">
								  <div id="texte">Je souhaite recevoir le catalogue, environ une fois par semaine, dès que des nouvelles bières ou brasseries sont ajoutées</div>
								  <input type="checkbox" checked="checked" name="newsletter"/>
								  <span class="checkmark"></span>
								</label>
								<?php if(isset($_GET['redirect'])) { ?>
									<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect"/>
								<?php } ?>
								<button class="btn" type="submit">Inscription &nbsp; <i class="icon-avion"></i></button>
								<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Connexion &nbsp; <i class="icon-user-circle"></i></button></a>
							</form>
						</div>
					</section>
					<!-- ADS ADB -->
					<a href="https://www.facebook.com/Autour-Dune-Bi%C3%A8re-1685043165046863/" target="meta" id="ads-adb">
						<div id="content-adb">
							<div id="main-invert"></div>
							<p>Jetez donc un oeil sur la page de nos deux bars toulousains, les</p><h3>Autour D'une Bière</h3><p> Aucamville & Avenue de Muret</p>
						</div>
					</a>
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
		<script type="text/javascript">
			$(function() {
				$("[data-mask='phone-int']").mask("+33 9999999999");
			});
		</script>
	</body>
</html>