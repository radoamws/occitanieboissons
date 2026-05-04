<?php 
	require("includes/configuration.php");
	require("includes/functions.php");

	$menu = "boutique";

	if(!isset($_SESSION['site'])) {
		header("Location: ".$url."/connexion/redirection/commande/");
		exit();
	} elseif($u->catalogue == 0) {
		header("Location: ".$url);
		exit();
	}

	// VERIFICATION PANIER
	if(ArticlePanier() <= 0 || PrixPanier("ht", TRUE) <= 0) {
		header("Location: ".$url."/panier/");
		exit();
	}

	$modifAdress = FALSE;
	if(isset($_GET['action'])) {
		if($_GET['action'] == "facturation") {
			$bdd->query("UPDATE ob_users SET adresse_l_f = '0' WHERE id = '".$u->id."'");
			$u->adresse_l_f = 0;
		} elseif(($_GET['action'] == "modifier" || $_GET['action'] == "supprimer") && isset($_GET['id'])) {
			$verifAdress = $bdd->prepare("SELECT * FROM ob_users_adresses WHERE id = :id AND userid = '".$u->id."'");
			$verifAdress->bindParam(":id", htmlentities($_GET['id']));
			$verifAdress->execute();
			$v = $verifAdress->fetch(PDO::FETCH_OBJ);
			if($verifAdress->rowCount() > 0) {
				if($_GET['action'] == "supprimer") {$bdd->query("DELETE FROM ob_users_adresses WHERE id = '".$v->id."'");}
				if($_GET['action'] == "modifier") {$modifAdress = TRUE;}
			} else {
				header("Location: ".$url."/commande/etape/2");
				exit();
			}
		} 
	}

	// ETAPE
	if(isset($_GET['step']) && $_GET['step'] < 5) {
		if($_GET['step'] == 1) {
			$step = 1;
		} elseif($_GET['step'] == 2) {
			$step = 2;
		} elseif($_GET['step'] == 3) {
			$infoAdress = $bdd->prepare("SELECT * FROM ob_users_adresses WHERE id = :adresse_livraison AND userid = :user_id");
			$infoAdress->bindParam(":adresse_livraison", $u->adresse_livraison);
			$infoAdress->bindParam(":user_id", $u->id);
			$infoAdress->execute();
			if($infoAdress->rowCount() > 0) {
				$i = $infoAdress->fetch(PDO::FETCH_OBJ);
			} else {
				header("Location: ".$url."/commande/etape/2");
				exit();
			}
			$step = 3;
		} else if($_GET['step'] == 4) {
			$step = 4;
		}
	} else {
		$step = 1;
	}

	// METHODE DE PAIEMENT
	switch($u->paiement_default) {
		case 0:
			$nom = "Virement comptant";
		break;
		case 1:
			$nom = "Traite 30 jours";
		break;
		case 2:
			$nom = "Virement 30 jours";
		break;
	}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Commande - Occitanie Boissons</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1"/>

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
		<div class="page panier">
			<!-- BARRE -->
			<?php require("./includes/barre.php"); ?>
			<!-- CONTAINER -->
			<div class="container">
				<div class="button">
					<a href="<?php echo $url; ?>/panier/"><button class="btn" type="button"><i class="icon-fleche-gauche"></i> Retour au panier</button></a>
					<a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2021.pdf" download><button class="btn" type="button"><i class="icon-pdf"></i> Conditions de livraison</button></a>
				</div>
				<!-- FLEX -->
				<div id="flex-panier">
					<!-- FORMULAIRE CONTACT -->
					<section id="panier-contenu">
						<div id="content-form" class="commande">
							<section class="checkout-etape">
    							<h1 class="etape-titre">
      								<i class="icon-user-circle"></i>
      								Informations personnelles
      								<?php if($step > 1) { ?><a href="<?php echo $url; ?>/commande/etape/1"><span class="etape-edit"><i class="icon-pen"></i> Modifier</span></a><?php } ?>
    							</h1>
    							<div class="content" <?php if($step != 1) { ?>style="display: none;"<?php } ?>>
    								<form class="formulaire" id="updateinformations">
    									<p class="bottom">Adresse email : <?php echo $u->email; ?> <a href="<?php echo $url; ?>/informations-personnelles/">Modifier</a></p>
										<div><label>Nom</label><input name="nom" type="text" value="<?php echo $u->nom; ?>" /></div>
										<div><label>Prénom</label><input name="prenom" type="text" value="<?php echo $u->prenom; ?>" /></div>
										<div><label>Téléphone</label><input data-mask="phone-int" name="phone" type="text" value="<?php echo $u->phone; ?>"/></div>
										<div><label>Entreprise (optionnel)</label><input name="entreprise" type="text" value="<?php echo $u->entreprise; ?>" /></div>
										<button class="btn" type="submit">Continuer &nbsp; <i class="icon-fleche-droite"></i></button>
									</form>
  								</div>
  							</section>
  							<section class="checkout-etape">
    							<h1 class="etape-titre <?php if($step < 2) { echo "not-allowed";} ?>">
      								<i class="icon-adresse"></i>
      								Adresses
      								<?php if($step > 2) { ?><a href="<?php echo $url; ?>/commande/etape/2"><span class="etape-edit"><i class="icon-pen"></i> Modifier</span></a><?php } ?>
    							</h1>
    							<div class="content" <?php if($step != 2) { ?>style="display: none;"<?php } ?>>
    								<p class="center">L'adresse sélectionnée sera utilisée à la fois comme adresse personnelle (pour la facturation) et comme adresse de livraison.</p>
    								<?php if(!$modifAdress) { ?>
	    								<!-- SELECTION -->
	    								<div id="selection-address">
		    								<?php
		    									$adresses = $bdd->query("SELECT * FROM ob_users_adresses WHERE userid = '".$u->id."' ORDER BY time DESC");
		    									if($adresses->rowCount() > 0) {
		    								?>
												<?php if($u->adresse_l_f == 0) { ?><h4 class="selector-title">Adresse de livraison</h4><?php } ?>
			    								<div class="address-selector">
				    								<?php 
				    									while($a = $adresses->fetch(PDO::FETCH_OBJ)) {
				    								?>
				    									<div class="address-content">
					     									<article class="livraison address-item <?php if($a->id == $u->adresse_livraison) {echo "selected";} ?>">
														    	<div class="top">
														        	<label class="radio-block">
														          		<span class="custom-radio adresses">
														            		<input type="radio" data-type="livraison" value="<?php echo $a->id; ?>" <?php if($u->adresse_livraison == $a->id) {echo 'checked="checked"';} ?>>
														            		<span></span>
														          		</span>
														          		<h4 class="address-alias">Mon adresse</h4>
														          		<div class="address"><?php echo $a->prenom; ?> <?php echo mb_strtoupper($a->nom, 'UTF-8'); ?><br><?php echo $a->adresse; ?><br><?php echo $a->adressec; ?><br><?php echo $a->codepostal; ?> <?php echo mb_strtoupper($a->ville, 'UTF-8'); ?><br><?php echo $a->pays; ?><br><?php echo $a->phone; ?><br><?php echo @$a->entreprise; ?><br><?php echo @$a->numerotva; ?></div>
														        	</label>
														      	</div>
					      										<hr>
					      										<div class="address-footer">
						                  							<a class="edit" href="<?php echo $url; ?>/commande/etape/2/action/modifier/<?php echo $a->id; ?>">
						            									<i class="icon-editc"></i> Modifier
						          									</a>
						          									<a class="edit" href="<?php echo $url; ?>/commande/etape/2/action/supprimer/<?php echo $a->id; ?>">
						            									<i class="icon-poubelle"></i> Supprimer
						          									</a>
					              								</div>
					    									</article>
					    								</div>
				    								<?php } ?>
				    							</div>
				    							<?php if($u->adresse_l_f == 0) { ?>
				    								<h4 class="selector-title">Adresse de facturation</h4>
					    							<div class="address-selector">
					    								<?php
					    									$adresses = $bdd->query("SELECT * FROM ob_users_adresses WHERE userid = '".$u->id."' ORDER BY time DESC"); 
					    									while($a = $adresses->fetch(PDO::FETCH_OBJ)) {
					    								?>
					    									<div class="address-content">
						     									<article class="facturation address-item <?php if($a->id == $u->adresse_facturation) {echo "selected";} ?>">
															    	<div class="top">
															        	<label class="radio-block">
															          		<span class="custom-radio adresses">
															            		<input type="radio" data-type="facturation" value="<?php echo $a->id; ?>" <?php if($u->adresse_facturation == $a->id) {echo 'checked="checked"';} ?>>
															            		<span></span>
															          		</span>
															          		<h4 class="address-alias">Mon adresse</h4>
															          		<div class="address"><?php echo $a->prenom; ?> <?php echo mb_strtoupper($a->nom, 'UTF-8'); ?><br><?php echo $a->adresse; ?><br><?php echo $a->adressec; ?><br><?php echo $a->codepostal; ?> <?php echo mb_strtoupper($a->ville, 'UTF-8'); ?><br><?php echo $a->pays; ?><br><?php echo $a->phone; ?><br><?php echo @$a->entreprise; ?><br><?php echo @$a->numerotva; ?></div>
															        	</label>
															      	</div>
						      										<hr>
						      										<div class="address-footer">
							                  							<a class="edit" href="<?php echo $url; ?>/commande/etape/2/action/modifier/<?php echo $a->id; ?>">
							            									<i class="icon-editc"></i> Modifier
							          									</a>
							          									<a class="edit" href="<?php echo $url; ?>/commande/etape/2/action/supprimer/<?php echo $a->id; ?>">
							            									<i class="icon-poubelle"></i> Supprimer
							          									</a>
						              								</div>
						    									</article>
						    								</div>
					    								<?php } ?>
					    							</div>
				    							<?php } ?>
				    							<p><a href="#" class="ajouter-adresse-active edit-active">+ Ajouter une adresse</a></p>
			    								<?php if($u->adresse_l_f == 1) { ?>
				    								<p><a href="<?php echo $url; ?>/commande/etape/2/action/facturation" class="edit-active">L'adresse de livraison est différente de l'adresse de facturation.</a></p>
			    								<?php } ?>
			        							<button class="btn next-step-adress" type="button">Continuer &nbsp; <i class="icon-fleche-droite"></i></button>
			        						<?php } ?>
		        						</div>
		        						<!-- AJOUTER UNE ADRESSE -->
		        						<form id="add-adresse" class="formulaire" data-action="ajouter" <?php if($adresses->rowCount() > 0) { ?>style="display: none;"<?php } ?>>
											<div><label>Nom</label><input name="nom" type="text"/></div>
											<div><label>Prénom</label><input name="prenom" type="text"/></div>
											<div><label>Entreprise (optionnel)</label><input name="entreprise" type="text"/></div>
											<div><label>Numéro de TVA (optionnel)</label><input name="tva" type="text"/></div>
											<div><label>Adresse</label><input name="adresse" type="text"/></div>
											<div><label>Complément adresse</label><input name="adressec" type="text"/></div>
											<div><label>Code postal</label><input name="codepostal" type="text"/></div>
											<div><label>Ville</label><input name="ville" type="text"/></div>
											<div><label>Pays</label><select name="pays"><option value="">Choisir un pays</option><option value="France">France</option></select></div>
											<div><label>Téléphone</label><input data-mask="phone-int" name="phone" type="text"/></div>
											<label class="catalogue-checkbox">
											  	<div>Utiliser cette adresse pour la facturation.</div>
											  	<input type="checkbox" checked="checked" name="facturation">
											  	<span class="checkmark"></span>
											</label>
											<button class="btn" type="submit">Continuer &nbsp; <i class="icon-fleche-droite"></i></button>
											<a href="<?php echo $url; ?>/commande/etape/2" class="retour-adresse"><button class="btn" type="button">Annuler &nbsp; <i class="icon-close"></i></button></a>
										</form>
		        					<?php } else { ?>
		        						<!-- MODIFIER UNE ADRESSE -->
		        						<form id="add-adresse" class="formulaire" data-action="modifier">
											<div><label>Nom</label><input name="nom" type="text" value="<?php echo $v->nom; ?>"/></div>
											<div><label>Prénom</label><input name="prenom" type="text" value="<?php echo $v->prenom; ?>"/></div>
											<div><label>Entreprise (optionnel)</label><input name="entreprise" type="text" value="<?php echo $v->entreprise; ?>"/></div>
											<div><label>Numéro de TVA (optionnel)</label><input name="tva" type="text" value="<?php echo $v->numerotva; ?>"/></div>
											<div><label>Adresse</label><input name="adresse" type="text" value="<?php echo $v->adresse; ?>"/></div>
											<div><label>Complément adresse</label><input name="adressec" type="text" value="<?php echo $v->adressec; ?>"/></div>
											<div><label>Code postal</label><input name="codepostal" type="text" value="<?php echo $v->codepostal; ?>"/></div>
											<div><label>Ville</label><input name="ville" type="text" value="<?php echo $v->ville; ?>"/></div>
											<div><label>Pays</label><select name="pays"><option value="">Choisir un pays</option><option value="France" <?php if($v->pays == "France") {echo "selected";} ?>>France</option></select></div>
											<div><label>Téléphone</label><input data-mask="phone-int" name="phone" type="text" value="<?php echo $v->phone; ?>"/></div>
											<label class="catalogue-checkbox">
											  	<div>Utiliser cette adresse pour la facturation.</div>
											  	<input type="checkbox" checked="checked" name="facturation">
											  	<span class="checkmark"></span>
											</label>
											<input name="idadresse" type="hidden" value="<?php echo $v->id; ?>"/>
											<button class="btn" type="submit">Continuer &nbsp; <i class="icon-fleche-droite"></i></button>
											<a href="<?php echo $url; ?>/commande/etape/2"><button class="btn" type="button">Annuler &nbsp; <i class="icon-close"></i></button></a>
										</form>
		        					<?php } ?>
  								</div>
  							</section>
  							<section class="checkout-etape">
    							<h1 class="etape-titre <?php if($step < 3) { echo "not-allowed";} ?>">
      								<i class="icon-livraison"></i>
      								Mode de livraison
      								<?php if($step > 3) { ?><a href="<?php echo $url; ?>/commande/etape/3"><span class="etape-edit"><i class="icon-pen"></i> Modifier</span></a><?php } ?>
    							</h1>
    							<div class="content" <?php if($step != 3) { ?>style="display: none;"<?php } ?>>
          							<form id="modif-livraison" data-action="livraison">
						                <div class="option-livraison">
	        								<div class="selection">
	          									<span class="custom-radio">
	            									<input type="radio" name="option_livraison" value="0" <?php if($u->option_livraison == 0) {echo "checked"; } ?>>
	            									<span></span>
	          									</span>
	          									<h4>Livraison (<?php echo number_format(LivraisonPrix(CodePostal()), 2, ',', ' ')."€"; ?> HT)</h4>
	        								</div>
	        								<label>
								                <div>Prix TTC : <strong><?php echo number_format(LivraisonPrix(CodePostal(), "ttc"), 2, ',', ' ')."€"; ?></strong></div>
							                    <div class="informations">
							                        Livré à l'adresse de livraison renseignée à l'étape précédente.<br/>
							                        <a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2020.pdf" download>Voir les conditions de livraison</a>
						                        </div>
						                   	</label>
						                </div>
  										<div class="option-livraison">
								            <div>
								              <p>Si vous voulez nous laisser un message à propos de votre commande, merci de bien vouloir le renseigner dans le champ ci-contre.</p>
								              <textarea rows="4" name="livraison_message"></textarea>
								            </div>
								        </div>
										<button class="btn" type="submit">Continuer <i class="icon-fleche-droite"></i></button>
									</form>
      							</div>
  							</section>
  							<section class="checkout-etape">
    							<h1 class="etape-titre <?php if($step < 4) { echo "not-allowed";} ?>">
      								<i class="icon-paiement"></i>
      								Confirmation
    							</h1>
    							<div class="content" <?php if($step != 4) { ?>style="display: none;"<?php } ?>>
    								<h3>Veuillez vérifier votre commande avant la confirmation</h3><br/>
    								<!-- ADRESSES -->
    								<h4>Adresses</h4><a class="edit-confirm edit-active" href="<?php echo $url; ?>/commande/etape/2"><i class="icon-editc"></i> Modifier</a>
    								<?php
										$al = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_livraison."'")->fetch(PDO::FETCH_OBJ);
										if($u->adresse_l_f == 1) {$af = $al;} else {
											$af = $bdd->query("SELECT * FROM ob_users_adresses WHERE id = '".$u->adresse_facturation."'")->fetch(PDO::FETCH_OBJ);
										}
    								?>
									<div class="address-selector">
    									<div class="address-content">
	     									<article class="livraison address-item">
										    	<div class="top">
										        	<label class="radio-block">
										          		<h4 class="address-alias">Votre adresse de livraison</h4>
										          		<div class="address"><?php echo $al->prenom; ?> <?php echo mb_strtoupper($al->nom, 'UTF-8'); ?><br><?php echo $al->adresse; ?><br><?php echo $al->adressec; ?><br><?php echo $al->codepostal; ?> <?php echo mb_strtoupper($al->ville, 'UTF-8'); ?><br><?php echo $al->pays; ?><br><?php echo $al->phone; ?></div>
										        	</label>
										      	</div>
	    									</article>
	    								</div>
	    								<div class="address-content">
	     									<article class="livraison address-item">
										    	<div class="top">
										        	<label class="radio-block">
										          		<h4 class="address-alias">Votre adresse de facturation</h4>
										          		<div class="address"><?php echo $af->prenom; ?> <?php echo mb_strtoupper($af->nom, 'UTF-8'); ?><br><?php echo $af->adresse; ?><br><?php echo $af->adressec; ?><br><?php echo $af->codepostal; ?> <?php echo mb_strtoupper($af->ville, 'UTF-8'); ?><br><?php echo $af->pays; ?><br><?php echo $af->phone; ?></div>
										        	</label>
										      	</div>
	    									</article>
	    								</div>
	    							</div>
									<!-- MODE DE LIVRAISON -->
									<h4>Mode de livraison</h4><a class="edit-confirm edit-active" href="<?php echo $url; ?>/commande/etape/3"><i class="icon-editc"></i> Modifier</a>
							        <div class="option-livraison">
        								<div class="selection">
          									<h4>Livraison (<?php echo number_format(LivraisonPrix(CodePostal(), "ttc"), 2, ',', ' ')."€"; ?> TTC)</h4>
        								</div>
        								<label>
						                    <div class="informations">
						                        Livré à l'adresse de livraison renseignée à l'étape précédente.<br/>
						                        <a href="<?php echo $url; ?>/Conditions de livraison OB.pdf" download>Voir les conditions de livraison</a>
					                        </div>
					                   	</label>
					                </div>
					                <!-- CONSIGNE -->
					                <h4>Consigne (<?php echo Consigne(); ?>€ TTC)</h4><br/>
								    <!-- PRODUITS -->
								   	<h4>Produits (<?php echo PrixPanier("ttc"); ?>€ TTC)</h4><a class="edit-confirm edit-active" href="<?php echo $url; ?>/panier/"><i class="icon-editc"></i> Modifier</a>	<?php
								   		/* CHECK CONSIGNE */
										$consigne = FALSE;
										foreach(json_decode($u->panier, true) as $p) {
											foreach($p as $p2 => $qte) {
												$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
												$element->bindParam(":id", $p2);
												$element->execute();
												$e = $element->fetch(PDO::FETCH_OBJ);
												if($e->consigne_caisse != 0) {$consigne = TRUE;}
											}
										}
									?>				
									<table class="boutique-table" style="margin: 10px 0 20px 0;width: 100%;">
										<thead>
											<tr>
												<?php if($consigne) { ?>
													<th width="40%">Produit</th>
													<th width="12.5%">Contenance</th>
													<th width="12.5%">% Alcool</th>
													<th width="12.5%">Prix HT HD</th>
													<th width="12.5%">Consigne</th>
													<th width="10%">Quantité</th>
												<?php } else { ?>
													<th width="45%">Produit</th>
													<th width="15%">Contenance</th>
													<th width="15%">% Alcool</th>
													<th width="15%">Prix HT HD</th>
													<th width="10%">Quantité</th>
												<?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach(json_decode($u->panier, true) as $p) {
													foreach($p as $p2 => $qte) {
														$element = $bdd->prepare("SELECT * FROM ob_catalogue_produits WHERE id = :id");
														$element->bindParam(":id", $p2);
														$element->execute();
														$e = $element->fetch(PDO::FETCH_OBJ);
										  	?>
												<tr class="boutique-element table-produit-<?php echo $e->id; ?>">
													<td class="nom"><?php if($e->marque == 2) { ?><span style="color: red;text-transform: uppercase;">Précommande</span><br class='no-margin'/><?php } ?><?php echo $e->nom."<br class='no-margin'/>".$e->nom_sup; ?></td>
													<td data-label="Contenance"><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
													<td data-label="% Alcool"><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
													<td data-label="Prix HT HD"><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
													<?php if($consigne) { ?><td data-label="Consigne"><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
													<td data-label="Quantité"><?php echo $qte; ?></td>
												</tr>
											<?php } } ?>
										</tbody>
									</table>
									<h4>Méthode de paiement</h4>
									<div class="option-livraison">
        								<div class="selection">
          									<h4 style="margin-bottom: 0px;"><?php echo $nom; ?> (<?php echo number_format(LivraisonPrix(CodePostal(),"ttc")+PrixPanier("ttc",TRUE)+Consigne(TRUE), 2, ',', ' ')."€"; ?> TTC)</h4>
        								</div>
					                </div>
									<p class="cgv">En poursuivant vous confirmez avoir lu les <a href="<?php echo $url; ?>/conditions-generales-de-vente/" target="_blank">conditions générales de vente</a> et y adhérer sans réserve.</p>
									<button id="commande-valide" class="btn" type="submit"><i class="icon-avion"></i> Envoyer la commande</button>
  								</div>
  							</section>
						</div>
					</section>
					<section id="panier-total">
						<div id="content-form">
							<div class="infos">Article<?php if(ArticlePanier() > 1) { ?>s<?php } ?> au total <span class="right"><span id="panier-articles"><?php echo ArticlePanier(); ?></span></span></div>
							<div class="infos">Prix HT HD <span class="right"><span class="panier-prix-ht"><?php echo PrixPanier("ht"); ?></span>€</span></div>
							<div class="infos">Prix HT DC <span class="right"><span class="panier-prix-droits"><?php echo PrixPanier("droits"); ?></span>€</span></div>
							<div class="infos">Prix TTC <span class="right"><span class="panier-prix"><?php echo PrixPanier("ttc"); ?></span>€</span></div>
							<hr/>
							<?php if($step < 3) { ?>
								<div class="infos">Livraison <span class="right"><a href="<?php echo $url; ?>/Tarifs-Transport-OccitanieBoissons-2020.pdf" download>Voir les conditions</a></span></div>
								<div class="infos">Consigne <span class="right"><span class="consigne-prix"><?php echo Consigne(); ?></span>€</span></div>
							<?php } else { ?>
								<div class="infos">Livraison TTC <span class="right"><?php echo number_format(LivraisonPrix(CodePostal(), "ttc"), 2, ',', ' ')."€"; ?></span></div>
								<div class="infos">Consigne <span class="right"><span class="consigne-prix"><?php echo Consigne(); ?></span>€</span></div>
								<hr/>
								<div class="infos">Total TTC <span class="right"><?php echo number_format(LivraisonPrix(CodePostal(),"ttc")+PrixPanier("ttc",TRUE)+Consigne(TRUE), 2, ',', ' ')."€"; ?></span></div>
							<?php } ?>
							<hr/>
							<div class="infos">Méthode de paiement <span class="right"><?php echo $nom; ?></span></div>
						</div>
					</section>
				</div>
				<!-- FOOTER -->
				<?php require("./includes/footer.php"); ?>
			</div>
		</div>

		<!-- JAVASCRIPT -->
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
		<script type="text/javascript" src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/general.js"></script>
		<script type="text/javascript" src="<?php echo $gallery; ?>/js/jquery.mask.js"></script>
		<script type="text/javascript">	
			$(function() {
				<?php if($step == '1') { ?>
					$("[data-mask='phone-int']").mask("+33 9999999999").val("<?php echo $u->phone; ?>");
				<?php } elseif($step == '2') { ?>
					<?php if(isset($_GET['action']) && $_GET['action'] == "modifier") { ?>
						$("[data-mask='phone-int']").mask("+33 9999999999").val("<?php echo $v->phone; ?>");
					<?php } else { ?>
						$("[data-mask='phone-int']").mask("+33 9999999999");
					<?php } ?>
				<?php } ?>
			});
		</script>
	</body>
</html>