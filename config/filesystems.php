<?php

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => 'storage',
        ],
        'www_dir' => [
            'driver' => 'local',
            'root' => '/var/www/interviewWorkspace'
        ],
        'public_html' => [
            'driver' => 'local',
            'root' => '/var/www/html'
        ],
    ],
];