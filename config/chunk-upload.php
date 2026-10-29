<?php

/**
 * @see https://github.com/pionl/laravel-chunk-upload
 */

declare(strict_types=1);

return [

    /*
     * The storage config
     */

    'storage' => [

        /*
         * Returns the folder name of the chunks. The location is in storage/app/{folder_name}
         */

        'chunks' => 'chunks',
        'disk'   => 'local',
    ],
    'clear' => [

        /*
         * How old chunks we should delete
         */

        'timestamp' => '-3 HOURS',
        'schedule'  => [
            'enabled' => true,
            // Run every hour on the 25th minute.
            'cron' => '25 * * * *',
        ],
    ],
    'chunk' => [
        // Setup for the chunk naming setup to ensure same name upload at same time.
        'name' => [
            'use' => [
                // Should the chunk name use the session id? The uploader must send cookie!
                'session' => true,
                // Instead of session we can use the ip and browser?
                'browser' => false,
            ],
        ],
    ],
    'handlers' => [
        // A list of handlers/providers that will be appended to existing list of handlers.
        'custom' => [],
        // Overrides the list of handlers - use only what you really want.
        'override' => [],
    ],
];
