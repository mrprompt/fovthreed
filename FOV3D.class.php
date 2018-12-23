<?php
/**
 * FOV3D  (Double Station 3D visualization)
 * Class to create a KML file that contains a semi-transparent polygon for the Field Of View's (FOV) of meteor cameras.
 *
 * Beware of bugs!!
 *
 * @author Geert Barentsen (geert@barentsen.be)
 * @version 2008-01-23
 *
 * Usage example:
 * $ges = new GoogleEarthStation(100,true);
 * $ges->addPlacemark("Detlef", 52.26339, 4.48933, 0, array(10,50,20,50,20,40,10,40), "8000ff00");
 * echo $ges->getKml();
 *
 * Angles are in decimal degrees!
 * Station height is in meter, FOV range in kilometer!!
 */
class GoogleEarthStation
{
    // Field of view parameters are stored as separate variables
    public $cut_horizontal;
    public $cut_range;
    public $placemarks = "";
    
    /**
	 * Class constructor
	 *
     * @param cut_range Range of the FOV polygon (km)
	 * @param cut_horizontal Cut top of FOV horizontally? (true/false)
	 * @param $camera_name Camera identifier
     */
    public function __construct($cut_range, $cut_horizontal, $camera_name = 'FOV3D')
    {
        $this->cut_horizontal = $cut_horizontal;
        $this->cut_range = $cut_range;
        $this->camera_name = $camera_name;
    }
    
    /**
    * Get the KML file containing placemarks
    **/
    public function getKml()
    {
        $kml = "<?xml version='1.0' encoding='UTF-8'?>";
        $kml .= "<kml xmlns='http://earth.google.com/kml/2.1'>";
        $kml .= "<Folder>";
        $kml .= "<name>{$this->camera_name}</name>";
        $kml .= "<open>1</open>";
        $kml .= $this->placemarks;
        $kml .= "</Folder>";
		$kml .= "</kml>";
		
        return $kml;
    }
    
    /**
    * Add a placemark for a single camera
    * @param name Station name
    * @param station_latitude Station latitude (decimal degrees)
    * @param station_longitude Station longitude (decimal degrees)
    * @param station_height Station altitude (decimal degrees)
    * @param fov_corners Text field with comma-separated azimuth,elevation values for the fov corners
    * @param color Polygon color and transparency html-code (8 characters, e.g. FF0000FF)
    **/
    public function addPlacemark($name, $station_latitude, $station_longitude, $station_height, $corner_array, $color = "8000ff00")
    {
        // Convert (azimuth,elevation) for the FOV corner points to cartesian lat/lon/height values
        $cartesian_corner_array = array();
		
		if (count($corner_array) % 2 == 0) {
            for ($i=0; $i<count($corner_array); $i=$i+2) {
                $cartesian_corner_array[] = $this->spherical2cartesian(
					$station_latitude, 
					$station_longitude, 
					$station_height, 
					$corner_array[ $i ], 
					$corner_array[ $i + 1 ], 
					$this->cut_range, 
					$this->cut_horizontal
				);
            }
        }
        
        // Create a description for the KML file (to document the contents)
        $description = '';
        $description .= "Range: ".$this->cut_range." km\n";
        $description .= "FOV (az,alt,az,alt ...): (".implode($corner_array, ", ").")\n";
        $description .=	"Longitude: ". $station_longitude." deg\n";
        $description .=	"Latitude: ".$station_latitude." deg\n";
        $description .=	"Altitude: ".$station_height." m\n\n";
        
        // Create the final KML file
        $result = "<Placemark id='$name'>
			     <Style id='camera'>
			      <LineStyle>
				<width>1.5</width>
			      </LineStyle>
			      <PolyStyle>
				<color>$color</color>
			      </PolyStyle>
			    </Style>
			    <styleUrl>#camera</styleUrl>
			    <name>$name</name>
			    <description>$description</description>
	
			    <MultiGeometry>";
            
        $station = array($station_longitude, $station_latitude, $station_height);
		
		// Run over FOV corner points and add relevant polygons
        for ($i=0; $i<count($cartesian_corner_array); $i++) {
            $i2 = $i+1;
			
			if ($i2 == count($cartesian_corner_array)) {
                $i2 = 0;
            }
			
			$result .= $this->getPolygon(array($station, $cartesian_corner_array[$i], $cartesian_corner_array[$i2]));
        }
		
		// Add the top polygon
        $result .= $this->getPolygon($cartesian_corner_array);

        $result .= "    </MultiGeometry>
			    		</Placemark>";
		
		$this->placemarks .= $result;
    }
    
