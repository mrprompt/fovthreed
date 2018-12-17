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
        
        
        $name = trim($_POST["name"]);
        $color = trim($_POST["opacity"]).trim($_POST["color"]);
        $latitude = trim($_POST["latitude"]);
        $longitude = trim($_POST["longitude"]);
        $altitude = trim($_POST["altitude"]);
        $azim = trim($_POST["azimuth"]);
        $elev = trim($_POST["elevation"]);
        $height = trim($_POST["height"]);
        $width = trim($_POST["width"]);
        
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
                
        $ges = new GoogleEarthStation(trim($_POST["fov_altitude"]), $_POST["horizontal_cut"]);
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
			max-width: 50rem; 
			margin: auto; 
			padding: 10px; 
			background-color: white; 
			font-family: Arial
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
		<h2>Field of view visualization using 3D GIS software (FOV3D)</h2>

		<h3>Introduction</h3>

		<p>To get a better understanding of the atmospheric volume that is monitored by a (meteor) camera,
		one may use 3D GIS-software such as <a href="http://earth.google.com/">Google Earth</a> to interactively explore the field of view of a camera.
		To do this, we created a PHP-script called "FOV3D". The script generates a 3D semi-transparant polygon representing the field of view of a given camera.</p>

		<p>The script produces output in the <a href="http://en.wikipedia.org/wiki/Keyhole_Markup_Language">KML-format</a>,
		which is an XML-based language for describing geospatial data. 
		KML-files can be imported into GIS-software such as <a href="http://earth.google.com/">Google Earth</a>, <a href="http://worldwind.arc.nasa.gov/">NASA World Wind</a> and <a href="http://www.esri.com/software/arcgis/explorer/index.html">ArcGIS</a>.
		However, the files generated by this script have only been verified to work with Google Earth.</p>

		<p align="center">
			<img src='fov3d.jpg' style='margin-top:2em; margin-bottom:1em;' />
			<br/>
			<i>Example of a FOV3D-generated file in Google Earth, showing the MRG <a href='http://adsabs.harvard.edu/abs/2010pim7.conf....7K'>double-station setup in the Netherlands</a>.</i>
		</p>

		<h3>Simple user interface</h3>

		<p>Use the form below to create a Google Earth-file for your camera setup.</p>

		<table class="table">
			<tr>
				<td>
					<form action='<?php echo $script_uri; ?>' method='post'>
						<table style='border: 1px solid #bbbbbb; padding:0.5em; width:17em;'>
							<tr><td colspan='2'><b>Camera details</b></td></tr>
							<tr><td>Name:</td><td><input type='text' name='name' value='MyCamera' style='width:9em;' /></td></tr>
							<tr><td>Latitude:</td><td><input type='text' name='latitude' style='width:9em;' />&nbsp;&deg;&nbsp;[-90,&nbsp;90]</td></tr>
							<tr><td>Longitude:</td><td><input type='text' name='longitude' style='width:9em;' />&nbsp;&deg;&nbsp;[-180,&nbsp;180]</td></tr>
							<tr><td>Altitude:</td><td><input type='text' name='altitude' style='width:9em;' /> m</td></tr>
							
							<tr><td colspan='2'><br/><b>Field of view (rectangle)</b></td></tr>
							<tr><td>Azimuth:</td><td><input type='text' name='azimuth' style='width:9em;' />&nbsp;&deg;&nbsp;[0,&nbsp;360]</td></tr>
							<tr><td>Elevation:</td><td><input type='text' name='elevation' style='width:9em;' />&nbsp;&deg;&nbsp;[0,&nbsp;90]</td></tr>
							<tr><td>Width:</td><td><input type='text' name='width' style='width:9em;' />&nbsp;&deg;&nbsp;[0,&nbsp;90]</td></tr>
							<tr><td>Height:</td><td><input type='text' name='height' style='width:9em;' />&nbsp;&deg;&nbsp;[0,&nbsp;180]</td></tr>
							<tr><td>Range/Alt.:</td><td><input type='text' name='fov_altitude' style='width:9em;' value='120' /> km</td></tr>
							<tr><td> </td><td><input type='checkbox' name='horizontal_cut' checked='checked' style='vertical-align:middle; margin-left:0em;' /> Fixed upper altitude</td></tr>
				
							<tr><td colspan='2'><br/><b>Display settings</b></td></tr>
							<tr>
							<td>Color:</td>
							<td>
								<select name='color' style='width:9em;'>
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
							</td></tr>
							<tr><td>Opacity:</td><td><select name='opacity' style='width:9em;'>
								<option value='00'>0%</option>
								<option value='40'>25%</option>
								<option value='80'>50%</option>
								<option value='c0' selected='selected'>75%</option>
								<option value='ff'>100%</option>
							</select></td></tr>
							
							<tr><td colspan='2'><br /><input type='submit' name='submit' value='Download KML file' style='width:17.5em;' /></td></tr>
						</table>
					</form>

				</td>
				<td style='vertical-align:top; padding-left:2em;'>
					Notes:
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
					<li>The "Range" value is the distance in kilometer from the camera to the end of the field of view. 
						If "Fixed upper altitude" is checked, then the field of view will be drawn up to the given altitude.</li>
					</ul>

				</td>
			</tr>
		</table>

		<h3>Source code</h3>

		FOV3D was written by Geert Barentsen, the source code was downloaded from this <a href="https://www.cosmos.esa.int/web/meteor/fov3d">ESA website</a>.
	</div>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>