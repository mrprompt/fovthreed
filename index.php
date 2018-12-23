<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Field of view visualization using 3D GIS software (FOV3D)</title>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap-grid.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css">
	<link rel="stylesheet" href="style.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
	<header>
      <!-- Fixed navbar -->
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="./">FOV3D</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        
		<div class="collapse navbar-collapse" id="navbarCollapse">
          	<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="#home">Home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#form">Simple Form</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#platepar">Upload Platepar</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#csv">Upload CSV</a>
				</li>
			</ul>
			<ul class="navbar-nav pull-right">
				<li class="nav-item">
					<a class="nav-link" href="https://globalmeteornetwork.org/">Go to GMN</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://github.com/mrprompt/fovthreed">Get Source Code</a>
				</li>
          </ul>
        </div>
      </nav>
    </header>

	<main role="main" class="container">
		<div class="row-fluid">
			<h2>Field of view visualization using 3D GIS software (FOV3D)</h2>

			<b>Introduction</b>

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

		<hr class="clearfix"/>

		<div class="row-fluid" id="form">
			<h3>Simple user interface</h3>

			<p>Use the form below to create a Google Earth-file for your camera setup.</p>
			
			<form action='process.php' method='post' role="form" class="form-inline">
				<fieldset class="col-lg-6 mb-5em">
					<legend>Camera details</legend>

					<div class="form-group">
						<label for="name" class="col-lg-4">Name:</label>
						<div class="col-lg-8">
							<input type='text' name='name' placeholder='My Camera' class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="latitude" class="col-lg-4">Latitude :</label>
						<div class="col-lg-8">
							<input type='text' name='latitude' placeholder="[-180, 180]" class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="longitude" class="col-lg-4">Longitude:</label>
						<div class="col-lg-8">
							<input type='text' name='longitude' placeholder="[-180, 180]" class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="altitude" class="col-lg-4">Altitude:</label>
						<div class="col-lg-8">
							<input type='text' name='altitude' placeholder="meters" class="form-control" required>
						</div>
					</div>
				</fieldset>

				<fieldset class="col-lg-6">
					<legend>Field of view (rectangle)</legend>

					<div class="form-group">
						<label for="azimuth" class="col-lg-4">Azimuth:</label>
						<div class="col-lg-8">
							<input type='text' name='azimuth' placeholder="[0, 360]" class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="elevation" class="col-lg-4">Elevation:</label>
						<div class="col-lg-8">
							<input type='text' name='elevation' placeholder="[0, 90]" class="form-control" required> 
						</div>
					</div>
					<div class="form-group">
						<label for="width" class="col-lg-4">Width:</label>
						<div class="col-lg-8">
							<input type='text' name='width' placeholder="[0, 90]" class="form-control" required> 
						</div>
					</div>
					<div class="form-group">
						<label for="height" class="col-lg-4">Height:</label>
						<div class="col-lg-8">
							<input type='text' name='height' placeholder="[0, 180]" class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="fov_altitude" class="col-lg-4">Range/Alt.:</label>
						<div class="col-lg-8">
							<input type='text' name='fov_altitude' placeholder="[km]" class="form-control" value='120' required>
						</div>
					</div>
					<div class="form-group">
						<label for="horizontal_cut" class="col-lg-4">Fixed upper altitude</label>
						<div class="col-lg-8">	 
							<input type='checkbox' name='horizontal_cut' checked='checked'>
						</div>
					</div>
				</fieldset>
				
				<fieldset class="col-lg-12">
					<legend>Display settings</legend>
							
					<div class="form-group">
						<label for="color" class="col-lg-4">Color:</label>
						<div class="col-lg-8">
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
					</div>									

					<div class="form-group">
						<label for="opacity" class="col-lg-4">Opacity:</label>
						<div class="col-lg-8">
							<select name='opacity' class="form-control"  required>
								<option value='00'>0%</option>
								<option value='40' selected='selected'>25%</option>
								<option value='80'>50%</option>
								<option value='c0'>75%</option>
								<option value='ff'>100%</option>
							</select>
						</div>
					</div>
				</fieldset>

				<div class="form-group">
					<input type="hidden" name="input_type" value="form"/>
					<input type='submit' name='submit' value='Send' class="btn btn-success">
				</div>
			</form>
		</div>

		<hr class="clearfix"/>

		<div class="row-fluid" id="platepar">
			<h3>Upload Platepar</h3>

			<p>Use the form below to upload your platepar file and generate a Google Earth-file for your camera setup.</p>
			
			<form action="process.php" method="post" enctype="multipart/form-data" role="form" class="form-inline">
				<fieldset class="col-lg-6 mb-2em">
					<legend>Display settings</legend>
							
					<div class="form-group">
						<label for="color" class="col-lg-4">Color:</label>
						<div class="col-lg-8">
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
					</div>

					<div class="form-group">
						<label for="opacity" class="col-lg-4">Opacity:</label>
						<div class="col-lg-8">
							<select name='opacity' class="form-control"  required>
								<option value='00'>0%</option>
								<option value='40' selected='selected'>25%</option>
								<option value='80'>50%</option>
								<option value='c0'>75%</option>
								<option value='ff'>100%</option>
							</select>
						</div>
					</div>
				</fieldset>

				<fieldset class="col-lg-6">
					<legend>Details</legend>

					<div class="form-group custom-file">
						<label for="platepar_file" class="custom-file-label">Platepar File</label>
						<input type='file' name='platepar_file' id="platepar_file" class="custom-file-input">
					</div>
					
					<div class="form-group">
						<label for="fov_altitude" class="col-lg-4">Range/Alt.:</label>
						<div class="col-lg-8">
							<input type='text' name='fov_altitude' placeholder="[km]" class="form-control" value='120' required>
						</div>
					</div>

					<div class="form-group">
						<label for="horizontal_cut" class="col-lg-4">Fixed upper altitude</label>
						<div class="col-lg-8">
							<input type='checkbox' name='horizontal_cut' id="horizontal_cut" checked='checked'>
						</div>
					</div>
				</fieldset>
				
				<div class="form-group text-right">
					<input type="hidden" name="input_type" value="platepar"/>
					<input type='submit' name='submit' value='Send' class="btn btn-success">
				</div>
			</form>
		</div>

		<hr class="clearfix"/>

		<div class="row-fluid" id="csv">
			<h3>Upload CSV</h3>

			<p>Use the form below to upload your csv file and generate a Google Earth-file for your camera setup.</p>
			
			<form action="process.php" method="post" enctype="multipart/form-data" role="form" class="form-inline">
				<fieldset class="col-lg-6">
					<legend>Field of view (rectangle)</legend>

					<div class="form-group">
						<label for="width" class="col-lg-4">Width:</label>
						<div class="col-lg-8">
							<input type='text' name='width' placeholder="[0, 90]" class="form-control" required> 
						</div>
					</div>
					<div class="form-group">
						<label for="height" class="col-lg-4">Height:</label>
						<div class="col-lg-8">
							<input type='text' name='height' placeholder="[0, 180]" class="form-control" required>
						</div>
					</div>
					<div class="form-group">
						<label for="fov_altitude" class="col-lg-4">Range/Alt.:</label>
						<div class="col-lg-8">
							<input type='text' name='fov_altitude' placeholder="[km]" class="form-control" value='120' required>
						</div>
					</div>
					<div class="form-group">
						<label for="horizontal_cut" class="col-lg-4">Fixed upper altitude</label>
						<div class="col-lg-8">	 
							<input type='checkbox' name='horizontal_cut' checked='checked'>
						</div>
					</div>
				</fieldset>

				<fieldset class="col-lg-6 mb-10em">
					<legend>Details</legend>

					<div class="form-group custom-file">
						<label for="csv_file" class="custom-file-label">CSV File</label>
						<input type='file' name='csv_file' id="csv_file" class="custom-file-input">
					</div>
				</fieldset>
				
				<div class="form-group col-lg-12">
					<input type="hidden" name="input_type" value="csv"/>
					<input type='submit' name='submit' value='Send' class="btn btn-success">
				</div>
			</form>
		</div>

		<hr class="clearfix"/>

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

				FOV3D was written by Geert Barentsen, the source code was downloaded from this <a href="https://www.cosmos.esa.int/web/meteor/fov3d">ESA website</a> 
				and rewrited to this version by <a href="https://github.com/mrprompt">Thiago Paes</a> to use on <a href="https://globalmeteornetwork.org">GMN</a> 
				website, you can get source code in <a href="https://github.com/mrprompt/fovthreed">GitHub</a>.
			</div>
		</div>
	</main>

    <footer class="footer">
      <div class="container text-center">
        <span class="text-muted">Field of view visualization using 3D GIS software (FOV3D) - <?= date('Y') ?></span>
      </div>
    </footer>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>