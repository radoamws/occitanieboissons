<?php
	@session_start();

	$host = "occitanila010301.mysql.db";
	$port = "3306";
	$user = "occitanila010301";
	$pass = "Lilian0103";
	$dbname = "occitanila010301";
	try {
		$bdd = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$dbname.'', $user, $pass);
		$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
		echo "Impossible de se connecter &agrave; la base de donn&eacute;es <b>".$host."</b>.<br />Veuillez v&eacute;rifier le contenu du fichier de configuration.";
	}

	################################
 	#     CONFIGURATION DU CMS     # 
 	################################
	$sitename = "LogistiqueOB";
	$url = "//".$_SERVER["HTTP_HOST"];
	$image_url = "https://logistique.occitanieboissons.com";
	$gallery = $url."/gallery";
	$urlupload = $gallery."/upload/";
	$mail_logistique = "logistique.ob@free.fr";
	$facebook = "https://www.facebook.com/occitanieboissons";

	#### GESTION UTILISATEUR
	if(isset($_SESSION['logistique'])) {
		$utilisateur = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email");
		$utilisateur->bindParam(":email", $_SESSION['logistique']);
		$utilisateur->execute();
		if($utilisateur->rowCount() < 1) {
			unset($_SESSION['logistique']);
			header("Refresh:0");
		} else {
			$u = $utilisateur->fetch(PDO::FETCH_OBJ);
		}
	}
?>