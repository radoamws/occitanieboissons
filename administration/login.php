<?php
  require("./includes/configuration.php");

  if(isset($_SESSION['username'])) {
	header("Location: ".$admurl);
	exit();
  }
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $sitename; ?> | Connexion</title>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Raleway:300,200,100' rel='stylesheet' type='text/css'>

    <!-- Bootstrap core CSS -->
    <link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet" />	
  </head>
  <body class="texture">
    <div id="cl-wrapper" class="login-container">
	  <div class="middle-login">
	    <div id="message"></div>
	    <div class="block-flat">
		  <div class="header">							
		    <h3 class="text-center"><img class="logo-img" src="images/logo.png" alt="logo"/>Administration <i class="fa fa-cog"></i></h3>
		  </div>
		  <div>
		    <form style="margin-bottom: 0px !important;" class="form-horizontal" id="login">
			  <div class="content">
			    <h4 class="title">Informations de connexion</h4>
			    <div class="form-group">
				  <div class="col-sm-12">
				    <div class="input-group">
				      <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
					  <input type="email" placeholder="Adresse email" name="email" id="username" class="form-control">
				    </div>
				  </div>
			    </div>
			    <div class="form-group">
				  <div class="col-sm-12">
				    <div class="input-group">
					  <span class="input-group-addon"><i class="fa fa-lock"></i></span>
					  <input type="password" placeholder="Mot de passe" name="mdp" id="password" class="form-control">
				    </div>
				  </div>
			    </div>
			  </div>
			  <div class="foot">
			    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Connexion</button>
			  </div>
		    </form>
		  </div>
	    </div>
	    <div class="text-center out-links"><a href="#">&copy; 2016-2017 <?php echo $sitename; ?></a></div>
	  </div> 	
    </div>
    <!-- JAVASCRIPT -->
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
	  });
    </script>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/behaviour/voice-commands.js"></script>
    <script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script>
    <script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script>
    <script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script>
    <script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>
  </body>
</html>
