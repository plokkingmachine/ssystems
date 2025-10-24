<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_apifetcher_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    // Example upgrade step:
    if ($oldversion < 2025102000) {
        // Add new fields / changes created with XMLDB editor should be implemented here,
        // then call upgrade_mod_savepoint(true, 2025102000, 'modname');
        upgrade_mod_savepoint(true, 2025102000, 'mod_apifetcher');
    }

    return true;
}
