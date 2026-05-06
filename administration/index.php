<?php
  require("./includes/configuration.php");
  
  if(!isset($_SESSION['username'])) {
	header("Location: ".$admurl."/login.php");
	exit();
  }

  $menu = "accueil";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $sitename; ?> | Accueil</title>
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Raleway:100' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700' rel='stylesheet' type='text/css'>

    <!-- Bootstrap core CSS -->
    <link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet" />
	<link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
	<link rel="stylesheet" type="text/css" href="js/jquery.gritter/css/jquery.gritter.css" />

	<link rel="stylesheet" type="text/css" href="js/jquery.nanoscroller/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="js/jquery.easypiechart/jquery.easy-pie-chart.css" />
	<link rel="stylesheet" type="text/css" href="js/bootstrap.switch/bootstrap-switch.css" />
	<link rel="stylesheet" type="text/css" href="js/jquery.niftymodals/css/component.css" />
	<link rel="stylesheet" type="text/css" href="js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" href="js/jquery.select2/select2.css" />
	<link rel="stylesheet" type="text/css" href="js/bootstrap.slider/css/slider.css" />
	<link rel="stylesheet" type="text/css" href="js/intro.js/introjs.css" />
	<!-- Custom styles for this template -->
	<link href="css/style.css" rel="stylesheet" />
  </head>
  <body>
    <?php require("header.php"); ?>
    <div class="container-fluid" id="pcont">
	  <div class="cl-mcont">
		<div class="dash-cols">
		  <div class="col-sm-6 col-md-6">
		  	<!-- VISITEURS / PAGES VUES
			<div class="block-flat">
			  <h2>Visiteurs / Pages vues</h2>
			  <form style="margin-bottom: 5px;" id="visiteurs_form">
				<div id="message" class="alert alert-danger" style="display: none;">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				  <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Aucune donnée reçu pour cette période.
				</div>
				<select name="mois" class="form-control" style="margin-bottom: 5px;"><?php foreach($mois as $m => $v) { echo "<option value='".$v."' ".(($v == date("m")) ? 'selected' : false).">".$m."</option>"; } ?></select>
				<select name="annee" class="form-control"><?php $a = date("Y"); for($i = 0; $i <= 5; $i++) { echo "<option value='".($a-$i)."'>".($a-$i)."</option>"; }?></select>
			  </form>
			  <h3 class="text-center mois_visiteurs"></h3>
			  <div class="content red-chart">
				<div id="site_statistics" style="height: 250px;"></div>
			  </div>
			  <div class="content">
				<div class="stat-data">
				  <div class="stat-number">
					<div><h2 class="visit-jour"></h2></div>
					<div>Visiteurs<br /><span>Aujourd'hui</span></div>
				  </div>
				  <div class="stat-number">
				    <div><h2 class="pages-jour"></h2></div>
					<div>Pages vues<br /><span>Aujourd'hui</span></div>
				  </div>
				</div>
				<div class="stat-data">
				  <div class="stat-number">
					<div><h2 class="visit-mois"></h2></div>
					<div>Visiteurs<br /><span class="mois_visiteurs"></span></div>
				  </div>
				  <div class="stat-number">
				    <div><h2 class="pages-mois"></h2></div>
					<div>Pages vues<br /><span class="mois_visiteurs"></span></div>
				  </div>
				</div>
				<div class="clear"></div>
			  </div>
			</div>
			-->
			<div class="block-flat">
			  <h2>Catalogue téléchargé</h2>
			  <form style="margin-bottom: 5px;" id="catalogue_form">
				<div id="message" class="alert alert-danger" style="display: none;">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				  <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Aucune donnée reçu pour cette période.
				</div>
				<select name="mois" class="form-control" style="margin-bottom: 5px;"><?php foreach($mois as $m => $v) { echo "<option value='".$v."' ".(($v == date("m")) ? 'selected' : false).">".$m."</option>"; } ?></select>
				<select name="annee" class="form-control"><?php $a = date("Y"); for($i = 0; $i <= 5; $i++) { echo "<option value='".($a-$i)."'>".($a-$i)."</option>"; }?></select>
			  </form>
			  <h3 class="text-center mois_telechargement"></h3>
			  <div class="content red-chart" style="padding: 20px;">
				<div id="catalogue_statistics" style="height: 250px;"></div>
			  </div>
			  <div class="content">
				<div class="stat-data" style="width: 100%;">
				  <div class="stat-number">
					<div><h2 class="telechargement-jour"></h2></div>
					<div>Téléchargements<br /><span>Aujourd'hui</span></div>
				  </div>
				  <div class="stat-number">
					<div><h2 class="telechargement-mois"></h2></div>
					<div>Téléchargements<br /><span class="mois_telechargement"></span></div>
				  </div>
				</div>
				<div class="clear"></div>
			  </div>
			  <h4>Liste des téléchargements</h4>
			  <ul class="list-group catalogue-list">
				<li id="chargement" class="list-group-item">Chargement...</li>			  
			  </ul>
			</div>
			<div class="block-flat">
			  <h2>Location tireuse</h2>
			  <form style="margin-bottom: 5px;" id="location_form">
				<div id="message" class="alert alert-danger" style="display: none;">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				  <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Aucune donnée reçu pour cette période.
				</div>
				<select name="mois" class="form-control" style="margin-bottom: 5px;"><?php foreach($mois as $m => $v) { echo "<option value='".$v."' ".(($v == date("m")) ? 'selected' : false).">".$m."</option>"; } ?></select>
				<select name="annee" class="form-control"><?php $a = date("Y"); for($i = 0; $i <= 5; $i++) { echo "<option value='".($a-$i)."'>".($a-$i)."</option>"; }?></select>
			  </form>
			  <h3 class="text-center mois_location"></h3>
			  <div class="content blue-chart">
				<div id="site_statistics4" style="height: 250px;"></div>
			  </div>
			  <div class="content">
				<div class="stat-data" style="width: 100%;">
				  <div class="stat-number">
					<div><h2 class="location-jour"></h2></div>
					<div>Demande<br /><span>Aujourd'hui</span></div>
				  </div>
				  <div class="stat-number">
					<div><h2 class="location-mois"></h2></div>
					<div>Demande<br /><span class="mois_location"></span></div>
				  </div>
				</div>
				<div class="clear"></div>
			  </div>
			</div>
		  </div>
		  <div class="col-sm-6 col-md-6">
			<div class="block-flat">
			  <h2>Inscription</h2>
			  <form style="margin-bottom: 5px;" id="inscription_form">
				<div id="message" class="alert alert-danger" style="display: none;">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				  <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Aucune donnée reçu pour cette période.
				</div>
				<select name="mois" class="form-control" style="margin-bottom: 5px;"><?php foreach($mois as $m => $v) { echo "<option value='".$v."' ".(($v == date("m")) ? 'selected' : false).">".$m."</option>"; } ?></select>
				<select name="annee" class="form-control"><?php $a = date("Y"); for($i = 0; $i <= 5; $i++) { echo "<option value='".($a-$i)."'>".($a-$i)."</option>"; }?></select>
			  </form>
			  <h3 class="text-center mois_inscr"></h3>
			  <div class="content blue-chart">
				<div id="site_statistics2" style="height: 250px;"></div>
			  </div>
			  <div class="content">
				<div class="stat-data" style="width: 100%;">
				  <div class="stat-number">
					<div><h2 class="inscr-jour"></h2></div>
					<div>Inscription<br /><span>Aujourd'hui</span></div>
				  </div>
				  <div class="stat-number">
					<div><h2 class="inscr-mois"></h2></div>
					<div>Inscription<br /><span class="mois_inscr"></span></div>
				  </div>
				</div>
				<div class="clear"></div>
			  </div>
			  <h4>Liste des inscriptions</h4>
			  <ul class="list-group inscription-list">
				<li id="chargement" class="list-group-item">Chargement...</li>			  
			  </ul>
			</div>
			<div class="block-flat">
			  <h2>Notre expérience</h2>
			  <form id="experience_form">
				<div id="message" class="alert alert-danger" style="display: none;">
				  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				  <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Aucune donnée reçu pour cette période.
				</div>
				<select name="mois" class="form-control" style="margin-bottom: 5px;"><?php foreach($mois as $m => $v) { echo "<option value='".$v."' ".(($v == date("m")) ? 'selected' : false).">".$m."</option>"; } ?></select>
				<select name="annee" class="form-control"><?php $a = date("Y"); for($i = 0; $i <= 5; $i++) { echo "<option value='".($a-$i)."'>".($a-$i)."</option>"; }?></select>
			  </form>
			  <h3 class="text-center" id="mois_experience"></h3>
			  <div id="site_statistics3" style="height:500px;margin-top:25px;"></div>
			  <?php if($ua->acces_stats == 1) { ?>
				  <hr/>
				  <h3>Ajouter une option à l'enquête</h3>
				  <form id="enquete_ajouter">
					<input name="type" type="text" class="form-control" placeholder="Type" style="margin-bottom: 5px;">
					<button id="valider" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Valider</button>
				  </form>
				  <div class="table-responsive">
					<table class="table no-border hover">
					  <thead class="no-border">
						<tr>
						  <th style="width:85%;"><strong>Type</strong></th>
						  <th style="width:15%;" class="text-center"><strong>Action</strong></th>
						</tr>
					  </thead>
					  <tbody class="no-border-y">
					  	<?php
					  		$enquete = $bdd->query("SELECT * FROM ob_enquete_type ORDER BY id DESC");
					  		while($e = $enquete->fetch(PDO::FETCH_OBJ)) {
					  	?>
						  <tr class="enquete" id="<?php echo $e->id; ?>">
						    <td><?php echo $e->type; ?></td>
						    <td class="text-center"><a class="btn btn-danger btn-xs delete-enquete" data-id="<?php echo $e->id; ?>" data-original-title="Supprimer" data-toggle="tooltip"><i class="fa fa-times"></i></a></td>
						  </tr>
						<?php } ?>
					  </tbod>
					</table>
				  </div>
			  <?php } ?>
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
            <div class="md-overlay"></div>
		  <!-- Modal -->
            <div class="md-modal md-effect-1" id="mod-confirm">
              <div class="md-content">
                <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                  <div class="text-center">
                    <div id="icon" class="i-circle warning"><i class="fa fa-exclamation"></i></div>
                    <h4 id="titre">Supprimer une enquête</h4>
                    <p id="message">Êtes-vous sûr de vouloir supprimer cette enquête ?</p>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" id="confirm-enquete" class="btn btn-success btn-flat">Supprimer</button>
                  <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Fermer</button>
                </div>
              </div>
            </div>
            <div class="md-overlay"></div>	
	    </div>
	  </div>
	</div>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.gritter/js/jquery.gritter.js"></script>
	<script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script>
	<script type="text/javascript" src="js/behaviour/general.js"></script>
	<script type="text/javascript" src="js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
	<script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script>  
	<script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script>
	<script type="text/javascript" src="js/jquery.easypiechart/jquery.easy-pie-chart.js"></script>
	<script type="text/javascript" src="js/jquery.nestable/jquery.nestable.js"></script>
	<script type="text/javascript" src="js/bootstrap.switch/bootstrap-switch.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script src="js/jquery.select2/select2.min.js" type="text/javascript"></script>
	<script src="js/skycons/skycons.js" type="text/javascript"></script>
	<script src="js/intro.js/intro.js" type="text/javascript"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script>
	<!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript">
      $(document).ready(function(){
        //initialize the javascript
        App.init({
          nanoScroller: false
        });
        
        App.dashBoard();        
      });
    </script>
    <script src="js/behaviour/voice-commands.js"></script>
	<script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>
  </body>
</html>
