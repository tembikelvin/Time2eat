/**
 * Universal Map Utilities for Time2Eat
 * Provides reusable map functions across all dashboards
 * Automatically uses Google Maps or Leaflet based on global MAP_CONFIG
 */

const LeafletMapUtils = {
    // Default map center (Bamenda, Cameroon)
    defaultCenter: [5.9631, 10.1591],
    defaultZoom: 13,

    // Check if using Google Maps
    isGoogleMaps() {
        return window.MAP_CONFIG && window.MAP_CONFIG.provider === 'google' && window.MAP_CONFIG.isGoogleMapsEnabled;
    },

    // Marker icon templates
    icons: {
        restaurant: {
            html: '<div style="background: #FF6B35; width: 36px; height: 36px; border-radius: 50%; border: 4px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        },
        customer: {
            html: '<div style="background: #10B981; width: 36px; height: 36px; border-radius: 50%; border: 4px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        },
        rider: {
            html: '<div class="pulse-marker" style="background: #3B82F6; width: 36px; height: 36px; border-radius: 50%; border: 4px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm-1.06 13.54L7.4 12l1.41-1.41 2.12 2.12 4.24-4.24 1.41 1.41-5.64 5.66z"/></svg></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        },
        delivery: {
            html: '<div style="background: #FF6B35; width: 40px; height: 50px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 4px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;"><div style="transform: rotate(45deg);"><svg width="20" height="20" fill="white" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg></div></div>',
            iconSize: [40, 50],
            iconAnchor: [20, 50]
        }
    },

    /**
     * Initialize a map (Google Maps or Leaflet based on config)
     */
    initMap(containerId, options = {}) {
        const {
            center = this.defaultCenter,
            zoom = this.defaultZoom,
            zoomControl = true,
            attributionControl = true,
            maxZoom = 19,
            minZoom = 3
        } = options;

        // Use Google Maps if configured
        if (this.isGoogleMaps() && typeof google !== 'undefined' && google.maps) {
            const map = new google.maps.Map(document.getElementById(containerId), {
                center: { lat: center[0], lng: center[1] },
                zoom: zoom,
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true,
                zoomControl: zoomControl
            });

            // Add a flag to identify this as Google Maps
            map._isGoogleMaps = true;

            return map;
        }

        // Fallback to Leaflet
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return null;
        }

        const map = L.map(containerId, {
            center,
            zoom,
            zoomControl,
            attributionControl,
            maxZoom,
            minZoom
        });

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Invalidate size after a short delay
        setTimeout(() => map.invalidateSize(), 250);

        // Add a flag to identify this as Leaflet
        map._isGoogleMaps = false;

        return map;
    },

    /**
     * Create a custom marker icon
     */
    createIcon(type = 'customer') {
        const iconConfig = this.icons[type] || this.icons.customer;
        return L.divIcon({
            className: 'custom-marker',
            html: iconConfig.html,
            iconSize: iconConfig.iconSize,
            iconAnchor: iconConfig.iconAnchor,
            popupAnchor: [0, -iconConfig.iconAnchor[1]]
        });
    },

    /**
     * Add a marker to the map (works with both Google Maps and Leaflet)
     */
    addMarker(map, lat, lng, options = {}) {
        const {
            type = 'customer',
            draggable = false,
            popup = null,
            onClick = null
        } = options;

        // Google Maps
        if (map._isGoogleMaps) {
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                draggable: draggable,
                title: popup || ''
            });

            // Add Leaflet-compatible methods for cross-compatibility
            marker.setLatLng = function(latLng) {
                if (Array.isArray(latLng)) {
                    this.setPosition({ lat: latLng[0], lng: latLng[1] });
                } else {
                    this.setPosition(latLng);
                }
            };

            marker.getLatLng = function() {
                const pos = this.getPosition();
                return { lat: pos.lat(), lng: pos.lng() };
            };

            if (popup) {
                const infoWindow = new google.maps.InfoWindow({
                    content: popup
                });
                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });
            }

            if (onClick) {
                marker.addListener('click', onClick);
            }

            return marker;
        }

        // Leaflet
        const icon = this.createIcon(type);
        const marker = L.marker([lat, lng], {
            icon,
            draggable
        }).addTo(map);

        if (popup) {
            marker.bindPopup(popup);
        }

        if (onClick) {
            marker.on('click', onClick);
        }

        if (draggable) {
            marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                if (options.onDragEnd) {
                    options.onDragEnd(pos.lat, pos.lng);
                }
            });
        }

        return marker;
    },

    /**
     * Update marker position
     */
    updateMarker(marker, lat, lng, animate = true) {
        if (animate) {
            marker.setLatLng([lat, lng]);
        } else {
            marker.setLatLng([lat, lng]);
        }
    },

    /**
     * Draw a route line between points (works with both providers)
     */
    drawRoute(map, points, options = {}) {
        const {
            color = '#3B82F6',
            weight = 4,
            opacity = 0.7,
            dashArray = null
        } = options;

        // Google Maps
        if (map._isGoogleMaps) {
            const path = points.map(p => ({ lat: p[0], lng: p[1] }));
            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: color,
                strokeOpacity: opacity,
                strokeWeight: weight
            });
            polyline.setMap(map);

            // Add removeLayer compatibility method
            polyline.removeLayer = function() {
                this.setMap(null);
            };

            return polyline;
        }

        // Leaflet
        const polyline = L.polyline(points, {
            color,
            weight,
            opacity,
            dashArray
        }).addTo(map);

        return polyline;
    },

    /**
     * Remove a layer/marker from the map (works with both providers)
     */
    removeLayer(map, layer) {
        if (!layer) return;

        if (map._isGoogleMaps) {
            // Google Maps
            if (layer.setMap) {
                layer.setMap(null);
            } else if (layer.removeLayer) {
                layer.removeLayer();
            }
        } else {
            // Leaflet
            if (map.removeLayer) {
                map.removeLayer(layer);
            }
        }
    },

    /**
     * Fit map bounds to show all markers (works with both providers)
     */
    fitBounds(map, markers, options = {}) {
        const {
            padding = [50, 50],
            maxZoom = 16
        } = options;

        if (markers.length === 0) return;

        // Google Maps
        if (map._isGoogleMaps) {
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => {
                if (marker.getPosition) {
                    bounds.extend(marker.getPosition());
                }
            });
            map.fitBounds(bounds);
            return;
        }

        // Leaflet
        const bounds = L.latLngBounds(markers.map(m => m.getLatLng()));
        map.fitBounds(bounds, { padding, maxZoom });
    },

    /**
     * Add a circle overlay (e.g., delivery radius)
     */
    addCircle(map, lat, lng, radius, options = {}) {
        const {
            color = '#3B82F6',
            fillColor = '#3B82F6',
            fillOpacity = 0.1,
            weight = 2
        } = options;

        const circle = L.circle([lat, lng], {
            radius,
            color,
            fillColor,
            fillOpacity,
            weight
        }).addTo(map);

        return circle;
    },

    /**
     * Add search control (geocoding)
     */
    addSearchControl(map, options = {}) {
        const {
            position = 'topright',
            placeholder = 'Search for address...',
            onResult = null
        } = options;

        if (typeof L.Control.Geocoder === 'undefined') {
            console.warn('Leaflet Geocoder plugin not loaded');
            return null;
        }

        const geocoder = L.Control.Geocoder.nominatim();
        const searchControl = L.Control.geocoder({
            geocoder,
            defaultMarkGeocode: false,
            placeholder,
            position
        }).on('markgeocode', (e) => {
            const latlng = e.geocode.center;
            map.setView(latlng, 16);
            if (onResult) {
                onResult(latlng.lat, latlng.lng, e.geocode.name);
            }
        }).addTo(map);

        return searchControl;
    },

    /**
     * Add locate control (find my location)
     */
    addLocateControl(map, options = {}) {
        const {
            position = 'topright',
            onLocationFound = null
        } = options;

        const locateControl = L.control({ position });
        
        locateControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            div.innerHTML = `
                <a href="#" title="Find my location" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: white; text-decoration: none; color: #333; font-size: 18px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </a>
            `;
            
            div.onclick = function(e) {
                e.preventDefault();
                map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
            };
            
            return div;
        };

        locateControl.addTo(map);

        if (onLocationFound) {
            map.on('locationfound', (e) => {
                onLocationFound(e.latlng.lat, e.latlng.lng, e.accuracy);
            });
        }

        map.on('locationerror', (e) => {
            console.error('Location error:', e.message);
            alert('Unable to get your location: ' + e.message);
        });

        return locateControl;
    },

    /**
     * Reverse geocode (get address from coordinates)
     */
    async reverseGeocode(lat, lng) {
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
            );
            const data = await response.json();
            return data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        } catch (error) {
            console.error('Reverse geocoding error:', error);
            return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    },

    /**
     * Calculate distance between two points (in meters)
     */
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lng2 - lng1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    },

    /**
     * Format distance for display
     */
    formatDistance(meters) {
        if (meters < 1000) {
            return `${Math.round(meters)}m`;
        }
        return `${(meters / 1000).toFixed(1)}km`;
    },

    /**
     * Add CSS for pulse animation
     */
    addPulseAnimation() {
        if (document.getElementById('leaflet-pulse-style')) return;

        const style = document.createElement('style');
        style.id = 'leaflet-pulse-style';
        style.textContent = `
            .pulse-marker {
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
                }
                70% {
                    box-shadow: 0 0 0 15px rgba(59, 130, 246, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
                }
            }
        `;
        document.head.appendChild(style);
    }
};

// Initialize pulse animation on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        LeafletMapUtils.addPulseAnimation();
    });
} else {
    LeafletMapUtils.addPulseAnimation();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LeafletMapUtils;
}

