<?php
	require('./includes/configuration.php');
	require('./includes/functions.php');
	require('./pdf/fpdf.php');

	$pdf = new FPDF( 'P', 'mm', 'A4' );

	// on sup les 2 cm en bas
	$pdf->SetAutoPagebreak(False);
	$pdf->SetMargins(0,0,0);

	if(!isset($_SESSION['site'])) {
		header("Location: ".$url."/connexion/redirection/pdf/facturation/".htmlentities($_GET['commandeid']));
		exit();
	}

	if($u->admin == 1 && $u->acces_commande == 1) {
		// nb de page pour le multi-page : 18 lignes
		$commande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id");
		$commande->bindParam(":id", $_GET['commandeid']);
		$commande->execute();
		$c = $commande->fetch(PDO::FETCH_OBJ);
		if($commande->rowCount() < 1) {
			echo "Ce numero de facture n'existe pas. Veuillez contacter l'entreprise.";
			exit();
		}
	} else {
		// nb de page pour le multi-page : 18 lignes
		$commande = $bdd->prepare("SELECT * FROM ob_users_commande WHERE id = :id AND userid = '".$u->id."'");
		$commande->bindParam(":id", $_GET['commandeid']);
		$commande->execute();
		$c = $commande->fetch(PDO::FETCH_OBJ);
		if($commande->rowCount() < 1) {
			echo "Ce numero de facture n'existe pas. Veuillez contacter l'entreprise.";
			exit();
		}
	}

	$elements = $bdd->query("SELECT * FROM ob_users_commande_element WHERE commandeid = '".$c->id."'");
	$nb_page = ceil($elements->rowCount()/18);
	
	$num_page = 1; $limit_inf = 0; $limit_sup = 18;
	while($num_page <= $nb_page) {
		$pdf->AddPage();
		
		// logo : 80 de largeur et 55 de hauteur
		$pdf->Image($image_url.'/gallery/images/ob2.png', 10, 10, 50, 49);

		// n° page en haute à droite
		$pdf->SetXY( 120, 5 ); $pdf->SetFont( "Arial", "B", 12 ); $pdf->Cell( 160, 8, $num_page . '/' . $nb_page, 0, 0, 'C');

		// n° facture, date echeance et reglement et obs
		$date = new DateTime("@$c->time");
		$annee = $date->format('Y');
		$num_fact = "COMMANDE N°".$c->id." - ".mPaiement($c->paiement_default);
		$pdf->SetLineWidth(0.1); $pdf->SetFillColor(192); $pdf->Rect(120, 15, 85, 8, "DF");
		$pdf->SetXY( 120, 15 ); $pdf->SetFont( "Arial", "B", 12 ); $pdf->Cell( 85, 8, $num_fact, 0, 0, 'C');
		
		// nom du fichier final
		$nom_file = "facture_".$annee.'-'.mPaiement($c->paiement_default).'-'.str_pad($c->id, 4, '0', STR_PAD_LEFT).".pdf";
		
		// date facture
		$date_fact = $date->format('d/m/Y');
		$pdf->SetFont('Arial','',11); $pdf->SetXY( 122, 25 );
		$pdf->Cell(60, 8, "Pechbonnieu, le ".$date_fact, 0, 0,'');
		
		// si derniere page alors afficher total
		if($num_page == $nb_page) {
			// les totaux, on n'affiche que le HT. le cadre après les lignes, demarre a 213
			$pdf->SetLineWidth(0.1); $pdf->SetFillColor(192); $pdf->Rect(5, 213, 90, 8, "DF");
			// HT, la TVA et TTC sont calculés après
			$nombre_format_francais = "Total Consigne : " . number_format($c->consigne, 2, ',', ' ') . " €";
			$pdf->SetFont('Arial','',10); $pdf->SetXY( 95, 213 ); $pdf->Cell( 63, 8, $nombre_format_francais, 0, 0, 'C');
			// en bas à droite
			$pdf->SetFont('Arial','B',8); $pdf->SetXY( 181, 227 ); $pdf->Cell( 24, 6, number_format($c->prixht, 2, ',', ' '), 0, 0, 'R');

			// trait vertical cadre totaux, 8 de hauteur -> 213 + 8 = 221
			$pdf->Rect(5, 213, 200, 8, "D"); $pdf->Line(95, 213, 95, 221); $pdf->Line(158, 213, 158, 221);

			// reglement
			$pdf->SetXY( 5, 225 ); $pdf->Cell( 38, 5, "Mode de Règlement :", 0, 0, 'R'); $pdf->Cell( 55, 5, mPaiement($c->paiement_default), 0, 0, 'L');

			// observations
			$pdf->SetFont( "Arial", "BU", 10 ); $pdf->SetXY( 5, 235 ) ; $pdf->Cell($pdf->GetStringWidth("Message livraison"), 0, "Message livraison", 0, "L");
			$pdf->SetFont( "Arial", "", 10 ); $pdf->SetXY( 5, 238 ) ; $pdf->MultiCell(190, 4, $c->message_livraison, 0, "L");
			/*// echeance
			$date_ech = $date->format('d/m/Y');
			$pdf->SetXY( 5, 230 ); $pdf->Cell( 38, 5, "Date Echéance :", 0, 0, 'R'); $pdf->Cell( 38, 5, $date_ech, 0, 0, 'L');*/
		}

		// Infos client
		$pdf->SetFont('Arial','BU',11); $x = 75 ; $y = 30;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, "Informations client", 0, 0, 'L'); $y += 6;
		$pdf->SetFont('Arial','B',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode(mb_strtoupper($c->nom , 'UTF-8'))." ".utf8_decode($c->prenom), 0, 0, ''); $y += 4;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->email), 0, 0, ''); $y += 4;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->phone), 0, 0, ''); $y += 4;
		if($c->entreprise) { $pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->entreprise), 0, 0, ''); $y += 4;}

		// adr livraison du client
		$pdf->SetFont('Arial','BU',11); $x = 75 ; $y = 55;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, "Adresse de livraison", 0, 0, 'L'); $y += 6;
		$pdf->SetFont('Arial','B',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode(mb_strtoupper($c->nom_l , 'UTF-8'))." ".utf8_decode($c->prenom_l), 0, 0, ''); $y += 4;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->phone_l), 0, 0, ''); $y += 4;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->adresse_l), 0, 0, ''); $y += 4;
		if ($c->adressec_f) { $pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->adressec_l), 0, 0, ''); $y += 4;}
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, $c->codepostal_l. ' ' .utf8_decode($c->ville_l) , 0, 0, ''); $y += 4;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->pays_l), 0, 0, ''); $y += 4;
		if($c->numerotva_f) { $pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, 'N° TVA Intra : ' .$c->numerotva_l, 0, 0, '');}

		// adr fact du client
		$pdf->SetFont('Arial','BU',11); $x = 135 ; $y = 55;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, "Adresse de facturation", 0, 0, 'L'); $y += 6;
		$pdf->SetFont('Arial','B',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode(mb_strtoupper($c->nom_f , 'UTF-8'))." ".utf8_decode($c->prenom_f), 0, 0, ''); $y += 4;
		$pdf->SetFont('Arial','',10);
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->phone_f), 0, 0, ''); $y += 4;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->adresse_f), 0, 0, ''); $y += 4;
		if ($c->adressec_f) { $pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->adressec_f), 0, 0, ''); $y += 4;}
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, $c->codepostal_f. ' ' .utf8_decode($c->ville_f) , 0, 0, ''); $y += 4;
		$pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, utf8_decode($c->pays_f), 0, 0, ''); $y += 4;
		if($c->numerotva_f) { $pdf->SetXY( $x, $y ); $pdf->Cell( 100, 8, 'N° TVA Intra : ' .$c->numerotva_f, 0, 0, '');}
		
		// ***********************
		// le cadre des articles
		// ***********************
		// cadre avec 18 lignes max ! et 118 de hauteur --> 95 + 118 = 213 pour les traits verticaux
		$pdf->SetLineWidth(0.1); $pdf->Rect(5, 95, 200, 118, "D");
		// cadre titre des colonnes
		$pdf->Line(5, 105, 205, 105);
		// les traits verticaux colonnes
		$pdf->Line(116, 95, 116, 213); $pdf->Line(129, 95, 129, 213); $pdf->Line(151, 95, 151, 213); $pdf->Line(161, 95, 161, 213); $pdf->Line(183, 95, 183, 213);
		// titre colonne
		$pdf->SetXY( 1, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 115, 8, "Libellé", 0, 0, 'C');
		$pdf->SetXY( 116, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 13, 8, "Qté", 0, 0, 'C');
		$pdf->SetXY( 129, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 22, 8, "PU HT HD", 0, 0, 'C');
		$pdf->SetXY( 151, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 10, 8, "TVA", 0, 0, 'C');
		$pdf->SetXY( 161, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 22, 8, "Consigne", 0, 0, 'C');
		$pdf->SetXY( 183, 96 ); $pdf->SetFont('Arial','B',8); $pdf->Cell( 22, 8, "TOTAL HT", 0, 0, 'C');
		
		// les articles
		$pdf->SetFont('Arial','',8);
		$y = 97;

		while ($e = $elements->fetch(PDO::FETCH_OBJ)) {

			// TVA
			switch($e->code_tva) {
				case 2:
					$tva = 20;
				break;
				case 3:
					$tva = 5.5;
				break;
				case 4:
					$tva = 10;
				break;
				case 5:
					$tva = 0;
				break;
			}

			// libelle
			$pdf->SetXY( 7, $y+9 ); $pdf->Cell( 140, 5, (($e->marque == 2) ? "Précommande - ":"").$e->code_produit." - ".utf8_decode($e->nom), 0, 0, 'L');
			// qte
			$pdf->SetXY( 112, $y+9 ); $pdf->Cell( 13, 5, strrev(wordwrap(strrev($e->qte), 3, ' ', true)), 0, 0, 'R');
			// PU
			$nombre_format_francais = number_format($e->prix_ht, 2, ',', ' ');
			$pdf->SetXY( 126, $y+9 ); $pdf->Cell( 18, 5, $nombre_format_francais, 0, 0, 'R');
			// Taux:""
			$nombre_format_francais = number_format($tva, 2, ',', ' ').'%';
			$pdf->SetXY( 152, $y+9 ); $pdf->Cell( 10, 5, $nombre_format_francais, 0, 0, 'R');
			// Consigne
			$nombre_format_francais = number_format($e->consigne_caisse, 2, ',', ' ');
			$pdf->SetXY( 166, $y+9 ); $pdf->Cell( 10, 5, $nombre_format_francais, 0, 0, 'R');
			// total
			$nombre_format_francais = number_format($e->prix_ht*$e->qte*$e->uv_caisse, 2, ',', ' ');
			$pdf->SetXY( 187, $y+9 ); $pdf->Cell( 18, 5, $nombre_format_francais, 0, 0, 'R');
			$pdf->Line(5, $y+14, 205, $y+14);
			$y += 6;
		}

		// si derniere page alors afficher cadre des TVA
		if($num_page == $nb_page) {
			// le detail des totaux, demarre a 221 après le cadre des totaux
			$pdf->SetLineWidth(0.1); $pdf->Rect(130, 221, 75, 30, "D");
			// les traits verticaux
			$pdf->Line(147, 221, 147, 251); $pdf->Line(164, 221, 164, 251); $pdf->Line(181, 221, 181, 251);
			// les traits horizontaux pas de 6 et demarre a 221
			$pdf->Line(130, 227, 205, 227); $pdf->Line(130, 233, 205, 233); $pdf->Line(130, 239, 205, 239); $pdf->Line(130, 245, 205, 245);
			// les titres
			$pdf->SetFont('Arial','B',8); $pdf->SetXY( 181, 221 ); $pdf->Cell( 24, 6, "TOTAL", 0, 0, 'C');
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY( 105, 221 ); $pdf->Cell( 25, 6, "Taux TVA", 0, 0, 'R');
			$pdf->SetXY( 105, 227 ); $pdf->Cell( 25, 6, "Total HT HD", 0, 0, 'R');
			$pdf->SetXY( 105, 233 ); $pdf->Cell( 25, 6, "Total HT DC", 0, 0, 'R');
			$pdf->SetXY( 105, 239 ); $pdf->Cell( 25, 6, "Total TVA", 0, 0, 'R');
			$pdf->SetXY( 105, 245 ); $pdf->Cell( 25, 6, "Total TTC", 0, 0, 'R');

			// les taux de tva et HT et TTC
			$col_ht = 0; $col_tva = 0; $col_ttc = 0; $col_htdc = 0;
			$taux = 0; $tot_tva = 0; $tot_ttc = 0; $tot_htdc = 0;
			$x = 130;
			
			$elements = $bdd->query("SELECT code_tva,sum(round(prix_ht*qte*uv_caisse,2)) tot_ht,sum(round((prix_ht+droits)*uv_caisse*qte,2)) tot_htdc FROM ob_users_commande_element WHERE commandeid = '".$c->id."' group by code_tva order by code_tva");
			while($e = $elements->fetch(PDO::FETCH_OBJ)) {
				switch($e->code_tva) {
					case 2:
						$tva = 20;
					break;
					case 3:
						$tva = 5.5;
					break;
					case 4:
						$tva = 10;
					break;
					case 5:
						$tva = 0;
					break;
				}

				$pdf->SetXY( $x, 221 ); $pdf->Cell( 17, 6, $tva.' %', 0, 0, 'C');
				
				$nombre_format_francais = number_format($e->tot_ht, 2, ',', ' ');
				$pdf->SetXY($x, 227); $pdf->Cell( 17, 6, $nombre_format_francais, 0, 0, 'R');

				$taux = $tva;

				$col_htdc = $e->tot_htdc;
				$nombre_format_francais = number_format($col_htdc, 2, ',', ' ');
				$pdf->SetXY( $x, 233 ); $pdf->Cell( 17, 6, $nombre_format_francais, 0, 0, 'R');
				
				$col_tva = $e->tot_htdc - ($e->tot_htdc * (1-($taux/100)));
				$nombre_format_francais = number_format($col_tva, 2, ',', ' ');
				$pdf->SetXY( $x, 239 ); $pdf->Cell( 17, 6, $nombre_format_francais, 0, 0, 'R');
				
				$col_ttc = $e->tot_htdc + $col_tva;
				$nombre_format_francais = number_format($col_ttc, 2, ',', ' ');
				$pdf->SetXY( $x, 245 ); $pdf->Cell( 17, 6, $nombre_format_francais, 0, 0, 'R');
				
				$tot_htdc += $col_htdc; $tot_tva += $col_tva ; $tot_ttc += $col_ttc;
				
				$x += 17;
			}

			// TOTAL HT DC
			$pdf->SetFont('Arial','B',8); $pdf->SetXY( 181, 233 ); $pdf->Cell( 24, 6, number_format($tot_htdc, 2, ',', ' '), 0, 0, 'R');

			$nombre_format_francais = "Net à payer TTC : " .number_format($tot_ttc+$c->livraison*1.2+$c->consigne, 2, ',', ' ') . " €";
			$pdf->SetFont('Arial','B',12); $pdf->SetXY( 5, 213 ); $pdf->Cell( 90, 8, $nombre_format_francais, 0, 0, 'C');
			// en bas à droite
			$pdf->SetFont('Arial','B',8); $pdf->SetXY( 181, 245 ); $pdf->Cell( 24, 6, number_format($tot_ttc, 2, ',', ' '), 0, 0, 'R');
			// TVA
			$nombre_format_francais = "Livraison TTC : " . number_format($c->livraison*1.2, 2, ',', ' ') . " €";
			$pdf->SetFont('Arial','',10); $pdf->SetXY( 158, 213 ); $pdf->Cell( 47, 8, $nombre_format_francais, 0, 0, 'C');
			// en bas à droite
			$pdf->SetFont('Arial','B',8); $pdf->SetXY( 181, 239 ); $pdf->Cell( 24, 6, number_format($tot_tva, 2, ',', ' '), 0, 0, 'R');
		}

		// **************************
		// pied de page
		// **************************
		$pdf->SetLineWidth(0.1); $pdf->Rect(5, 260, 200, 6, "D");
		$pdf->SetXY( 1, 260 ); $pdf->SetFont('Arial','',7);
		$pdf->Cell( $pdf->GetPageWidth(), 7, "Clause de réserve de propriété (loi 80.335 du 12 mai 1980) : Les marchandises vendues demeurent notre propriété jusqu'au paiement intégral de celles-ci.", 0, 0, 'C');
		
		$y1 = 270;
		//Positionnement en bas et tout centrer
		$pdf->SetXY( 1, $y1 ); $pdf->SetFont('Arial','B',10);
		$pdf->Cell( $pdf->GetPageWidth(), 5, "TVA INTRA : FR 29519664346", 0, 0, 'C');

		$pdf->SetXY( 1, $y1+4 ); $pdf->SetFont('Arial','B',10);
		$pdf->Cell( $pdf->GetPageWidth(), 5, "NUMERO ENTREPOSITAIRE : FR012445A0170", 0, 0, 'C');

		$pdf->SetXY( 1, $y1 + 8 ); 
		$pdf->Cell( $pdf->GetPageWidth(), 5, "Occitanie Boissons", 0, 0, 'C');
		
		$pdf->SetXY( 1, $y1 + 12 );
		$pdf->Cell( $pdf->GetPageWidth(), 5, "3 rue des Artisans ZA Le Grand - 31140 PECHBONNIEU", 0, 0, 'C');

		$pdf->SetXY( 1, $y1 + 16 );
		$pdf->Cell( $pdf->GetPageWidth(), 5, "0561825078 - commercial@occitanieboissons.com - 519664346", 0, 0, 'C');

		$pdf->SetXY( 1, $y1 + 20 );
		$pdf->Cell( $pdf->GetPageWidth(), 5, "www.occitanieboissons.com", 0, 0, 'C');
		
		// par page de 18 lignes
		$num_page++; $limit_inf += 18; $limit_sup += 18; 
	}
	
	$pdf->SetTitle($nom_file);
	$pdf->Output("I", $nom_file);
?>
