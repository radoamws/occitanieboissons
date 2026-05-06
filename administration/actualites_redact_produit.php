<?php
  require("./includes/configuration.php");

  if(!isset($_SESSION['username'])) {
    header("Location: ".$admurl."/login.php");
    exit();
  }

  $menu = "actualites_produit";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="images/favicon.png">

  <title><?php echo $sitename; ?> | Actualités</title>
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
        <h2>Rédiger une actualité produit</h2>
        <ol class="breadcrumb">
          <li><a href="<?php echo $admurl; ?>">Accueil</a></li>
          <li><a href="<?php echo $admurl; ?>/actualites.php">Actualités</a></li>
          <li class="active">Rédaction</li>
        </ol>
      </div>
      <div class="cl-mcont">		
        <div class="row wizard-row">
          <div class="col-md-12 fuelux">
            <div class="block-wizard">
              <div id="actualites_redac" class="wizard wizard-ux">
                <ul class="steps">
                  <li data-target="#step1" class="active">Rédaction<span class="chevron"></span></li>
                  <li data-target="#step2">Images<span class="chevron"></span></li>
                  <li data-target="#step3">Prévisualisation<span class="chevron"></span></li>
                </ul>
                <div class="actions">
                  <button type="button" class="btn btn-xs btn-prev btn-default"> <i class="fa fa-chevron-left"></i>Précédent</button>
                  <button type="button" class="btn btn-xs btn-next btn-default" data-last="none">Suivant<i class="fa fa-chevron-right"></i></button>
                </div>
              </div>
              <div class="step-content">
                <form id="actualites_produit" class="form-horizontal group-border-dashed actualites" name="publier"> 
                  <div class="step-pane active" id="step1">
                    <div class="form-group no-padding">
                      <div class="col-sm-12">
                        <h3 class="hthin">Rédaction</h3>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Titre</label>
                      <div class="col-sm-9">
                        <input name="titre" type="text" class="form-control" placeholder="Titre">
                      </div>
                    </div>	
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Brasseries</label>
                      <div class="col-sm-9">
                        <input name="brasseries" class="brasseries" type="hidden">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Tags</label>
                      <div class="col-sm-9">
                        <input name="tags" class="tags" type="hidden"></input> 
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Contenu</label>
                      <div class="col-sm-9">
                        <textarea style="height: 250px;" name="contenu" id="contenu" class="form-control"></textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Elements (facultatif)</label>
                      <div class="col-sm-9">
                        <input name="element" class="element" type="hidden">
                      </div>
                    </div> 
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Cacher</label>
                      <div class="col-sm-9">
                        <div class="radio"> 
                          <label> <input type="checkbox" name="hide" class="icheck"></label> 
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-12"> 
                        <button data-wizard="#actualites_redac" class="btn btn-primary wizard-next">Étape suivante <i class="fa fa-caret-right"></i></button>
                      </div>
                    </div>									
                  </div>
                  <div class="step-pane" id="step2">
                    <div class="form-group no-padding">
                      <div class="col-sm-12">
                        <h3 class="hthin">Images</h3>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Image principale</label>
                      <div class="col-sm-9">
                        <input class="btn btn-primary" type="file" name="image" id="image">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="image_preview" class="col-sm-9">
                        <div class="thumbnail hidden">
                          <img style="display: inline-table;height: 200px;" />
                          <div style="display: inline-table;" class="caption">
                            <h4></h4>
                            <p></p>
                            <button type="button" class="btn btn-default"><i class="fa fa-times"></i>Supprimer</button>
                          </div>
                          <div style="clear: both;"></div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-12">
                        <button data-wizard="#actualites_redac" class="btn btn-default wizard-previous"><i class="fa fa-caret-left"></i> Retour</button>
                        <button data-wizard="#actualites_redac" class="btn btn-primary wizard-next">Étape suivante <i class="fa fa-caret-right"></i></button>
                      </div>
                    </div>	
                  </div>
                  <div class="step-pane" id="step3">
                    <div class="form-group no-padding">
                      <div class="col-sm-12">
                        <h3 class="hthin">Prévisualisation - Newsletter</h3>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-12">
                        <div id="prev_newsletter" style='font-family: Helvetica !important;'>
                          <a style='text-decoration: none;' href='#'>
                            <table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 25px;background-color: #000;'>
                              <td style="border: none;"><img style='width: 200px;height: 200px;' src='<?php echo $gallery; ?>/images/ob2.png'/></td>
                              <td align='right' style='border: none;vertical-align: bottom;'>
                                <div style='margin-bottom: 10px;'>
                                  <a href='https://www.instagram.com/occitanieboissons/' style='margin-right: 10px;'><img alt='insta' src='<?php echo $gallery; ?>/images/insta.png'></a>
                                  <a href='https://www.facebook.com/Occitanie-Boissons-1619195328335124'><img alt='fb' src='<?php echo $gallery; ?>/images/fb.png'></a>
                                </div>
                                <div style='display: inline-block;color: #000;padding: 5px;font-size: 1.7em;background-color: #fff;font-weight: bold;margin-left: auto;'>#OCCITANIEBOISSONS</div>
                              </td>
                            </table>
                          </a>
                          <table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
                            <td style='border: none;width: 100%;vertical-align: top;'>
                              <div style='color: #000;font-size: 1.4em;font-weight: bold;margin-bottom: 10px;' id="titre_newsletter"></div>
                              <div style='font-size: 1.1em;font-weight: bold;margin-bottom: 20px;' id="brasseries_newsletter"></div>
                              <div style='word-break: break-word;font-size: 1em;' id="contenu_newsletter"></div>
                            </td>
                          </table>
                          <table style='border-collapse: separate;width: 100%;box-sizing: border-box;padding: 30px;background-color: #fff;'>  
                            <td style='border: none;width: 100%;vertical-align: top;text-align: center;'><img id="image_newsletter"/></td>
                          </table>
                          <table style='width: 100%;text-align: center;'>
                            <td style='border: none;position: relative;font-size: 0.8em;text-align: center;margin-top: 20px;color: #404040;width: 100%;'>L'abus d'alcool est dangereux pour la santé, à consommer avec modération. Copyright 2017 © Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu</td>
                          </table>
                          <hr>
                          <table style='width: 100%;'>
                            <td style='vertical-align: top;border: none;'>
                              <div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>Occitanie Boissons</div>
                              <div style='font-weight: bold;'>05 61 82 50 78</div>
                              <div>contact@occitanieboissons.com</div>
                              <div>ZA-Le Grand, 3 rue des Artisants</div>
                              <div>31140 PECHBONNIEU</div>
                            </td>
                            <td style='vertical-align: top;border: none;'>
                              <div style='font-weight: bold;font-size: 1.2em;margin: 8px 0;'>Baptiste Perrinet</div>
                              <div style='font-weight: bold;'>06 73 43 50 20</div>
                              <div>commercial.ob@free.fr</div>          
                            </td>
                          </table>
                          <hr style='margin-top: 8px;'>
                          <table style='width: 100%;text-align: center;'>
                            <td style='border: none;position: relative;font-size: 0.9em;text-align: center;color: #404040;width: 100%;'>
                              <p>Si vous souhaitez vous déscrinscrire de la newsletter, veuillez vous rendre <a href='https://www.occitanieboissons.com/newsletter/desinscription/'>sur cette page</a>.</p>
                            </td>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-12">
                        <button data-wizard="#actualites_redac" class="btn btn-default wizard-previous"><i class="fa fa-caret-left"></i> Retour</button>
                        <button id="valider" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Valider</button>
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
        App.textEditor();

        $('#contenu').summernote();
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