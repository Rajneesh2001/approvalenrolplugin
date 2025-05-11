<?php

$observers = [
    [
        'eventname' => '\enrol_approvalenrol\event\approval_requests_updated',
        'callback' => '\enrol_approvalenrol\event\observer::process_request',
    ]
];
