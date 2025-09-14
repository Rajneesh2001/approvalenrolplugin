<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_approvalenrol_uninstall(){

    global $DB;

    try {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('enrol_approval_requests');

        if($dbman->table_exists($table)){
            $dbman->drop_table($table);
        }

        //Remove all config variables for enrol_approvalenrol plugin
        unset_all_config_for_plugin('enrol_approvalenrol');


        //Remove Roles created for the enrol_approvalenrol plugin
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'approver']);

        if($roleid){
             delete_role($roleid);
        }

        return true;

    } catch(Exception $e){
        debugging('Error During enrol_approvalenrol uninstall: '.$e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }


}

