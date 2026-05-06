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

				<form id="emplacements_ajout">
					<h4 class="titre rouge">Implanter un produit</h4><br/>
					<div class="input-container">
						<label>Libellé</label>		
						<input name="libelle" type="text" class="input" placeholder="Libellé"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">
						<label>Zone géographique</label>
						<input name="zone" type="text" class="input" placeholder="Zone géographique"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">
						<label>Allée</label>		
						<input name="allee" type="text" class="input" placeholder="Allée" />
						<span class="focus-input"></span>
					</div>
					<div class="input-container">
						<label>Case</label>
						<input name="case" type="number" class="input" placeholder="Case" />
						<span class="focus-input"></span>
					</div>
					<div class="input-container">
						<label>Emplacement</label>
						<input name="emplacement" type="number" class="input" placeholder="Emplacement" />
						<span class="focus-input"></span>
					</div>
					<div class="content-btn">
						<button class="btn" type="submit">Implanter</button>
					</div>
				</form>
				<form id="emplacements_echange">
					<h4 class="titre rouge">Echanger deux produits</h4><br/>
					<div class="input-container">
						<label>Libellé 1</label>		
						<input name="libelle1" type="text" class="input" placeholder="Libellé 1"/>
						<span class="focus-input"></span>
					</div>
					<div class="input-container">
						<label>Libellé 2</label>		
						<input name="libelle2" type="text" class="input" placeholder="Libellé 2"/>
						<span class="focus-input"></span>
					</div>
					<div class="content-btn">
						<button class="btn" type="submit">Echanger</button>
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