<?php

return [
    /**
     * Rate limiting configuration for different rate limiters you might use.
     * Add additional blocks for other limiters.
     *
     * All attempts are 'per minute'.
     */
    'rate_limiting' => [
        // Based on IPv4
        'ipv4' => [
            'max_attempts' => 20,
        ],

        // Based on Bearer token
        'api_token' => [
            'max_attempts' => 20,
        ],
    ],
];
