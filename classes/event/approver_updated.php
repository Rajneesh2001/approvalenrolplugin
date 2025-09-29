<?php

namespace enrol_approvalenrol\event;

class approver_updated extends \core\event\base {

    /**
     * Initialize the event
     * @return void
     */
    public function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol_approvalenrol_approvers';
    }

    /**
     * Return localize event name
     * @return string event name
     */
    public static function get_name() {
        return get_string('approverupdated', 'enrol_approvalenrol');
    }

    /**
     * Returns localized description of what happened
     * @return string event description
     */
    public function get_description() {
        $descriptionobj = new \stdClass();
        $descriptionobj->userid = $this->userid;
        $descriptionobj->courseid = $this->courseid;

        return get_string('approverupdated:desc', 'enrol_approvalenrol', $descriptionobj);
    }

    /**
     * Returns the URL relevant to the approver updated event.
     *
     * @return \moodle_url
     */
    public function get_url() {
         return new \moodle_url('/enrol/approvalenrol/select_approver.php', ['courseid' => $this->courseid]);
    }

    /**
     * Return objectid mapping for backup/restore.
     *
     * @return array
     */
    public static function get_objectid_mapping()
    {
        return ['db' => 'enrol_approvalenrol_approvers', 'restore' => 'enrol_approvalenrol_approvers'];
    }

    /**
     * Define Mapping for additional data fields
     */
    public static function get_other_mapping()
    {
        return [];
    }


}

