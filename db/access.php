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
    ],
    'enrol/approvalenrol:viewapprovaldashboard' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ]
    ],
    'enrol/approvalenrol:managecourseapprover' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        ]
    ] 
];