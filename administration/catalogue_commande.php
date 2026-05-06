  <?php
  require("./includes/configuration.php");
  require("./includes/functions.php");

  if(!isset($_SESSION['username'])) {
  	header("Location: ".$admurl."/login.php");
  }

  if(isset($_GET['paiement']) && isset($_GET['statut'])) {
  	$commande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id");
  	$commande->bindParam(":id", $_GET['paiement']);
  	$commande->execute();
  	if($commande->rowCount() > 0) {
  		$commandeUpdate = $bdd->prepare("UPDATE ob_users_commande SET paiement = :statut WHERE id = :id");
  		$commandeUpdate->bindParam(":statut", $_GET['statut']);
  		$commandeUpdate->bindParam(":id", $_GET['paiement']);
  		$commandeUpdate->execute();
  	} else {
  		header("Location: ".$admurl."/catalogue_commande.php");
  		exit();
  	}
  }

  $visualisation = FALSE;
  if(isset($_GET['visualisation'])) {
  	$commande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id");
  	$commande->bindParam(":id", $_GET['visualisation']);
  	$commande->execute();
  	if($commande->rowCount() > 0) {
  		$c = $commande->fetch(PDO::FETCH_OBJ);
  		$visualisation = TRUE;
  	} else {
  		header("Location: ".$admurl."/catalogue_commande.php");
  		exit();
  	}
  }

  	$menu = "catalogue_commande";
  ?>
  <!DOCTYPE html>
  <html lang="fr">
  <head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<meta name="description" content="">
  	<meta name="author" content="">
  	<link rel="shortcut icon" href="images/favicon.png">

  	<title><?php echo $sitename; ?> | Commandes</title>
  	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
  	<link href='//fonts.googleapis.com/css?family=Raleway:300,200,100' rel='stylesheet' type='text/css'>

  	<!-- DateRange -->
  	<link rel="stylesheet" type="text/css" href="js/bootstrap.daterangepicker/daterangepicker-bs3.css" />

  	<!-- Bootstrap core CSS -->
  	<link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
  	<link rel="stylesheet" type="text/css" href="js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
  	<link rel="stylesheet" type="text/css" href="js/jquery.select2/select2.css" />
  	<link rel="stylesheet" type="text/css" href="js/bootstrap.slider/css/slider.css" />
  	<link href="js/fuelux/css/fuelux.css" rel="stylesheet">
  	<link href="js/fuelux/css/fuelux-responsive.min.css" rel="stylesheet">
  	<link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">
  	<link rel="stylesheet" href="css/pygments.css">
  	<link rel="stylesheet" type="text/css" href="js/jquery.niftymodals/css/component.css" />
  	<link rel="stylesheet" type="text/css" href="js/bootstrap.wysihtml5/dist/bootstrap3-wysihtml5.min.css"></link>
  	<link rel="stylesheet" type="text/css" href="js/bootstrap.summernote/dist/summernote.css" />

  	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <![endif]-->
  <link rel="stylesheet" type="text/css" href="js/jquery.nanoscroller/nanoscroller.css" />
  <link rel="stylesheet" type="text/css" href="js/bootstrap.switch/bootstrap-switch.css" />
  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <link href="js/jquery.icheck/skins/square/blue.css" rel="stylesheet">
