<div id="connexion-barre">
	<?php if(@$_SESSION['site']) { ?>
		<div class="texte">Bonjour <?php echo $u->prenom; ?>, c'est un plaisir de vous revoir parmis nous !</div>
		<div class="dropdown">
		  <button class="dropbtn"><i class="icon-user-circle"></i> <?php echo $u->prenom; ?> <i class="icon-arrow-dropdown"></i></button>
		  <div class="dropdown-content">
		    <a href="<?php echo $url; ?>/informations-personnelles/">Informations personnelles</a>
		    <a href="<?php echo $url; ?>/commande-visualisation/">Commandes</a>
		    <a href="<?php echo $url; ?>/panier/">Panier</a>
		    <a href="<?php echo $url; ?>/deconnexion/">Déconnexion</a>
		  </div>
		</div>
	<?php } else { ?>
		<div class="statut">Vous n'êtes actuellement connecté à aucun compte.</div>
		<div>
			<a href="<?php echo $url; ?>/connexion/"><button class="btn" type="button">Connexion &nbsp; <i class="icon-user-circle"></i></button></a>
			<a href="<?php echo $url; ?>/inscription/"><button class="btn" type="button">Inscription &nbsp; <i class="icon-avion"></i></button></a>
		</div>
	<?php } ?>
</div>