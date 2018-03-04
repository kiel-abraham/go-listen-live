<?php 
session_start();
require_once("functions.php");

if (isset($_SESSION["user"])) {
	if ($_SESSION["user"] == true) {
		header("Location: events.php");
		exit();
	}
}

$_SESSION["user"] = false;
$error = "";

if (isset($_POST["login"])) {
	$username = trim($_POST["login"]["username"]);
	$password = trim($_POST["login"]["password"]);
	if (empty($username) || empty($password)) {
		$error = "Please enter username and password";
	} else {
		$username = mysqli_real_escape_string($conn, $username);
		$password = mysqli_real_escape_string($conn, $password);
		$result = check_user($username);
		if (mysqli_num_rows($result) == 0) {
			$error = "User could not be found";
		} else {
			$x = mysqli_fetch_assoc($result);
			if (password_verify($password, $x["password"])) {
				$_SESSION["user"] = true;
				header("Location: events.php");
				exit();
			} else {
				$error = "Incorrect password";
			}
		}
	}
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="css/bootstap.min.css">
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>

	<title>Login | Go Listen Live</title>
</head>

<body>
	<header>
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="index.php">Go Listen Live</a>
				</div>
			</div>
		</nav>
	</header>

	<main class="container">
		<div class="row">
			<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" class="form-horizontal col-sm-6 col-sm-offset-3">
				<fieldset>
					<legend>Login</legend>
					<?php if (!empty($error)): ?>
					<div class="alert alert-dismissible alert-danger">
						<?php echo $error; ?>
					</div>
					<?php endif; ?>
					<div class="form-group">
						<label for="username" class="col-lg-2 control-label">Username</label>
						<div class="col-lg-10">
							<input type="text" class="form-control" id="username" name="login[username]" placeholder="Username">
						</div>
					</div>
					<div class="form-group">
						<label for="password" class="col-lg-2 control-label">Password</label>
						<div class="col-lg-10">
							<input type="password" class="form-control" id="password" name="login[password]" placeholder="Password">
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-10 col-lg-offset-2">
							<a href="index.php" class="btn btn-default">Cancel</a>
							<button type="submit" class="btn btn-primary">Login</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<div class="row">
			<div class="well col-sm-6 col-sm-offset-3 text-center">
				<p>Username: admin</p>
				<p>Password: Password123</p>
			</div>
		</div>
	</main>
	<footer class="navbar-fixed-bottom" style="padding-top: 100px">
		<div class="row ">
			<div class="col-xs-12 text-center">
				<p>&copy; Go Listen Live</p>
			</div>
		</div>
	</footer>
</body>
</html>