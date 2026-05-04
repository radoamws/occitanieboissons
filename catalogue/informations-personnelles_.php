<?php 
	require("includes/configuration.php");
	require("includes/functions.php");
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Informations personnelles - Occitanie Boissons</title>
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
		<?php require("./includes/catalogue-header.php"); ?>

		<!-- PAGE -->
		<div class="page contact">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<!-- FLEX -->
				<div id="flex-contact">
					<!-- FORMULAIRE CONTACT -->
					<section id="formulaire-contact">
						<div id="content-form">
							<div class="titre">Modification des informations personnelles</div>
							<p style="margin-top: 20px;">Maintenez vos informations personnelles à jour afin que nous poussiez vous contacter plus facilement.</p>
							<form class="formulaire updateinformations" data-action="informations">
								<div><label>Nom</label><input name="nom" type="text" value="<?php echo $u->nom; ?>" /></div>
								<div><label>Prénom</label><input name="prenom" type="text" value="<?php echo $u->prenom; ?>" /></div>
								<div><label>Téléphone</label><input data-mask="phone-int" name="phone" type="text" value="<?php echo $u->phone; ?>"/></div>
								<div><label>Entreprise (optionnel)</label><input name="entreprise" type="text" value="<?php echo $u->entreprise; ?>" /></div>
								<button class="btn" type="submit">Modifier &nbsp; <i class="icon-user-circle"></i></button>
							</form>
						</div>
					</section>

					<section id="formulaire-contact">
						<div id="content-form">
							<div class="titre">Modification des identifiants</div>
							<h3 id="titre-bleu" style="margin-top: 20px;">Adresse email</h3>
							<h5>Adresse email actuelle : <?php echo $u->email; ?></h5>
							<form class="formulaire updateinformations" data-action="identifiants_mail">
								<div><label>Adresse email</label><input name="email" type="email"/></div>
								<div><label>Confirmez-la</label><input name="reemail" type="email"/></div>
								<button class="btn" type="submit">Modifier &nbsp; <i class="icon-envelope"></i></button>
							</form>
							<h3 id="titre-bleu" style="margin-top: 20px;">Mot de passe</h3>
							<form class="formulaire updateinformations" data-action="identifiants_pwd">
								<div><label>Mot de passe actuel</label><input name="actualpwd" type="password"/></div>
								<div><label>Nouveau mot de passe</label><input name="pwd" type="password"/></div>
								<div><label>Confirmez-le</label><input name="repwd" type="password"/></div>
								<button class="btn" type="submit">Modifier &nbsp; <i class="icon-pwd"></i></button>
							</form>
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
			$(function() {
				$("[data-mask='phone-int']").mask("+33 9999999999").val("<?php echo $u->phone; ?>");
			});
		</script>	
	</body>
</html>