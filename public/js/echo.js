/*!
 * Laravel Echo for Chat Module
 */

// Pusher.js included from CDN
// <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

class Echo {
    constructor(options) {
        this.options = options;
        console.log('Creating Pusher instance with options:', options);

        // Make sure auth options are properly formatted
        const authOptions = {
            headers: options.auth?.headers || {}
        };

        console.log('Auth options:', authOptions);

        this.connector = new Pusher(options.key, {
            wsHost: options.wsHost,
            wsPort: options.wsPort,
            wssPort: options.wssPort,
            forceTLS: options.forceTLS,
            enabledTransports: options.enabledTransports || ['ws', 'wss'],
            disableStats: options.disableStats || true,
            authEndpoint: '/broadcasting/auth',
            auth: authOptions,
            authTransport: 'ajax',
            cluster: options.cluster || 'mt1',
            activity_timeout: 120000,  // Increase to 2 minutes
            pong_timeout: 30000,      // How long to wait for pong response
            unavailable_timeout: 60000, // How long to wait before giving up
            enabledTransports: ['ws', 'wss', 'xhr_streaming', 'xhr_polling'], // Add fallback transports
            autoReconnect: true,
            maxReconnectionAttempts: 10,
            maxReconnectGap: 60000 // 1 minute max between reconnection attempts
        });

        // Log connection events
        this.connector.connection.bind('connected', () => {
            console.log('Connected to Pusher with socket ID:', this.connector.connection.socket_id);
        });

        this.connector.connection.bind('error', (err) => {
            console.error('Pusher connection error:', err);
            // Attempt to reconnect on errors
            setTimeout(() => {
                console.log('Attempting to reconnect after error...');
                this.connector.connect();
            }, 5000);
        });

        // Add disconnection event handler
        this.connector.connection.bind('disconnected', () => {
            console.log('Disconnected from websocket. Attempting to reconnect...');
            setTimeout(() => {
                this.connector.connect();
            }, 3000);
        });

        // Add state change event handler
        this.connector.connection.bind('state_change', (states) => {
            console.log(`Connection state changed from ${states.previous} to ${states.current}`);
            if (states.current === 'unavailable' || states.current === 'failed') {
                console.log('Connection failed or unavailable, attempting to reconnect...');
                setTimeout(() => {
                    this.connector.connect();
                }, 5000);
            }
        });

        this.startKeepAlive();
        this.channels = {};
    }

    /**
     * Start keep-alive mechanism to prevent disconnections
     */
    startKeepAlive() {
        // Don't create multiple keep-alive intervals
        if (this.keepAliveInterval) {
            clearInterval(this.keepAliveInterval);
        }

        // Check connection every 45 seconds
        this.keepAliveInterval = setInterval(() => {
            if (this.connector && this.connector.connection) {
                // If not connected, try to connect
                if (this.connector.connection.state !== 'connected') {
                    console.log('Connection not active, attempting to reconnect...');
                    this.connector.connect();
                } else {
                    console.log('Connection is healthy');

                    // Send a ping to the server if the connection is idle
                    const idleTime = Date.now() - (this.connector.connection.lastActivity || 0);
                    if (idleTime > 60000) { // If idle for more than 1 minute
                        console.log('Connection idle for too long, sending heartbeat...');
                        // Instead of trying to send an explicit heartbeat, just trigger a harmless activity
                        // that will reset the connection's lastActivity timestamp
                        try {
                            // Use a safer approach - just query the connection state
                            const state = this.connector.connection.state;
                            console.log('Current connection state:', state);
                            // This should update lastActivity without sending anything invalid
                            this.connector.connection.timeline.info({ action: 'heartbeat' });
                        } catch (e) {
                            console.warn('Error in heartbeat:', e);
                            // If we can't send a heartbeat, don't try to reconnect automatically
                            // as this might cause more errors
                        }
                    }
                }
            }
        }, 45000);
    }

    /**
     * Clean up resources when Echo is no longer needed
     */
    disconnect() {
        if (this.keepAliveInterval) {
            clearInterval(this.keepAliveInterval);
            this.keepAliveInterval = null;
        }

        if (this.connector) {
            this.connector.disconnect();
        }

        this.channels = {};
    }

    /**
     * Listen for an event on a channel.
     */
    listen(channel, event, callback) {
        return this.channel(channel).listen(event, callback);
    }

    /**
     * Get a channel instance by name.
     */
    channel(channel) {
        if (!this.channels[channel]) {
            console.log(`Subscribing to channel: ${channel}`);
            this.channels[channel] = this.connector.subscribe(channel);
        }

        return this.channels[channel];
    }

    /**
     * Get a private channel instance by name.
     */
    private(channel) {
        const privateChannel = 'private-' + channel;
        console.log(`Subscribing to private channel: ${privateChannel}`);

        if (!this.channels[privateChannel]) {
            this.channels[privateChannel] = this.connector.subscribe(privateChannel);

            // Debug subscription events
            this.channels[privateChannel].bind('pusher:subscription_succeeded', () => {
                console.log(`Successfully subscribed to ${privateChannel}`);
            });

            this.channels[privateChannel].bind('pusher:subscription_error', (error) => {
                console.error(`Error subscribing to ${privateChannel}:`, error);

                // Let's try a direct subscription as a workaround
                console.log('Attempting direct authentication as a workaround...');
                this.authenticateChannel(privateChannel);
            });
        }

        return {
            listen: (event, callback) => {
                console.log(`Binding to event: ${event} on channel ${privateChannel}`);
                this.channels[privateChannel].bind(event, (data) => {
                    console.log(`Received event: ${event} with data:`, data);
                    callback(data);
                });

                return this;
            }
        };
    }

    /**
     * Attempt to authenticate a channel directly
     */
    authenticateChannel(channel) {
        const socketId = this.connector.connection.socket_id;
        if (!socketId) {
            console.error('No socket ID available for authentication');
            return;
        }

        // Create a form data object
        const formData = new FormData();
        formData.append('socket_id', socketId);
        formData.append('channel_name', channel);

        // Perform the auth request
        fetch('/broadcasting/auth', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.options.auth?.headers['X-CSRF-TOKEN'],
                'Authorization': this.options.auth?.headers['Authorization'],
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Authentication successful:', data);

                // No need to do anything else as Pusher will handle this automatically
                // This is just to help debug
            })
            .catch(error => {
                console.error('Authentication error:', error);
            });
    }

    /**
     * Get a presence channel instance by name.
     */
    join(channel) {
        const presenceChannel = 'presence-' + channel;

        if (!this.channels[presenceChannel]) {
            this.channels[presenceChannel] = this.connector.subscribe(presenceChannel);
        }

        return this.channels[presenceChannel];
    }

    /**
     * Leave a channel.
     */
    leave(channel) {
        if (this.channels[channel]) {
            this.connector.unsubscribe(channel);
            delete this.channels[channel];
        }
    }
}
