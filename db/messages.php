<?php

$messageproviders = [
    'approval_notifications' => [
        'defaults' => [
            'pop-up' => MESSAGE_PERMITTED,
            'email' => MESSAGE_PERMITTED,
        ],
        'capability' => 'enrol/approvalenrol:config',
    ]
];