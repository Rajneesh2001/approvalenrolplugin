<?php

$capabilities= [
    'enrol/approvalenrol:enrol'=>[
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ],
    'enrol/approvalenrol:unenrol' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'=>[
            'manager' => CAP_ALLOW
        ]
    ],
    'enrol/approvalenrol:config' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'=>[
            'manager' => CAP_ALLOW
        ]
    ],
    'enrol/approvalenrol:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ]
];