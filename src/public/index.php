<?php
$script_uri = "index.php";


// Converts the value of a checkbox to a PHP boolean
function check2bool($check)
{
    if ($check == "on") {
        return true;
    }
    return false;
}


// If the form was submitted, run DS3D
if (isset($_POST["submit"])) {
    require_once("FOV3D.class.php");
    
    if (isset($_POST["csv"])) {
        $ges = new GoogleEarthStation(trim($_POST["fov_altitude"]), $_POST["horizontal_cut"]);
        $csv = str_replace(array("\r\n", "\n\r", "\r"), "\n", $_POST["csv"]);
        $line_array = explode("\n", $csv);
		
		foreach ($line_array as $line) {
			$input = explode(",", $line);
			
            if (count($input) > 5) {
                $name = trim($input[0]);
                $color = trim($input[1]);
                $latitude = trim($input[2]);
                $longitude = trim($input[3]);
                $height = trim($input[4]);
                $corners = array_slice($input, 5);
    
                $ges->addPlacemark($name, $latitude, $longitude, $height, $corners, $color);
            }
        }
    } else {
		/*
        if ($_POST["name"] == ""
            or $_POST["opacity"] == ""
            or $_POST["color"] == ""
            or $_POST["latitude"] == ""
            or $_POST["longitude"] == ""
            or $_POST["altitude"] == ""
            or $_POST["azimuth"] == ""
            or $_POST["elevation"] == ""
            or $_POST["height"] == ""
            or $_POST["width"] == ""
            or $_POST["fov_altitude"] == "") {
            echo "ERROR: You must provide a value for all fields.";
            exit;
        }
        */
		
        if (!isset($_FILES['plateparFile'])) {
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
		}
		
		if (isset($_FILES['plateparFile'])) {
			$platepar_raw = file_get_contents($_FILES['plateparFile']['tmp_name']);
			$platepar_json = json_decode($platepar_raw, true);

            $name = $platepar_json['station_code'];
            $color = trim($_POST["opacity"]) . trim($_POST["color"]);
            $latitude = $platepar_json['lat'];
            $longitude = $platepar_json['lon'];
            $altitude = $platepar_json['alt_centre'];
            $azim = $platepar_json['az_centre'];
            $elev = $platepar_json['elev'];
            $height = $platepar_json['Y_res'];
			$width = $platepar_json['X_res'];
			$fov_altitude = $platepar_json['fov_h'];
			$horizontal_cut = $_POST["horizontal_cut"];
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
                
        $ges = new GoogleEarthStation($fov_latitude, $horizontal_cut);
        $ges->addPlacemark($name, $latitude, $longitude, $altitude, $corners, $color);
    }
    
    
    // Let the browser know that this is a special application file (KML)
    header('Content-Type: application/vnd.google-earth.kml+xml');
    header('Content-Disposition: attachment; filename="fov3d.kml"');
    echo $ges->getKml();
    exit();
}

if (isset($_GET["getsource"])) {
    $file = "FOV3D.class.php";
    
    header('Content-Type: plain/text');
    header('Content-Disposition: attachment; filename="'.$file.'"');
    $contents = file($file);
    echo implode($contents);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Field of view visualization using 3D GIS software (FOV3D)</title>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
	<style>
		body {
			background-color: #DEDEDE;
		}

		body > div.container {
			background-color: white;
		}
	</style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2>Field of view visualization using 3D GIS software (FOV3D)</h2>

				<h3>Introduction</h3>

				<p>To get a better understanding of the atmospheric volume that is monitored by a (meteor) camera,
				one may use 3D GIS-software such as <a href="http://earth.google.com/">Google Earth</a> to interactively explore the field of view of a camera.
				To do this, we created a PHP-script called "FOV3D". The script generates a 3D semi-transparant polygon representing the field of view of a given camera.</p>

				<p>The script produces output in the <a href="http://en.wikipedia.org/wiki/Keyhole_Markup_Language">KML-format</a>,
				which is an XML-based language for describing geospatial data. 
				KML-files can be imported into GIS-software such as <a href="http://earth.google.com/">Google Earth</a>, <a href="http://worldwind.arc.nasa.gov/">NASA World Wind</a> and <a href="http://www.esri.com/software/arcgis/explorer/index.html">ArcGIS</a>.
				However, the files generated by this script have only been verified to work with Google Earth.</p>

				<p class="text-center">
					<img src='fov3d.jpg' style='margin-top:2em; margin-bottom:1em;'>
					<br/>
					<i>Example of a FOV3D-generated file in Google Earth, showing the MRG <a href='http://adsabs.harvard.edu/abs/2010pim7.conf....7K'>double-station setup in the Netherlands</a>.</i>
				</p>
			</div>
		</div>

		<div class="row">
			<!-- left collumn -->
			<div class="col-lg-6">
				<h3>Simple user interface</h3>

				<p>Use the form below to create a Google Earth-file for your camera setup.</p>

				<form action='<?php echo $script_uri; ?>' method='post'>
					<fieldset>
						<legend>Camera details</legend>

						<div class="form-group">
							<label for="name">Name:</label>
							<input type='text' name='name' placeholder='My Camera' class="form-control" required>
						</div>
						<div class="form-group">
							<label for="latitude">Latitude:</label>
							<input type='text' name='latitude' class="form-control" required>&nbsp;&deg;&nbsp;[-90,&nbsp;90]
						</div>
						<div class="form-group">
							<label for="longitude">Longitude:</label>
							<input type='text' name='longitude' class="form-control" required>&nbsp;&deg;&nbsp;[-180,&nbsp;180]
						</div>
						<div class="form-group">
							<label>Altitude:</label>
							<input type='text' name='altitude' class="form-control" required> m
						</div>
					</fieldset>

					<fieldset>
						<legend>Field of view (rectangle)</legend>

						<div class="form-group">
							<label for="azimuth">Azimuth:</label>
							<input type='text' name='azimuth' class="form-control" required>&nbsp;&deg;&nbsp;[0,&nbsp;360]
						</div>
						<div class="form-group">
							<label for="elevation">Elevation:</label>
							<input type='text' name='elevation' class="form-control" required>&nbsp;&deg;&nbsp;[0,&nbsp;90]
						</div>
						<div class="form-group">
							<label for="width">Width:</label>
							<input type='text' name='width' class="form-control" required>&nbsp;&deg;&nbsp;[0,&nbsp;90]
						</div>
						<div class="form-group">
							<label for="height">Height:</label>
							<input type='text' name='height' class="form-control" required>&nbsp;&deg;&nbsp;[0,&nbsp;180]
						</div>
						<div class="form-group">
							<label for="fov_altitude">Range/Alt.:</label>
							<input type='text' name='fov_altitude' class="form-control" value='120' required> km
						</div>
						<div class="form-group">
							<label for="horizontal_cut">Fixed upper altitude</label>	 
							<input type='checkbox' name='horizontal_cut' checked='checked'>
						</div>
					</fieldset>

					<fieldset>
						<legend>Display settings</legend>
								
						<div class="form-group">
							<label for="color">Color:</label>
							<select name='color' class="form-control"  required>
								<option value='0000FF'>Red</option>
								<option value='FFFF00'>Cyan</option>
								<option value='FF0000'>Blue</option>
								<option value='0A0000'>DarkBlue</option>
								<option value='6E8DDA'>LightBlue</option>
								<option value='080008'>Purple</option>
								<option value='00FFFF'>Yellow</option>
								<option value='00FF00'>Lime</option>
								<option value='FF00FF'>Magenta</option>
								<option value='FFFFFF'>White</option>
								<option value='0C0C0C'>Silver</option>
								<option value='080808'>Gray</option>
								<option value='000000'>Black</option>
								<option value='005AFF'>Orange</option>
								<option value='A2A25A'>Brown</option>
								<option value='000008'>Maroon</option>
								<option value='000800'>Green</option>
								<option value='000808'>Olive</option>
							</select>
						</div>									

						<div class="form-group">
							<label for="opacity">Opacity:</label>
							<select name='opacity' class="form-control"  required>
								<option value='00'>0%</option>
								<option value='40'>25%</option>
								<option value='80'>50%</option>
								<option value='c0' selected='selected'>75%</option>
								<option value='ff'>100%</option>
							</select>
						</div>
					</fieldset>

					<div class="form-group">	
						<input type='submit' name='submit' value='Download KML file' style='width:17.5em;'>
					</div>
				</form>
			</div>
			<!-- end of left collumn -->

			<!-- right collumn" -->
			<div class="col-lg-6">
				<h3>Upload file</h3>

				<p>Use the form below to upload your platepar file and generate a Google Earth-file for your camera setup.</p>

				<form action="<?php echo $script_uri; ?>" class="form" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="platepar_file">Platepar File</label>
						<input type='file' name='platepar_file' id="platepar_file" class="form-control">
					</div>

					<div class="form-group">
						<label for="horizontal_cut">Fixed upper altitude</label>
						<input type='checkbox' name='horizontal_cut' id="horizontal_cut" checked='checked'>
					</div>

					<fieldset>
						<legend>Display settings</legend>
								
						<div class="form-group">
							<label for="color">Color:</label>
							<select name='color' class="form-control" required>
								<option value='0000FF'>Red</option>
								<option value='FFFF00'>Cyan</option>
								<option value='FF0000'>Blue</option>
								<option value='0A0000'>DarkBlue</option>
								<option value='6E8DDA'>LightBlue</option>
								<option value='080008'>Purple</option>
								<option value='00FFFF'>Yellow</option>
								<option value='00FF00'>Lime</option>
								<option value='FF00FF'>Magenta</option>
								<option value='FFFFFF'>White</option>
								<option value='0C0C0C'>Silver</option>
								<option value='080808'>Gray</option>
								<option value='000000'>Black</option>
								<option value='005AFF'>Orange</option>
								<option value='A2A25A'>Brown</option>
								<option value='000008'>Maroon</option>
								<option value='000800'>Green</option>
								<option value='000808'>Olive</option>
							</select>
						</div>									

						<div class="form-group">
							<label for="opacity">Opacity:</label>
							<select name='opacity' class="form-control"  required>
								<option value='00'>0%</option>
								<option value='40'>25%</option>
								<option value='80'>50%</option>
								<option value='c0' selected='selected'>75%</option>
								<option value='ff'>100%</option>
							</select>
						</div>
					</fieldset>

					<div class="form-group">	
						<input type='submit' name='submit' value='Upload platepar file'>
					</div>
				</form>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-12">
				<h4>Notes:</h4>

				<ul>
					<li>The script will generate a KML-file that should be opened locally on your computer using Google Earth.</li>
					<li>All angles should be entered in decimal degrees.</li>
					<li>Western longitudes and southern latitudes should be given as negative values.</li>
					<li>For azimuth, north is 0 degrees and south is 180 degrees.</li>
					<li>The simple user interface will generate a rectangular field of view. The 4 corners of this rectangle will have the following relative coordinates (azimuth, elevation): 
						<ul>
							<li>1: (azimuth - width/2, elevation + height/2);</li>
							<li>2: (azimuth + width/2, elevation + height/2);</li>
							<li>3: (azimuth + width/2, elevation - height/2);</li>
							<li>4: (azimuth - width/2, elevation - height/2).</li>
						</ul>
					</li>
					<li>The "Range" value is the distance in kilometer from the camera to the end of the field of view. If "Fixed upper altitude" is checked, then the field of view will be drawn up to the given altitude.</li>
				</ul>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-12">
				<h3>Source code</h3>

				FOV3D was written by Geert Barentsen, the source code was downloaded from this <a href="https://www.cosmos.esa.int/web/meteor/fov3d">ESA website</a>.
			</div>
		</div>
	</div>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>