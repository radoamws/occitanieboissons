<?php 
 	require("../includes/configuration.php");
  
  	if(isset($_POST['email']) && isset($_POST['mdp'])) {
		if(empty($_POST['email']) || empty($_POST['mdp'])) {
		  $message = '
		    <div class="alert alert-danger">
		      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		      <i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Veuillez remplir les champs vides.
		    </div>
		  ';
		} else {
			$verif = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
			$verif->bindParam(":email", $_POST['email']);
			$verif->execute();
			$v = $verif->fetch(PDO::FETCH_OBJ); 
		  	if($verif->rowCount() < 1) {
				$message = '
			  		<div class="alert alert-danger">
		        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		        		<i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Votre adresse email ne correspond à aucun compte utilisateur.
		      		</div>
				';
		  	} else {
		  		if($v->admin == 0) {
					$message = '
				  		<div class="alert alert-danger">
			        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			        		<i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Vous n\'avez pas les droits requis.
			      		</div>
					';

		  		} else {
		  			if(!password_verify($_POST['mdp'], $v->mdp)) {
		  				$message = '
					  		<div class="alert alert-danger">
				        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				        		<i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Le mot de passe est incorrect.
				      		</div>
						';
		  			} else {
						$_SESSION['username'] = $_POST['email'];
						$message = '
					     	<div class="alert alert-success">
					       		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					        	<i class="fa fa-check sign"></i><strong>Succès !</strong> Vous êtes maintenant connecté.
					      	</div>
				    	';
				    	$redirect = $admurl;
				    }
			    } 	
			}
		}
  	} else {
  		$message = '
	  		<div class="alert alert-danger">
        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        		<i class="fa fa-times-circle sign"></i><strong>Erreur !</strong> Une erreur est survenue, veuillez réessayer plus tard.
      		</div>
		';
  	}
  echo json_encode(array('message' => @$message, 'redirect' => @$redirect));
?>