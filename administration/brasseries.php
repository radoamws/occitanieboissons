<?php
  require("./includes/configuration.php");
  
  if(!isset($_SESSION['username'])) {
	header("Location: ".$admurl."/login.php");
  }
  $menu = "brasseries";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $sitename; ?> | Brasseries</title>
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
    <link rel="stylesheet" type="text/css" href="js/jquery.niftymodals/css/component.css" />
    <link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/pygments.css">
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
       <h2>Gérer les brasseries</h2>
        <ol class="breadcrumb">
          <li><a href="<?php echo $admurl; ?>">Acceuil</a></li>
          <li class="active">Brasseries</li>
        </ol>
      </div>
      <div class="cl-mcont">		
        <div class="row wizard-row">
          <div class="col-md-12">
		    <div class="block-flat">
			  <div class="content">
			    <div>
				  <table class="table table-bordered" id="datatable">
  					<thead>
  					  <tr>
  						<th>Nom</th>
  						<th>Pays</th>
  						<th>Publication</th>
  						<th>Auteur</th>
  						<th width="8%">#</th>
  					  </tr>
  					</thead>
  					<tbody>
  					  <?php
  						$brasseries = $bdd->query("SELECT * FROM ob_brasseries ORDER BY name DESC");
  						while($b = $brasseries->fetch(PDO::FETCH_OBJ)) {
  					  ?>
  					    <tr id="<?php echo $b->id; ?>" class="odd gradeX articles">
  						  <td><?php echo $b->name; ?></td>
  						  <td><?php echo $b->country; ?></td>
  						  <td><?php echo $b->date_time; //echo date("Y/m/d H:i", $b->date_time); ?></td>
  						  <td><?php echo $b->author; ?></td>
  						  <td class="center">
  						    <a class="btn btn-danger btn-xs delete-brasserie" data-id="<?php echo $b->id; ?>" data-original-title="Supprimer" data-toggle="tooltip"><i class="fa fa-times"></i></a>
  						    <a class="btn btn-primary btn-xs" href="<?php echo $admurl; ?>/brasseries_modif.php?modif=<?php echo $b->id; ?>" data-original-title="Modifier" data-toggle="tooltip"><i class="fa fa-pencil"></i></a>
  						  </td>
  					    </tr>
  					  <?php } ?>
  					</tbody>
  				  </table>							
				  </div>
			  </div>
			</div>	
			<!-- Modal -->
            <div class="md-modal md-effect-1" id="mod-confirm">
              <div class="md-content">
                <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                  <div class="text-center">
                    <div id="icon" class="i-circle warning"><i class="fa fa-exclamation"></i></div>
                    <h4 id="titre">Supprimer une brasserie</h4>
                    <p id="message">Êtes-vous sûr de vouloir supprimer cette brasserie ?</p>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="confirm-brasseries" class="btn btn-success btn-flat">Supprimer</button>
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
	<script type="text/javascript" src="js/behaviour/general.js"></script>
	<script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script> 
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
