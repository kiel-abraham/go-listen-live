<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="css/bootstap.min.css">
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" media="all" />
	<link rel="stylesheet" href="css/style.css">

	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/custom.js"></script>

	<title>Go Listen Live</title>
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
						<li class="dropdown location">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="true">Change Location <span class="fa fa-angle-down"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li>
									<div class="input-group">
										<input type="text" id="post-val" class="form-control" maxlength="4" placeholder="Postcode" autofocus>
										<span class="input-group-btn">
											<button id="post-btn" class="btn btn-primary" title="Search"><i class="fa fa-search"></i></button>
										</span>
									</div>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>
	</header>

	<main class="container">

		<section id="map" class="row" style="padding-bottom: 50px;">
			<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2">
				<div id="map-result" style="height: 400px; width: 100%"></div>
				<!--this is where map will be-->
			</div>
		</section>

		<section id="list" class="row">
			<div id="search-result" class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2">
				<!--this is where event list results will be-->
			</div>
		</section>
	</main>

	<footer style="padding-top: 100px">
		<div class="row">
			<div class="col-xs-4 text-center">
				<a href="add-event.php">Submit an event</a>
			</div>
			<div class="col-xs-4 text-center">
				<a href="#" data-toggle="modal" data-target="#email-modal"><span class="text-danger">Submit an edit</span></a>
			</div>
			<div class="col-xs-4 text-center">
				<a href="login.php">Admin Login</a>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-xs-12 text-center">
				<p>&copy; Go Listen Live</p>
			</div>
		</div>
	</footer>

	<div id="email-modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Submit an edit</h4>
				</div>
				<div class="modal-body">
					<form class="form-horizontal col-sm-6 col-sm-offset-3">
						<fieldset>
							<div class="form-group">
								<label for="email" class="control-label">Email</label>
								<div>
									<input type="text" class="form-control" id="email" placeholder="Your email">
								</div>
							</div>
							<div class="form-group">
								<label for="message">Message</label>
								<div>
									<textarea class="form-control" id="message" rows="3" placeholder="Please enter your message..."></textarea>
								</div>
							</div>
							<div class="form-group">
								<div>
									<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
									<a href="#" class="btn btn-primary disabled">Send</a>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<div class="modal-footer"></div>
			</div>
		</div>
	</div>
	<!--end email modal-->
	
	<!--Geolocation map-->
	<script>
		
		function getLocation() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {
					latlng = {
						lat: position.coords.latitude,
						lng: position.coords.longitude
					};
					initMap(latlng);
				}, function() {
					// if error or location service declined
					noGeo();
				});
			} else {
				// Browser doesn't support Geolocation
				noGeo();
			}
		}
		
		function noGeo() {
			var loc = prompt("Please enter a postcode for your location");
			manualPostcode(loc);
		}

		function manualPostcode(postcode) {
			$.get("https://maps.googleapis.com/maps/api/geocode/json?components=country:AU|postal_code:" + postcode + "+&sensor=false&key=AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s", function (d) {
				if (d.results.length === 1) {
					var latlng = {
						lat: d.results[0].geometry.location.lat,
						lng: d.results[0].geometry.location.lng
					};
					initMap(latlng);
				} else {
					alert("Invalid postcode. Please try again.");
					$('.location').addClass('open');
				}
			});
			$('#post-val').val("");
		}
		
		$('#post-btn').click(function () {
			manualPostcode($('#post-val').val());
		});
		
		$('#post-val').keypress(function (e) {
			if (e.which === 13) {
				manualPostcode($('#post-val').val());
				$('.location').removeClass('open');
			}
		});
		
		$('div').on('click', '.alert', function () {
			noGeo();
		});
		
		
		function initMap(currentLocation) {
			
			var map = new google.maps.Map(document.getElementById('map-result'), {
				center: currentLocation,
				zoom: 13,
				mapTypeId: 'roadmap'
			});
			
			var currentMarker = new google.maps.Marker({
				position: currentLocation,
				map: map,
				title:"Your location",
				icon: {
					path: google.maps.SymbolPath.CIRCLE,
					scale: 5
				}
			});
			
			var events;
			(function () {
				$.ajax({
					type: "GET",
					url: "search.php",
					async: false,
					data: {lat: currentLocation.lat, lng: currentLocation.lng, distance: 3},
					success: function (res) {
						events = JSON.parse(res);
						if ("none" in events) {
							var output;
							output  = '<div class="alert alert-danger text-center">';
							output += 'No events found. Trying <a href="#">changing location</a>';
							output += '</div>';
							$('#search-result').html(output);
						} else {
							$('#search-result').html("");
							$.each(events, function(idx, obj){
								var output;
								output  = '<div id="' + obj.id + '" class="panel panel-default">';
								output += '<div class="panel-heading">';
								output += '<h2>' + obj.artist_name + ' @ ' + obj.venue_name + '</h2>';
								output += '</div>';
								output += '<div class="panel-body">';
								output += '<div class="col-sm-6">';
								output += '<img src="';
								if (obj.url === null || obj.url === "") {
									output += 'images/placeholder.jpg';
								} else {
									output += obj.url;
								}
								output += '" class="img-rounded img-responsive" alt="' + obj.artist_name + ' @ ' + obj.venue_name + '">';
								output += '</div>';
								output += '<div class="col-sm-6">';
								output += '<p>' + obj.date + '</p>';
								output += '<p class="text-primary">' + parseFloat(obj.distance).toFixed(1) + ' km</p>';
								output += '<p>' + obj.address + '</p>';
								output += '<p>Details: ' + obj.details + '</p>';
								output += '<a href="#map">Back to map</a>';
								output += '</div>';
								output += '</div>';
								$('#search-result').append(output);
							});
						}
					}
				});
			})();
			
			for (var i = 0; i < events.length; i++) {
				addMarker(events[i]);
			}
			
			function addMarker(x) {
				var lat = parseFloat(x.lat);
				var lng = parseFloat(x.lng);
				var marker = new google.maps.Marker({
					map: map,
					position: {lat: lat, lng: lng}
				});
				var infowindow = new google.maps.InfoWindow({
					content: '<a href="#' + x.id + '">' + x.venue_name + '</a>'
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
			}
		}

	</script>
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s&callback=getLocation"></script>
	
</body>

</html>
