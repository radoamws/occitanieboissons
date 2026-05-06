<?php
	@session_start();
    $_SESSION['username'] = 'occitanieboissons@free.fr';
    header("https://administration.occitanieboissons.com/index.php");
?>