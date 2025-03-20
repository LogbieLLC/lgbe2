/**
 * Performance Metrics Collection Module
 * 
 * This module collects web performance metrics using the web-vitals library
 * and sends them to the server for analysis.
 */

import { onLCP, onCLS, onINP, onFCP } from 'web-vitals';

const PerformanceMetrics = {
    /**
     * Configuration options
     */
    config: {
        endpoint: '/api/metrics',
        samplingRate: 10, // Percentage of page loads to measure (1-100)
        cookieName: 'performance_opt_out',
        batchSize: 3, // Number of metrics to batch before sending
        debug: false, // Enable console logging
    },

    /**
     * Collected metrics waiting to be sent
     */
    metrics: [],

    /**
     * Initialize the performance metrics collection
     * @param {Object} options - Configuration options
     */
    init(options = {}) {
        // Merge options with defaults
        this.config = { ...this.config, ...options };
        
        // Check if user has opted out
        if (!this.hasUserConsent()) {
            this.log('User has opted out of performance measurement');
            return;
        }
        
        // Apply sampling rate - only measure a percentage of page loads
        if (!this.shouldMeasure()) {
            this.log('Skipping measurement due to sampling rate');
            return;
        }
        
        this.log('Initializing performance metrics collection');
        
        // Measure Core Web Vitals
        onLCP(this.handleMetric.bind(this));
        onCLS(this.handleMetric.bind(this));
        onINP(this.handleMetric.bind(this));
        onFCP(this.handleMetric.bind(this));
        
        // Measure onload time using Navigation Timing API
        window.addEventListener('load', () => {
            // Wait a bit to ensure the load event has completed
            setTimeout(() => {
                const navigationEntry = performance.getEntriesByType('navigation')[0];
                if (navigationEntry) {
                    this.handleMetric({
                        name: 'LOAD',
                        value: navigationEntry.loadEventEnd - navigationEntry.startTime,
                        id: window.location.pathname
                    });
                }
            }, 0);
        });
        
        // Send metrics when page is about to unload
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden' && this.metrics.length > 0) {
                this.sendMetrics();
            }
        });
        
        // Send metrics when page is about to unload (fallback)
        window.addEventListener('pagehide', () => {
            if (this.metrics.length > 0) {
                this.sendMetrics();
            }
        });
    },
    
    /**
     * Handle a metric measurement
     * @param {Object} metric - The measured metric
     */
    handleMetric(metric) {
        this.log(`Measured ${metric.name}: ${metric.value}`);
        
        // Add to batch of metrics
        this.metrics.push({
            name: metric.name,
            value: metric.value,
            path: window.location.pathname,
            timestamp: Date.now()
        });
        
        // Send metrics if we've reached the batch size or if this is the load metric
        if (this.metrics.length >= this.config.batchSize || metric.name === 'LOAD') {
            this.sendMetrics();
        }
    },
    
    /**
     * Send collected metrics to the server
     */
    sendMetrics() {
        if (this.metrics.length === 0) {
            return;
        }
        
        this.log(`Sending ${this.metrics.length} metrics to server`);
        
        // Prepare the payload with metrics and context
        const payload = {
            metrics: [...this.metrics],
            context: this.getContextData()
        };
        
        // Clear the metrics array
        this.metrics = [];
        
        // Use Beacon API for reliability during page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            const success = navigator.sendBeacon(this.config.endpoint, blob);
            
            if (success) {
                this.log('Metrics sent successfully via Beacon API');
                return;
            }
            
            this.log('Beacon API failed, falling back to fetch');
        }
        
        // Fallback to fetch with keepalive
        fetch(this.config.endpoint, {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: { 'Content-Type': 'application/json' },
            keepalive: true
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            this.log('Metrics sent successfully via fetch');
        })
        .catch(error => {
            this.log(`Error sending metrics: ${error.message}`);
        });
    },
    
    /**
     * Get contextual data about the user's environment
     * @returns {Object} Context data
     */
    getContextData() {
        const context = {
            userAgent: navigator.userAgent,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            isAuthenticated: this.isUserAuthenticated(),
            timestamp: Date.now()
        };
        
        // Add connection information if available
        if (navigator.connection) {
            context.connection = {
                type: navigator.connection.type,
                effectiveType: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt
            };
        }
        
        return context;
    },
    
    /**
     * Check if the user has consented to performance measurement
     * @returns {boolean} True if the user has consented
     */
    hasUserConsent() {
        // Check for opt-out cookie
        return !this.getCookie(this.config.cookieName);
    },
    
    /**
     * Allow users to opt out of performance measurement
     */
    optOut() {
        // Set opt-out cookie for 1 year
        const oneYear = 365 * 24 * 60 * 60 * 1000;
        const expires = new Date(Date.now() + oneYear).toUTCString();
        document.cookie = `${this.config.cookieName}=1; expires=${expires}; path=/; SameSite=Lax`;
        
        this.log('User opted out of performance measurement');
    },
    
    /**
     * Allow users to opt back in to performance measurement
     */
    optIn() {
        // Remove opt-out cookie
        document.cookie = `${this.config.cookieName}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax`;
        
        this.log('User opted in to performance measurement');
    },
    
    /**
     * Check if the current page load should be measured based on sampling rate
     * @returns {boolean} True if this page load should be measured
     */
    shouldMeasure() {
        // If sampling rate is 100%, always measure
        if (this.config.samplingRate >= 100) {
            return true;
        }
        
        // Generate a random number between 0-100
        const random = Math.floor(Math.random() * 100);
        
        // Measure if the random number is less than the sampling rate
        return random < this.config.samplingRate;
    },
    
    /**
     * Check if the user is authenticated
     * @returns {boolean} True if the user is authenticated
     */
    isUserAuthenticated() {
        // This is a simple example - replace with your actual authentication check
        // For example, you might check for a specific cookie or a global variable
        return document.body.classList.contains('user-authenticated') || 
               !!document.querySelector('meta[name="user-authenticated"]');
    },
    
    /**
     * Get a cookie value by name
     * @param {string} name - Cookie name
     * @returns {string|null} Cookie value or null if not found
     */
    getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    },
    
    /**
     * Log a message to the console if debug mode is enabled
     * @param {string} message - Message to log
     */
    log(message) {
        if (this.config.debug) {
            console.log(`[Performance Metrics] ${message}`);
        }
    }
};

export default PerformanceMetrics;
