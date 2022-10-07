/**
 * Class component based on:
 * https://dev.to/megazear7/the-vanilla-javascript-component-pattern-37la
 */
import {
    event,
    selectDoc,
    ajax, selectArr
} from "../utils/wonder";

import {inputEmpty} from "../helpers/service.validator";

import {displayModalError} from "../helpers/service.dialog";

export class Map {
    /**
     * Forms constructor
     */
    constructor() {
        this.init();
    }

    /**
     * Initialize class
     */
    init() {
        this.props();
        this.initMap()
    }

    /**
     * Initialize class props
     */
    props() {
        this.body = selectDoc("body");
        this.html = selectDoc("html");
        this.currentMarker = null;
        this.map = null;
        this.currentLocationQuery = '';
        this.searchAjax = null;
        this.mapSearch = null;
        this.mapReady = false;
    }

    updateMapLocation(latitude, longitude, checkout = false) {
        let self = this;
        if (latitude && longitude) {
            let latitudeInput = selectDoc('input[name*="address_latitude"]'),
                longitudeInput = selectDoc('input[name*="address_longitude"]'),
                changeEvent = new Event('change'),
                pinHide = !!selectDoc('input[name*="map_pin_hide"]');

            if (this.map && !checkout) {
                this.currentMarker ? this.currentMarker.setMap(null) : '';
                this.currentMarker = new google.maps.Marker({
                    map: this.map,
                    position: {
                        'lat': parseFloat(latitude),
                        'lng': parseFloat(longitude),
                    },
                    draggable: true,
                    opacity: pinHide ? 0 : 1
                });

                latitudeInput.value = latitude;
                longitudeInput.value = longitude;

                latitudeInput.dispatchEvent(changeEvent);
                longitudeInput.dispatchEvent(changeEvent);

                self.markerDragEvents();

                this.geocodePosition(this.currentMarker.getPosition());
            }
        }
    }

    centerMap(latitude, longitude) {
        if (latitude && longitude) {
            if (this.map) {
                this.map.setCenter({
                    'lat': parseFloat(latitude),
                    'lng': parseFloat(longitude),
                });
                this.map.setZoom(17);
            }
        }
    }

    searchLocation(clear) {
        let self = this;
        if (this.searchAjax) {
            this.searchAjax.abort();
        }

        this.searchAjax = new XMLHttpRequest()
        ajax('GET', `/rest/V1/kip/map/search/${self.currentLocationQuery}`, {},
            null, this.searchAjax).then(function (response) {
            const data = JSON.parse(response);
            if (data.status !== undefined) {
                if (data.status === 200) {
                    self.mapSearch.parentNode.classList.remove('loading')
                    let coords = JSON.parse(data.response);
                    if (Array.isArray(coords)) {
                        if (coords.length === 2) {
                            self.updateMapLocation(coords[0], coords[1])
                            self.centerMap(coords[0], coords[1])
                            self.mapSearch.blur();
                            if (clear) {
                                self.mapSearch.value = '';
                            }
                        }
                    }
                }
            }
        });
    }

    updateCoordinates(e, latitudeInput, longitudeInput, changeEvent) {
        let latLng = e.latLng.toString()
            .replace('(', '')
            .replace(')', '')
            .split(',')

        latitudeInput.value = parseFloat(latLng[0]);
        longitudeInput.value = parseFloat(latLng[1]);

        latitudeInput.dispatchEvent(changeEvent);
        longitudeInput.dispatchEvent(changeEvent);
    }

    geocodePosition(pos){
        let geocoder = new google.maps.Geocoder();
        geocoder.geocode({
            latLng: pos
        }, function(responses) {
            if (responses && responses.length > 0) {
                let address = responses[0].formatted_address,
                    postcode = selectDoc('input#zip') ?? selectDoc('[name*="shippingAddress.postcode"] input');
                    //city = selectDoc('input#city') ?? selectDoc('[name*="shippingAddress.city"] input'),
                    //street = selectDoc('input#street_0') ?? selectDoc('[name*="shippingAddress.street.0"] input');
                if(postcode && address) {
                    postcode.value = address.substring(0, 17) + '...';
                    let changeEvent = new Event('change');
                    postcode.dispatchEvent(changeEvent);
                }
            }
        });
    }

