<?php
	require('fpdf.php');
	require('../includes/configuration.php');

	class PDF extends FPDF {
		protected $B = 0;
		protected $I = 0;
		protected $U = 0;
		protected $HREF = '';
		function WriteHTML($html) {
		    // Parseur HTML
		    $html = str_replace("\n",' ',$html);
		    $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		    foreach($a as $i=>$e)
		    {
		        if($i%2==0)
		        {
		            // Texte
		            if($this->HREF)
		                $this->PutLink($this->HREF,$e);
		            else
		                $this->Write(5,$e);
		        }
		        else
		        {
		            // Balise
		            if($e[0]=='/')
		                $this->CloseTag(strtoupper(substr($e,1)));
		            else
		            {
		                // Extraction des attributs
		                $a2 = explode(' ',$e);
		                $tag = strtoupper(array_shift($a2));
		                $attr = array();
		                foreach($a2 as $v)
		                {
		                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
		                        $attr[strtoupper($a3[1])] = $a3[2];
		                }
		                $this->OpenTag($tag,$attr);
		            }
		        }
		    }
		}
		function OpenTag($tag, $attr) {
		    // Balise ouvrante
		    if($tag=='B' || $tag=='I' || $tag=='U')
		        $this->SetStyle($tag,true);
		    if($tag=='A')
		        $this->HREF = $attr['HREF'];
		    if($tag=='BR')
		        $this->Ln(6);
		   	if($tag=='LI')
		        $this->Ln(5);
		}
		function CloseTag($tag) {
		    // Balise fermante
		    if($tag=='B' || $tag=='I' || $tag=='U')
		        $this->SetStyle($tag,false);
		    if($tag=='A')
		        $this->HREF = '';
		}
		function SetStyle($tag, $enable) {
		    // Modifie le style et sélectionne la police correspondante
		    $this->$tag += ($enable ? 1 : -1);
		    $style = '';
		    foreach(array('B', 'I', 'U') as $s)
		    {
		        if($this->$s>0)
		            $style .= $s;
		    }
		    $this->SetFont('',$style);
		}
		function PutLink($URL, $txt) {
		    // Place un hyperlien
		    $this->SetTextColor(0,0,255);
		    $this->SetStyle('U',true);
		    $this->Write(5,$txt,$URL);
		    $this->SetStyle('U',false);
		    $this->SetTextColor(0);
		}

		//======== CREATION DU PDF
		function Header() {
		    
		}

		function Footer() {
		    $this->SetY(-10);
		    $this->SetFont('Helvetica','',10);
		    $footer = "L'abus d'alcool est dangereux pour la santé, à consommer avec modération.\n Copyright 2017 © Occitanie Boissons - 3 rue des Artisants 31140 Pechbonnieu © photo header - Nicolas Jahan";
		    $this->MultiCell(0,4,utf8_decode($footer),0,'C');
		}

		//======== ACTUALITES
		function TexteCorpsA($titre, $image, $brasserie, $contenu) {
			// HEADER
			$this->Image('../gallery/images/header.png',0,0,210,60);
		    $this->Image('../gallery/images/ob.png',10,10,40,40);
		    $this->SetFillColor(255,255,255);
		    $this->SetFont('Helvetica','B',15);
		    $this->Ln(30);
		    $this->Cell(125);
		    $this->Cell(65, 10, '#OCCITANIEBOISSONS', 0, 0, 'C', true);
			// TITRE
			$this->SetY(65);
			$this->SetFont('Helvetica','B',14);
		    $this->MultiCell(0,5,utf8_decode($titre),0,'L');
		    // IMAGE
		    $this->SetY(75);
		    $this->Image($image,10,80,65,51.07);
			// BRASSERIE
		    $this->Ln(50);
		    $this->Cell(70);
			$this->SetFont('Helvetica','B',12);
			$w = $this->GetStringWidth($brasserie);
		    $this->Cell($w+2,5,$brasserie,0,1);
			// CONTENU
			$this->SetY(130);
			$this->Ln(5);
			$this->SetFont('Helvetica','',11);
		    $this->WriteHTML(htmlspecialchars_decode(str_replace("?", "'", utf8_decode(html_entity_decode($contenu)))));
		}
		function TexteCorpsA2($titre, $image, $contenu) {
			// HEADER
			$this->Image('../gallery/images/header.png',0,0,210,60);
		    $this->Image('../gallery/images/ob.png',10,10,40,40);
		    $this->SetFillColor(255,255,255);
		    $this->SetFont('Helvetica','B',15);
		    $this->Ln(30);
		    $this->Cell(125);
		    $this->Cell(65, 10, '#OCCITANIEBOISSONS', 0, 0, 'C', true);
			// TITRE
			$this->SetY(65);
			$this->SetFont('Helvetica','B',14);
		    $this->MultiCell(0,5,utf8_decode($titre),0,'L');
		    // IMAGE
		    $this->SetY(75);
		    $this->Image($image,10,80,65,51.07);
			// CONTENU
			$this->SetY(130);
			$this->Ln(5);
			$this->SetFont('Helvetica','',11);
		    $this->WriteHTML(htmlspecialchars_decode(str_replace("?", "'", utf8_decode(html_entity_decode($contenu)))));
		}

		function AjouterCorpsA($titre, $brasserie, $contenu, $image) {
		    $this->TexteCorpsA($titre,$image,$brasserie,$contenu);
		}
		function AjouterCorpsA2($titre, $contenu, $image) {
		    $this->TexteCorpsA2($titre,$image,$contenu);
		}

		//======== BRASSERIE
		function TexteCorpsB($image, $logo, $titre, $contenu, $drapeau) {
			// HEADER
			$this->Image('../gallery/images/header.png',0,0,210,60);
		    $this->Image('../gallery/images/ob.png',10,10,40,40);
		    $this->SetFillColor(255,255,255);
		    $this->SetFont('Helvetica','B',15);
		    $this->Ln(30);
		    $this->Cell(125);
		    $this->Cell(65, 10, '#OCCITANIEBOISSONS', 0, 0, 'C', true);
			// IMAGE
			$this->Image($image,10,70,65,48.75);
			// LOGO
			$this->Image($logo,80,70,30,30);
			// TITRE & DRAPEAU
			$this->Ln(65);
			$this->Cell(70);
			$this->SetFont('Helvetica','B',14);
			$w = $this->GetStringWidth($titre);
		    $this->Cell($w,5,utf8_decode(strtoupper($titre)),0,0,'L',$this->Image($drapeau,$this->GetX()+$w+10,$this->GetY()+0.5,6,4));
		    // CONTENU
			$this->Ln(20);
			$this->SetFont('Helvetica','',11);
		    $this->WriteHTML(htmlspecialchars_decode(str_replace("?", "'", utf8_decode(html_entity_decode($contenu)))));
		}

		function AjouterCorpsB($titre, $contenu, $image, $logo, $drapeau) {
		    $this->TexteCorpsB($image,$logo,$titre,$contenu,$drapeau);
		}
	}

	if(isset($_GET['type'])) {
		switch($_GET['type']) {
			case 'actualite':
				if(isset($_GET['id']) && intval($_GET['id'])) {
					$pdf = $bdd->prepare("SELECT * FROM ob_actualites WHERE id = :id");
					$pdf->bindParam(":id", $_GET['id']);
					$pdf->execute();
					if($pdf->rowCount() > 0) {
						$p = $pdf->fetch(PDO::FETCH_OBJ);

						// IMAGE
						$image = str_replace("//", "https://", $p->image);
														 
						// Création du PDF
						$pdf = new PDF('P','mm','A4');
						$pdf->AddPage();

						if(empty($p->brasseries)) {
							$pdf->AjouterCorpsA2(mb_strtoupper($p->titre, 'UTF-8'), $p->contenu, $image);
						} else {
							// BRASSERIES
							$all_b = array();
							$brasseries = explode(",", $p->brasseries);
							foreach($brasseries as $t) {
								$text_b = $bdd->query("SELECT motclef FROM ob_motsclefs WHERE id = '".$t."'")->fetch(PDO::FETCH_OBJ);
								$all_b[] = $text_b->motclef;
							}
							$pdf->AjouterCorpsA(mb_strtoupper($p->titre, 'UTF-8'), implode(", ", $all_b), $p->contenu, $image);
						}

						$pdf->Output('I', str_replace(" ", "-", utf8_decode($p->titre))."-".$p->id.".pdf");
					} else {
						echo "false";
					}
				} else {
					echo "false";
				}
			break;
			case 'brasserie':
				if(isset($_GET['id']) && intval($_GET['id'])) {
					$pdf = $bdd->prepare("SELECT * FROM ob_brasseries WHERE id = :id");
					$pdf->bindParam(":id", $_GET['id']);
					$pdf->execute();
					if($pdf->rowCount() > 0) {
						$p = $pdf->fetch(PDO::FETCH_OBJ);

						// RECUPERATION DU DRAPEAU
						foreach($pays_brasseries as $nom => $d) {
							if($nom == $p->pays) {
								$drapeau = $d;
							}
						}

						// IMAGE
						$image = str_replace("//", "https://", $p->image);
						// LOGO
						$logo = str_replace("//", "https://", $p->logo);
						// DRAPEAU
						$drapeau = str_replace("//", "https://", $drapeau);
						
						// Création du PDF
						$pdf = new PDF('P','mm','A4');
						$pdf->AddPage();
						$pdf->AjouterCorpsB($p->nom, $p->contenu, $image, $logo, $drapeau);
						$pdf->Output('I', strtolower(str_replace(" ", "-", utf8_decode($p->nom)))."-".$p->id.".pdf");
					} else {
						echo "false";
					}
				} else {
					echo "false";
				}
			break;
		}
	} else {
		echo "false";
	}
?>