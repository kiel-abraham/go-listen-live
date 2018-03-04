<?php
	require_once("functions.php");

	mysqli_real_escape_string($conn, $_POST['id']);
	$update = implode(", ", $_POST['id']);
	publish_event($update);
?>