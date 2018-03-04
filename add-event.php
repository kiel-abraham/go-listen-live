<?php
session_start();
require_once("functions.php");

if (isset($_SESSION['user'])) {
	$user = $_SESSION['user'];
} else {
	$user = false;
}

$event = array();
$errors = array();

if (isset($_POST['event'])) {
	foreach ($_POST['event'] as $key => $value) {
		if (empty($value)) {
			if ($key == "artist_id" || $key == "venue_id" || $key == "url") {
				continue;
			}
			if (!empty($_POST['event']['venue_id'])) {
				if ($key == "address" || $key == "lat" || $key == "lng") {
					continue;
				}
			}
			if ($key == "artist_name") {
				$e = "Artist";
			} elseif ($key == "venue_name") {
				$e = "Venue";
			} else {
				$e = ucfirst($key);
			}
			$errors[$key] = $e . " required";
		} else {
			$value = trim($value);
			$value = stripslashes($value);
			$value = htmlspecialchars($value);
			if ($key == "street" || $key == "suburb") {
				$value = ucwords($value);
			}
			$event[$key] = $value;
		}
		if ($key == "date") {
			if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $value)) {
				$errors[$key] = "Please enter valid " . $key;
			}
		}
		if ($key == "url" && $value != "") {
			if (filter_var($value, FILTER_VALIDATE_URL)) {
				$exts = array("gif", "jpg", "jpeg", "png");
				$str = strtolower(pathinfo($value, PATHINFO_EXTENSION));
				if (!in_array($str, $exts)) {
					$errors[$key] = "Please enter a valid image URL";
				}
			} else {
				$errors[$key] = "Please enter a valid image URL";
			}
		}
	}

	// if no errors add event to database
	if (empty($errors)) {
		foreach ($event as $key => $value) {
			$event[$key] = mysqli_real_escape_string($conn, $value);
		}
		// if no artist already exists insert one
		if (!isset($event['artist_id'])) {
			add_artist($event['artist_name']);
			$event['artist_id'] = mysqli_insert_id($conn);
		}
		// if no venue already exists insert one
		if (!isset($event['venue_id'])) {
			add_venue($event['venue_name'], $event['address'], $event['lat'], $event['lng']);
			$event['venue_id'] = mysqli_insert_id($conn);
		}
		// if artist_id and venue_id are set then add event
		if (isset($event['artist_id']) && isset($event['venue_id'])) {
			$published = 0;
			if (isset($event['published'])) {
				if ($event['published'] == "on") {
					$published = 1;
				}
			}
			if (!isset($event['url'])) {
				$event['url'] = "";
			}
			add_event($event['artist_id'], $event['venue_id'], $event['details'], $event['date'], $event['url'], $published);
			if (mysqli_affected_rows($conn)) {
				header("Location: events.php");
				exit();
			}
		}
	}
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/bootstap.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/bootstrap-datepicker3.min.css">
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/custom.js"></script>
	<script src="js/bootstrap-datepicker.min.js"></script>
	<script src="js/bootstrap-datepicker.en-AU.min.js"></script>
	
	<title>Add Event | Go Listen Live</title>
