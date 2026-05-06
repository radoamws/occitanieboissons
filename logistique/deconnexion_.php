<?php
	require("includes/configuration.php");
	require("includes/functions.php");

	unset($_SESSION["logistique"]);
	header("Location: ".$url);
?>