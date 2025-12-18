/**
 * High-Accuracy GPS Tracking System
 * Provides maximum accuracy location tracking with fallback mechanisms
 */

class HighAccuracyGPS {
    constructor(options = {}) {
        this.options = {
            enableHighAccuracy: true,
            timeout: 30000, // 30 seconds
            maximumAge: 0, // Always get fresh location
            desiredAccuracy: 10, // meters
            maxAttempts: 5,
            updateInterval: 3000, // 3 seconds for continuous tracking
            ...options
        };

        this.currentLocation = null;
        this.watchId = null;
        this.isTracking = false;
        this.callbacks = {
            onSuccess: [],
            onError: [],
            onUpdate: []
        };
        this.attemptCount = 0;
        this.locationHistory = [];
        this.maxHistorySize = 10;
    }

    /**
     * Check if geolocation is supported
     */
    isSupported() {
        return 'geolocation' in navigator;
    }

    /**
     * Request location permission
     */
    async requestPermission() {
        if (!this.isSupported()) {
            throw new Error('Geolocation is not supported by this browser');
        }

        try {
            // Try to get permission by requesting location once
            const position = await this.getCurrentPositionPromise({
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: Infinity
            });
            return true;
        } catch (error) {
            if (error.code === 1) {
                throw new Error('Location permission denied');
            }
            throw error;
        }
    }

    /**
     * Get current position as a Promise
     */
    getCurrentPositionPromise(options = {}) {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                ...this.options,
                ...options
            });
        });
    }

    /**
     * Get single high-accuracy location
     */
    async getLocation() {
        if (!this.isSupported()) {
            throw new Error('Geolocation is not supported');
        }

        this.attemptCount = 0;
        let bestPosition = null;
        let bestAccuracy = Infinity;

        // Try multiple times to get the best accuracy
        while (this.attemptCount < this.options.maxAttempts) {
            try {
                const position = await this.getCurrentPositionPromise();
                const accuracy = position.coords.accuracy;

                console.log(`GPS Attempt ${this.attemptCount + 1}: Accuracy ${accuracy.toFixed(2)}m`);

                // Keep the most accurate position
                if (accuracy < bestAccuracy) {
                    bestPosition = position;
                    bestAccuracy = accuracy;
                }

                // If we achieved desired accuracy, stop trying
                if (accuracy <= this.options.desiredAccuracy) {
                    console.log(`✓ Achieved desired accuracy: ${accuracy.toFixed(2)}m`);
                    break;
                }

                this.attemptCount++;

                // Wait a bit before next attempt
                if (this.attemptCount < this.options.maxAttempts) {
                    await this.sleep(1000);
                }
            } catch (error) {
                console.error(`GPS Attempt ${this.attemptCount + 1} failed:`, error.message);
                this.attemptCount++;

                if (this.attemptCount >= this.options.maxAttempts) {
                    throw error;
                }

                await this.sleep(1000);
            }
        }

        if (!bestPosition) {
            throw new Error('Failed to get location after multiple attempts');
        }

        const locationData = this.extractLocationData(bestPosition);
        this.currentLocation = locationData;
        this.addToHistory(locationData);

        return locationData;
    }

    /**
     * Start continuous location tracking
     */
    startTracking() {
        if (!this.isSupported()) {
            throw new Error('Geolocation is not supported');
        }

        if (this.isTracking) {
            console.warn('Tracking is already active');
            return;
        }

        this.isTracking = true;
        console.log('🎯 Starting high-accuracy GPS tracking...');

        // Use watchPosition for continuous tracking
        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.handlePositionUpdate(position),
            (error) => this.handlePositionError(error),
            {
                enableHighAccuracy: true,
                timeout: this.options.timeout,
                maximumAge: 0 // Always get fresh location
            }
        );
    }

    /**
     * Stop continuous tracking
     */
    stopTracking() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        this.isTracking = false;
        console.log('⏹️ GPS tracking stopped');
    }

    /**
     * Handle position update
     */
    handlePositionUpdate(position) {
        const locationData = this.extractLocationData(position);
        
        // Only update if accuracy is acceptable or better than current
        if (!this.currentLocation || 
            locationData.accuracy <= this.options.desiredAccuracy * 2 ||
            locationData.accuracy < this.currentLocation.accuracy) {
            
            this.currentLocation = locationData;
            this.addToHistory(locationData);

            console.log(`📍 Location updated: ${locationData.latitude.toFixed(6)}, ${locationData.longitude.toFixed(6)} (±${locationData.accuracy.toFixed(1)}m)`);

            // Trigger update callbacks
            this.callbacks.onUpdate.forEach(callback => {
                try {
                    callback(locationData);
                } catch (error) {
                    console.error('Error in update callback:', error);
                }
            });

            // Trigger success callbacks
            this.callbacks.onSuccess.forEach(callback => {
                try {
                    callback(locationData);
                } catch (error) {
                    console.error('Error in success callback:', error);
                }
            });
        }
    }

    /**
     * Handle position error
     */
    handlePositionError(error) {
        console.error('GPS Error:', error.message);

        const errorData = {
            code: error.code,
            message: error.message,
            timestamp: new Date()
        };

        this.callbacks.onError.forEach(callback => {
            try {
                callback(errorData);
            } catch (err) {
                console.error('Error in error callback:', err);
            }
        });
    }

    /**
     * Extract location data from position object
     */
    extractLocationData(position) {
        return {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitudeAccuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            timestamp: position.timestamp,
            formattedTime: new Date(position.timestamp).toLocaleTimeString()
        };
    }

    /**
     * Add location to history
     */
    addToHistory(location) {
        this.locationHistory.unshift(location);
        if (this.locationHistory.length > this.maxHistorySize) {
            this.locationHistory.pop();
        }
    }

    /**
     * Get average location from history (smoothing)
     */
    getSmoothedLocation() {
        if (this.locationHistory.length === 0) {
            return null;
        }

        const sum = this.locationHistory.reduce((acc, loc) => ({
            latitude: acc.latitude + loc.latitude,
            longitude: acc.longitude + loc.longitude,
            accuracy: acc.accuracy + loc.accuracy
        }), { latitude: 0, longitude: 0, accuracy: 0 });

        const count = this.locationHistory.length;

        return {
            latitude: sum.latitude / count,
            longitude: sum.longitude / count,
            accuracy: sum.accuracy / count,
            timestamp: Date.now(),
            smoothed: true
        };
    }

    /**
     * Register callbacks
     */
    onSuccess(callback) {
        this.callbacks.onSuccess.push(callback);
        return this;
    }

    onError(callback) {
        this.callbacks.onError.push(callback);
        return this;
    }

    onUpdate(callback) {
        this.callbacks.onUpdate.push(callback);
        return this;
    }

    /**
     * Get current location (cached)
     */
    getCurrentLocation() {
        return this.currentLocation;
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    static calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // Distance in meters
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HighAccuracyGPS;
}

