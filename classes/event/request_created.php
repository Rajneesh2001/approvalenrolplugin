<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event class for the creation of an enrolment request.
 *
 * This event is triggered when a user creates a new course enrolment request 
 * in the approvalenrol plugin. It primarily serves to log this action within 
 * Moodle's event API, enabling tracking and any other event-driven processing.
 *
 * @package    enrol_approvalenrol
 * @copyright  2025 Rajneesh Sajwan
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_approvalenrol\event;

class request_created extends \core\event\base{

    /**
     * Initialises the event.
     * @return void
     */
    protected function init(){
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol_approvalenrol_requests';
    }

    /**
     * Returns the localized event name.
     *
     * @return string The name of the event.
     */
    public static function get_name(){
        return get_string('eventrequestcreated', 'enrol_approvalenrol');
    }

     /**
     * Returns a description of what happened.
     *
     * @return string A detailed description of the event.
     */
    public function get_description(){
        return "The User with '{$this->userid}' created a request for course enrolment with requestid '{$this->objectid}' for course id '{$this->other["courseid"]}'";
    }

    /**
     * Returns the URL relevant to the event.
     *
     * @return \moodle_url A URL to view the all the pending requests that is created when request is created
     */
    public function get_url(){
        return new \moodle_url('/enrol/approvalenrol/approval.php', ['courseid'=>$this->courseid, 'status' => 2]);
    }

    // Add Validation
    protected function validate_data(){
        parent::validate_data();
        if(!isset($this->other['courseid'])){
            throw new \moodle_exception('The \'courseid\' value must be set in other.');
        }
    }

}