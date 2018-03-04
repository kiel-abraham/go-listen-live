<?php
require_once("functions.php");

//Get events from the database based on distance from location.
//Could be enhanced to use date as well.
function search_events($latitude, $longitude, $radius) {
	global $conn;
    $query  = "SELECT *, events.id AS event_id, artists.name AS artist_name, venues.name AS venue_name, ";
	$query .="( 6371 * acos( cos( radians($latitude) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( lat ) ) ) ) AS distance ";
	$query .= "FROM events ";
	$query .= "JOIN artists ON (events.artist_id = artists.id) ";
	$query .= "JOIN venues ON (events.venue_id = venues.id) ";
	$query .= "HAVING distance < $radius ";
	$query .= "AND published = 1 ";
	$query .= "ORDER BY distance ";
	$query .= "LIMIT 0, 20";
    $result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

if (isset($_GET)) {
	$lat = mysqli_real_escape_string($conn, $_GET['lat']);
	$lng = mysqli_real_escape_string($conn, $_GET['lng']);
	$distance = mysqli_real_escape_string($conn, $_GET['distance']);
	
	$results = search_events($lat, $lng, $distance);
	if (mysqli_num_rows($results) > 0) {
		$rows = array();
		while ($row = mysqli_fetch_assoc($results)) {
			$rows[] = $row;
		}
		echo json_encode($rows);
	} else {
		echo '{"none": "true"}';
	}
}

// SELECT *,events.id AS event_id, artists.name AS artist_name, venues.name AS venue_name, ( 6371 * acos( cos( radians(-27.47665) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(153.01667) ) + sin( radians(-27.47665) ) * sin( radians( lat ) ) ) ) AS distance FROM events JOIN artists ON (events.artist_id = artists.id) JOIN venues ON (events.venue_id = venues.id) HAVING distance < 5 AND published = 1 ORDER BY distance LIMIT 0, 20
?>