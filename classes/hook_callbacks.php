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
 * Hook callback implementations for local_sectionmarker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sectionmarker;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook callback handler class.
 */
class hook_callbacks {

    /**
     * Hook: \core\hook\output\before_standard_head_html_generation
     *
     * Loads all module markers for the current course and passes them to the
     * AMD module so it can colour the correct <li id="module-{cmid}"> elements.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(
        \core\hook\output\before_standard_head_html_generation $hook
    ): void {
        global $PAGE, $DB;

        if (!in_array($PAGE->pagelayout, ['course', 'incourse'], true)) {
            return;
        }

        if (!$PAGE->context || $PAGE->context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $courseid = (int) $PAGE->course->id;
        if ($courseid <= 1) {
            return;
        }

        // Load all module markers for this course, keyed by cmid.
        $records = $DB->get_records('local_sectionmarker', ['courseid' => $courseid], '', 'cmid, marker');

        $markerdata = [];
        foreach ($records as $rec) {
            $markerdata[(string) $rec->cmid] = $rec->marker;
        }

        $PAGE->requires->js_call_amd(
            'local_sectionmarker/sectionmarker',
            'init',
            [$courseid, $markerdata, $PAGE->user_is_editing()]
        );
    }
}
