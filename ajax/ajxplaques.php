<?php 
// uncomment below to turn error reporting on
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
 * ajxplaque.php
 * returns plaque points as geojson 
 */

// get the server credentials from a shared import file
$idb= $_SERVER['DOCUMENT_ROOT']."/include/db.php";
include $idb;

if (isset($_GET['bbox'])) {
	$bbox=$_GET['bbox'];
} else {
	// invalid request
	$ajxres=array();
	$ajxres['resp']=4;
	$ajxres['dberror']=0;
	$ajxres['msg']='missing bounding box';
	sendajax($ajxres);
}
// split the bbox into it's parts
list($left,$bottom,$right,$top)=explode(",",$bbox);

// open the database
try {
	$db = new PDO('mysql:host=localhost;dbname='.$dbname.';charset=utf8', $dbuser, $dbpass);
} catch(PDOException $e) {
	// send the PDOException message
	$ajxres=array();
	$ajxres['resp']=40;
	$ajxres['dberror']=$e->getCode();
	$ajxres['msg']=$e->getMessage();
	sendajax($ajxres);
}

//$stmt = $db->prepare("SELECT * FROM hbtarget WHERE lon>=:left AND lon<=:right AND lat>=:bottom AND lat<=:top ORDER BY targetind");
//$stmt->bindParam(':left', $left, PDO::PARAM_STR);
//$stmt->bindParam(':right', $right, PDO::PARAM_STR);
//$stmt->bindParam(':bottom', $bottom, PDO::PARAM_STR);
//$stmt->bindParam(':top', $top, PDO::PARAM_STR);
//$stmt->execute();


try {
	$sql="SELECT plaqueid,lat,lon,plaquedesc,colour,imageid FROM plaques WHERE lon>=:left AND lon<=:right AND lat>=:bottom AND lat<=:top";
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':left', $left, PDO::PARAM_STR);
	$stmt->bindParam(':right', $right, PDO::PARAM_STR);
	$stmt->bindParam(':bottom', $bottom, PDO::PARAM_STR);
	$stmt->bindParam(':top', $top, PDO::PARAM_STR);
	$stmt->execute();
} catch(PDOException $e) {
	print "db error ".$e->getCode()." ".$e->getMessage();
}
	
$ajxres=array(); // place to store the geojson result
$features=array(); // array to build up the feature collection
$ajxres['type']='FeatureCollection';

// go through the list adding each one to the array to be returned	
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$lat=$row['lat'];
	$lon=$row['lon'];
	$prop=array();
	$prop['plaqueid']=$row['plaqueid'];
	$prop['plaquedesc']=$row['plaquedesc'];
	$prop['colour']=$row['colour'];
	$prop['imageid']=$row['imageid'];
	$f=array();
	$geom=array();
	$coords=array();
	
	$geom['type']='Point';
	$coords[0]=floatval($lon);
	$coords[1]=floatval($lat);
	
	$geom['coordinates']=$coords;
	$f['type']='Feature';
	$f['geometry']=$geom;
	$f['properties']=$prop;

	$features[]=$f;
	
}
	
// add the features array to the end of the ajxres array
$ajxres['features']=$features;
// tidy up the DB
$db = null;
sendajax($ajxres); // no return from there

function sendajax($ajx) {
	// encode the ajx array as json and return it.
	$encoded = json_encode($ajx);
	exit($encoded);
}
?>
