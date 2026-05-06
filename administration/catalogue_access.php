<?php
  require("./includes/configuration.php");
  require("./includes/functions.php");
  
  if(!isset($_SESSION['username'])) {
  	header("Location: ".$admurl."/login.php");
  }

  $modification = FALSE;
  if(isset($_GET['modification'])) {
  	$access = $bdd->prepare("SELECT * FROM ob_users WHERE id = :id");
  	$access->bindParam(":id", htmlentities($_GET['modification']));
  	$access->execute();
  	if($access->rowCount() > 0) {
  		$a = $access->fetch(PDO::FETCH_OBJ);
  		$modification = TRUE;
  	} else {
  		header("Location: ".$admurl."/catalogue_access.php");
  		exit();
  	}
  }

  $menu = "catalogue_access";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<meta name="description" content="">
  	<meta name="author" content="">
  	<link rel="shortcut icon" href="images/favicon.png">

  	<title><?php echo $sitename; ?> | Accès</title>
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
				<li class="active">Gérer les accès</li>
			</ol>
		</div>
		<div class="cl-mcont">		
			<div class="row wizard-row">
				<div class="col-md-12">
					<?php if(!$modification) { ?>
						<div class="block-flat">
							<div class="content">
								<h3>Liste des utilisateurs</h3>
								<div>
									<table class="table table-bordered" id="datatable">
										<thead>
											<tr>
												<th width="15%">N° Client</th>
												<th width="15%">Nom - Prénom</th>
												<th width="20%">Email</th>
												<th width="20%">Téléphone</th>
												<th width="10%">Entreprise</th>
												<th width="10%">Catégorie</th>
												<th width="10%">#</th>
											</tr>
										</thead>
										<tbody>
				          					<?php
				          						$utilisateurs = $bdd->query("SELECT * FROM ob_users ORDER BY nom");
				          						while($p = $utilisateurs->fetch(PDO::FETCH_OBJ)) {
				          					?>
				          					    <tr id="<?php echo $p->id; ?>" class="odd gradeX commande">
				                          			<td><?php echo $p->numero_client; ?></td>
				        						  	<td><?php echo $p->nom." - ".$p->prenom; ?></td>
				        						  	<td><?php echo $p->email; ?></td>
				        						  	<td><?php echo $p->phone; ?></td>
				        						  	<td><?php echo $p->entreprise; ?></td>
				        						  	<td><?php echo CategorieClient($p->categorie); ?></td>
				        						  	<td class="center">
				        						    	<a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/catalogue_access.php?modification=<?php echo $p->id; ?>" data-original-title="Modifier" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>
				        						  	</td>
				          					    </tr>
				          					<?php } ?>
	          							</tbody>
									</table>              
								</div>
							</div>
						</div> 
					<?php } else { ?>
						<form id="catalogue_access" class="form-horizontal group-border-dashed"> 
		                    <div class="form-group no-padding">
		                      <div class="col-sm-12">
		                        <h3 class="hthin"><?php echo $a->email; ?></h3>
		                        <h4 class="hthin"><?php echo $a->prenom." ".$a->nom; if(!empty($a->entreprise)) { echo " - ".$a->entreprise; } ?></h4>
		                      </div>
		                    </div>
		                    <div class="form-group">
		                      <label class="col-sm-2 control-label">Accès catalogue</label>
		                      <div class="col-sm-9">
		                        <div class="radio"> 
		                          <label> <input type="checkbox" name="access" class="icheck" <?php if($a->catalogue == "1") {echo "checked";} ?>></label> 
		                        </div>
		                      </div>
		                    </div>
		                    <div class="form-group">
		                      <label class="col-sm-2 control-label">Mode de paiement</label>
		                      <div class="col-sm-9">
		                        <select name="paiement" class="form-control">
		                        	<?php echo $a->paiement_default; ?>
                             		<option value="0" <?php if($a->paiement_default == "0") {echo "selected";} ?>>Virement comptant</option>
                          			<option value="1" <?php if($a->paiement_default == "1") {echo "selected";} ?>>Traite 30 jours</option>	
                          			<option value="2" <?php if($a->paiement_default == "2") {echo "selected";} ?>>Virement 30 jours</option>	
                            	</select>
		                      </div>
		                    </div>
		                    <div class="form-group">
		                      <label class="col-sm-2 control-label">N° Client</label>
		                      <div class="col-sm-9">
		                        <input name="numero_client" type="text" class="form-control" placeholder="N° Client" value="<?php echo $a->numero_client; ?>">
		                      </div>
		                    </div>
		                    <div class="form-group">
		                      <label class="col-sm-2 control-label">Catégorie Client</label>
		                      <div class="col-sm-9">
		                        <select name="categorie" class="form-control">
		                        	<?php
										$i = 1;
										while($i < CategorieClient(0)) {
											echo "<option value='".$i."' ".(($i == $a->categorie) ? "selected" : "").">".CategorieClient($i)."</option>";
											$i++;
										}
									?>            
                            	</select>
		                      </div>
		                    </div>
		                    <div class="col-sm-9">
		                    	<input name="id" type="hidden" value="<?php echo $a->id; ?>">
		                        <button id="valider" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Modifier</button>
		                    </div>
                   		</form>
					<?php } ?>
					<!-- Modal -->
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
    <script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
    <script src="js/jquery.select2/select2.js" type="text/javascript"></script>
    <script src="js/jquery.select2/select2_locale_fr.js" type="text/javascript"></script>
    <script src="js/jquery.parsley/dist/parsley.js" type="text/javascript"></script>
    <script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script>   
    <script type="text/javascript" src="js/bootstrap.summernote/dist/summernote.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.wysihtml5/dist/wysihtml5-0.3.0.js"></script>
    <script type="text/javascript" src="js/bootstrap.wysihtml5/dist/bootstrap3-wysihtml5.all.min.js"></script>
    <script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
    <script type="text/javascript" src="js/jquery.datatables/jquery.datatables.min.js"></script>
	<script type="text/javascript" src="js/jquery.datatables/bootstrap-adapter/js/datatables.js"></script>
    <script src="js/ckeditor/ckeditor.js"></script>
    <script src="js/ckeditor/adapters/jquery.js"></script>
    <script src="js/modernizr.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/fuelux/wizard.js"></script>
    <script type="text/javascript" src="js/behaviour/general.js"></script>
    <script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script>
    <script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script>
    <script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
    <script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.js"></script>
    <script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script>
    <script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
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
