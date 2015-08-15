window.maps = (function() {
    var MQMaps = {
        map: {},
        initMap: function(container){
            container = container || 'mq-tickets-map';

                // create an object for options
                var options = {
                    elt: document.getElementById(container),       // ID of map element on page
                    zoom: 10,                                  // initial zoom level of the map
                    latLng: { lat: 39.7439, lng: -105.0200 },  // center of map in latitude/longitude
                    mtype: 'map',                              // map type (map, sat, hyb); defaults to map
                    bestFitMargin: 0,                          // margin offset from map viewport when applying a bestfit on shapes
                    zoomOnDoubleClick: true                    // enable map to be zoomed in when double-clicking
                };

                // construct an instance of MQA.TileMap with the options object
            window.MQMAP = new MQA.TileMap(options);

        },
        geocodeResults : {},
        fixTimeout:false,
        batchGeocode: function(addresses,callback,skip){
            var self = this,
                limitPerRequest = 100,
                interval = 200,
                i = 0,
                c = 0,
                skipped = 0,
                skip = skip || 0,
                limit = limitPerRequest + skip,
                toGeocode = {},
                fixLimit = 10000,
                fixTimeout;
            if(!skip){
                this.geocodeResults = {};
            }
            if(!window.MQMAP){
                this.initMap();
            }

            for(i in addresses){
                if(c++ < limit){
                    if(c > skip){
                        skip++;
                        toGeocode[i] = addresses[i];
                    }
                }
            }

            clearTimeout(self.fixTimeout);

            self.geocodeAddresses(toGeocode,function(geocoded){
                clearTimeout(self.fixTimeout);
                if(geocoded && utils.objectLength(geocoded)){
                    self.geocodeResults = $.extend(self.geocodeResults, geocoded);
                    localStorage.temporaryGeocoded = JSON.stringify(self.geocodeResults);
                }
                if(skip < utils.objectLength(addresses)-1){
                    setTimeout(function(){
                        self.batchGeocode(addresses,callback,skip);
                    },interval);
                }else{

                    callback(self.mergeCoordsWithAddresses(addresses,self.geocodeResults));
                }
            }, function(e){
                debugger;
                console.log(e);
                if(skip < utils.objectLength(addresses)-1){
                    setTimeout(function(){
                        self.batchGeocode(addresses,callback,skip);
                    },interval);
                }else{

                    callback(self.mergeCoordsWithAddresses(addresses,self.geocodeResults));
                }
            });

            self.fixTimeout = setTimeout(function(){
                self.batchGeocode(addresses,callback,skip);
            },fixLimit);

        },
        mergeCoordsWithAddresses: function(addresses, coords){
            var i,merged ={};
            for(i in addresses){
                if(coords[addresses[i]]){
                    merged[i] = {coords:coords[addresses[i]], address: addresses[i]}
                }
            }
            return merged;
        },



        geocodeAddresses: function(data, callback) {
            var self = this,
                i,
                coords,
                addresses = [],
                jsonAddresses = [],
                resolved = {};
            for(i in data){
                addresses.push(data[i]);
                jsonAddresses.push({"country":"AU","street":data[i]});
            }

            MQA.withModule('geocoder', function() {
                // override the POI construction function to customize the Rollover and InfoWindow states

                MQA.Geocoder.constructPOI = function(location) {
                    var lat = location.latLng.lat,
                        lng = location.latLng.lng,
                        city = location.adminArea5,
                        state = location.adminArea3,
                        p = new MQA.Poi({ lat: lat, lng: lng });

                    p.setRolloverContent('<div style="white-space: nowrap">' + city + ', ' + state + '</div>');
                    p.setInfoTitleHTML(p.getRolloverContent());
                    p.setInfoContentHTML('<div style="white-space: nowrap">' + city + ', '
                        + state + '<br/>LatLng: ' + lat + ', ' + lng + '</div>');

                    return p;
                };
                window.MQMAP.geocodeAndAddLocations(jsonAddresses,function(results){
                    for(i in results.results){
                        try {
                            coords = results.results[i].locations[0].latLng;
                            resolved[results.results[i].providedLocation.street] = coords;
                        }catch(e){
                            console.log('!!ERROR!!', e);
                        }

                    }
                    callback(resolved);
                });
            });
        }
    };
    var GoogleMaps = {

        geocoder: false,
        map:false,
        markers: [],
        bounds: new google.maps.LatLngBounds(),


        initMap: function (container) {
                var self = this;

            this.map = new google.maps.Map(document.getElementById(container), {
                center: {lat: -34.397, lng: 150.644},
                zoom: 13
            });

            this.geocoder = new google.maps.Geocoder();

//            var infoWindow = new google.maps.InfoWindow({map: this.map});

//            // Try HTML5 geolocation.
//            if (navigator.geolocation) {
//                navigator.geolocation.getCurrentPosition(function(position) {
//                    var pos = {
//                        lat: position.coords.latitude,
//                        lng: position.coords.longitude
//                    };
//
//                    infoWindow.setPosition(pos);
//                    infoWindow.setContent('Location found.');
//                    this.map.setCenter(pos);
//                }, function() {
//                    self.handleLocationError(true, infoWindow, map.getCenter());
//                });
//            } else {
//                // Browser doesn't support Geolocation
//                self.handleLocationError(false, infoWindow, this.map.getCenter());
//            }
        },

        batchGeocode: function(addresses,callback){
            var self = this;
            console.log(addresses.length);


            this.geocodeAddress(addresses.pop(),function(){
                if(addresses.length){
                    setTimeout(function(){
                        self.batchGeocode(addresses);
                    },1000);
                }else{
                    callback();
                }
            });
        },



        geocodeAddress: function(address, callback, resultsMap) {
            var self = this;
            resultsMap = resultsMap || this.map;
            this.geocoder.geocode({'address': address}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
//                    resultsMap.setCenter(results[0].geometry.location);
                    self.bounds.extend(results[0].geometry.location);
                    resultsMap.fitBounds(self.bounds);
                    var marker = new google.maps.Marker({
                        map: resultsMap,
                        position: results[0].geometry.location
                    });

                    if (callback){
                        callback();
                    }
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        },

        handleLocationError: function (browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos);
            infoWindow.setContent(browserHasGeolocation ?
                'Error: The Geolocation service failed.' :
                'Error: Your browser doesn\'t support geolocation.');
        }


    };
    return {GoogleMaps:GoogleMaps,MQMaps: MQMaps};
})(window);

