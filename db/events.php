<?php

$observers = [
    [
        'eventname' => '\enrol_approvalenrol\event\approval_requests_updated',
        'callback' => '\enrol_approvalenrol\event\observer::process_request',
    ],
    [
        'eventname' => '\core\event\config_log_created',
        'callback' => '\enrol_approvalenrol\event\observer::save_configdata'
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\enrol_approvalenrol\event\observer::user_unenrolled'
    ],
];
