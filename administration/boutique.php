  <?php
  require("./includes/configuration.php");
  
  if(!isset($_SESSION['username'])) {
	header("Location: ".$admurl."/login.php");
  }
  $menu = "boutique";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $sitename; ?> | Boutique</title>
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
          <li class="active">Boutique</li>
        </ol>
      </div>
      <div class="cl-mcont">		
        <div class="row wizard-row">
          <div class="col-md-12">
    		    <div class="block-flat">
    			    <div class="content">
                <h3>Gérer les produits</h3>
      			    <div>
        				  <table class="table table-bordered" id="datatable">
          					<thead>
          					  <tr>
          						<th width="20%">Nom</th>
          						<th width="20%">Categorie</th>
          						<th width="20%">Publication</th>
          						<th width="20%">Auteur</th>
                      <th width="12%">Caché</th>
          						<th width="8%">#</th>
          					  </tr>
          					</thead>
          					<tbody>
          					  <?php
          						  $produits = $bdd->query("SELECT * FROM ob_boutique_produit ORDER BY time");
          						  while($p = $produits->fetch(PDO::FETCH_OBJ)) {
          					  ?>
          					    <tr id="<?php echo $p->id; ?>" class="odd gradeX boutique">
            						  <td><?php echo $p->nom; ?></td>
            						  <td>
                            <?php echo $bdd->query("SELECT * FROM ob_boutique_categorie WHERE id = '".$p->categorie."'")->fetch(PDO::FETCH_OBJ)->nom; ?>
                          </td>
            						  <td><?php echo date("Y/m/d H:i", $p->time); ?></td>
            						  <td><?php echo $p->auteur; ?></td>
                          <td><?php echo $p->hide; ?></td>
            						  <td class="center">
            						    <a class="btn btn-danger btn-xs delete-produit" data-id="<?php echo $p->id; ?>" data-original-title="Supprimer" data-toggle="tooltip"><i class="fa fa-times"></i></a>
            						    <a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/boutique_produits_modif.php?modif=<?php echo $p->id; ?>" data-original-title="Modifier" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>
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
                <h3>Gérer les éléments</h3>
                <div>
                  <table class="table table-bordered" id="datatable2" >
                    <thead>
                      <tr>
                        <th width="20%">Nom</th> 
                        <th width="8%">Contenance</th>
                        <th width="8%">Alcool (en °)</th>
                        <th width="8%">Prix HT (en €)</th>
                        <th width="8%">TVA</th>
                        <th width="8%">Stock</th>
                        <th width="15%">Brasserie</th>
                        <th width="13%">Auteur</th>
                        <th width="8%">#</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $elements = $bdd->query("SELECT * FROM ob_boutique_produit_element ORDER BY time");
                        while($e = $elements->fetch(PDO::FETCH_OBJ)) {
                      ?>
                        <tr id="<?php echo $e->id; ?>" class="odd gradeX produit-element">
                          <td><span style="display: none;"><?php echo $e->nom; ?></span><input type="text" class="form-control modification-element" data-id="<?php echo $e->id; ?>" data-action="nom" placeholder="Nom" value="<?php echo $e->nom; ?>"></td>
                          <td><span style="display: none;"><?php echo $e->contenance; ?></span><input type="text" class="form-control modification-element" data-id="<?php echo $e->id; ?>" data-action="contenance" placeholder="Contenance" value="<?php echo $e->contenance; ?>"></td>
                          <td><span style="display: none;"><?php echo $e->alcool; ?></span><input type="text" class="form-control modification-element" data-id="<?php echo $e->id; ?>" data-action="alcool" placeholder="Alcool" value="<?php echo $e->alcool; ?>"></td>
                          <td><span style="display: none;"><?php echo $e->prixht; ?></span><input type="text" class="form-control modification-element" data-id="<?php echo $e->id; ?>" placeholder="Prix HT" data-action="prixht" value="<?php echo $e->prixht; ?>"></td>
                          <td><span style="display: none;"><?php echo $e->tva; ?></span><input type="text" data-mask="tva" data-action="tva" data-id="<?php echo $e->id; ?>" class="form-control modification-element" placeholder="__,__%" value="<?php echo number_format($e->tva, 2, ',', ' '); ?>%"></td>
                          <td><span style="display: none;"><?php echo $e->stock; ?></span><input class="form-control modification-element" data-id="<?php echo $e->id; ?>" data-action="stock" type="number" value="<?php echo $e->stock; ?>" min="0" placeholder="Stock"></td> 
                          <td>
                            <?php
                              $brasserieaffiche = explode(",", $e->brasserie);
                              $all_b = array();
                              foreach($brasserieaffiche as $b) {
                                $all_b[] = $bdd->query("SELECT nom FROM ob_brasseries WHERE id = '".$b."'")->fetch(PDO::FETCH_OBJ)->nom;
                              }
                              echo implode(", ", $all_b);
                           ?>
                          </td>
                          <td><?php echo $e->auteur; ?></td>
                          <td class="center">
                            <a class="btn btn-danger btn-xs delete-element" data-id="<?php echo $e->id; ?>" data-original-title="Supprimer" data-toggle="tooltip"><i class="fa fa-times"></i></a>
                            <a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/boutique_element_modif.php?modif=<?php echo $e->id; ?>" data-original-title="Modifier" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>              
                </div>
              </div>
            </div>  
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
            <div class="md-modal md-effect-1" id="mod-confirm">
              <div class="md-content">
                <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                  <div class="text-center">
                    <div id="icon" class="i-circle warning"><i class="fa fa-exclamation"></i></div>
                    <h4 id="titre">Supprimer un produit</h4>
                    <p id="message">Êtes-vous sûr de vouloir supprimer ce produit ?</p>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="confirm-produit" class="btn btn-success btn-flat">Supprimer</button>
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
  <script src="js/jquery.maskedinput/jquery.maskedinput.js" type="text/javascript"></script>
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
        App.masks();

	       $('.dataTables_filter input').addClass('form-control').attr('placeholder','Rechercher');
        $('.dataTables_length select').addClass('form-control');		
	  });
    </script>
    <script src="js/behaviour/voice-commands.js"></script>
    <script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
  </body>
</html>
