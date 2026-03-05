<?php

namespace enrol_approvalenrol\task;
require_once($CFG->dirroot . '/enrol/approvalenrol/locallib.php');

class expire_pending_requests extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('taskexpirepending', 'enrol_approvalenrol');
    }


    public function execute() {
        \enrol_approvalenrol\local\approvalenrolrequests::bulk_update_pending_requests(\enrol_approvalenrol\approval_enrol::REQUEST_EXPIRED);
        mtrace('Task successfully completed');
    }

}