    initMap(renderSearch = true) {
        let self = this,
            checkout = false;

        if (!this.mapReady) {
            let mapKey = selectDoc('input[name*="map_key"]'),
                viewOnly = !!selectDoc('input[name*="map_view_only"]'),
                pinHide = !!selectDoc('input[name*="map_pin_hide"]'),
                mapCenter = JSON.parse(selectDoc('input[name*="map_center"]') ? selectDoc('input[name*="map_center"]').value : '{}'),
                mapPolygon = selectDoc('input[name*="map_polygon"]'),
                mapPolygonRestricted = selectDoc('input[name*="map_polygon_restricted"]');

            if (window.checkoutConfig) {
                if (window.checkoutConfig.kipping && selectDoc('body.checkout-index-index')) {
                    mapKey = window.checkoutConfig.kipping.key;
                    mapCenter = JSON.parse(window.checkoutConfig.kipping.center);
                    mapPolygon = window.checkoutConfig.kipping.polygon;
                    mapPolygonRestricted = window.checkoutConfig.kipping.polygon_restricted;
                    checkout = true;

                    let region = selectDoc('[name="shippingAddress.region"] input');
                    if(region) {
                        region.parentNode.parentNode.style.display = 'none';
                    }
                }
            }

            if (mapKey) {
                let scriptTag = document.createElement('script'),
                    mapContainer = document.createElement('div'),
                    searchInput = document.createElement('div');

                this.googleKey = !checkout ? mapKey.value : mapKey;
                scriptTag.setAttribute('src', `https://maps.googleapis.com/maps/api/js?key=${this.googleKey}`+`&t=m`);
                scriptTag.onload = () => {
                    this.map = new google.maps.Map(document.getElementById('google-map'), {
                        zoom: 13,
                        center: mapCenter ? mapCenter : '',
                        gestureHandling: 'greedy',
                        disableDefaultUI: viewOnly,
                        scrollwheel: false,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });

                    let latitudeInput = selectDoc('input[name*="address_latitude"]'),
                        longitudeInput = selectDoc('input[name*="address_longitude"]'),
                        changeEvent = new Event('change');

                    if (mapPolygon) {
                        // Define the LatLng coordinates for the polygon's path.
                        const polygonCoordsArray = JSON.parse(checkout ? mapPolygon : mapPolygon.value);
                        self.polygons = [];
                        self.restrictedPolygons = [];
                        polygonCoordsArray.map(polygonCoords => {
                            // Construct the polygon.
                            const polygon = new google.maps.Polygon({
                                paths: polygonCoords,
                                strokeColor: "#33BE81",
                                strokeOpacity: 1,
                                strokeWeight: 1,
                                fillColor: "#48d597",
                                fillOpacity: 0.2,
                            });
                            polygon.setMap(this.map);

                            //Add marker
                            if (!viewOnly) {
                                polygon.addListener('click', function (e) {
                                    self.currentMarker ? self.currentMarker.setMap(null) : '';
                                    self.currentMarker = new google.maps.Marker({
                                        map: self.map,
                                        position: e.latLng,
                                        draggable: true,
                                        opacity: pinHide ? 0 : 1
                                    });
                                    self.updateCoordinates(e, latitudeInput, longitudeInput, changeEvent);
                                    self.geocodePosition(self.currentMarker.getPosition());
                                    self.markerDragEvents();
                                });
                            }

                            self.polygons.push(polygon)
                        });

                        self.markerDragEvents();

                        if (latitudeInput.value && longitudeInput.value) {
                            self.updateMapLocation(latitudeInput.value, longitudeInput.value);
                            self.centerMap(latitudeInput.value, longitudeInput.value);
                        } else {
                            if (mapCenter) {
                                self.updateMapLocation(mapCenter.lat, mapCenter.lng, checkout)
                            }
                            new Promise(function (resolve) {
                                if (!navigator.geolocation) {
                                    console.log('Geolocation is not supported by your browser');
                                    resolve(false);
                                } else {
                                    navigator.geolocation.getCurrentPosition(
                                        function (position) {
                                            self.centerMap(position.coords.latitude, position.coords.longitude)
                                            self.updateMapLocation(position.coords.latitude, position.coords.longitude, checkout)
                                            resolve(true);
                                        },
                                        function (e) {
                                            console.log(e)
                                            resolve(false);
                                        });
                                }
                            }).then(function (response) {})
                        }
                    }

                    if (mapPolygonRestricted) {
                        // Define the LatLng coordinates for the polygon's path.
                        const polygonCoordsArray = JSON.parse(checkout ? mapPolygonRestricted : mapPolygonRestricted.value);
                        polygonCoordsArray.map(polygonCoords => {
                            // Construct the polygon.
                            const polygon = new google.maps.Polygon({
                                paths: polygonCoords,
                                strokeColor: "black",
                                strokeOpacity: 0.5,
                                strokeWeight: 1,
                                fillColor: "black",
                                fillOpacity: 0.5,
                            });
                            polygon.setMap(this.map);

                            //Add marker
                            if (!viewOnly) {
                                polygon.addListener('click', function () {
                                    displayModalError('Lo sentimos, aún no hemos llegado a esta zona.')
                                })
                            }

                            self.restrictedPolygons.push(polygon);
                        });
                    }
                };
                document.body.appendChild(scriptTag);

                let container = null;
                if (checkout) {
                    let dummy = document.createElement('div'),
                        shippingForm = selectDoc('#co-shipping-form');
                    if (shippingForm) {
                        dummy.id = 'map-container';
                        selectDoc('#co-shipping-form').appendChild(dummy);
                        container = selectDoc('#map-container');
                    }
                } else {
                    container = mapKey.parentNode;
                }

                if (renderSearch && searchInput && container) {
                    searchInput.id = 'google-map-search'
                    searchInput.innerHTML = `<input type="text" id="map-search" placeholder="Buscar dirección de entrega..." />`
                    container.appendChild(searchInput)

                    let typingTimer;
                    self.mapSearch = selectDoc('#map-search');
                    event(self.mapSearch, 'keyup', function () {
                        clearTimeout(typingTimer);
                        self.currentLocationQuery = self.mapSearch.value
                        if (!inputEmpty(self.currentLocationQuery)) {
                            typingTimer = setTimeout(function () {
                                self.searchLocation(self.mapSearch.value);
                            }, 1000);
                        } else {
                            self.mapSearch.parentNode.classList.remove('loading')
                        }
                    })

                    event(self.mapSearch, 'keydown', function () {
                        self.mapSearch.parentNode.classList.add('loading')
                        self.currentLocationQuery = self.mapSearch.value
                        clearTimeout(typingTimer);
                    })
                }

                if (container) {
                    mapContainer.id = 'google-map';
                    container.appendChild(mapContainer);
                    this.mapReady = true;
                }
            }
        }
    }

    markerDragEvents() {
        let self = this,
            latitudeInput = selectDoc('input[name*="address_latitude"]'),
            longitudeInput = selectDoc('input[name*="address_longitude"]'),
            changeEvent = new Event('change');
        if(self.currentMarker) {
            self.currentMarker.addListener('dragstart',function() {
                self.startDragPosition = this.getPosition();
            });
            self.currentMarker.addListener('dragend', function (event) {
                let is = false;
                self.polygons.map(p => {
                    if(google.maps.geometry.poly.containsLocation(event.latLng, p)) {
                        is = true;
                    }
                })
                self.restrictedPolygons.map(pr => {
                    if(google.maps.geometry.poly.containsLocation(event.latLng, pr)) {
                        is = false;
                    }
                });
                if(is) {
                    self.updateCoordinates(event, latitudeInput, longitudeInput, changeEvent)
                } else {
                    self.currentMarker.setPosition(self.startDragPosition);
                    displayModalError('Lo sentimos, aún no hemos llegado a esta zona.')
                }
            });
        }
    }
}
