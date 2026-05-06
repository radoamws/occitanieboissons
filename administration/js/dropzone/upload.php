<?php
  require("../../includes/config.php");
  
  $ds = DIRECTORY_SEPARATOR;
  if(!empty($_FILES)) { 
    $tempFile = $_FILES['file']['tmp_name'];            
    $targetPath = dirname( __FILE__ ).$ds.$urlupload.$ds;
    $targetFile = $targetPath.$_FILES['file']['name'];
    move_uploaded_file($tempFile, $targetFile);
  }
  switch($_GET['etablissement']) {
	case "aucamville":
	  $e = "1";
	break;
	case "muret";
	  $e = "2";
	break;
  }
  $photos = $bdd->prepare("INSERT INTO adb_albumphotos (lien,etablissement,time) VALUES (:lien,:etablissement,:time)");
  $photos->bindParam(":lien", $_FILES['file']['name']);
  $photos->bindParam(":etablissement", $e);
  $photos->bindParam(":time", time());
  $photos->execute();
?>