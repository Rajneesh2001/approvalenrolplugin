<?php
$functions = array('enrol_approvalenrol_updaterequests' => array(
    'classname' => '\\enrol_approvalenrol\\external\\updaterequests',
    'methodname' => 'execute',
    'classpath' => '/enrol/approvalenrol/classes/external/updaterequests.php',
    'description' => 'Manage Approval Requests',
    'type' => 'write',
    'ajax' => true,
    'services' => [
        // A standard Moodle install includes one default service:
        // - MOODLE_OFFICIAL_MOBILE_SERVICE.
        // Specifying this service means that your function will be available for
        // use in the Moodle Mobile App.
        MOODLE_OFFICIAL_MOBILE_SERVICE,
    ]
));