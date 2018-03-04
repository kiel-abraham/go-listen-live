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

// get unpublished events
$unpub_events = get_events("", 0);

$no_unpub = false;
if (mysqli_num_rows($unpub_events) == 0) {
	$no_unpub = true;
}

if (isset($_GET['status']) && $_GET['status'] == "unpublished") {
	$events = $unpub_events;
	$published = false;
	if (mysqli_num_rows($events) == 0) {
		header('Location: events.php');
		exit();
	}
} else {
	$search = "";
	if (isset($_GET['search'])) {
		$search = mysqli_real_escape_string($conn, $_GET['search']);
	}
	// get published events
	$events = get_events($search, 1);
	$published = true;
}

$no_results = false;
if (mysqli_num_rows($events) == 0) {
	$no_results = true;
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
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" media="all"/>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/custom.js"></script>
	
	<title>Events | Go Listen Live</title>
</head>


<!--only accessible by admin-->


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
						<li><a href="add-event.php">Add Event</a></li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</nav>
	</header>

	<main class="container">
		<div class="row">
			<div class="col-xs-12">
				<h1>Events</h1>
			</div>
		</div>
		<div class="row">
			<?php if (!$no_unpub): ?>
			<form method="get" action="events.php">
				<div class="col-xs-12 col-sm-3">
					<button class="btn btn-default" name="status" value="<?php echo ($published) ? 'unpublished': 'published'; ?>">
						<?php echo ($published) ? "Unpublished ": "Published"; ?>
						<?php if ($published): ?>
							<span class="badge">
								<?php echo mysqli_num_rows($unpub_events); ?>
							</span>
						<?php endif; ?>
					</button>
				</div>
			</form>
			<?php endif; ?>
			<?php if ($published): ?>
			<form role="search" method="get" action="events.php">
				<div class="form-group col-xs-12 col-sm-9 pull-right">
					<label class="control-label hidden">Search</label>
					<div class="input-group">
						<input type="text" class="form-control" placeholder="Search for Artist or Venue" name="search" autofocus>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="submit" title="Search"><i class="fa fa-search"></i></button>
						</span>
					</div>
				</div>
			</form>
			<?php endif; ?>
		</div>
		<div class="row">
			<?php if (!$no_results): ?>
			<div class="col-xs-12">
				<table class="table table-striped table-hover">
					<thead class="text-primary">
						<tr>
							<th><input type="checkbox" id="selectAll"></th>
							<th>Artist</th>
							<th>Venue</th>
							<th>Address</th>
							<th>Date</th>
							<th>Edit</th>
						</tr>
					</thead>
					<tbody>
						
						<?php if (mysqli_num_rows($events) > 0):
							while ($row = mysqli_fetch_assoc($events)): ?>
						
						<tr>
							<td><input type="checkbox" class="check-event" data-id="<?php echo $row['event_id']; ?>"></td>
							<td><?php echo $row['artist_name']; ?></td>
							<td><?php echo $row['venue_name']; ?></td>
							<td><?php echo $row['address']; ?></td>
							<td><?php echo $row['date']; ?></td>
							<td>
								<a href="edit-event.php?id=<?php echo $row['event_id']; ?>" class="no-underline edit-btn"><span class="text-default"><i class="fa fa-edit" title="Edit"></i>Edit</span></a>
							</td>
						</tr>
						
						<?php endwhile; endif; ?>	
							
					</tbody>
				</table>
				<button type="submit" class="btn btn-danger btn-off" data-toggle="modal" data-target="#delete-modal" disabled>Delete</button>
				<?php if (!$published): ?>
				<button type="submit" class="btn btn-primary btn-off publish-btn" disabled>Publish</button>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if ($no_results): ?>
			<div class="col-xs-12">
				<div class="alert alert-dismissible alert-danger">
					<p class="text-center">No events found! <a href="events.php">Go back</a> or search again</p>
				</div>
			</div>
			<?php endif; ?>
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
	
<!--	end of footer and page-->
	
	<div id="delete-modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Delete Event</h4>
				</div>
				<div class="modal-body">
					<p>You are about to delete <span id="count"></span> event(s). This action is final!</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-danger delete-btn">Delete</button>
				</div>
			</div>
		</div>
	</div>
	
</body>
</html>