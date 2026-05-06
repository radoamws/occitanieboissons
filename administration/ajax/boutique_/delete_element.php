<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id']) && intval($_POST['id'])) {
		$delete = $bdd->prepare("DELETE FROM ob_boutique_produit_element WHERE id = :id");
		$delete->bindParam(':id', $_POST['id']);
		$delete->execute();
	}
?>