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
	$sitename = "Occitanie Boissons";
	$url = "//".$_SERVER["HTTP_HOST"];
	$admurl = "//".$_SERVER["HTTP_HOST"];
	$gallery = "//occitanieboissons.com/gallery";
	$drapeaux = "//occitanieboissons.com/gallery";
	$img_brasseries_url = "//upload.occitanieboissons.com/brasseries";

	if(isset($_SESSION['username'])) {
		$utilisateur = $bdd->prepare("SELECT * FROM ob_users WHERE email = :email AND admin = '1'");
		$utilisateur->bindParam(":email", $_SESSION['username']);
		$utilisateur->execute();
		if($utilisateur->rowCount() < 1) {
			unset($_SESSION['username']);
			header("Refresh:0");
		} else {
			$ua = $utilisateur->fetch(PDO::FETCH_OBJ);
		}
	}

	// ASSOCIATION D'UN DRAPEAU A UN PAYS
	$pays_brasseries = array(
		"Allemagne" => $drapeaux."Allemagne.png",
		"Angleterre" => $drapeaux."Angleterre.png",
		"Autriche" => $drapeaux."Autriche.png",
		"Belgique" => $drapeaux."Belgique.png",
		"Canada" => $drapeaux."Canada.png",
		"Danemark" => $drapeaux."Danemark.png",
		"Espagne" => $drapeaux."Espagne.png",
		"Etats-Unis" => $drapeaux."USA.png",
		"Finlande" => $drapeaux."Finlande.png",
		"France" => $drapeaux."France.png",
		"Hongrie" => $drapeaux."Hongrie.png",
		"Irlande" => $drapeaux."Irlande.png",
		"Italie" => $drapeaux."Italie.png",
		"Norvège" => $drapeaux."Norvege.png",
		"Pays Basque" => $drapeaux."Pays basque.png",
		"Pays de Galles" => $drapeaux."Pays de Galles.png",
		"Pologne" => $drapeaux."Pologne.png",
		"Portugal" => $drapeaux."Portugal.png",
		"Royaume-Uni" => $drapeaux."UK.png",
		"Suède" => $drapeaux."Suede.png",
		"Suisse" => $drapeaux."Suisse.png",
	);
?>