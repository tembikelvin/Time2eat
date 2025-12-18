/**
 * Unified Map Provider System
 * Supports both Google Maps and Leaflet (OpenStreetMap)
 * Automatically switches based on admin settings
 */

class MapProvider {
    constructor(options = {}) {
        this.provider = options.provider || 'leaflet'; // 'google' or 'leaflet'
        this.apiKey = options.apiKey || '';
        this.container = options.container;
        this.center = options.center || [5.9631, 10.1591]; // Bamenda, Cameroon
        this.zoom = options.zoom || 13;
        this.map = null;
        this.markers = {};
        this.polylines = {};
        this.circles = {};

        // High-accuracy GPS tracker
        this.gpsTracker = null;
        if (options.enableGPS) {
            this.gpsTracker = new HighAccuracyGPS(options.gpsOptions || {});
        }
    }

    /**
     * Initialize the map based on provider
     */
    async init() {
        if (this.provider === 'google' && this.apiKey) {
            await this.initGoogleMaps();
        } else {
            await this.initLeaflet();
        }
        return this.map;
    }

    /**
     * Initialize Google Maps
     */
    async initGoogleMaps() {
        // Load Google Maps API if not already loaded
        if (typeof google === 'undefined' || !google.maps) {
            await this.loadGoogleMapsAPI();
        }

        this.map = new google.maps.Map(document.getElementById(this.container), {
            center: { lat: this.center[0], lng: this.center[1] },
            zoom: this.zoom,
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });

        console.log('✓ Google Maps initialized');
    }

    /**
     * Initialize Leaflet (OpenStreetMap)
     */
    async initLeaflet() {
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }

        this.map = L.map(this.container, {
            center: this.center,
            zoom: this.zoom,
            zoomControl: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
            minZoom: 3
        }).addTo(this.map);

