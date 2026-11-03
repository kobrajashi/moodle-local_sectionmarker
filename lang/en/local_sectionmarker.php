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
 * English language strings for local_sectionmarker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core plugin strings.
$string['pluginname'] = 'Section marker';

// Action-menu items.
$string['mark_info']    = 'Mark: Info';
$string['mark_note'] = 'Mark: Note';
$string['mark_ok']      = 'Mark: Successful';
$string['remove_mark']  = 'Remove marking';

// Feedback messages returned by web services.
$string['marker_set']     = 'Section marking has been set.';
$string['marker_removed'] = 'Section marking has been removed.';

// Capability string.
$string['sectionmarker:manage'] = 'Manage section markers';

// Privacy API strings.
$string['privacy:metadata']                             = 'The section marker plugin stores which sections have been marked and who last changed them.';
$string['privacy:metadata:local_sectionmarker']         = 'Section marker records';
$string['privacy:metadata:local_sectionmarker:courseid']     = 'The ID of the course containing the section.';
$string['privacy:metadata:local_sectionmarker:cmid']         = 'The ID of the marked course module.';
$string['privacy:metadata:local_sectionmarker:marker']       = 'The marker type applied (info, note, ok).';
$string['privacy:metadata:local_sectionmarker:timemodified'] = 'The time the marker was last changed.';
$string['privacy:metadata:local_sectionmarker:usermodified'] = 'The ID of the user who last changed the marker.';
