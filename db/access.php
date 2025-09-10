<?php

$capabilities= [
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
    ],
];