<?php

function xmldb_approvalenrol_install(){
    $roleid = create_role('Approver', 'approver', 'This Allow user to approve the request of users who want to enrol specific course');
    assign_capability(
        'enrol/approvalenrol:manage',
        CAP_ALLOW,
        $roleid,
        (\context_system::instance())->id,
        true
    );
}