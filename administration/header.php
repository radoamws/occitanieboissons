<!-- Fixed navbar -->
<div id="head-nav" class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="fa fa-gear"></span>
      </button>
      <a class="navbar-brand" href="<?php echo $admurl; ?>"><span>Administration <i class="fa fa-cog"></i></span></a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="<?php echo $url; ?>">Retour au site</a></li>
      </ul>
    </div> 
  </div>
</div>

<div id="cl-wrapper" class="fixed-menu">
  <div class="cl-sidebar">
   <div class="cl-toggle"><i class="fa fa-bars"></i></div>
   <div class="cl-navblock">
    <div class="menu-space">
      <div class="content">
        <ul class="cl-vnavigation">
          <li <?php if(@$menu == "accueil") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>"><i class="fa fa-home"></i><span>Accueil</span></a></li>
          <?php if($ua->acces_blog == 1) { ?>
            <li><a href="#"><i class="fa fa-edit"></i><span>Actualités</span></a>
              <ul class="sub-menu">
                <li <?php if(@$menu == "actualites") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/actualites.php">Gérer les actualités</a></li>
                <li <?php if(@$menu == "actualites_redact") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/actualites_redact.php">Rédiger une actualité</a></li>
                <li <?php if(@$menu == "actualites_produit") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/actualites_redact_produit.php">Rédiger une actualité produit</a></li>
              </ul>
            </li>
            <li><a href="#"><i class="fa fa-beer"></i><span>Brasseries</span></a>
              <ul class="sub-menu">
                <li <?php if(@$menu == "brasseries") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/brasseries.php">Gérer les brasseries</a></li>
                <li <?php if(@$menu == "brasseries_redact") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/brasseries_redact.php">Ajouter une brasserie</a></li>
              </ul>
            </li>
          <?php } ?>
          <?php if($ua->acces_catalogue == 1) { ?>
            <li><a href="#"><i class="fa fa-book"></i><span>Catalogue</span></a>
              <ul class="sub-menu">
                <li <?php if(@$menu == "catalogue") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/catalogue.php">Catalogue téléchargeable</a></li>
                <li <?php if(@$menu == "catalogue_commande") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/catalogue_commande.php">Visualisation des commandes</a></li>
                <li <?php if(@$menu == "catalogue_access") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/catalogue_access.php">Gérer les accès</a></li>
              </ul>
            </li>
          <?php } ?>
          <?php if($ua->acces_espace_degustation == 1) { ?>
            <li <?php if(@$menu == "espace_degustation") { ?>class="active"<?php } ?>><a href="<?php echo $admurl; ?>/espace_degustation.php"><i class="fa fa-book"></i><span>Carte espace dégustation</span></a></li>
          <?php } ?>
          </ul>
        </div>
      </div>
      <div class="text-right collapse-button" style="padding:7px 9px;">
        <button id="sidebar-collapse" class="btn btn-default"><i style="color:#fff;" class="fa fa-angle-left"></i></button>
      </div>
    </div>
  </div>