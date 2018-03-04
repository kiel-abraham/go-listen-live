<?php 
require_once('config.php');

function confirm_query($result) {
	global $conn;
	if (!$result) {
		die ("Error getting information: " . mysqli_error($conn));
	}
}

function delete_old_events($date) {
	global $conn;
	$query  = "DELETE FROM events WHERE date < '$date'";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

$date_today = date("Y-m-d");
if (date("l") == "Sunday") {
	delete_old_events($date_today);
}

function get_events($search, $p) {
	$date_today = date("Y-m-d");
	global $conn;
    $query  = "SELECT *,events.id AS event_id, artists.name AS artist_name, venues.name AS venue_name FROM events ";
	$query .= "JOIN artists ON (events.artist_id = artists.id) ";
	$query .= "JOIN venues ON (events.venue_id = venues.id) ";
	$query .= "WHERE published = $p ";
	if (isset($search) && ($search != "")) {
		$query .= "AND (venues.name LIKE '%$search%' ";
    	$query .= "OR artists.name LIKE '%$search%') ";
	}
	$query .= "AND date >= '$date_today' ";
	$query .= "ORDER BY date ";
	$query .= "LIMIT 30";
    $result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function get_event_by_id($id) {
	global $conn;
    $query  = "SELECT *,events.id AS event_id, artists.name AS artist_name, venues.name AS venue_name FROM events ";
	$query .= "JOIN artists ON (events.artist_id = artists.id) ";
	$query .= "JOIN venues ON (events.venue_id = venues.id) ";
	$query .= "WHERE events.id = $id ";
	$query .= "LIMIT 1";
    $result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function add_event($artist_id, $venue_id, $details, $date, $url, $published) {
	global $conn;
	$query  = "INSERT INTO events ";
	$query .= "(artist_id, venue_id, details, date, url, published) ";
	$query .= "VALUES ('$artist_id', '$venue_id', '$details', '$date', '$url', '$published')";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function update_event($id, $date, $url, $details, $published) {
	global $conn;
	$query  = "UPDATE events SET ";
	$query .= "date = '$date', ";
	$query .= "url = '$url', ";
	$query .= "details = '$details', ";
	$query .= "published = '$published' ";
	$query .= "WHERE id = $id ";
	$query .= "LIMIT 1";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function publish_event($id) {
	global $conn;
	$query  = "UPDATE events ";
	$query .= "SET published = 1 ";
	$query .= "WHERE id IN ($id)";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function delete_event($id) {
	// need to update so that it actually deletes
	global $conn;
//	$query = "SELECT * FROM events WHERE id IN ($id)";
	$query = "DELETE FROM events WHERE id IN ($id)";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function add_artist($name) {
	global $conn;
	$query  = "INSERT INTO artists ";
	$query .= "(name) ";
	$query .= "VALUES ('$name')";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function get_artist($name) {
	global $conn;
	$query  = "SELECT * FROM artists ";
	$query .= "WHERE name LIKE '$name%' ";
	$query .= "LIMIT 1";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function add_venue($name, $address, $lat, $lng) {
	global $conn;
	$query  = "INSERT INTO venues ";
	$query .= "(name, address, lat, lng) ";
	$query .= "VALUES ('$name', '$address', '$lat', '$lng')";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function get_venue($name) {
	global $conn;
	$query  = "SELECT * FROM venues ";
	$query .= "WHERE name LIKE '%$name%' ";
	$query .= "LIMIT 1";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}

function check_user($username) {
	global $conn;
	$query  = "SELECT * FROM users ";
	$query .= "WHERE username = '$username' ";
	$query .= "LIMIT 1";
	$result = mysqli_query($conn, $query);
    confirm_query($result);
    return $result;
}


?>