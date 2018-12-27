<?php
require_once("FOV3D.class.php");

function corners($azim, $elev, $width, $height)
{
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
    
    return $corners;
}

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
            
            $corners = corners($azim, $elev, $width, $height);

            $ges = new GoogleEarthStation($fov_altitude, $horizontal_cut, $name);
            $ges->addPlacemark($name, $latitude, $longitude, $altitude, $corners, $color);
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
                
                $corners = corners($azim, $elev, $width, $height);

                $ges = new GoogleEarthStation($fov_altitude, $horizontal_cut, $name);
                $ges->addPlacemark($name, $latitude, $longitude, $altitude, $corners, $color);
			}
			break;

        case 'csv':
            if (isset($_FILES['csv_file']) && file_exists($_FILES['csv_file']['tmp_name'])) {
				$colors = [
					'0000FF',
					'FFFF00',
					'FF0000',
					'0A0000',
					'6E8DDA',
					'080008',
					'00FFFF',
					'00FF00',
					'FF00FF',
					'FFFFFF',
					'0C0C0C',
					'080808',
					'000000',
					'005AFF',
					'A2A25A',
					'000008',
					'000800',
					'000808',
				];

				$row = 0;

                if (($handle = fopen($_FILES['csv_file']['tmp_name'], "r")) !== FALSE) {
                    $fov_altitude = trim($_POST["fov_altitude"]);
                    $horizontal_cut = $_POST["horizontal_cut"];

                    $ges = new GoogleEarthStation($fov_altitude, $horizontal_cut);

                    while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
                        $name = $row[0];
                        $color = trim($_POST["opacity"]) . $row[1];
                        $latitude = $row[2];
                        $longitude = $row[3];
						$altitude = $row[4];
						$height = $row[6];
						$width = $row[5];

						if ($row[1] == '-1') {
							$color = trim($_POST["opacity"]) . $colors[ array_rand($colors, 1) ];
						}

                        $corners = array_slice($row, 7);

                        $ges->addPlacemark($name, $latitude, $longitude, $altitude, $corners, $color);

                        $row++;
                    }
                    
                    fclose($handle);

                    // overwriting name to create download file
                    $name = 'FOV3D';
                }
            }
			break;
	}
	
	if (empty($name)) {
		$name = 'FOV3D';
	}

    // Let the browser know that this is a special application file (KML)
    header('Content-Type: application/vnd.google-earth.kml+xml');
    header('Content-Disposition: attachment; filename="' . $name . '.kml"');
    echo $ges->getKml();
    exit();
}
