<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_approvalenrol_uninstall(){

    global $DB;

    try {
        // Get database manager instance
        $dbman = $DB->get_manager();

        $table1 = new xmldb_table('enrol_approvalenrol_requests');
        $table2 = new xmldb_table('enrol_approvalenrol_approvers');

        // Drop tables if they exist.
        if ($dbman->table_exists($table1)){
            $dbman->drop_table($table1);
        }

        if ($dbman->table_exists($table2)) {
            $dbman->drop_table($table2);
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

