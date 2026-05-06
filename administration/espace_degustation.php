<?php
  require("./includes/configuration.php");
  
  if(!isset($_SESSION['username'])) {
    header("Location: ".$admurl."/login.php");
    exit();
  }

  if(isset($_FILES['espace_degustation']['name'])) {
    if(empty($_FILES['espace_degustation']['name'])) {
      $message = '<div class="alert alert-danger alert-white rounded">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div class="icon"><i class="fa fa-info-close"></i></div>
                    <strong>Erreur!</strong> Veuillez remplir les champs vides.
                  </div>';
      $couleur = "rouge";
    } else {
      $info = pathinfo($_FILES["espace_degustation"]["name"]);
      $extentions = $info["extension"];
      $extentionsAutorises = array('pdf','xlsx');
      if(!in_array($extentions,$extentionsAutorises)) {
        $message = '<div class="alert alert-danger alert-white rounded">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div class="icon"><i class="fa fa-info-close"></i></div>
                    <strong>Erreur!</strong> Le format de la carte n\'est pas supporté. Format autorisé : pdf, xlsx.
                  </div>';
      } else {
        // IMPORTATION DU CATALOGUE
          // GENERATION NOM CATALOGUE
          $nom_carte = "Carte_La_Cave_d'Occitanie.".$info["extension"];
        // GENERATION LIEN CATALOGUE
          $upload = '../www/gallery/espace_degustation/';
          $pdf_lien = str_replace("../www/gallery", $gallery, $upload);
          $pdf_lien = $pdf_lien."/".$nom_carte;
          $tmp_name = $_FILES["espace_degustation"]["tmp_name"];
        if(!move_uploaded_file($tmp_name, "$upload/$nom_carte")) {
          $message = '<div class="alert alert-danger alert-white rounded">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div class="icon"><i class="fa fa-info-close"></i></div>
                    <strong>Erreur!</strong> Erreur lors de l\'importation de la carte, veuillez réessayer.
                  </div>';
        } else {
          $espace_degustation = $bdd->prepare("UPDATE ob_espace_degustation SET lien = :lien, time = '".time()."'");
          $espace_degustation->bindParam(":lien", $pdf_lien);
          $espace_degustation->execute();

          $message = '<div class="alert alert-success alert-white rounded">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div class="icon"><i class="fa fa-check"></i></div>
                    <strong>Bravo!</strong> La carte a bien été modifiée.
                  </div>';
        }
      } 
    }   
  }
  
  $menu = "espace_degustation";
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png">

    <title><?php echo $sitename; ?> | Carte espace dégustation</title>
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
    <link rel="stylesheet" type="text/css" href="js/bootstrap.wysihtml5/dist/bootstrap3-wysihtml5.min.css"></link>
    <link rel="stylesheet" href="fonts/font-awesome-4/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/pygments.css">
    <link rel="stylesheet" type="text/css" href="js/jquery.gritter/css/jquery.gritter.css" />

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="js/jquery.niftymodals/css/component.css" />
    <link rel="stylesheet" type="text/css" href="js/jquery.easypiechart/jquery.easy-pie-chart.css" />
    <link rel="stylesheet" type="text/css" href="js/jquery.nanoscroller/nanoscroller.css" />
    <link rel="stylesheet" type="text/css" href="js/bootstrap.switch/bootstrap-switch.css" />
    <!-- Custom styles for this template -->
    <link href="js/jquery.icheck/skins/square/blue.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" />
    <style>.wysihtml5-toolbar a {color: #333;}.wysihtml5-toolbar a:hover {color: inherit;text-decoration: none;}</style>
  </head>
  <body>
    <?php require("header.php"); ?>
    <div class="container-fluid" id="pcont">
      <div class="page-head">
        <h2>Modification de la carte espace dégustation</h2>
        <ol class="breadcrumb">
          <li><a href="<?php echo $admurl; ?>">Accueil</a></li>  
          <li class="active">Carte espace dégustation</li>
        </ol>
      </div>
      <div class="cl-mcont">		
        <div class="row">
          <div class="col-md-12">
            <?php if(isset($message)) {echo $message;} ?>
            <div class="block-flat">
              <form method="post" class="form-horizontal group-border-dashed" enctype="multipart/form-data"> 
                <div class="form-group">
                  <label class="col-sm-2 control-label">Carte espace dégustation</label>
                  <div class="col-sm-9">
                    <input type="file" class="btn btn-primary" name="espace_degustation"/>
                  </div>
                </div>
                <div class="form-group">
                  <button id="valider" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Valider</button>
                </div>
              </form>
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
    <script type="text/javascript" src="js/bootstrap.wysihtml5/dist/wysihtml5-0.3.0.js"></script>
    <script type="text/javascript" src="js/bootstrap.wysihtml5/dist/bootstrap3-wysihtml5.all.min.js"></script>
    <script type="text/javascript" src="js/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script type="text/javascript" src="js/jquery.icheck/icheck.min.js"></script>
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
      $(document).ready(function() {
        //initialize the javascript
        App.init();
        App.wizard();
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