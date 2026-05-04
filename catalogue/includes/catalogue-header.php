<header>
	<!-- CONTAINER -->
	<div class="container">
		<!-- LOGO -->
		<a href="<?php echo $url; ?>"><figure id="logo"></figure></a>
		<!-- NAV -->
		<nav>
			<!-- SOCIAL -->
			<aside id="social">
				<a href="https://www.instagram.com/occitanieboissons/" target="meta"><i class="icon-insta"></i></a>
				<a href="https://www.facebook.com/Occitanie-Boissons-1619195328335124" target="meta"><i class="icon-fb"></i></a>
			</aside>
			<!-- BTNS -->
			<div class="btn-header">
				<div id="livraison-container">
					<form id="livraison">
						<a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2020.pdf" download><button class="btn" type="button"><i class="icon-livraison"></i> Livraison <span id="livraison_prix"><?php if(isset($_COOKIE['CodePostal'])) { echo number_format(LivraisonPrix($_COOKIE['CodePostal']), 2, ',', ' ')."€"; } ?></span></button></a>
						<?php if(!isset($_COOKIE['CodePostal'])) { ?>
							<div class="content-btn">
								<input type="text" name="code_postal" placeholder="Code postal" class="form_livraison" />
								<button type="submit" class="btn form_livraison"><i class="icon-avion"></i></button>
							</div>
						<?php } ?>
					</form>
				</div>
				<a class="panier-btn" href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-cart"></i> Panier <span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€ HT HD</button></a>
				<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-plus"></i> Consigne <span class="consigne-prix"><?php echo Consigne(); ?></span>€</button></a>
			</div>
		</nav>
	</div>

	<!-- MESSAGE -->
	<div id="message-content">
		<p></p>
		<button type="button" class="close" data-message="close">×</button>
	</div>
</header>