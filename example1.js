/*
 * global variables
 */
var map;							// global map object
var lyrOsm;							// the Mapnik base layer of the map
var lyrPlq;							// the geoJson layer to display plaques with

// when the whole document has loaded call the init function
$(document).ready(init);

function init() {		
	// map stuff
	// base layer
	var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';    
	var osmAttrib='Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
	
	lyrOsm = new L.TileLayer(osmUrl, {
		minZoom: 9, 
		maxZoom: 19, 
		attribution: osmAttrib
	});
	
	// a geojson layer
	lyrPlq = L.geoJson(plaques, {
		onEachFeature: makePopup
		}
	);
	
	// set the starting location for the centre of the map
	var start = new L.LatLng(53.7610,-0.3529);	
	
	// create the map
	map = new L.Map('mapdiv', {		// use the div called mapdiv
		center: start,				// centre the map as above
		zoom: 12,					// start up zoom level
		layers: [lyrOsm,lyrPlq]		// layers to add 
	});
	
	// create a layer control
	// add the base layers
	var baseLayers = {
		"OpenStreetMap": lyrOsm
	};

	// add the overlays
	var overlays = {
		"Plaques": lyrPlq
	};

	// add the layers to a layer control
	L.control.layers(baseLayers, overlays).addTo(map);
	
	// create the hash url on the browser address line
	var hash = new L.Hash(map);
}

function makePopup(feature, layer) {
    // create a popup for each point
    if (feature.properties && feature.properties.plaquedesc) {
        layer.bindPopup(feature.properties.plaquedesc);
    }
}
