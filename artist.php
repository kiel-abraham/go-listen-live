<?php 
require_once("functions.php");

$req = "";

if (isset($_POST['artist'])) {
	$req = $_POST['artist'];
}
if (isset($_POST['venue'])) {
	$req = $_POST['venue'];
}

$value = mysqli_real_escape_string($conn, $req);
if ($value != "") {
	if (isset($_POST['artist'])) {
		$res = get_artist($value);
	}
	if (isset($_POST['venue'])) {
		$res = get_venue($value);
	}
	if (mysqli_num_rows($res) > 0) {
		while ($row = mysqli_fetch_assoc($res)) {
			echo json_encode($row);
		}
	} else {
		echo '{"name":""}';
	}
}
?>