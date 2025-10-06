<?php

$observers = [
    [
        'eventname' => '\enrol_approvalenrol\event\approval_requests_updated',
        'callback' => '\enrol_approvalenrol\event\observer::process_request',
    ],
    [
        'eventname' => '\core\event\config_log_created',
        'callback' => '\enrol_approvalenrol\event\observer::save_config_data'
    ]
];
