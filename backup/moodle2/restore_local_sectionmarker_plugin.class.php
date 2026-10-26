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
 * Restore support for local_sectionmarker.
 *
 * Counterpart to backup_local_sectionmarker_plugin: reads the marker back from
 * the module structure and writes it against the newly created cmid.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restores the colour marker of a course module.
 */
class restore_local_sectionmarker_plugin extends restore_local_plugin {

    /**
     * Declare the path we expect inside each module's backup.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure() {
        return [
            new restore_path_element(
                'local_sectionmarker_marker',
                $this->get_pathfor('/sectionmarkers/sectionmarker')
            ),
        ];
    }

    /**
     * Write one marker for the module currently being restored.
     *
     * @param array|object $data The backed-up marker record.
     */
    public function process_local_sectionmarker_marker($data) {
        global $DB, $USER;

        $data = (object) $data;

        // Only the three known values are written; anything else is dropped
        // rather than ending up in the database unchecked.
        if (!in_array($data->marker, ['info', 'achtung', 'ok'], true)) {
            return;
        }

        $cmid = $this->task->get_moduleid();
        $courseid = $this->task->get_courseid();

        if (empty($cmid) || empty($courseid)) {
            return;
        }

        $record = (object) [
            'courseid'     => $courseid,
            'cmid'         => $cmid,
            'marker'       => $data->marker,
            'timemodified' => !empty($data->timemodified) ? (int) $data->timemodified : time(),
            'usermodified' => $USER->id,
        ];

        // A restore into an existing course can meet a module that already
        // carries a marker. The unique index on (courseid, cmid) would reject a
        // second row, so update in that case.
        $existing = $DB->get_record('local_sectionmarker', [
            'courseid' => $courseid,
            'cmid'     => $cmid,
        ], 'id');

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_sectionmarker', $record);
        } else {
            $DB->insert_record('local_sectionmarker', $record);
        }
    }
}
