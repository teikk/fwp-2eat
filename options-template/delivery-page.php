<h1><?= esc_html(get_admin_page_title()); ?></h1>
<script src="https://maps.googleapis.com/maps/api/js?libraries=places,drawing,geometry&key=<?php echo $this->options['global']['gmap_api']; ?>"></script>
<input id="input_szukaj" class="regular-text controls" type="text" placeholder="Wpisz misato lub region" value="Wojska Polskiego 2, Mysłowice">
	<div>
		<input id="save" class="button button-primary controls" type="button" value="SAVE">
		<input id="delete" class="button button-secondary controls" type="button" value="DELETE">
	</div>
	<div>
		<label for="dojazd">
			Dojazd (zł):
			<input id="dojazd" name="dojazd" class="controls" type="number" step="0.01">
			<!--					<input id="dojazd" name="dojazd" class="controls" type="text" >-->
			<input id="dojazd_save" name="dojazd_save" class="controls" type="button" value="Przypisz cenę">
		</label>
	</div>	
	<div id="map" style="height: 570px; width: 100%"></div>


<script type="application/javascript">
	(function ($) {
		
		var map;
		var shapes = [],
			goo = google.maps,
			shape, tmp;
		
		
		var selectedShape;
		
		var myLatlng = new google.maps.LatLng(52.000000, 19.000000);
		// Map Options
		var mapOptions = {
			center: myLatlng,
			zoom: 6,
			mapTypeControl: false,
			scaleControl: false,
			streetViewControl: false,
			rotateControl: false
		};
		
		var byId = function (s) {
			return document.getElementById(s)
		};
		
		map = new google.maps.Map(document.getElementById('map'),
			mapOptions);
		// DIRECTIONS_SERVICE
		var directionsService = new google.maps.DirectionsService();
		var directionsDisplay = new google.maps.DirectionsRenderer();
		directionsDisplay.setMap(map);
		
		
		var drawingManager = new google.maps.drawing.DrawingManager({
			drawingControlOptions: {
				position: google.maps.ControlPosition.TOP_CENTER,
				drawingModes: ['rectangle', 'circle', 'polygon']
				
				
			},
			circleOptions: {
				fillColor: '#f00',
				fillOpacity: 0.1,
				strokeWeight: 1,
				clickable: true,
				draggable: true,
				editable: true
//				zIndex: 1
			},
			rectangleOptions: {
				fillColor: '#f00',
				fillOpacity: 0.1,
				strokeWeight: 1,
				clickable: true,
				draggable: true,
				editable: true
//				zIndex: 1
			},
			polygonOptions: {
				fillColor: '#f00',
				fillOpacity: 0.1,
				strokeWeight: 1,
				clickable: true,
				draggable: true,
				editable: true
//				zIndex: 1
			},
			drawingControl: true,
			map: map
		});
		
		
		google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
			
			drawingManager.setDrawingMode(null);
			
			console.log(event.type);
			var shape = event.overlay;
			shape.type = event.type;
			
			//
			//
//			var newShape = e.overlay;
//			newShape.type = e.type;
//			google.maps.event.addListener(newShape, 'click', function () {
//				setSelection(newShape);
//			});
//			setSelection(newShape);
			//
			//
			
			
			goo.event.addListener(shape, 'click', function () {
				setSelection(shape);
			});
			setSelection(shape);
			clearSelection();
			shape.set(
				'dojazd', parseFloat(0)
			);
			
			shapes.push(shape);
			
			
		});
		
		
		// Clear the current selection when the drawing mode is changed, or when the
		// map is clicked.
