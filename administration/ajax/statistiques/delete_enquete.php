<?php
	require("../../includes/configuration.php");

	if(isset($_POST['id']) && intval($_POST['id'])) {
		$delete = $bdd->prepare("DELETE FROM ob_enquete_type WHERE id = :id");
		$delete->bindParam(':id', $_POST['id']);
		$delete->execute();

		$delete_data = $bdd->prepare("DELETE FROM ob_enquete WHERE enquete_id = :id");
		$delete_data->bindParam(':id', $_POST['id']);
		$delete_data->execute();
	}
?>