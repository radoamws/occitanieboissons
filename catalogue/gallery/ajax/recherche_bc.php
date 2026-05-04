<?php
	require("../../includes/configuration.php");

	if(isset($_GET['term'])) {
		$term = "%".$_GET['term']."%";

		$motclef = $bdd->prepare('SELECT * FROM ob_brasseries WHERE name = :nom AND hiden = "1"');
		$motclef->bindParam(':nom', $_GET['term']);
	    $motclef->execute();
	    if($motclef->rowCount() < 1) {
	    	unset($motclef);
			$motclef = $bdd->prepare('SELECT * FROM ob_brasseries WHERE name LIKE :nom AND hiden = "1"');
		    $motclef->bindParam(':nom', $term);
		    $motclef->execute();
		}

		if(@$_GET['recherche'] == 1) {
			$data = array();
			while($m = $motclef->fetch(PDO::FETCH_OBJ)) {
				unset($affichage);
				if(!isset($affichage)) {
					$affichage = true;
					$link = $m->name."-".$m->id;
	 				$data[] = array("id" => $link, "nom" => $m->name);
	 			}
			}
			echo json_encode(end($data)); 
		} else {			
			// AFFICHAGE DU MOT CLE
			$data = array();
			while($m = $motclef->fetch(PDO::FETCH_OBJ)) {
				unset($affichage);
				if(!isset($affichage)) {
					$affichage = true;
					$link = $m->name."-".$m->id;
	 				$data[] = array("id" => $link, "nom" => $m->name);
	 			}
			}
			echo json_encode($data);
		}
	}

	/* JSON 
		$all_a = array();
		$actualites = $bdd->query("SELECT * FROM ob_actualites");
		while($a = $actualites->fetch(PDO::FETCH_OBJ)) { 
			// TAGS
			$tags = explode(",", $a->taglist);
			$all_t = array();
			foreach($tags as $t) {
				$text_t = $bdd->query("SELECT motclef FROM ob_motsclefs WHERE id = '".$t."'")->fetch(PDO::FETCH_OBJ);
				$all_t[] = $text_t->motclef;
			}
			// GENERATION DU JSON
			$all_a[] = array(
				"titre" => $a->titre, 
				"tags" => implode(", ", $all_t),
				"contenu" => implode(", ", $all_t)
			);
		}
		echo json_encode(array("recherche" => $all_a));
	*/
?>