</head>
<body>
	<header>
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">Go Listen Live</a>
				</div>
				<?php if($user): ?>
				<!--hide if not logged in-->
				<div class="collapse navbar-collapse" id="menu-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="events.php">Events</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
				<!--/hide-->
				<?php endif; ?>
			</div>
		</nav>
	</header>
	
	<main class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>Add Event</h1>
			</div>
		</div>
		<div class="row">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" class="form-horizontal col-xs-12">
				<fieldset>
					<input type="hidden" id="artist_id" name="event[artist_id]" value="<?php if (isset($event['artist_id'])) echo $event['artist_id']; ?>">
					<div class="form-group <?php if (isset($errors['artist_name'])) echo 'has-error'; ?>">
						<label for="artist" class="col-sm-3 control-label">Artist</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="artist" name="event[artist_name]" value="<?php if (isset($event['artist_name'])) echo $event['artist_name']; ?>" placeholder="Search or add artist">
							<span id="artist-message"></span>
							<span class="text-danger">
								<?php if (isset($errors['artist_name'])) echo $errors['artist_name']; ?>
							</span>
						</div>
					</div>
					<input type="hidden" id="venue_id" name="event[venue_id]" value="<?php if (isset($event['venue_id'])) echo $event['venue_id']; ?>">
					<div class="form-group <?php if (isset($errors['venue_name'])) echo 'has-error'; ?>">
						<label for="venue" class="col-sm-3 control-label">Venue</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="venue" name="event[venue_name]" value="<?php if (isset($event['venue_name'])) echo $event['venue_name']; ?>" placeholder="Search or add venue">
							<span id="venue-message"></span>
							<span class="text-danger">
								<?php if (isset($errors['venue_name'])) echo $errors['venue_name']; ?>
							</span>
						</div>
					</div>
					<input type="hidden" id="lat" name="event[lat]">
					<input type="hidden" id="lng" name="event[lng]">
					<div class="form-group <?php if (isset($errors['address'])) echo 'has-error'; ?>">
						<label for="address" class="col-sm-3 control-label">Address</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="address" name="event[address]" value="<?php if (isset($event['address'])) echo $event['address']; ?>" placeholder="Address">
							<div id="address-results"></div>
							<span class="text-danger">
								<?php if (isset($errors['address'])) echo $errors['address']; ?>
							</span>
						</div>
					</div>
					<div class="form-group <?php if (isset($errors['url'])) echo 'has-error'; ?>">
						<label for="url" class="col-sm-3 control-label">Image URL<br><small>*Optional</small></label>
						<div class="col-sm-9">
							<input type="text" id="url" class="form-control" name="event[url]" placeholder="Enter a URL for your event image" value="<?php if (isset($event['url'])) echo $event['url']; ?>">
							<span class="text-danger">
								<?php if (isset($errors['url'])) echo $errors['url']; ?>
							</span>
						</div>
					</div>
					<div class="form-group <?php if (isset($errors['date'])) echo 'has-error'; ?>">
						<label for="date" class="col-sm-3 control-label">Date</label>
						<div class="col-sm-9">
							<input type="text" id="date" class="form-control" name="event[date]" placeholder="Please select date" value="<?php if (isset($event['date'])) echo $event['date']; ?>">
							<span class="text-danger">
								<?php if (isset($errors['date'])) echo $errors['date']; ?>
							</span>
						</div>
					</div>
					<?php if($user): ?>
					<!--hide if not logged in-->
					<div class="form-group">
						<label class="col-sm-3 control-label">Published</label>
						<div class="col-sm-9">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="event[published]" <?php if ((isset($event['published']) && $event['published'] == 1) || (isset($event['published']) && $event['published'] == "on")) echo 'checked'; ?>>
								</label>
							</div>
						</div>
					</div>
					<!--/hide-->
					<?php endif; ?>
					<div class="form-group <?php if (isset($errors['details'])) echo 'has-error'; ?>">
						<label for="details" class="col-sm-3 control-label">Details</label>
						<div class="col-sm-9">
							<textarea class="form-control" rows="3" id="details" name="event[details]" placeholder="Details"><?php if (isset($event['details'])) echo $event['details']; ?></textarea>
							<span class="text-danger">
								<?php if (isset($errors['details'])) echo $errors['details']; ?>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-9 col-sm-offset-3">
							<a href="<?php echo ($user)? 'events.php': 'index.php'; ?>" class="btn btn-default">Cancel</a>
							<button type="submit" class="btn btn-primary"><?php echo ($user)? 'Create': 'Submit'; ?></button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</main>
	
	<footer class="navbar-fixed-bottom hidden-xs">
		<div class="row">
			<div class="col-xs-12 text-center">
				<p>&copy; Go Listen Live</p>
			</div>
		</div>
	</footer>
	
	<footer class="visible-xs">
		<div class="row">
			<div class="col-xs-12 text-center">
				<p>&copy; Go Listen Live</p>
			</div>
		</div>
	</footer>
	
	<script>
		$('#date').datepicker({
			format: "yyyy-mm-dd",
			weekStart: 1,
			orientation: "bottom left",
			autoclose: true,
			todayHighlight: true
		});
	</script>
</body>
</html>