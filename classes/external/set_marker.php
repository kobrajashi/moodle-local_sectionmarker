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
 * External function: set or update a module (activity) marker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sectionmarker\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web service to set (insert or update) a course-module colour marker.
 */
class set_marker extends external_api {
    /**
     * Describes the parameters of the function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'ID of the course'),
            'cmid'     => new external_value(PARAM_INT, 'ID of the course module (mdl_course_modules.id)'),
            'marker'   => new external_value(PARAM_ALPHA, 'Marker type: info, note, or ok'),
        ]);
    }

    /**
     * Sets the marker of a course module.
     *
     * @param int    $courseid
     * @param int    $cmid
     * @param string $marker
     * @return array
     */
    public static function execute(int $courseid, int $cmid, string $marker): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'cmid'     => $cmid,
            'marker'   => $marker,
        ]);

        $allowedmarkers = ['info', 'note', 'ok'];
        if (!in_array($params['marker'], $allowedmarkers, true)) {
            throw new \invalid_parameter_exception(
                'Invalid marker value "' . $params['marker'] . '". Allowed: ' . implode(', ', $allowedmarkers)
            );
        }

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('local/sectionmarker:manage', $context);

        // Validate that the module belongs to this course.
        $DB->get_record('course_modules', ['id' => $params['cmid'], 'course' => $params['courseid']], '*', MUST_EXIST);

        $existing = $DB->get_record('local_sectionmarker', [
            'courseid' => $params['courseid'],
            'cmid'     => $params['cmid'],
        ]);

        $now = time();

        if ($existing) {
            $existing->marker       = $params['marker'];
            $existing->timemodified = $now;
            $existing->usermodified = $USER->id;
            $DB->update_record('local_sectionmarker', $existing);
        } else {
            $record               = new \stdClass();
            $record->courseid     = $params['courseid'];
            $record->cmid         = $params['cmid'];
            $record->marker       = $params['marker'];
            $record->timemodified = $now;
            $record->usermodified = $USER->id;
            $DB->insert_record('local_sectionmarker', $record);
        }

        return [
            'success' => true,
            'message' => get_string('marker_set', 'local_sectionmarker'),
        ];
    }

    /**
     * Describes the return value of the function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'message' => new external_value(PARAM_TEXT, 'Human-readable result message'),
        ]);
    }
}
