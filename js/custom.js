/*global $:false*/

$(document).ready(function () {

	$('#selectAll').change(function () {
		if ($(this).prop("checked")) {
			$(".check-event").prop("checked", true);
		} else {
			$(".check-event").prop("checked", false);
		}
	});
	
	$('input:checkbox').change(function () {
		if ($('.check-event:checked').length > 0) {
			$('.btn-off').prop('disabled', false);
		} else {
			$('#selectAll').prop("checked", false);
			$('.btn-off').prop('disabled', true);
		}
		if ($('.check-event:checked').length === $('.check-event').length) {
			$('#selectAll').prop("checked", true);
		} else {
			$('#selectAll').prop("checked", false);
		}
		var count = $('.check-event:checked').length;
		$('#count').text(count);
	});


	var items = [];
	$('.btn-off').click(function () {
		items = [];
		$('.check-event:checked').each(function () {
			items.push($(this).data('id'));
		});
	});
	$('.publish-btn').click(function () {
		$.ajax({
			type: "POST",
			url: "publish.php",
			data: {id: items},
			success: function () {
				location.reload();
			}
		});
	});
	$('.delete-btn').click(function () {
		$.ajax({
			type: "POST",
			url: "delete.php",
			data: {id: items},
			success: function () {
				$('.modal-body').html('<div class="alert alert-success">Item(s) deleted</div>');
				$('.modal-footer').remove();
				setTimeout(function () {
					location.reload();
				}, 1000);
			}
		});
	});


	var artistData;
	$('#artist').keyup(function () {
		$(this).parent().find('.text-danger').empty();
		var name = $(this).val();
		if (name !== "") {
			$.ajax({
				type: "POST",
				url: "artist.php",
				data: {artist: name},
				success: function (res) {
					artistData = JSON.parse(res);
					$('#artist-message').html(artistData.name);
				}
			});
		} else {
			$('#artist-message').empty();
			$('#artist_id').val('');
		}
	});
	$('#artist-message').click(function () {
		$('#artist').val($(this).text());
		$('#artist_id').val(artistData.id);
		$(this).closest('.form-group').removeClass('has-error');
		$(this).empty();
	});

	var venueData;
	$('#venue').keyup(function () {
		$(this).parent().find('.text-danger').empty();
		var name = $(this).val();
		if (name !== "") {
			$.ajax({
				type: "POST",
				url: "artist.php",
				data: {venue: name},
				success: function (res) {
					venueData = JSON.parse(res);
					$('#venue-message').html(venueData.name);
				}
			});
		} else {
			$('#venue-message').empty();
			$('#venue_id').val('');
			$('#address').val('');
			$('#address').closest('.form-group').removeClass('hidden');
		}
	});
	$('#venue-message').click(function () {
		$('#venue').val($(this).text());
		$('#venue_id').val(venueData.id);
		$('#address').val(venueData.address);
		$('#address').closest('.form-group').addClass('hidden');
		$(this).closest('.form-group').removeClass('has-error');
		$(this).empty();
	});
	if ($('#venue_id').val() === "") {
		$('#address').closest('.form-group').removeClass('hidden');
	} else {
		$('#address').closest('.form-group').addClass('hidden');
	}


	// Geocoding
	// API Key: AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s
	
	// Get address and return formatted address and coords
	$('#address').on('blur', function () {
		var address = $(this).val();
		if (address !== "") {
			address = address.replace(/ /g, "+");
			$.get("https://maps.googleapis.com/maps/api/geocode/json?address=" + address + "+AU&key=AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s", function (d) {
				if (d.results.length === 1) {
					$('#address').val(d.results[0].formatted_address);
					$('#lat').val(d.results[0].geometry.location.lat);
					$('#lng').val(d.results[0].geometry.location.lng);
				}
				if (d.results.length > 1) {
					$.each(d.results, function (e, f) {
						$('#address-results').append("<li data-lat='" + f.geometry.location.lat + "' data-lng='" + f.geometry.location.lng + "'>" + f.formatted_address + "</li>");
					});
				}
			});
			$(this).closest('.form-group').removeClass('has-error');
			$(this).parent().find('.text-danger').empty();
		}
	});

	$('#address').on('keyup', function () {
		var address = $(this).val();
		$('#address-results').html("");
		if (address === "") {
			$('#lat').val("");
			$('#lng').val("");
		}
	});

	$('#address-results').on('click', 'li', function () {
		$('#address').val($(this).text());
		$('#lat').val($(this).attr("data-lat"));
		$('#lng').val($(this).attr("data-lng"));
		$('#address-results').empty();
	});
		
		// https://maps.googleapis.com/maps/api/geocode/json?address=35+boundary+st,+South+Brisbane,+QLD&key=AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s
		// https://maps.googleapis.com/maps/api/geocode/json?address=35+boundary+st+South+Brisbane+queensland&key=AIzaSyD48QGfqX_4WNqWhhy5PSmnlGWgvfb4v8s


});