//		google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
//		google.maps.event.addListener(map, 'click', clearSelection);
//		google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
		
		
		goo.event.addDomListener(byId('save'), 'click', function () {
			inn(shapes, false);
		});
		
		google.maps.event.addListener(map, 'click', clearSelection);
		google.maps.event.addDomListener(byId('delete'), 'click', deleteSelectedShape);
		google.maps.event.addDomListener(byId('dojazd_save'), 'click', dojazdCena);
		
		var to_draw = '[]';
		
		<?php
		$delivery_area = fwpr_get_delivery_area();
		if( !empty( $delivery_area ) ) :?>
		to_draw = '<?php echo $delivery_area; ?>';
		<?php 
		endif;
		?>
		
		
		outt(JSON.parse(to_draw), map);
		
		//
		// SHAPE CONTROL FUNCTIONS
		//
		
		function clearSelection() {
			if (selectedShape) {
//				dojazd_cena = '';
				byId('dojazd').value = '';
				selectedShape.setEditable(false);
				selectedShape = null;
			}
		}
		
		function setSelection(shape) {
			clearSelection();
			selectedShape = shape;
			shape.setEditable(true);
			shape.setDraggable(true);
			
			byId('dojazd').value = shape.dojazd;

//			selected_shape.set((selected_shape.type === google.maps.drawing.OverlayType.MARKER ) ? 'draggable' : 'editable', false);
		}
		
		function dojazdCena() {
			
			var dojazd_cena = byId('dojazd').value;
			
			if (selectedShape) {
				selectedShape.set(
					'dojazd', parseFloat(dojazd_cena)
				);
			}
		}
		
		function deleteSelectedShape() {
			if (selectedShape) {
				selectedShape.set(
					'visible', false
				);
				selectedShape.setMap(null);
			}
		}
		
		//
		//
		
		function clearShapes() {
			for (var i = 0; i < shapes.length; ++i) {
				shapes[i].setMap(null);
			}
			shapes = [];
		}
		
		
		function outt(arr, map) {
			var goo = google.maps,
				map = map || null,
				shape, tmp;
			
			for (var i = 0; i < arr.length; i++) {
				shape = arr[i];
				
				switch (shape.type) {
					case 'CIRCLE':
//						tmp = new goo.Circle({radius: Number(shape.radius), center: this.pp_.apply(this, shape.geometry), editable: true});
						tmp = new goo.Circle({
							radius: Number(shape.radius),
							center: pp_.apply(this, shape.geometry)
						});
						break;
					case 'MARKER':
						tmp = new goo.Marker({position: pp_.apply(this, shape.geometry)});
						
						break;
					case 'RECTANGLE':
						tmp = new goo.Rectangle({bounds: bb_.apply(this, shape.geometry)});
						break;
					case 'POLYLINE':
						tmp = new goo.Polyline({path: ll_(shape.geometry)});
						
						break;
					case 'POLYGON':
						tmp = new goo.Polygon({paths: mm_(shape.geometry)});
						
						break;
				}
				tmp.setValues({
					map: map,
					id: shape.id,
					dojazd: shape.dojazd,
					type: shape.type.toLowerCase(),
					fillColor: '#0FF',
					fillOpacity: 0.1,
					strokeWeight: 1,
					clickable: true
//					draggable: true,
//					editable: true
				});
				
				goo.event.addListener(tmp, 'click', function () {
					setSelection(this);
				});
				
				
				shapes.push(tmp);
				
			}
			return shapes;
		}
		
		
		function inn(arr,//array with google.maps.Overlays
						 encoded//boolean indicating whether pathes should be stored encoded
		) {
			var shapes = [],
				goo = google.maps,
				shape, tmp;
			
			console.log(arr);
			
			for (var i = 0; i < arr.length; i++) {
				shape = arr[i];
				tmp = {type: t_(shape.type), id: shape.id || null, dojazd: shape.dojazd};
				
				if (shape.visible) {
					
					switch (tmp.type) {
						case 'CIRCLE':
							tmp.radius = shape.getRadius();
							tmp.geometry = p_(shape.getCenter());
							break;
						case 'MARKER':
							tmp.geometry = p_(shape.getPosition());
							break;
						case 'RECTANGLE':
							tmp.geometry = b_(shape.getBounds());
							break;
						case 'POLYLINE':
							tmp.geometry = l_(shape.getPath(), encoded);
							break;
						case 'POLYGON':
							tmp.geometry = m_(shape.getPaths(), encoded);
							break;
					}
					shapes.push(tmp);
				}
			}
			
			var delivery_areas = JSON.stringify(shapes);
										  jQuery('#fwpr_delivery_area').val(delivery_areas);
			return shapes;
		}
		
		
		// todo FUNCTIONS
		
		function l_(path, e) {
			path = (path.getArray) ? path.getArray() : path;
			if (e) {
				return google.maps.geometry.encoding.encodePath(path);
			} else {
				var r = [];
				for (var i = 0; i < path.length; ++i) {
					r.push(p_(path[i]));
				}
				return r;
			}
		}
		
		function ll_(path) {
			if (typeof path === 'string') {
				return google.maps.geometry.encoding.decodePath(path);
			}
			else {
				var r = [];
				for (var i = 0; i < path.length; ++i) {
					r.push(pp_.apply(this, path[i]));
				}
				return r;
			}
		}
		
		function m_(paths, e) {
			var r = [];
			paths = (paths.getArray) ? paths.getArray() : paths;
			for (var i = 0; i < paths.length; ++i) {
				r.push(l_(paths[i], e));
			}
			return r;
		}
		
		function mm_(paths) {
			var r = [];
			for (var i = 0; i < paths.length; ++i) {
				r.push(ll_.call(this, paths[i]));
				
			}
			return r;
		}
		
		function p_(latLng) {
			return ([latLng.lat(), latLng.lng()]);
		}
		
		function pp_(lat, lng) {
			return new google.maps.LatLng(lat, lng);
		}
		
		function b_(bounds) {
			return ([p_(bounds.getSouthWest()),
				p_(bounds.getNorthEast())]);
		}
		
		function bb_(sw, ne) {
			return new google.maps.LatLngBounds(pp_.apply(this, sw), pp_.apply(this, ne));
		}
		
		
		function t_(s) {
			var t = ['CIRCLE', 'MARKER', 'RECTANGLE', 'POLYLINE', 'POLYGON'];
			for (var i = 0; i < t.length; ++i) {
				if (s === google.maps.drawing.OverlayType[t[i]]) {
					return t[i];
				}
			}
		}
		
		
		function initialize() {
			
			// Search box
			//
			var input_szukaj = (document.getElementById('input_szukaj'));
//			input_szukaj.value = ''; 
			var options = {
//					types: ['(cities)'],
				componentRestrictions: {country: 'pl'}
			};
			autocomplete = new google.maps.places.Autocomplete(input_szukaj, options);
			
			autocomplete.bindTo('bounds', map);
			var marker = new google.maps.Marker({
				map: map,
				anchorPoint: new google.maps.Point(0, -29)
			});
			autocomplete.addListener('place_changed', function () {
				marker.setVisible(false);
				var place = autocomplete.getPlace();
				if (!place.geometry) {
					window.alert("Autocomplete's returned place contains no geometry");
					return;
				}
				// If the place has a geometry, then present it on a map.
				if (place.geometry.viewport) {
//						map.fitBounds(place.geometry.viewport);
				} else {
					map.setCenter(place.geometry.location);
					map.setZoom(17);  // Why 17? Because it looks good.
				}
				marker.setIcon(/** @type {google.maps.Icon} */({
					url: place.icon,
//						url: '<?php //echo get_template_directory_uri(); ?>///img/icons/icon-map-cluster.png',
					size: new google.maps.Size(71, 71),
					origin: new google.maps.Point(0, 0),
					anchor: new google.maps.Point(17, 34),
					scaledSize: new google.maps.Size(35, 35)
				}));

//				console.log((place.geometry.location));
				
				var address_array = '';
				if (place.address_components) {
					address_array = [
						(place.address_components[0] && place.address_components[0].short_name || ''),
						(place.address_components[1] && place.address_components[1].short_name || ''),
						(place.address_components[2] && place.address_components[2].short_name || ''),
						(place.address_components[3] && place.address_components[3].short_name || ''),
						(place.address_components[4] && place.address_components[4].short_name || ''),
						(place.address_components[5] && place.address_components[5].short_name || ''),
						(place.address_components[6] && place.address_components[6].short_name || '')
					].join(' ');
				}
				
				console.log(place.address_components);
				console.log(address_array);
				//
				//
				//
				
				var componentForm = {
					street_number: 'short_name',
					route: 'long_name',
					locality: 'long_name',
					administrative_area_level_1: 'short_name',
					country: 'long_name'
				};
				
				
				for (var component in componentForm) {
					document.getElementById(component).value = '';
					document.getElementById(component).disabled = false;
				}
				
				// Get each component of the address from the place details
				// and fill the corresponding field on the form.
				for (var i = 0; i < place.address_components.length; i++) {
					var addressType = place.address_components[i].types[0];
//					console.log(addressType);
					if (componentForm[addressType]) {
						var val = place.address_components[i][componentForm[addressType]];
						document.getElementById(addressType).value = val;
					}
				}
				
				
				//
				//
				//


//					find_closest_marker(place.geometry.location.lat(), place.geometry.location.lng());
				
				var point = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
				
				isPointInside(point);
				console.log(isPointInside(point));
				marker.setPosition(place.geometry.location);
				marker.setVisible(true);
//					infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
//					infowindow.open(map, marker);
			});
			//
			// Search box END
			//
			
			
			//
			// STYL MAPY
			//
			var styl_mapy =
				[{"featureType": "all", "elementType": "all", "stylers": [{"saturation": "-100"}, {"lightness": "0"}]}, {"featureType": "all", "elementType": "labels.text.fill", "stylers": [{"saturation": 36}, {"color": "#333333"}, {"lightness": 40}]}, {"featureType": "all", "elementType": "labels.text.stroke", "stylers": [{"visibility": "on"}, {"color": "#ffffff"}, {"lightness": 16}]}, {"featureType": "all", "elementType": "labels.icon", "stylers": [{"visibility": "simplified"}, {"saturation": "-100"}, {"gamma": "5"}]}, {"featureType": "administrative", "elementType": "geometry.fill", "stylers": [{"color": "#fefefe"}, {"lightness": "20"}, {"gamma": "1"}]}, {"featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{"color": "#fefefe"}, {"lightness": 17}, {"weight": 1.2}]}, {"featureType": "landscape", "elementType": "geometry", "stylers": [{"lightness": "20"}, {"gamma": "1.00"}, {"color": "#f2f2f2"}]}, {
					"featureType": "poi",
					"elementType": "geometry.fill",
					"stylers": [{"visibility": "on"}, {"saturation": "-100"}]
				}, {"featureType": "poi", "elementType": "geometry.stroke", "stylers": [{"visibility": "off"}]}, {"featureType": "poi.park", "elementType": "geometry", "stylers": [{"color": "#dedede"}, {"lightness": 21}]}, {"featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{"color": "#ffffff"}, {"lightness": 17}]}, {"featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [{"color": "#ffffff"}, {"lightness": 29}, {"weight": 0.2}]}, {"featureType": "road.arterial", "elementType": "geometry", "stylers": [{"color": "#ffffff"}, {"lightness": 18}]}, {"featureType": "road.local", "elementType": "geometry", "stylers": [{"color": "#ffffff"}, {"lightness": 16}]}, {"featureType": "transit", "elementType": "geometry", "stylers": [{"color": "#f2f2f2"}, {"lightness": 19}]}, {"featureType": "water", "elementType": "geometry", "stylers": [{"color": "#e9e9e9"}, {"lightness": 17}]}];
			map.setOptions({styles: styl_mapy});
			
		}
		
		//
		// todo CzyPunktJestwKształcie
		//
		function isPointInside(position) {
			
			var dojazd_tmp = [];

//			console.log(position.lat() +', '+ position.lng());
			
			for (var i = 0; i < shapes.length; i++) {
				
				if (shapes[i].visible) {
					if (shapes[i].type == 'circle') {
						if (google.maps.geometry.spherical.computeDistanceBetween(shapes[i].getCenter(), position) <= shapes[i].getRadius()) {
							dojazd_tmp.push(shapes[i].dojazd);
//						return true;
						}
					}
					if (shapes[i].type == 'rectangle') {
						if (shapes[i].getBounds().contains((position))) {
							dojazd_tmp.push(shapes[i].dojazd);
//						return true;
						}
					}
					if (shapes[i].type == 'polygon') {
						if (google.maps.geometry.poly.containsLocation(position, shapes[i])) {
							dojazd_tmp.push(shapes[i].dojazd);
//						return true;
						}
					}
				}
			}
			
			//todo SORT Array
			dojazd_tmp.sort();
			console.log(dojazd_tmp[0] >= 0 ? dojazd_tmp[0] : false);

//			for (var i = 0; i < dojazd_tmp.length; i++) {
//				console.log( '"' + dojazd_tmp[i] + '"');
//			}
//			return false;
			
		}
		
		//
		// todo CzyPunktJestwKształcie END
		//
		
		
		//
		//
//		for (var i = 0; i < shapes.length; i++) {
//
//			console.log(shapes[i]);
//
//			goo.event.addListener(shapes[i], 'click', function () {
//				setSelection(this);
//			});
//
//
//		}
		
		//
		//
		
		
		google.maps.event.addDomListener(window, 'load', initialize);
		
		
	})(jQuery);
</script>
<div id="fwpr-debug">
	<table id="address">
		<tr>
			<td class="label">Street address</td>
			<td class="slimField">
				<input class="field" id="street_number" disabled="true"></input>
			</td>
			<td class="wideField" colspan="2">
				<input class="field" id="route" disabled="true"></input>
			</td>
		</tr>
		<tr>
			<td class="label">City</td>
			<!-- Note: Selection of address components in this example is typical.
				 You may need to adjust it for the locations relevant to your app. See
				 https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
			-->
			<td class="wideField" colspan="3">
				<input class="field" id="locality" disabled="true"></input>
			</td>
		</tr>
		<tr>
			<td class="label">State</td>
			<td class="slimField">
				<input class="field" id="administrative_area_level_1" disabled="true"></input>
			</td>
		</tr>
		<tr>
			<td class="label">Country</td>
			<td class="wideField" colspan="3">
				<input class="field" id="country" disabled="true"></input>
			</td>
		</tr>
	</table>
</div>
<form action="options.php" method="post">
<?php
settings_fields('fwpr_delivery_page');
do_settings_sections('fwpr_delivery_page');
submit_button( __('Zapisz zmiany','fwpr') );
?>
</form>









