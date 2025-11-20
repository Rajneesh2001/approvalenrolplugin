<?php

$capabilities= [
    'enrol/approvalenrol:config' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE
    ],
    'enrol/approvalenrol:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE
    ],
    'enrol/approvalenrol:unenrol' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetype' => [
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW
        ]
    ]
];