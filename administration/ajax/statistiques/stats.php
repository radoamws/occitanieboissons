<?php
	require("../../includes/configuration.php");
	require("../../includes/functions.php");
	UpdateStats();
	
	// MOIS
	if(!isset($_GET['mois'])) {
		$mois = date("m");
	} else {
		$mois = $_GET['mois'];
	}
	  // ANNEE
	if(!isset($_GET['annee'])) {
		$annee = date("Y");
	} else {
		$annee = $_GET['annee'];
	}

	switch($_GET['u']) {
		case "catalogue":
		  	// GRAPHIQUE
			$catalogue = $bdd->query('SELECT * FROM ob_statistiques');
			$c_graph = array();
			while($c = $catalogue->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($c->date);
				if($date['month'] == $mois && $date['year'] == $annee) {
					$c_graph[] = array($date['day'], $c->catalogue);
				}
			}
			// ABONNES PAR JOUR
			$tel_jour = $bdd->query('SELECT * FROM ob_statistiques WHERE date = "'.date('Y-m-d').'" LIMIT 1')->fetch(PDO::FETCH_OBJ);
			// ABONNES PAR MOIS
			$tel_mois = $bdd->query('SELECT * FROM ob_statistiques');
			$c_mois = 0;
			while($c = $tel_mois->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($c->date);
				//$m_tel = ucfirst(strftime('%B %Y', $lastday = mktime(0, 0, 0, $mois, 1, $annee)));
				$formatter = new IntlDateFormatter(
					'fr_FR', // Locale
					IntlDateFormatter::NONE,
					IntlDateFormatter::NONE,
					null,
					null,
					'LLLL yyyy' // Format: full month name + year
				);

				$lastday = mktime(0, 0, 0, $mois, 1, $annee);
				$m_tel = ucfirst($formatter->format($lastday));

				if($date['month'] == $mois && $date['year'] == $annee) {
					$c_mois = $c_mois+$c->catalogue;
				}
			}
			// CONTACT PAR MOIS
			$con_mois = array();
			$contact = $bdd->query("SELECT * FROM ob_catalogue_download");
			while($con = $contact->fetch(PDO::FETCH_OBJ)) {
				$date_c = getdate($con->time);
				if($date_c['mon'] == $mois && $date_c['year'] == $annee && !empty($con->email)) {
					if(!array_key_exists($c->email, $con_mois)) {
						$con_mois[] = $con->email;
					}
				}
			}

			if(!empty($c_graph)) echo json_encode(array(
				"c_graph" => $c_graph,
				"tel_jour" => $tel_jour->catalogue, 
				"tel_mois" => $c_mois,
				"contact_mois" => $con_mois,
				"mois_tel" => $m_tel
			));  
		break;
		case "inscription":
		  	// GRAPHIQUE
			$inscription = $bdd->query('SELECT * FROM ob_statistiques');
			$i_graph = array();
			while($i = $inscription->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($i->date);
				if($date['month'] == $mois && $date['year'] == $annee) {
					$i_graph[] = array($date['day'], $i->inscription);
				}
			}
			// ABONNES PAR JOUR
			$inscr_jour = $bdd->query('SELECT * FROM ob_statistiques WHERE date = "'.date('Y-m-d').'" LIMIT 1')->fetch(PDO::FETCH_OBJ);
			// ABONNES PAR MOIS
			$inscr_mois = $bdd->query('SELECT * FROM ob_statistiques');
			$i_mois = 0;
			while($i = $inscr_mois->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($i->date);
				//$m_inscr = ucfirst(strftime('%B %Y', $lastday = mktime(0, 0, 0, $mois, 1, $annee)));
				$lastday = mktime(0, 0, 0, $mois, 1, $annee);
				$formatter = new IntlDateFormatter(
					'fr_FR',
					IntlDateFormatter::NONE,
					IntlDateFormatter::NONE,
					null,
					null,
					'LLLL yyyy'
				);
				$m_inscr = ucfirst($formatter->format($lastday));
				if($date['month'] == $mois && $date['year'] == $annee) {
					$i_mois = $i_mois+$i->inscription;
				}
			}
			// CONTACT PAR MOIS
			$c_mois = array();
			$contact = $bdd->query("SELECT * FROM ob_users ORDER BY id DESC");
			while($c = $contact->fetch(PDO::FETCH_OBJ)) {
				$date_c = getdate($c->time_creation);
				if($date_c['mon'] == $mois && $date_c['year'] == $annee) {
					$c_mois[] = $c->email;
				}
			}

			if(!empty($i_graph)) echo json_encode(array(
				"i_graph" => $i_graph, 
				"inscr_jour" => $inscr_jour->inscription, 
				"inscr_mois" => $i_mois,
				"contact_mois" => $c_mois,
				"mois_inscr" => $m_inscr
			));  
		break;
		case "experience":
		  	// GRAPHIQUE
			$experience_type = $bdd->query('SELECT * FROM ob_enquete_type');
			$e_graph = array();
			while($t = $experience_type->fetch(PDO::FETCH_OBJ)) {
				$total = 0;
				$experience = $bdd->query('SELECT * FROM ob_enquete WHERE enquete_id = "'.$t->id.'"'); 
				while($e = $experience->fetch(PDO::FETCH_OBJ)) {
					$date = date_parse($e->date);
					if($date['month'] == $mois && $date['year'] == $annee) {
						$total = $total+$e->valeur;
					}
				}
				$label = $t->type." (".$total.")";
				$e_graph[] = array("label" => $label, "data" => $total);
			}

			/*echo json_encode(array(
				"e_graph" => $e_graph, 
				"mois_experience" => ucfirst(strftime('%B %Y', $lastday = mktime(0, 0, 0, $mois, 1, $annee)))
			));  */
			$lastday = mktime(0, 0, 0, $mois, 1, $annee);
			$formatter = new IntlDateFormatter(
				'fr_FR',
				IntlDateFormatter::NONE,
				IntlDateFormatter::NONE,
				null,
				null,
				'LLLL yyyy'
			);
			$mois_experience = ucfirst($formatter->format($lastday));

			echo json_encode(array(
				"e_graph" => $e_graph, 
				"mois_experience" => $mois_experience
			));
		break;
		case "location":
			// GRAPHIQUE
			$location = $bdd->query('SELECT * FROM ob_statistiques');
			$l_graph = array();
			while($l = $location->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($l->date);
				if($date['month'] == $mois && $date['year'] == $annee) {
					$l_graph[] = array($date['day'], $l->location);
				}
			}
			// ABONNES PAR JOUR
			$location_jour = $bdd->query('SELECT * FROM ob_statistiques WHERE date = "'.date('Y-m-d').'" LIMIT 1')->fetch(PDO::FETCH_OBJ);
			// ABONNES PAR MOIS
			$location_mois = $bdd->query('SELECT * FROM ob_statistiques');
			$l_mois = 0;
			while($l = $location_mois->fetch(PDO::FETCH_OBJ)) {
				$date = date_parse($l->date);
				//$m_location = ucfirst(strftime('%B %Y', $lastday = mktime(0, 0, 0, $mois, 1, $annee)));
				$lastday = mktime(0, 0, 0, $mois, 1, $annee);
				$formatter = new IntlDateFormatter(
					'fr_FR',
					IntlDateFormatter::NONE,
					IntlDateFormatter::NONE,
					null,
					null,
					'LLLL yyyy'
				);
				$m_location = ucfirst($formatter->format($lastday));
				if($date['month'] == $mois && $date['year'] == $annee) {
					$l_mois = $l_mois+$l->location;
				}
			} 
			if(!empty($l_graph)) echo json_encode(array(
				"l_graph" => $l_graph, 
				"location_jour" => $location_jour->location, 
				"location_mois" => $l_mois, 
				"mois_location" => $m_location
			));  
		break;
	}
?>