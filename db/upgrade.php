<?php

function xmldb_enrol_approvalenrol_upgrade($oldversion){
    global $DB;
    $plugininfo=core_plugin_manager::instance()->get_plugin_info('enrol_approvalenrol');
    $newversion = $plugininfo->versiondisk??0;
    $dbman = $DB->get_manager();
    if($oldversion < $newversion){
        // Define table enrol_approvalenrol to be created.
        $table = new xmldb_table('user_enrol_approval_requests');

        // Adding fields to table enrol_approvalenrol.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,null);
        $table->add_field('approval_status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        // Adding keys to table enrol_approvalenrol.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, ['email'], 'user', ['email']);

        // Conditionally launch create table for enrol_approvalenrol.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, $newversion,'enrol','approvalenrol');
    }
    if($oldversion < $newversion){
        $table = new xmldb_table('user_enrol_approval_requests');
        $field = new xmldb_field('approval_status');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $dbman->add_field($table, $field );
        upgrade_plugin_savepoint(true, $newversion, 'enrol', 'approvalenrol');
    }
    return true;
}