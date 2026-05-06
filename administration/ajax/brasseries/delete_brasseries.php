<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id']) && intval($_POST['id'])) {
		$delete = $bdd->prepare("DELETE FROM ob_brasseries WHERE id = :id");
		$delete->bindParam(':id', $_POST['id']);
		$delete->execute();

		$motclef = $bdd->prepare("DELETE FROM ob_motsclefs WHERE brasserie_id = :id");
		$motclef->bindParam(':id', $_POST['id']);
		$motclef->execute();
	}
?>