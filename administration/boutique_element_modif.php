<?php
  require("./includes/configuration.php");

  if(!isset($_SESSION['username'])) {
    header("Location: ".$admurl."/login.php");
    exit();
  }

  $menu = "boutique";

  if(isset($_GET['modif'])) {
      if(!intval($_GET['modif'])) {
        header("Location: ".$admurl."/boutique.php");
        exit();
      } else {
        $elements = $bdd->prepare("SELECT * FROM ob_boutique_produit_element WHERE id = :id");
        $elements->bindParam(":id", $_GET['modif']);
        $elements->execute();
        if($elements->rowCount() < 1) {
          header("Location: ".$admurl."/boutique.php");
          exit();
        } else {
          $e = $elements->fetch(PDO::FETCH_OBJ); 
        }
      }
    } else {
      header("Location: ".$admurl."/boutique.php");  
      exit();
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="images/favicon.png">

  <title><?php echo $sitename; ?> | Modification d'un élément</title>
  <link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,400italic,700,800' rel='stylesheet' type='text/css'>
  <link href='//fonts.googleapis.com/css?family=Raleway:300,200,100' rel='stylesheet' type='text/css'>

  <!-- DateRange -->
  <link rel="stylesheet" type="text/css" href="js/bootstrap.daterangepicker/daterangepicker-bs3.css" />

  <!-- Bootstrap core CSS -->
  <link href="js/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="js/bootstrap.datetimepicker/css/bootstrap-datetimepicker.min.css" />
  <link rel="stylesheet" type="text/css" href="js/jquery.select2/select2.css" />
  <link rel="stylesheet" type="text/css" href="js/bootstrap.slider/css/slider.css" />
  <link rel="stylesheet" type="text/css" href="css/wizard.css"></link>
  <link rel="stylesheet" type="text/css" href="js/bootstrap.wysihtml5/dist/bootstrap3-wysihtml5.min.css"></link>
  <link rel="stylesheet" type="text/css" href="js/bootstrap.summernote/dist/summernote.css" />
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
        <h2>Modification d'un élément</h2>
        <ol class="breadcrumb">
          <li><a href="<?php echo $admurl; ?>">Accueil</a></li>
          <li><a href="<?php echo $admurl; ?>/boutique.php">Boutique</a></li>
          <li class="active">Modification d'un élément</li>
        </ol>
      </div>
      <div class="cl-mcont">    
        <div class="row wizard-row">
          <div class="col-md-12 fuelux">
            <div class="block-wizard">
              <div id="boutique_produits_element" class="wizard wizard-ux">
                <ul class="steps">
                  <li data-target="#step1" class="active">Modification<span class="chevron"></span></li>
                </ul>
                <div class="actions">
                  <button type="button" class="btn btn-xs btn-prev btn-default"> <i class="fa fa-chevron-left"></i>Précédent</button>
                  <button type="button" class="btn btn-xs btn-next btn-default" data-last="none">Suivant<i class="fa fa-chevron-right"></i></button>
                </div>
              </div>
              <div class="step-content">
                <form id="boutique_element" class="form-horizontal group-border-dashed boutique" name="modifier"> 
                  <div class="step-pane active" id="step1">
                    <div class="form-group no-padding">
                      <div class="col-sm-12">
                        <h3 class="hthin">Modification d'un élément</h3>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Nom</label>
                      <div class="col-sm-9">
                        <input name="nom" type="text" class="form-control" placeholder="Nom" value="<?php echo $e->nom; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Description (facultatif)</label>
                      <div class="col-sm-9">
                        <input name="descr" type="text" class="form-control" placeholder="Description" value="<?php echo $e->description; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Contenance</label>
                      <div class="col-sm-9">
                        <input name="contenance" type="text" class="form-control" placeholder="Contenance" value="<?php echo $e->contenance; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Alcool (°)</label>
                      <div class="col-sm-9">
                        <input name="alcool" type="text" class="form-control" placeholder="Alcool" value="<?php echo $e->alcool; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Stock</label>
                      <div class="col-sm-9">
                        <input name="stock" type="text" class="form-control" placeholder="Stock" value="<?php echo $e->stock; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Prix HT (en €)</label>
                      <div class="col-sm-9">
                        <input name="prix" type="text" class="form-control" placeholder="Prix HT" value="<?php echo $e->prixht; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">TVA</label>
                      <div class="col-sm-9">
                        <input name="tva" type="text" data-mask="tva" class="form-control" placeholder="__,__%" value="<?php echo number_format($e->tva, 2, ',', ' '); ?>%">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Brasserie (facultatif)</label>
                      <div class="col-sm-9">
                        <input name="brasserie" class="brasseriesid" type="hidden" value="<?php echo $e->brasserie; ?>">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Cacher</label>
                      <div class="col-sm-9">
                        <div class="radio"> 
                          <label> <input type="checkbox" name="hide" class="icheck" <?php if($e->hide == "1"){echo "checked";} ?>></label> 
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-12"> 
                        <button id="valider" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Valider</button>
                        <input name="id" type="hidden" value="<?php echo $e->id; ?>">
                      </div>
                    </div>                  
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
    </div> 
    <script src="js/jquery.js"></script>
    <script src="js/jquery.ui/jquery-ui.js" type="text/javascript"></script>
    <script src="js/jquery.select2/select2.js" type="text/javascript"></script>
    <script src="js/jquery.select2/select2_locale_fr.js" type="text/javascript"></script>
    <script src="js/jquery.parsley/dist/parsley.js" type="text/javascript"></script>
    <script src="js/bootstrap.slider/js/bootstrap-slider.js" type="text/javascript"></script>
    <script src="js/jquery.maskedinput/jquery.maskedinput.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/jquery.niftymodals/js/jquery.modalEffects.js"></script>   
    <script type="text/javascript" src="js/bootstrap.summernote/dist/summernote.min.js"></script>
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
        App.masks();

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