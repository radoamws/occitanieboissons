<header>
	<?php if(isset($_SESSION['logistique'])) { ?>
		<ul id="menu-top">
			<a title="Informations personnelles" href="<?php echo $url; ?>/informations-personnelles/"><div class="gris"><i class="icon-parametres"></i></div></a>
			<a title="Déconnexion" href="<?php echo $url; ?>/deconnexion/"><div class="rouge"><i class="icon-deconnexion"></i></div></a>
		</ul>
	<?php } ?>
	<a href="<?php echo $url; ?>">
		<figure id="logo"></figure>
	</a>
	<?php if(isset($_SESSION['logistique'])) { ?>
		<!-- MENU -->
		<ul id="menu">
			<li onclick="window.location.href='<?php echo $url; ?>/emplacements/'" title="Emplacements"><div><i class="icon-dossier"></i><div class="text">Emplacements</div></div></li>
			<li onclick="window.location.href='<?php echo $url; ?>/dluo/'" title="DLUO"><div><i class="icon-paiement"></i><div class="text">DLUO</div></div></li>
		</ul>
	<?php } ?>
</header>