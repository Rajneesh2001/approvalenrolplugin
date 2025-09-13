<?php

function xmldb_enrol_approvalenrol_install()
{
    global $DB;
    if (!$DB->record_exists('role', ['shortname' => 'approver'])) {
        $roleid = create_role(get_string('approverole', 'enrol_approvalenrol'), 'approver', get_string('approverrole:desc', 'enrol_approvalenrol'));
        if($roleid){
            assign_capability(
            'enrol/approvalenrol:manage',
            CAP_ALLOW,
            $roleid,
            (\context_system::instance())->id,
            true
        );
        }
    }
}
