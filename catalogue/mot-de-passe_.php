 <?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	if(isset($_SESSION['site'])) {
		header("Location: ".$url);
		exit();
	}

	$stepModif = FALSE;
	$modifPwd = FALSE;
	if(isset($_GET['email'])) {
		$verifEmail = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
		$verifEmail->bindParam(":email", htmlentities($_GET['email']));
		$verifEmail->execute();
		if($verifEmail->rowCount() > 0) {
			if(isset($_GET['token'])) {
				// VERIFICATION DU TOKEN
				$verifToken = $bdd->prepare("SELECT * FROM ob_users_password WHERE email = :email AND token = :token");
				$verifToken->bindParam(":email", htmlentities($_GET['email']));
				$verifToken->bindParam(":token", htmlentities($_GET['token']));
				$verifToken->execute();
				if($verifToken->rowCount() > 0) {
					$modifPwd = TRUE;
					$stepModif = TRUE;
				} else {
					$verifUser = $bdd->prepare("SELECT * FROM ob_users_password WHERE email = :email");
					$verifUser->bindParam(":email", htmlentities($_GET['email']));
					$verifUser->execute();
					if($verifToken->rowCount() > 0) {
						$stepModif = TRUE;
					} else {
						if(isset($_GET['redirection'])) {
							header("Location: ".$url."/mot-de-passe/redirection/".htmlentities($_GET['redirection']));
							exit();
						} else {
							header("Location: ".$url."/mot-de-passe/");
							exit();
						}
					}
				}
			} else {
				$verifToken = $bdd->prepare("SELECT * FROM ob_users_password WHERE email = :email");
				$verifToken->bindParam(":email", htmlentities($_GET['email']));
				$verifToken->execute();
				if($verifToken->rowCount() > 0) {
					$stepModif = TRUE;
				} else {
					if(isset($_GET['redirection'])) {
						header("Location: ".$url."/mot-de-passe/redirection/".htmlentities($_GET['redirection']));
						exit();
					} else {
						header("Location: ".$url."/mot-de-passe/");
						exit();
					}
				}
			}
		} else {
			if(isset($_GET['redirection'])) {
				header("Location: ".$url."/mot-de-passe/redirection/".htmlentities($_GET['redirection']));
				exit();
			} else {
				header("Location: ".$url."/mot-de-passe/");
				exit();
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Mot de passe oublié - Occitanie Boissons</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<meta name="description" content=""/>
        <meta name="keywords" content="cave bière pechbonnieu, cave vin pechbonnieu, location tireuse pechbonnieu, perfect draft pechbonnieu, philips HD3620 pechbonnieu, occitanie boissons, cave bière toulouse, cave vin toulouse, location tireuse toulouse, perfect draft toulouse, philips HD3620 toulouse, compte, inscription, connexion, identifiant, identification, utilisateur"/>

		<!-- FAVICON -->
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $gallery; ?>/images/favicon.ico">
		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/style.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/catalogue.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $gallery; ?>/css/screen.css" type="text/css">
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
							<div class="titre">Mot de passe oublié</div>
							<?php if(!$stepModif) { ?>
								<h3 id="titre-bleu">Vous avez oublié votre mot de passe ?</h3>
								<p>Pas de panique ! Saisissez ci-dessous l'adresse email associée à votre compte. Nous vous enverrons un email contenant un lien pour modifier votre mot de passe.</p>
								<form class="formulaire" id="motdepasse" data-action="demande">
									<div><label>Email</label><input name="email" type="email" /></div>
									<?php if(isset($_GET['redirect'])) { ?>
										<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect">
									<?php } ?>
									<button class="btn" type="submit">Confirmer &nbsp; <i class="icon-pwd"></i></button>
									<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour &nbsp; <i class="icon-fleche-droite"></i></button></a>
								</form>
							<?php } else { ?>
								<?php if(!$modifPwd) { ?>
									<h3 id="titre-bleu">En attente de confirmation</h3>
									<p>Nous vous avons envoyé un email à l'adresse <strong>"<?php echo htmlentities($_GET['email']); ?>"</strong> contenant le lien qui vous permettra de modifier votre mot de passe.<br/>
										Si après plusieurs minutes vous ne recevez pas d'email, veuillez vérifier vos spams. Sinon, refaites une tentative <a href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">en cliquant ici</a>.</p>
									<br/>
									<a href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Recommencer &nbsp; <i class="icon-pwd"></i></button></a>
									<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour &nbsp; <i class="icon-fleche-droite"></i></button></a>
								<?php } else { ?>
									<h3 id="titre-bleu">Modification du mot de passe</h3>
									<p>Veuillez à présent choisir un nouveau mot de passe pour votre compte. Ce sera le nouveau mot de passe avec lequel vous devrez vous connecter.</p>
									<form class="formulaire" id="motdepasse" data-action="modification">
										<div><label>Mot de passe</label><input name="pwd" type="password" /></div>
										<div><label>Confirmez-le</label><input name="repwd" type="password" /></div>
										<?php if(isset($_GET['redirect'])) { ?>
											<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect">
										<?php } ?>
										<input type="hidden" value="<?php echo htmlentities($_GET['token']); ?>" name="token">
										<input type="hidden" value="<?php echo htmlentities($_GET['email']); ?>" name="email">
										<button class="btn" type="submit">Confirmer &nbsp; <i class="icon-pwd"></i></button>
										<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour &nbsp; <i class="icon-fleche-droite"></i></button></a>
									</form>
								<?php } ?>
							<?php } ?>
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
	</body>
</html>