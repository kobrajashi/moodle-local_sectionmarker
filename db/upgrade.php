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
 * Upgrade steps for local_sectionmarker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always true.
 */
function xmldb_local_sectionmarker_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025051901) {
        $table = new xmldb_table('local_sectionmarker');

        // Existing records reference sectionid values that cannot be mapped to
        // cmid values, so delete them before altering the schema.
        $DB->delete_records('local_sectionmarker');

        // ------------------------------------------------------------------
        // 1. Drop unique index that includes sectionid.
        // ------------------------------------------------------------------
        $index = new xmldb_index('courseid_sectionid', XMLDB_INDEX_UNIQUE, ['courseid', 'sectionid']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // ------------------------------------------------------------------
        // 2. Drop the FK key on sectionid (required before dropping the column
        //    in MySQL/InnoDB where FK constraints are enforced at DB level).
        // ------------------------------------------------------------------
        if ($dbman->field_exists($table, new xmldb_field('sectionid'))) {
            try {
                $key = new xmldb_key('fk_sectionid', XMLDB_KEY_FOREIGN, ['sectionid'], 'course_sections', ['id']);
                $dbman->drop_key($table, $key);
            } catch (\Exception $e) {
                // The FK constraint may not exist at DB level in all engines; continue.
                debugging('local_sectionmarker upgrade: could not drop fk_sectionid key: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }

            // ------------------------------------------------------------------
            // 3. Drop the sectionid column.
            // ------------------------------------------------------------------
            $dbman->drop_field($table, new xmldb_field('sectionid'));
        }

        // ------------------------------------------------------------------
        // 4. Add the cmid column (after courseid).
        // ------------------------------------------------------------------
        if (!$dbman->field_exists($table, new xmldb_field('cmid'))) {
            $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'courseid');
            $dbman->add_field($table, $field);
        }

        // ------------------------------------------------------------------
        // 5. Add the FK key on cmid.
        // ------------------------------------------------------------------
        $key = new xmldb_key('fk_cmid', XMLDB_KEY_FOREIGN, ['cmid'], 'course_modules', ['id']);
        $dbman->add_key($table, $key);

        // ------------------------------------------------------------------
        // 6. Add new unique index on (courseid, cmid).
        // ------------------------------------------------------------------
        $index = new xmldb_index('courseid_cmid', XMLDB_INDEX_UNIQUE, ['courseid', 'cmid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2025051901, 'local', 'sectionmarker');
    }

    if ($oldversion < 2026102700) {
        // Rename the German marker value 'achtung' to 'note'.
        $DB->set_field('local_sectionmarker', 'marker', 'note', ['marker' => 'achtung']);

        upgrade_plugin_savepoint(true, 2026102700, 'local', 'sectionmarker');
    }

    return true;
}
