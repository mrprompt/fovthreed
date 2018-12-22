<?php
require_once("FOV3D.class.php");

$script_uri = "index.php";

// If the form was submitted, run DS3D
if (isset($_POST["submit"])) {
	switch ($_POST['input_type']) {
		case 'form':
			$name = trim($_POST["name"]);
			$color = trim($_POST["opacity"]).trim($_POST["color"]);
			$latitude = trim($_POST["latitude"]);
			$longitude = trim($_POST["longitude"]);
			$altitude = trim($_POST["altitude"]);
			$azim = trim($_POST["azimuth"]);
			$elev = trim($_POST["elevation"]);
			$height = trim($_POST["height"]);
			$width = trim($_POST["width"]);
			$fov_altitude = trim($_POST["fov_altitude"]);
			$horizontal_cut = $_POST["horizontal_cut"];
			break;
	
		case 'platepar':
			if (isset($_FILES['platepar_file']) && file_exists($_FILES['platepar_file']['tmp_name'])) {
				$platepar_raw = file_get_contents($_FILES['platepar_file']['tmp_name']);
				$platepar_json = json_decode($platepar_raw, true);

				$name = $platepar_json['station_code'];
				$color = trim($_POST["opacity"]) . trim($_POST["color"]);
				$latitude = $platepar_json['lat'];
				$longitude = $platepar_json['lon'];
				$altitude = $platepar_json['elev'];
				$azim = $platepar_json['az_centre'];
				$elev = $platepar_json['alt_centre'];
				$height = $platepar_json['fov_v'];
				$width = $platepar_json['fov_h'];
				$fov_altitude = trim($_POST["fov_altitude"]);
				$horizontal_cut = $_POST["horizontal_cut"];
			}
			break;

		case 'csv':
			break;
	}
	
	// The order of the corners is very important (should always be clockwise!!)
	$corners = array();
	
	if ($elev + ($height/2.0) > 90) {
		$corners[] = $azim - ($width/2.0);
		$corners[] = $elev + ($height/2.0);
		$corners[] = $azim + ($width/2.0);
		$corners[] = $elev + ($height/2.0);
		$corners[] = $azim - ($width/2.0);
		$corners[] = $elev - ($height/2.0);
		$corners[] = $azim + ($width/2.0);
		$corners[] = $elev - ($height/2.0);
	} else {
		$corners[] = $azim - ($width/2.0);
		$corners[] = $elev + ($height/2.0);
		$corners[] = $azim + ($width/2.0);
		$corners[] = $elev + ($height/2.0);
		$corners[] = $azim + ($width/2.0);
		$corners[] = $elev - ($height/2.0);
		$corners[] = $azim - ($width/2.0);
		$corners[] = $elev - ($height/2.0);
	}

	$ges = new GoogleEarthStation($fov_altitude, $horizontal_cut, $name);
	$ges->addPlacemark($name, $latitude, $longitude, $altitude, $corners, $color);
    
    // Let the browser know that this is a special application file (KML)
    header('Content-Type: application/vnd.google-earth.kml+xml');
    header('Content-Disposition: attachment; filename="' . $name . '.kml"');
    echo $ges->getKml();
    exit();
}
