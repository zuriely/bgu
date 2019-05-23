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
 * This filter provides for teacher sharing students in teams and make groups
 * Submissions in each team just in activity assign
 *
 * @package    filter_teamwork
 * @copyright 2019 onwards - Weizmann institute @author Devlion info@devlion.co
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_filter_teamwork_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018030611) {
        $table = new xmldb_table('teamwork_groups');
        $fieldnew = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'teamworkid');
        $dbman->add_field($table, $fieldnew);
    }

    if ($oldversion < 2018101600) {
        $table = new xmldb_table('teamwork');

        // Launch drop field maxnumber.
        $field = new xmldb_field('maxnumber');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field teamnumbers to be added to teamwork.
        $field = new xmldb_field('teamnumbers', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'studentediting');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field teamusernumbers to be added to teamwork.
        $field = new xmldb_field('teamusernumbers', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'teamnumbers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field teamuserenddate to be added to teamwork.
        $field = new xmldb_field('teamuserenddate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'teamusernumbers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Teamwork savepoint reached.
        upgrade_plugin_savepoint(true, 2018101600, 'filter', 'teamwork');
    }

    if ($oldversion < 2018121200) {
        $table = new xmldb_table('teamwork');

        // Define field teamuserallowenddate to be added to teamwork.
        $field =
                new xmldb_field('teamuserallowenddate', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'teamusernumbers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Teamwork savepoint reached.
        upgrade_plugin_savepoint(true, 2018121200, 'filter', 'teamwork');
    }

    return true;
}
