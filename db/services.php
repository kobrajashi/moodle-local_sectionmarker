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
 * External function (webservice) definitions for local_sectionmarker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// External functions available as AJAX-callable web services.
$functions = [
    'local_sectionmarker_set_marker' => [
        'classname'   => \local_sectionmarker\external\set_marker::class,
        'description' => 'Set or update a colour marker for a course section.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/sectionmarker:manage',
        'loginrequired' => true,
    ],
    'local_sectionmarker_remove_marker' => [
        'classname'   => \local_sectionmarker\external\remove_marker::class,
        'description' => 'Remove the colour marker from a course section.',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'local/sectionmarker:manage',
        'loginrequired' => true,
    ],
];
