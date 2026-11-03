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
 * Backup support for local_sectionmarker.
 *
 * Markers belong to a single course module, so the marker is attached to that
 * module's own backup structure. That way it survives a course backup, a course
 * import and the restore of a single activity alike, and the new cmid is known
 * at restore time without any mapping of our own.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds the colour marker of a course module to its backup.
 */
class backup_local_sectionmarker_plugin extends backup_local_plugin {
    /**
     * Attach the marker to the structure of every backed-up course module.
     *
     * @return backup_plugin_element
     */
    protected function define_module_plugin_structure() {
        $plugin = $this->get_plugin_element();

        $wrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($wrapper);

        $markers = new backup_nested_element('sectionmarkers');
        $wrapper->add_child($markers);

        // The user who last edited the marker is deliberately left out: it is
        // not needed to restore the colour, and it would create a dependency on
        // the backup containing users.
        $marker = new backup_nested_element('sectionmarker', ['id'], [
            'marker', 'timemodified',
        ]);
        $markers->add_child($marker);

        $marker->set_source_table('local_sectionmarker', [
            'cmid' => backup::VAR_MODID,
        ]);

        return $plugin;
    }
}