        console.log('✓ Leaflet (OpenStreetMap) initialized');
    }

    /**
     * Load Google Maps API dynamically
     */
    loadGoogleMapsAPI() {
        return new Promise((resolve, reject) => {
            if (typeof google !== 'undefined' && google.maps) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=geometry,places`;
            script.async = true;
            script.defer = true;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Add a marker to the map
     */
    addMarker(id, lat, lng, options = {}) {
        if (this.provider === 'google') {
            return this.addGoogleMarker(id, lat, lng, options);
        } else {
            return this.addLeafletMarker(id, lat, lng, options);
        }
    }

    /**
     * Add Google Maps marker
     */
    addGoogleMarker(id, lat, lng, options = {}) {
        const markerOptions = {
            position: { lat, lng },
            map: this.map,
            title: options.title || '',
            draggable: options.draggable || false
        };

        // Custom icon
        if (options.icon) {
            markerOptions.icon = this.getGoogleIcon(options.icon);
        }

        const marker = new google.maps.Marker(markerOptions);

        // Add popup
        if (options.popup) {
            const infoWindow = new google.maps.InfoWindow({
                content: options.popup
            });
            marker.addListener('click', () => {
                infoWindow.open(this.map, marker);
            });
        }

        // Add click handler
        if (options.onClick) {
            marker.addListener('click', options.onClick);
        }

        // Add drag end handler
        if (options.onDragEnd) {
            marker.addListener('dragend', (e) => {
                options.onDragEnd(e.latLng.lat(), e.latLng.lng());
            });
        }

        this.markers[id] = marker;
        return marker;
    }

    /**
     * Add Leaflet marker
     */
    addLeafletMarker(id, lat, lng, options = {}) {
        const markerOptions = {
            draggable: options.draggable || false
        };

        // Custom icon
        if (options.icon) {
            markerOptions.icon = this.getLeafletIcon(options.icon);
        }

        const marker = L.marker([lat, lng], markerOptions).addTo(this.map);

        // Add popup
        if (options.popup) {
            marker.bindPopup(options.popup);
        }

        // Add click handler
        if (options.onClick) {
            marker.on('click', options.onClick);
        }

        // Add drag end handler
        if (options.onDragEnd) {
            marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                options.onDragEnd(pos.lat, pos.lng);
            });
        }

        this.markers[id] = marker;
        return marker;
    }

    /**
     * Handle map click event
     */
    onMapClick(callback) {
        if (this.provider === 'google') {
            this.map.addListener('click', (e) => {
                callback(e.latLng.lat(), e.latLng.lng());
            });
        } else {
            this.map.on('click', (e) => {
                callback(e.latlng.lat, e.latlng.lng);
            });
        }
    }

    /**
     * Get Google Maps icon
     */
    getGoogleIcon(type) {
        const icons = {
            restaurant: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="16" fill="#F97316" stroke="white" stroke-width="4"/>
                        <path d="M18 12v12M15 15h6M15 21h6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(36, 36),
                anchor: new google.maps.Point(18, 18)
            },
            customer: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="16" fill="#10B981" stroke="white" stroke-width="4"/>
                        <circle cx="18" cy="18" r="4" fill="white"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(36, 36),
                anchor: new google.maps.Point(18, 18)
            },
            rider: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="16" fill="#3B82F6" stroke="white" stroke-width="4"/>
                        <path d="M18 12l-6 6h4v6h4v-6h4l-6-6z" fill="white"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(36, 36),
                anchor: new google.maps.Point(18, 18)
            },
            'delivery-location': {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="40" height="50" viewBox="0 0 40 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 0C8.954 0 0 8.954 0 20c0 14 20 30 20 30s20-16 20-30C40 8.954 31.046 0 20 0z" fill="#FF6B35"/>
                        <circle cx="20" cy="20" r="8" fill="white"/>
                        <path d="M20 14v6m0 0v6m0-6h6m-6 0h-6" stroke="#FF6B35" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(40, 50),
                anchor: new google.maps.Point(20, 50)
            }
        };
        return icons[type] || icons.customer;
    }

    /**
     * Get Leaflet icon (use existing LeafletMapUtils)
     */
    getLeafletIcon(type) {
        if (type === 'delivery-location') {
            return L.divIcon({
                className: 'custom-delivery-marker',
                html: `
                    <div style="position: relative;">
                        <svg width="40" height="50" viewBox="0 0 40 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 0C8.954 0 0 8.954 0 20c0 14 20 30 20 30s20-16 20-30C40 8.954 31.046 0 20 0z" fill="#FF6B35"/>
                            <circle cx="20" cy="20" r="8" fill="white"/>
                            <path d="M20 14v6m0 0v6m0-6h6m-6 0h-6" stroke="#FF6B35" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                `,
                iconSize: [40, 50],
                iconAnchor: [20, 50],
                popupAnchor: [0, -50]
            });
        }

        if (typeof LeafletMapUtils !== 'undefined') {
            return LeafletMapUtils.createIcon(type);
        }
        return L.icon({
            iconUrl: '/images/markers/default-marker.png',
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });
    }

    /**
     * Update marker position
     */
    updateMarker(id, lat, lng) {
        if (!this.markers[id]) return;

        if (this.provider === 'google') {
            this.markers[id].setPosition({ lat, lng });
        } else {
            this.markers[id].setLatLng([lat, lng]);
        }
    }

    /**
     * Remove marker
     */
    removeMarker(id) {
        if (!this.markers[id]) return;

        if (this.provider === 'google') {
            this.markers[id].setMap(null);
        } else {
            this.markers[id].remove();
        }

        delete this.markers[id];
    }

    /**
     * Draw polyline (alias for drawRoute but auto-generates ID if not provided)
     */
    drawPolyline(coordinates, options = {}) {
        const id = options.id || 'polyline-' + Date.now();
        return this.drawRoute(id, coordinates, options);
    }

    /**
     * Draw route/polyline
     */
    drawRoute(id, coordinates, options = {}) {
        if (this.provider === 'google') {
            return this.drawGooglePolyline(id, coordinates, options);
        } else {
            return this.drawLeafletPolyline(id, coordinates, options);
        }
    }

    /**
     * Draw Google Maps polyline
     */
    drawGooglePolyline(id, coordinates, options = {}) {
        const path = coordinates.map(coord => ({ lat: coord[0], lng: coord[1] }));

        const polyline = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: options.color || '#3B82F6',
            strokeOpacity: options.opacity || 0.8,
            strokeWeight: options.weight || 4,
            map: this.map
        });

        this.polylines[id] = polyline;
        return polyline;
    }

    /**
     * Draw Leaflet polyline
     */
    drawLeafletPolyline(id, coordinates, options = {}) {
        const polyline = L.polyline(coordinates, {
            color: options.color || '#3B82F6',
            weight: options.weight || 4,
            opacity: options.opacity || 0.8,
            dashArray: options.dashArray || null
        }).addTo(this.map);

        this.polylines[id] = polyline;
        return polyline;
    }

    /**
     * Remove polyline
     */
    removePolyline(id) {
        if (!this.polylines[id]) return;

        if (this.provider === 'google') {
            this.polylines[id].setMap(null);
        } else {
            this.polylines[id].remove();
        }

        delete this.polylines[id];
    }

    /**
     * Fit bounds to show all markers or coordinates
     */
    fitBounds(items) {
        if (!items || items.length === 0) return;

        // Check if items are coordinates (arrays) or markers (objects)
        const isCoordinates = Array.isArray(items[0]) || (items[0] && typeof items[0].lat === 'number');

        if (this.provider === 'google') {
            const bounds = new google.maps.LatLngBounds();
            items.forEach(item => {
                if (isCoordinates) {
                    // Handle [lat, lng] or {lat, lng}
                    const lat = Array.isArray(item) ? item[0] : item.lat;
                    const lng = Array.isArray(item) ? item[1] : item.lng;
                    bounds.extend({ lat, lng });
                } else {
                    // Handle Marker object
                    bounds.extend(item.getPosition());
                }
            });
            this.map.fitBounds(bounds);
        } else {
            if (isCoordinates) {
                // Handle coordinates for Leaflet
                // Leaflet fitBounds takes an array of [lat, lng]
                const bounds = items.map(item => {
                    return Array.isArray(item) ? item : [item.lat, item.lng];
                });
                this.map.fitBounds(bounds, { padding: [20, 20] });
            } else {
                // Handle markers
                const group = L.featureGroup(items);
                this.map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    }

    /**
     * Set map center
     */
    setCenter(lat, lng, zoom) {
        if (this.provider === 'google') {
            this.map.setCenter({ lat, lng });
            if (zoom) this.map.setZoom(zoom);
        } else {
            this.map.setView([lat, lng], zoom || this.map.getZoom());
        }
    }

    /**
     * Get current center
     */
    getCenter() {
        if (this.provider === 'google') {
            const center = this.map.getCenter();
            return [center.lat(), center.lng()];
        } else {
            const center = this.map.getCenter();
            return [center.lat, center.lng];
        }
    }

    /**
     * Start high-accuracy GPS tracking
     */
    startGPSTracking(callback) {
        if (!this.gpsTracker) {
            this.gpsTracker = new HighAccuracyGPS({
                desiredAccuracy: 10,
                maxAttempts: 5
            });
        }

        this.gpsTracker.onUpdate(callback);
        this.gpsTracker.startTracking();
    }

    /**
     * Stop GPS tracking
     */
    stopGPSTracking() {
        if (this.gpsTracker) {
            this.gpsTracker.stopTracking();
        }
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MapProvider;
}