    /**
     * Construct a KML polygon from an array of points
     * @param $point_array Array of arrays containing lon/lat/height values
     */
    private function getPolygon($point_array)
    {
        // Open the KML Polygon element
        $result =  "<Polygon>\n";
        $result .= "  <extrude>0</extrude>\n";
        $result .= "  <altitudeMode>absolute</altitudeMode>\n";
        $result .= "  <outerBoundaryIs>\n";
        $result .= "	<LinearRing>\n";
        $result .= "	  <coordinates>\n";
        
        // Add the coordinates for all points
        foreach ($point_array as $point) {
            $result .= "	    ".$point[0].",".$point[1].",".$point[2]."\n";
        }
        
        // The first point must be repeated to close the polygon
        $result .= "	    ".$point_array[0][0].",".$point_array[0][1].",".$point_array[0][2]."\n";
        
        // Close the Polygon element
        $result .= "	  </coordinates>\n";
        $result .= "	</LinearRing>\n";
        $result .= "  </outerBoundaryIs>\n";
        $result .= "</Polygon>\n";
		
		return $result;
    }
    
    /**
     * Convert azimuth and elevation to lon/lat/height values.
     * This function uses spherical-to-cartesian coordinate transformations.
     */
    private function spherical2cartesian($station_latitude, $station_longitude, $station_height, $az, $ev, $cut_range, $cut_horizontal)
    {
        // Calculate range (distance between station and FOV corner point) in meter
        if ($cut_horizontal) {
            // Horizontal cut: fixed altitude (+ convert to meter: *1000)
            if ($ev < 0.5) {
                $range = 1000 * $cut_range / sin(deg2rad(0.5));
            } else {
                $range = 1000 * $cut_range / sin(deg2rad($ev));
            }
        } else {
            // No horizontal cut: fixed range (+ convert to meter: *1000)
            $range = 1000 * $cut_range;
        }
        
        
        /* LATITUDE */
        // Distance of the corner point from the station, expressed in earth degrees (+-111km per degree for spherical earth)
        $fov_distance = ($range / 111000);
		
		// Project the spherical coordinates to latitude using spherical-to-cartesian transformation
        $delta_lat = $fov_distance * sin(deg2rad(90-$ev)) * cos(deg2rad($az));
        $latitude = ($station_latitude + $delta_lat);

        /* LONGITUDE */
        // Distance of the corner point from the station, expressed in earth degrees (+-111km per degree for spherical earth)
        $fov_distance = ($range / 111000);
		
		// Project the spherical coordinates to longitude using spherical-to-cartesian transformation
        $delta_lon = $fov_distance * sin(deg2rad(90-$ev)) * sin(deg2rad($az));
		
		// Note: we must divide delta_lon by the cosine of the altitude! (Longitudes get shorter towards the poles.)
        $longitude = ($station_longitude + ($delta_lon / cos(deg2rad($latitude))));
        
        /* HEIGHT */
		$delta_height = $range * cos(deg2rad(90-$ev));
		
        // Correct for the height for the curvature of the earth
        if ($cut_horizontal == false) {
            // First calculate the angle between the station and the FOV corner point (Pythagoras)
            $dist_angle = sqrt($delta_lon*$delta_lon + $delta_lat*$delta_lat);
            // Calculate the altitude difference caused by the earth curvature (if the earth was a sphere)
            $radius_earth = 6372797;
            $curv_corr = $radius_earth * (1-cos(deg2rad($dist_angle)));
            // Add this altitude difference to delta_alt
            $delta_height = $delta_height + $curv_corr;
        }
        // Calculate final absolute altitude
        $height = ($station_height + $delta_height);
            
        /* RETURN THE RESULT */
        $result = array();
        $result[0] = $longitude;
        $result[1] = $latitude;
        $result[2] = $height;
		
		return $result;
    }
}
