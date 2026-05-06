 <?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	if(isset($_SESSION['site'])) {
		header("Location: ".$url."/accueil/");
		exit();
	}

	$stepModif = FALSE;
	$modifPwd = FALSE;
	if(isset($_GET['email'])) {
		$verifEmail = $bdd->prepare("SELECT * FROM cppiades_users WHERE email = :email");
		$verifEmail->bindParam(":email", htmlentities($_GET['email']));
		$verifEmail->execute();
		if($verifEmail->rowCount() > 0) {
			if(isset($_GET['token'])) {
				// VERIFICATION DU TOKEN
				$verifToken = $bdd->prepare("SELECT * FROM cppiades_users_password WHERE email = :email AND token = :token");
				$verifToken->bindParam(":email", htmlentities($_GET['email']));
				$verifToken->bindParam(":token", htmlentities($_GET['token']));
				$verifToken->execute();
				if($verifToken->rowCount() > 0) {
					$modifPwd = TRUE;
					$stepModif = TRUE;
				} else {
					$verifUser = $bdd->prepare("SELECT * FROM cppiades_users_password WHERE email = :email");
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
				$verifToken = $bdd->prepare("SELECT * FROM cppiades_users_password WHERE email = :email");
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
		<title>Mot de passe oublié - <?php echo $sitename; ?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<meta name="description" content=""/>
        <meta name="keywords" content=""/>

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

		<div class="boxe petite">
			<div class="content-boxe">
				<!-- HEADER -->
				<?php require("./includes/header.php"); ?>

				<h3 class="titre-bleu">Mot de passe oublié</h3><br/>
				<?php if(!$stepModif) { ?>
					<h4 class="titre">Tu as oublié ton mot de passe ?</h4><br/>
					<p>Pas de panique ! Saisis ci-dessous l'adresse email associée à ton compte CPPien. Nous t'enverrons très rapidement un email contenant un lien pour modifier ton mot de passe.</p><br/>
					<form id="motdepasse" data-action="demande">
						<div class="input-container">		
							<input type="mail" placeholder="Adresse email" name="email" class="input"/>
							<span class="focus-input"></span>
						</div>
						<?php if(isset($_GET['redirect'])) { ?>
							<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect">
						<?php } ?>
						<div class="content-btn">
							<button class="btn" type="submit">Confirmer</button>
							<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour</button></a>
						</div>
					</form>
				<?php } else { ?>
			<?php if(!$modifPwd) { ?>
				<h4 class="titre">En attente de confirmation</h4><br/>
				<p>Nous t'avons envoyé un email à l'adresse <strong>"<?php echo htmlentities($_GET['email']); ?>"</strong> contenant le lien qui te permettra de modifier ton mot de passe.<br/>
					Si après plusieurs minutes tu ne reçois pas d'email vérifies tes spams. Sinon, refais une tentative <a href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>">en cliquant ici</a>.</p>
				<br/>
				<div class="content-btn">
					<a href="<?php echo $url; ?>/mot-de-passe/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Recommencer</button></a>
					<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour</button></a>
				</div>
			<?php } else { ?>
				<h4 class="titre">Modification du mot de passe</h4><br/>
				<p>À présent, choisis un nouveau mot de passe pour ton compte CPPien. Ce sera le nouveau mot de passe avec lequel tu devras te connecter.</p><br/>
				<form id="motdepasse" data-action="modification">
					<div class="input-container">		
						<input type="password" placeholder="Mot de passe" name="pwd" class="input"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">		
						<input type="password" placeholder="Confirme-le" name="repwd" class="input"/>
						<span class="focus-input"></span>
					</div>
					<?php if(isset($_GET['redirect'])) { ?>
						<input type="hidden" value="<?php echo htmlentities($_GET['redirect']); ?>" name="redirect">
					<?php } ?>
					<input type="hidden" value="<?php echo htmlentities($_GET['token']); ?>" name="token">
					<input type="hidden" value="<?php echo htmlentities($_GET['email']); ?>" name="email">
					<div class="content-btn">
						<button class="btn" type="submit">Confirmer</button>
						<a href="<?php echo $url; ?>/connexion/<?php if(isset($_GET['redirect'])) {echo "redirection/".htmlentities($_GET['redirect']);} ?>"><button class="btn" type="button">Retour</button></a>
					</div>
				</form>
			<?php } ?>
		<?php } ?>
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