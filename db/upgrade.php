<?php

function xmldb_enrol_approvalenrol_upgrade($oldversion){
   global $DB;
   if($oldversion < 2024042201.30){
      $manager = $DB->get_manager();

      $table = new xmldb_table('user_enrol_approval_requests');
   
      $field = new xmldb_field('userid',XMLDB_TYPE_INTEGER, '10',null,XMLDB_NOTNULL, false,'0','0'); 
   
      $manager->add_field($table,$field);
   
      upgrade_plugin_savepoint(true, $oldversion, 'enrol', 'approvalenrol');
   }

   return true;
}