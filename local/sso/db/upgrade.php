<?php  //$Id: upgrade.php,v 1.2 2007/08/08 22:36:54 stronk7 Exp $

// This file keeps track of upgrades to
// the local_sso module

function xmldb_local_sso_upgrade($oldversion=0) {

    global $CFG, $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    if ($oldversion < 2012032100) {
        
        $table = new xmldb_table('sso_sites');
        $dbman->rename_table($table, 'local_sso_sites');
        
        $table = new xmldb_table('sso_keys');
        $dbman->rename_table($table, 'local_sso_keys');

        // upgrade_mod_savepoint(true, 2012032100, 'local_sso');
    }

    return true;
}

