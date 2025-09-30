<?php

function xmldb_enrol_approvalenrol_install()
{
    global $DB;
    if (!$DB->record_exists('role', ['shortname' => 'approver'])) {
        $roleid = create_role(get_string('approverrole', 'enrol_approvalenrol'), 'approver', get_string('approverrole:desc', 'enrol_approvalenrol'));
        if($roleid){
            assign_capability(
            'enrol/approvalenrol:manage',
            CAP_ALLOW,
            $roleid,
            (\context_course::instance())->id,
            true
        );
        }else{
            throw new \moodle_exception('noapproverrole', 'enrol_approvalenrol');
        }
    }
}
