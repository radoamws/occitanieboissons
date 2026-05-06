<?php
	require("../../includes/configuration.php");

	$liste = file_get_contents("liste.txt");
	$liste = explode(";", $liste);

	$move_liste = 13;

	$total = 0;
	foreach($liste as $val => $v) {
		if(filter_var($v, FILTER_VALIDATE_EMAIL)) {
			$total++;
			$row = $bdd->prepare("SELECT * FROM ob_newsletter WHERE email = :value");
			$row->bindParam(":value", $v);
			$row->execute();
			echo $row->rowCount();
			if($row->rowCount() < 1) {
				$add = $bdd->prepare("INSERT INTO ob_newsletter (email, time, liste_id, auto) VALUES (:email, ".time().", ".$move_liste.", 1)");
				$add->bindParam(":email", $v);
				$add->execute();
			} else {
				$active = false;
				while($n = $row->fetch(PDO::FETCH_OBJ)) {
					if($n->liste_id !== 1) {
						$active = true;
					}
				}
				if($active == true) {
					$add = $bdd->query("UPDATE ob_newsletter SET liste_id = '".$move_liste."' WHERE email = '".$v."'");
				}
			}
		}
	}

	echo "\n".$total;

?>