</head>
<body>
	<?php require("header.php"); ?>
	<div class="container-fluid" id="pcont">
		<div class="page-head">
			<h2>Gérer les produits</h2>
			<ol class="breadcrumb">
				<li><a href="<?php echo $admurl; ?>">Accueil</a></li>
				<li><a href="<?php echo $admurl; ?>/boutique.php">Boutique</a></li>
				<?php if(isset($_GET['visualisation'])) { ?>
					<li><a href="<?php echo $admurl; ?>/catalogue_commande.php.php">Commandes</a></li>
					<li class="active">Commande n°<?php echo $c->id; ?></li>
				<?php } else { ?>
					<li class="active">Commandes</li>
				<?php } ?>
			</ol>
		</div>
		<div class="cl-mcont">		
			<div class="row wizard-row">
				<div class="col-md-12">
					<?php if(!$visualisation) { ?>
						<div class="block-flat">
							<div class="content">
								<h3>Commandes impayées</h3>
								<div>
									<table class="table table-bordered" id="datatable">
										<thead>
											<tr>
												<th width="10%">N° Commande</th>
												<th width="25%">Nom</th>
												<th width="20%">Date</th>
												<th width="10%">Nombre d'articles</th>
												<th width="10%">Prix TTC</th>
												<th width="15%">Statut</th>
												<th width="10%">#</th>
											</tr>
										</thead>
										<tbody>
				          					<?php
				          						$commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE paiement = '0' OR paiement = '2' AND hide = '0' ORDER BY id DESC");
				          						while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
				          					?>
				          					    <tr id="<?php echo $c->id; ?>" class="odd gradeX commande">
				                          			<td><?php echo $c->id; ?></td>
				        						  	<td><?php echo $c->nom." ".$c->prenom; ?></td>
				        						  	<td><?php echo date('d/m/Y', $c->time); ?></td>
				        						  	<td><?php echo $c->articles; ?></td>
				        						  	<td><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</td>
						                          	<td>
						                            	<select data-id="<?php echo $c->id; ?>" id="deplace_commande" class="form-control">
						                             		<option value="0" <?php if($c->statut == 0) { echo "selected"; } ?>>Non traitée</option>
						                          			<option value="1" <?php if($c->statut == 1) { echo "selected"; } ?>>Expédiée</option> 
						                              		<option value="2" <?php if($c->statut == 2) { echo "selected"; } ?>>Reçue</option>	
						                            	</select>
						                            	<?php if($c->paiement == 2) {echo "- Paiement refusé";} ?>
						                         	</td>
				        						  	<td class="center">
				        						    	<a class="btn btn-success btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?visualisation=<?php echo $c->id; ?>" data-original-title="Voir" data-toggle="tooltip"><i class="fa fa-search"></i></a>
				        						    	<a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?paiement=<?php echo $c->id; ?>&statut=1" data-original-title="Payée" data-toggle="tooltip"><i class="fa fa-credit-card"></i></a>
				        						    	<a target="_blank" class="btn btn-warning btn-xs" href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" data-original-title="Facture" data-toggle="tooltip"><i class="fa fa-file"></i></a>
				        						    	<a class="btn btn-danger btn-xs delete-commande" data-id="<?php echo $c->id; ?>" data-original-title="Supprimer" data-toggle="tooltip"><i class="fa fa-times"></i></a>
				        						  	</td>
				          					    </tr>
				          					<?php } ?>
	          							</tbody>
									</table>              
								</div>
							</div>
						</div> 
						<div class="block-flat">
							<div class="content">
								<h3>Commandes non traitées</h3>
								<div>
									<table class="table table-bordered" id="datatable2">
										<thead>
											<tr>
												<th width="10%">N° Commande</th>
												<th width="25%">Nom</th>
												<th width="20%">Date</th>
												<th width="10%">Nombre d'articles</th>
												<th width="10%">Prix TTC</th>
												<th width="15%">Statut</th>
												<th width="10%">#</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE statut = '0' AND paiement = '1' AND hide = '0' ORDER BY id DESC");
												while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
											?>
												<tr id="<?php echo $c->id; ?>" class="odd gradeX boutique">
													<td><?php echo $c->id; ?></td>
													<td><?php echo $c->nom." ".$c->prenom; ?></td>
													<td><?php echo date('d/m/Y', $c->time); ?></td>
													<td><?php echo $c->articles; ?></td>
													<td><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</td>
													<td>
														<select data-id="<?php echo $c->id; ?>" id="deplace_commande" class="form-control">
															<option value="0" <?php if($c->statut == 0) { echo "selected"; } ?>>Non traitée</option>
															<option value="1" <?php if($c->statut == 1) { echo "selected"; } ?>>Expédiée</option> 
															<option value="2" <?php if($c->statut == 2) { echo "selected"; } ?>>Reçue</option>
														</select>
													</td>
													<td class="center">
														<a class="btn btn-success btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?visualisation=<?php echo $c->id; ?>" data-original-title="Voir" data-toggle="tooltip"><i class="fa fa-search"></i></a>
														<a target="_blank" class="btn btn-warning btn-xs" href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" data-original-title="Facture" data-toggle="tooltip"><i class="fa fa-file"></i></a>
														<a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?paiement=<?php echo $c->id; ?>&statut=0" data-original-title="Payée" data-toggle="tooltip"><i class="fa fa-credit-card"></i></a>
													</td>
												</tr>
											<?php } ?>
										</tbody>
									</table>							
								</div>
							</div>
						</div>
						<div class="block-flat">
							<div class="content">
								<h3>Commandes en cours</h3>
								<div>
									<table class="table table-bordered" id="datatable3">
										<thead>
											<tr>
												<th width="10%">N° Commande</th>
												<th width="25%">Nom</th>
												<th width="20%">Date</th>
												<th width="10%">Nombre d'articles</th>
												<th width="10%">Prix TTC</th>
												<th width="15%">Statut</th>
												<th width="10%">#</th>
											</tr>
										</thead>
										<tbody>
				          					<?php
				          						$commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE statut = '1' AND paiement = '1' AND hide = '0' ORDER BY id DESC");
				          						while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
				          					?>
				          					    <tr id="<?php echo $c->id; ?>" class="odd gradeX boutique">
				                          			<td><?php echo $c->id; ?></td>
				        						  	<td><?php echo $c->nom." ".$c->prenom; ?></td>
				        						  	<td><?php echo date('d/m/Y', $c->time); ?></td>
				        						  	<td><?php echo $c->articles; ?></td>
				        						  	<td><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</td>
						                          	<td>
						                            	<select data-id="<?php echo $c->id; ?>" id="deplace_commande" class="form-control">
						                             		<option value="0" <?php if($c->statut == 0) { echo "selected"; } ?>>Non traitée</option>
					                                		<option value="1" <?php if($c->statut == 1) { echo "selected"; } ?>>Expédiée</option> 
						                              		<option value="2" <?php if($c->statut == 2) { echo "selected"; } ?>>Reçue</option>
						                            	</select>
						                         	</td>
				        						  	<td class="center">
				        						    	<a class="btn btn-success btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?visualisation=<?php echo $c->id; ?>" data-original-title="Voir" data-toggle="tooltip"><i class="fa fa-search"></i></a>
				        						    	<a target="_blank" class="btn btn-warning btn-xs" href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" data-original-title="Facture" data-toggle="tooltip"><i class="fa fa-file"></i></a>
				        						    	<a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?paiement=<?php echo $c->id; ?>&statut=0" data-original-title="Payée" data-toggle="tooltip"><i class="fa fa-credit-card"></i></a>
				        						  	</td>
				          					    </tr>
				          					<?php } ?>
	          							</tbody>
									</table>              
								</div>
							</div>
						</div> 
						<div class="block-flat">
							<div class="content">
								<h3>Commandes traitées</h3>
								<div>
									<table class="table table-bordered" id="datatable4">
										<thead>
											<tr>
												<th width="10%">N° Commande</th>
												<th width="25%">Nom</th>
												<th width="20%">Date</th>
												<th width="10%">Nombre d'articles</th>
												<th width="10%">Prix TTC</th>
												<th width="15%">Statut</th>
												<th width="10%">#</th>
											</tr>
										</thead>
										<tbody>
			          					  <?php
			          						  $commandes = $bdd->query("SELECT * FROM ob_users_commande WHERE statut = '2' AND paiement = '1' AND hide = '0' ORDER BY id DESC");
			          						  while($c = $commandes->fetch(PDO::FETCH_OBJ)) {
			          					  ?>
			          					    <tr id="<?php echo $c->id; ?>" class="odd gradeX boutique">
			                          			<td><?php echo $c->id; ?></td>
			        						  	<td><?php echo $c->nom." ".$c->prenom; ?></td>
			        						  	<td><?php echo date('d/m/Y', $c->time); ?></td>
			        						  	<td><?php echo $c->articles; ?></td>
			        						  	<td><?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€</td>
					                          	<td>
					                            	<select data-id="<?php echo $c->id; ?>" id="deplace_commande" class="form-control">
					                             		<option value="0" <?php if($c->statut == 0) { echo "selected"; } ?>>Non traitée</option>
				                              			<option value="1" <?php if($c->statut == 1) { echo "selected"; } ?>>Expédiée</option> 
					                              		<option value="2" <?php if($c->statut == 2) { echo "selected"; } ?>>Reçue</option>
					                            	</select>
					                         	</td>
			        						  	<td class="center">
			        						    	<a class="btn btn-success btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?visualisation=<?php echo $c->id; ?>" data-original-title="Voir" data-toggle="tooltip"><i class="fa fa-search"></i></a>
			        						    	<a class="btn btn-warning btn-xs" href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>" data-original-title="Facture" data-toggle="tooltip"><i class="fa fa-file"></i></a>
			        						    	<a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/catalogue_commande.php?paiement=<?php echo $c->id; ?>&statut=0" data-original-title="Payée" data-toggle="tooltip"><i class="fa fa-credit-card"></i></a>
			        						  	</td>
			          					    </tr>
			          					  <?php } ?>
			          					</tbody>
									</table>              
								</div>
							</div>
						</div>
					<?php } else { ?>
						<div class="block-flat">
							<div class="content">
								<a href="<?php echo $admurl; ?>/catalogue_commande.php">
				                  <button type="button" class="btn btn-default"><i class="fa fa-caret-left"></i> Retour</button>
				                </a>
								<h3>Commande n°<?php echo $c->id; ?> - <?php echo mPaiement($c->paiement_default); ?></h3>
								<h4>Paiement : <?php if($c->paiement == 0){echo "<span style='color:red;'>Non réglée</span>";}elseif($c->paiement == 1){echo "<span style='color:green;'>Payée</span>";}elseif($c->paiement == 2){echo"<span style='color:red;'>Paiement refusé</span>";}?></h4>
								<p>
									Prix HT HD : <?php echo number_format($c->prixht, 2, ',', ' '); ?>€<br/>
									Prix HT DC : <?php echo number_format($c->prixdroits, 2, ',', ' '); ?>€<br/>
									Livraison HT : <?php echo number_format($c->livraison, 2, ',', ' '); ?>€<br/>
									Total TTC : <?php echo number_format($c->total_paiement, 2, ',', ' '); ?>€<br/>
								</p>
								<a target="_blank" href="<?php echo $url; ?>/pdf/facturation/<?php echo $c->id; ?>"><button type="button" class="btn btn-primary"><i class="fa fa-file"></i> Voir la facture</button></a>
								<h4>Date - <?php echo date('d/m/Y', $c->time); ?></h4>
								<h4>Statut</h4>
								<form class="form-horizontal group-border-dashed" action="#" style="border-radius: 0px;">
	                            	<div class="form-group">
						                <label class="col-sm-3 control-label">Statut</label>
						                <div class="col-sm-6">
						                  <select data-id="<?php echo $c->id; ?>" id="deplace_commande" class="form-control">
						                    <option value="0" <?php if($c->statut == 0) { echo "selected"; } ?>>Non traitée</option>
		                            		<option value="1" <?php if($c->statut == 1) { echo "selected"; } ?>>Expédiée</option> 
		                              		<option value="2" <?php if($c->statut == 2) { echo "selected"; } ?>>Reçue</option>
						                  </select>									
						                </div>
						            </div>
						            <div class="form-group">
						            	<label class="col-sm-3 control-label">Paiement</label>
						            	<div class="col-sm-6">
						               		<a href="<?php echo $admurl; ?>/catalogue_commande.php?paiement=<?php echo $c->id; ?>&statut=0"><button type="button" class="btn btn-primary"><i class="fa fa-credit-card"></i> Non payée</button></a>
						               	</div>
						            </div>
						        </form>
						        <h4>Coordonnées</h4>
						        <div>
									<div>
										<article>
									    	<div>
									        	<label>
									          		<h5><?php echo $c->email; ?></h5>
									          		<div><?php echo $c->prenom." ".strtoupper($c->nom); ?><br/><?php echo $c->phone; ?><br/><?php echo @$c->entreprise; ?>
									        	</label>
									      	</div>
										</article>
									</div>
								</div>
								<h4>Adresses</h4>
								<div>
									<div>
										<article>
									    	<div>
									        	<label>
									          		<h5>Adresse de livraison</h5>
									          		<div><?php echo $c->prenom_l; ?> <?php echo strtoupper($c->nom_l); ?><br><?php echo $c->adresse_l; ?><br><?php echo $c->adressec_l; ?><br><?php echo strtoupper($c->codepostal_l); ?> <?php echo strtoupper($c->ville_l); ?><br><?php echo $c->pays_l; ?><br><?php echo $c->phone_l; ?><br><?php echo @$c->entreprise_l; ?><br><?php if(@$c->numerotva_l) { ?>Numéro TVA <?php echo $c->numerotva_l; ?><br><?php } ?></div>
													<p><?php echo @$c->message_livraison; ?>
									        	</label>
									      	</div>
										</article>
									</div>
									<div>
										<article>
									    	<div>
									        	<label>
									          		<h5>Adresse de facturation</h5>
									          		<div><?php echo $c->prenom_f; ?> <?php echo strtoupper($c->nom_f); ?><br><?php echo $c->adresse_f; ?><br><?php echo $c->adressec_f; ?><br><?php echo strtoupper($c->codepostal_f); ?> <?php echo strtoupper($c->ville_f); ?><br><?php echo $c->pays_f; ?><br><?php echo $c->phone_f; ?><br><?php echo @$c->entreprise_f; ?><br><?php if(@$c->numerotva_f) { ?>Numéro TVA <?php echo $c->numerotva_f; ?><?php } ?></div>
									        	</label>
									      	</div>
										</article>
									</div>
								</div>
								<h4>Produits (<?php echo $c->articles; ?>)</h4>
								<?php
									$element = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
									$element->bindParam(":commandeid", $c->id);
									$element->execute();
									/* CHECK CONSIGNE */
									$consigne = FALSE;
									while($e = $element->fetch(PDO::FETCH_OBJ)) {
										if($e->consigne_caisse != 0) {$consigne = TRUE;}
									}
								?>
								<table>
									<thead>
										<tr>
											<?php if($consigne) { ?>
												<th width='25%' align="center">Produit</th>
												<th width='12.5%'>Contenance</th>
												<th width='10%'>% Alcool</th>
												<th width='10%'>Prix HT HD</th>
												<th width='10%'>Droits accise</th>
												<th width='10%'>Prix HT DC</th>
												<th width='10%'>Consigne</th>
												<th width='12.5%'>Quantité</th>
											<?php } else { ?>
												<th width='25%'>Produit</th>
												<th width='15%'>Contenance</th>
												<th width='10%'>% Alcool</th>
												<th width='12.5%'>Prix HT HD</th>
												<th width='10%'>Droits accise</th>
												<th width='12.5%'>Prix HT DC</th>
												<th width='15%'>Quantité</th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php
											$element = $bdd->prepare("SELECT * FROM ob_users_commande_element WHERE commandeid = :commandeid");
											$element->bindParam(":commandeid", $c->id);
											$element->execute();
											while($e = $element->fetch(PDO::FETCH_OBJ)) {
									  	?>
											<tr align="center">
												<td><?php echo $e->nom; ?><br style='margin: 0';/><?php echo $e->nom_sup; ?></td>
												<td><?php echo Conditionnement($e->condition_vente,$e->uv_caisse,$e->contenance); ?></td>
												<td><?php echo number_format($e->degre, 1, ',', ' '); ?>°</td>
												<td><?php echo number_format($e->prix_ht, 2, ',', ' '); ?>€</td>
												<td><?php echo number_format($e->droits, 2, ',', ' '); ?>€</td>
												<td><?php echo number_format($e->prix_ht+$e->droits, 2, ',', ' '); ?>€</td>
												<?php if($consigne) { ?><td ><?php echo number_format($e->consigne_caisse, 2, ',', ' '); ?>€</td><?php } ?>
												<td><?php echo $e->qte; ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					<?php } ?>
					<!-- Modal -->
					<div class="md-modal md-effect-1" id="mod-confirm">
		              <div class="md-content">
		                <div class="modal-header">
		                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                </div>
		                <div class="modal-body">
		                  <div class="text-center">
		                    <div id="icon" class="i-circle warning"><i class="fa fa-exclamation"></i></div>
		                    <h4 id="titre">Supprimer cette commande</h4>
		                    <p id="message">Êtes-vous sûr de vouloir supprimer cette commande ?</p>
		                  </div>
		                </div>
		                <div class="modal-footer">
		                  <button type="button" id="confirm-commande" class="btn btn-success btn-flat">Supprimer</button>
		                  <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Fermer</button>
		                </div>
		              </div>
		            </div>
		            <div class="md-modal md-effect-1" id="mod-message">
						<div class="md-content">
							<div class="modal-header">
								<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
							</div>
							<div class="modal-body">
								<div class="text-center">
									<div id="icon" class="i-circle success"><i class="fa fa-check"></i></div>
									<h4 id="titre"></h4>
									<p id="message"></p>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Fermer</button>
							</div>
						</div>
					</div>
		            <div class="md-overlay"></div>	 
				</div>
			</div>
		</div>
	</div> 
	<script src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script>
	<script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script>
	<script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script>  
	<script type="text/javascript" src="js/behaviour/general.js"></script>
	<script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script>
	<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script>
	<script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script>
	<script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script src="js/jquery.select2/select2.min.js" type="text/javascript"></script>
	<script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
        //initialize the javascript
        App.init();
        App.dataTables();

        $('.dataTables_filter input').addClass('form-control').attr('placeholder','Rechercher');
        $('.dataTables_length select').addClass('form-control');		
    });
</script>
<script src="js/behaviour/voice-commands.js"></script>
<script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
