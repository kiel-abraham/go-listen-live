<?php
session_start();
require_once("functions.php");

if (!isset($_SESSION['user'])) {
	$_SESSION['user'] = false;
}
if ($_SESSION['user'] == false) {
	header("Location: index.php");
	exit();
}

$id = "";
$event = array();
$errors = array();

if (isset($_GET['id'])) {
	$id = mysqli_real_escape_string($conn, $_GET['id']);
	$x = get_event_by_id($id);
	$event = mysqli_fetch_assoc($x);
	if (mysqli_num_rows($x) == 0) {
		header("Location: events.php");
		exit();
	}
}

if (isset($_POST['event'])) {
	$id = $_POST['event']['id'];
	foreach ($_POST['event'] as $key => $value) {
		if (empty($value)) {
			if ($key == "url") {
				continue;
			}
			$errors[$key] = ucfirst($key) . " required";
		} else {
			$value = trim($value);
			$value = stripslashes($value);
			$value = htmlspecialchars($value);
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

	// if no errors then update event in database
	if (empty($errors)) {
		foreach ($event as $key => $value) {
			$event[$key] = mysqli_real_escape_string($conn, $value);
		}
		if (isset($event['published']) && $event['published'] == "on") {
			$published = 1;
		} else {
			$published = 0;
		}
		if (!isset($event['url'])) {
			$event['url'] = "";
		}
		update_event($event['id'], $event['date'], $event['url'], $event['details'], $published);
		if (mysqli_affected_rows($conn)) {
			header("Location: events.php");
			exit();
		}
	}
}

if (!isset($_POST['event']) && !isset($_GET['id'])) {
	header("Location: events.php");
	exit();
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
	
	<title>Edit Event | Go Listen Live</title>
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
				<div class="collapse navbar-collapse" id="menu-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="events.php">Events</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</nav>
	</header>
	
	<main class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>Edit Event</h1>
			</div>
		</div>
		<div class="row">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" class="form-horizontal col-xs-12">
				<fieldset>
					<input type="hidden" name="event[artist_name]" value="<?php echo $event['artist_name']; ?>">
					<div class="form-group">
						<label for="artist" class="col-sm-3 control-label">Artist</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="artist" value="<?php echo $event['artist_name']; ?>" disabled>
						</div>
					</div>
					<input type="hidden" name="event[venue_name]" value="<?php echo $event['venue_name']; ?>">
					<div class="form-group">
						<label for="venue" class="col-sm-3 control-label">Venue</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="venue" value="<?php echo $event['venue_name']; ?>" disabled>
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
					<div class="form-group <?php if (isset($errors['details'])) echo 'has-error'; ?>">
						<label for="details" class="col-sm-3 control-label">Details</label>
						<div class="col-sm-9">
							<textarea class="form-control" rows="3" id="details" name="event[details]"><?php if (isset($event['details'])) echo $event['details']; ?></textarea>
							<span class="text-danger">
								<?php if (isset($errors['details'])) echo $errors['details']; ?>
							</span>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-9 col-sm-offset-3">
							<a href="events.php" class="btn btn-default">Cancel</a>
							<button type="submit" name="event[id]" value="<?php echo $id; ?>" class="btn btn-primary">Update</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</main>
	
	<footer>
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